<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Concepto;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TipoEquipo;
use App\Models\NombreConcepto;
use Carbon\Carbon;
use App\Models\Recibo;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\CapabilityProfile;
 

class ConceptoController extends Controller
{
    /**
     * Display a listing of the resource.
     
    public function index()
    {
     
    }

    /**
     * Show the form for creating a new resource.
     
    public function create()
    {
    }
    */
    
  public function guardar(Request $request)
{
    $request->validate([
        'concepto_id.*' => 'nullable|numeric', // Cambiado a nullable
        'concepto.*' => 'required|string', // Añadido para nuevos conceptos
        'cantidad.*' => 'required|numeric|min:1',
        'precio_unitario.*' => 'required|numeric|min:0.01',
        'recibos_id' => 'required|numeric',
        'tipo_pago' => 'required',
        'categoria.*' => 'required|numeric|in:1,2',
    ]);

    $fechaActual = Carbon::now();
    $nombreUsuario = Auth::user()->nombre;

    // Crear el ticket
    $ticket = new Ticket();
    $ticket->id_recibo = $request->recibos_id;
    $ticket->id_tipoPago = $request->tipo_pago;
    $ticket->fecha = $fechaActual;
    $ticket->usuario = $nombreUsuario;
    $ticket->total = str_replace('$', '', $request->total_general);
    $ticket->save();

    // Procesar cada concepto
    foreach ($request->concepto as $key => $nombre) {
        // Si hay un ID, usar concepto existente
        if (!empty($request->concepto_id[$key])) {
            $nombreConcepto = NombreConcepto::find($request->concepto_id[$key]);
        } else {
            // Crear nuevo concepto
            $nombreConcepto = NombreConcepto::create([
                'nombre' => $nombre,
                'precio' => $request->precio_unitario[$key],
                'id_categoria' => $request->categoria[$key],
                'cantidad' => ($request->categoria[$key] == 2) ? 0 : null,
            ]);
        }

        // Actualizar precio por si cambió
        $nombreConcepto->precio = $request->precio_unitario[$key];
        $nombreConcepto->save();

        // Manejar inventario para productos (categoría 2)
        if ($request->categoria[$key] == 2) {
            if ($nombreConcepto->cantidad < $request->cantidad[$key]) {
                $nombreConcepto->cantidad = 0;
            } else {
                $nombreConcepto->decrement('cantidad', $request->cantidad[$key]);
            }
        }

        // Crear concepto del ticket
        Concepto::create([
            'cantidad' => $request->cantidad[$key],
            'total' => str_replace('$', '', $request->total[$key]),
            'id_ticket' => $ticket->id,
            'id_nombreConcepto' => $nombreConcepto->id,
        ]);
    }

    return $this->imprimir($ticket->id);
}


    public function imprimir($ticketId)
    {
        try {
            $nombreImpresora="Bixolon";
            $connector= new WindowsPrintConnector($nombreImpresora);
            $printer= new Printer($connector);

            $ticket = Ticket::findOrFail($ticketId);
            $concepto = $ticket->concepto;


            $printer->setJustification(Printer::JUSTIFY_CENTER); //JUSTIFICA AL CENTRO EL TEXTO
            $printer->text("Soluciones PC\n");
            $printer->text("RFC: ZARE881013I12\n");
            $printer->text("Telefono: 6161362976\n");

            $printer->text("\n");
            $printer->text("\n");
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("Fecha: ".  date('d/m/Y', strtotime($ticket->fecha)));
            
            $printer->text("\n");
            $printer->text("\n");
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Cliente: " . $ticket->recibo->tipoEquipo[0]->cliente->nombre);
            $printer->text("\nColonia: ");
            if ($ticket->recibo->tipoEquipo[0]->cliente->colonia) {
                $printer->text($ticket->recibo->tipoEquipo[0]->cliente->colonia->colonia);
                $printer->text("\n\n");
            } else {
                $printer->text("\n\n");
            }
            $printer->setJustification(Printer::JUSTIFY_CENTER); //JUSTIFICA AL CENTRO EL TEXTO
            $cantidadText = str_pad("Cant", 2);
            $conceptoText = str_pad("Concepto", 17);
            $precioText = str_pad("Precio", 6);
            $totalText = str_pad("SubTotal", 8);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("$cantidadText  $conceptoText   $precioText  $totalText");
            $printer->text("\n\n");

            foreach ($concepto as $conceptos) {
                // Asegura que cada columna tenga la longitud deseada y esté centrada
                $cantidad = str_pad($conceptos->cantidad, 4, ' ');
                $precio = '$' . str_pad($conceptos->nombreConcepto->precio, 5, ' ');
                $total = '$' . str_pad($conceptos->total, 5, ' ');
                // Divide el concepto en varias líneas si es demasiado largo
                $conceptoTexto = wordwrap($conceptos->nombreConcepto->nombre, 18, "\n", true);
                $lineasConcepto = explode("\n", $conceptoTexto);
            
                // Imprime cada línea del concepto con las columnas correspondientes
                foreach ($lineasConcepto as $indice => $linea) {
                    $cantidadImp = ($indice === 0) ? $cantidad : str_repeat(' ', strlen($cantidad));
                    $precioImp = ($indice === 0) ? $precio : str_repeat(' ', strlen($precio));
                    $totalImp = ($indice === 0) ? $total : str_repeat(' ', strlen($total));
                
                    // Si es la primera línea del concepto, imprime todas las columnas
                    if ($indice === 0) {
                        $conceptoImp = str_pad($linea, 18, ' ');
                        $printer->text("$cantidadImp   $conceptoImp   $precioImp   $totalImp");
                    } else {
                        // Si es una línea subsiguiente del concepto, imprime todas las columnas pero con el mismo relleno que la primera línea
                        $conceptoImp = str_pad($linea, 18, ' ');
                        $printer->text("$cantidadImp   $conceptoImp   $precioImp   $totalImp");
                    }
                }
                $printer->text("\n");
            }

            $printer->text("\n");
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("Total: " . '$' . number_format($ticket->total, 2));

            $printer->text("\n\n");
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Pago: " . $ticket->tipoPago->tipoPago);
            $printer->text("\n\n");
            $printer->text("Cobrado por: " . $ticket->usuario. "\n\n");

            $testStr = "Cliente: " . $ticket->recibo->tipoEquipo[0]->cliente->nombre;

            $printer->qrCode($testStr, Printer::QR_ECLEVEL_L, 16); // Aquí 10 es el tamaño del QR (10 módulos)

            $printer->feed();
            $printer->cut();
            $printer->close();
            /* Print a "Hello world" receipt" */
            
             // Actualizar el estado del recibo al que pertenece el ticket
             
            $recibo = $ticket->recibo;
             
            $ticket->estado_id=3;
            $ticket->save(); 

            if ($recibo) {
                $recibo->id_estado = 3;
               // $recibo->fechaReparacion = Carbon::now()->toDateString(); // Obtiene la fecha actual en formato 'YYYY-MM-DD'
                $recibo->save();
            }

            return redirect('/completados')->with('success', 'La impresión se realizó correctamente.');

        } catch (Exception $e) {
            return redirect('/completados')->with('error', 'No se pudo imprimir en esta impresora: ' . $e->getMessage());
        }
    
    }


    /**
     * 
     * Store a newly created resource in storage.
     
    public function store(Request $request)
    { 
    }


    /**
     * Display the specified resource.
     
    public function show(Concepto $concepto)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     
    public function edit(Concepto $concepto)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     
    public function update(Request $request, Concepto $concepto)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     
    public function destroy(Concepto $concepto)
    {
        //
    }
        */
}
