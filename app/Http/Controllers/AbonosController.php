<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Auth;
use App\Models\Abono;
use App\Models\VentaAbono;
use App\Models\VentaAbonoDetalle;
use App\Models\NombreConcepto;
use App\Models\Estado;
use App\Models\Clientes;
use App\Models\TipoPago;
use Illuminate\Http\Request;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\CapabilityProfile;

class AbonosController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ventas = VentaAbono::with(['cliente', 'estado', 'abonos', 'detalles.concepto'])
                    ->orderBy('fecha_venta', 'desc')
                    ->paginate(10);
        
        $clientes = Clientes::orderBy('nombre')->get();
        $conceptos = NombreConcepto::orderBy('nombre')->get();
        $tiposPago = TipoPago::orderBy('tipoPago')->get(); // Nueva línea
        
        return view('abonos.abono', compact('ventas', 'clientes', 'conceptos', 'tiposPago'));
    }
    /**
 * Store a newly created cliente.
 */
public function storeCliente(Request $request)
{
    $request->validate([
        'nombre' => 'required|string|max:255',
        'telefono' => 'nullable|string|max:20'
    ]);

    // Verificar si ya existe un cliente con el mismo nombre (ignorando mayúsculas/minúsculas)
    $clienteExistente = Clientes::whereRaw('LOWER(nombre) = ?', [strtolower($request->nombre)])->first();

    if ($clienteExistente) {
        return response()->json([
            'success' => false,
            'message' => 'El cliente ya está registrado, no se puede volver a registrar.'
        ], 409); // Código 409 = conflicto
    }

    // Crear nuevo cliente si no existe
    $cliente = Clientes::create([
        'nombre' => $request->nombre,
        'telefono' => $request->telefono
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Cliente registrado correctamente',
        'cliente' => [
            'id' => $cliente->id,
            'nombre' => $cliente->nombre
        ]
    ]);
}

    /**
     * Funcion Para Buscar Clientes que han realizado algun abono.
     */

    
    /**
     * Store a newly created abono.
     */
    public function store(Request $request)
    {
        $request->validate([
            'venta_id' => 'required|exists:venta_abono,id',
            'monto' => 'required|numeric|min:0.01'
        ]);

        return DB::transaction(function () use ($request) {
            $venta = VentaAbono::findOrFail($request->venta_id);
            
            if ($request->monto > $venta->saldo_restante) {
                return response()->json([
                    'success' => false,
                    'message' => 'El monto excede el saldo restante'
                ], 422);
            }

            $abono = Abono::create([
                'venta_abono_id' => $request->venta_id,
                'monto' => $request->monto,
                'tipo_pago_id' => $request->tipo_pago_id1 
            ]);

            $venta->saldo_restante -= $request->monto;
            
            if ($venta->saldo_restante <= 0) {
                $venta->estado_id = 2; // Estado "Pagado"
            }
            
            $venta->save();

              // Imprimir ticket del abono
            $this->printAbonoTicket($venta, $abono, false);

            return response()->json([
                'success' => true,
                'message' => 'Abono registrado correctamente',
                'saldo_restante' => $venta->saldo_restante
            ]);
        });
    }

    /**
     * Get abonos for a specific venta.
     */
    public function getAbonos($ventaId)
    {
         $abonos = Abono::with(['tipoPago']) // Carga la relación tipoPago
                ->where('venta_abono_id', $ventaId)
                ->orderBy('fecha_abono', 'desc')
                ->get();
                
        return response()->json($abonos);
    }

    /**
     * Remove the specified abono.
     */
    public function destroy(Abono $abono)
    {
        // Verificar si el usuario es admin
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para esta acción'
            ], 403); // Código HTTP 403 Forbidden
        }

        return DB::transaction(function () use ($abono) {
            $venta = $abono->venta;
            $venta->saldo_restante += $abono->monto;
            
            if ($venta->saldo_restante > 0 && $venta->estado_id == 2) {
                $venta->estado_id = 1;
            }
            
            $venta->save();
            $abono->delete();

            return response()->json([
                'success' => true,
                'message' => 'Abono eliminado correctamente'
            ]);
        });
    }

    /**
     * Store a new venta a crédito with multiple productos.
     */
    public function storeVenta(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array',
            'productos.*.nombre' => 'required|string|max:255',
            'productos.*.precio' => 'required|numeric|min:0',
            'productos.*.cantidad' => 'required|integer|min:1',
            'abono_inicial' => 'nullable|numeric|min:0'
        ]);

        return DB::transaction(function () use ($request) {
            $venta = VentaAbono::create([
                'id_cliente' => $request->cliente_id,
                'total' => 0,
                'saldo_restante' => 0,
                'usuario' => Auth::user()->nombre,
                'estado_id' => 1,
                'fecha_venta' => now()
            ]);

            $total = 0;
            
            foreach ($request->productos as $producto) {
                // Crear o actualizar el concepto
                $concepto = NombreConcepto::updateOrCreate(
                    ['nombre' => $producto['nombre']],
                    [
                        'precio' => $producto['precio'],
                        'id_categoria' => 3 // Categoría fija
                    ]
                );
                
                $subtotal = $producto['precio'] * $producto['cantidad'];
                
                VentaAbonoDetalle::create([
                    'venta_abono_id' => $venta->id,
                    'nombreconcepto_id' => $concepto->id,
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio'],
                    'subtotal' => $subtotal
                ]);
                
                $total += $subtotal;
            }

            // Actualizar totales
            $abonoInicial = $request->abono_inicial ?? 0;
            $venta->update([
                'total' => $total,
                'saldo_restante' => $total - $abonoInicial
            ]);
            // Inicializar $abono como null
            $abono = null;
            // Registrar abono inicial si existe
            if ($abonoInicial > 0) {
                $abono= Abono::create([
                    'venta_abono_id' => $venta->id,
                    'tipo_pago_id' => $request->tipo_pago_id, // Nuevo campo
                    'monto' => $abonoInicial
                ]);

                if ($venta->saldo_restante <= 0) {
                    $venta->update(['estado_id' => 2]);
                }
            }

             // Imprimir ticket de la nueva venta
             $this->printAbonoTicket($venta, $abono, true);

            return response()->json([
                'success' => true,
                'message' => 'Venta a crédito registrada correctamente',
                'venta' => $venta
            ]);
        });
    }
    public function buscarClientes(Request $request)
    {
        $termino = $request->input('termino');
        
        $clientes = Clientes::where('nombre', 'LIKE', "%{$termino}%")
                    ->limit(7)
                    ->get(['id', 'nombre']);
        
        return response()->json($clientes);
    }

  
    public function printAbonoTicket($venta, $abono = null, $isNewVenta = false)
    {
        try {
            // Configuración de la impresora (ajusta según tu configuración)
            $nombreImpresora="Bixolon";
            $connector= new WindowsPrintConnector($nombreImpresora);
            $printer= new Printer($connector);
            // O alternativamente para impresión directa:
            // $connector = new FilePrintConnector("LPT1");
            
            $printer = new Printer($connector);
            
            // Encabezado del ticket
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT);
            $printer->text("SOLUCIONES PC\n");
            $printer->selectPrintMode();
            $printer->text("RFC: ZARE881013I12\n");
            $printer->text("Telefono: 6161362976\n");
            $printer->text("--------------------------------\n");
            
            if ($isNewVenta) {
                $printer->setEmphasis(true);
                $printer->text("RECIBO DE ABONO\n");
                $printer->setEmphasis(false);
            } else {
                $printer->setEmphasis(true);
                $printer->text("RECIBO DE ABONO\n");
                $printer->setEmphasis(false);
            }
            
            $printer->text("--------------------------------\n");
            
            // Datos del cliente
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Cliente: " . $venta->cliente->nombre . "\n");
            $printer->text("Fecha: " . now()->format('d/m/Y H:i') . "\n");
            
            $printer->text("--------------------------------\n");
            
            if ($isNewVenta) {
                // Detalles de la venta (nueva venta)
                $printer->text("PRODUCTOS:\n");
                foreach ($venta->detalles as $detalle) {
                    $printer->text($detalle->cantidad . " x " . $detalle->concepto->nombre . " $" . 
                        number_format($detalle->precio_unitario, 2) . "\n");
                    $printer->text("  Subtotal: $" . number_format($detalle->subtotal, 2) . "\n");
                }
                $printer->text("--------------------------------\n");
                $printer->setEmphasis(true);
                $printer->text("TOTAL: $" . number_format($venta->total, 2) . "\n");
                $printer->setEmphasis(false);
                
                if ($abono) {
                    $printer->text("Abono inicial: $" . number_format($abono->monto, 2) . "\n");
                    $printer->text("Saldo restante: $" . number_format($venta->saldo_restante, 2) . "\n");
                }
            } else {
                // Detalles para abonos posteriores
                $printer->text("PRODUCTOS:\n");
                foreach ($venta->detalles as $detalle) {
                    $printer->text($detalle->cantidad . " x " . $detalle->concepto->nombre . " $" . 
                        number_format($detalle->precio_unitario, 2) . "\n");
                    $printer->text("  Subtotal: $" . number_format($detalle->subtotal, 2) . "\n");
                }
                $printer->text("--------------------------------\n");
                $printer->setEmphasis(true);
                $printer->text("TOTAL: $" . number_format($venta->total, 2) . "\n");
                $printer->setEmphasis(false);
                
                if ($abono) {
                    $printer->text("Saldo anterior: $" . number_format($venta->saldo_restante + $abono->monto, 2) . "\n");
                    $printer->text("Monto abonado: $" . number_format($abono->monto, 2) . "\n");
                    
                    if ($venta->saldo_restante <= 0) {
                        $printer->setJustification(Printer::JUSTIFY_CENTER);
                        $printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_EMPHASIZED);
                        $printer->text("¡PAGADO!\n");
                        $printer->selectPrintMode();
                        $printer->setJustification(Printer::JUSTIFY_LEFT);
                    } else {
                        $printer->text("Nuevo saldo: $" . number_format($venta->saldo_restante, 2) . "\n");
                    }
                }
            }

            $printer->text("Tipo Pago: " . $abono->tipoPago->tipoPago . "\n");
            
            
            $printer->text("--------------------------------\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Gracias por su preferencia\n");
            
            // Cortar papel
            $printer->cut();
            
            // Cerrar conexión
            $printer->close();
            
            return true;
        } catch (\Exception $e) {
            \Log::error("Error al imprimir ticket: " . $e->getMessage());
            return false;
        }
    }
}
    

