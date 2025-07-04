<?php

namespace App\Http\Controllers;

use App\Models\Recibo;
use Illuminate\Http\Request;
use App\Models\TipoEquipo;
use Carbon\Carbon;
use PDF; 

class ReciboController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {       
            $recibos = Recibo::where('id_estado', 1)
                        ->orderBy('created_at', 'desc') // Ordenar por created_at en orden descendente
                        ->paginate(5);
             $totalRecibos = Recibo::where('id_estado', 1)->count(); // Obtenemos el total de tipos de equipo con estado 1
             return view('recibos.recibos', compact('recibos', 'totalRecibos'));
    }

    //recibos cancelados
    public function rechazado(Request $request)
    {
        $recibos = Recibo::where('id_estado', 4)
        ->orderBy('created_at', 'desc') // Ordenar por created_at en orden descendente
        ->paginate(5);
        $totalRecibos = Recibo::where('id_estado', 4)->count(); // Obtenemos el total de tipos de equipo con estado 1
        return view('recibos.recibos-rechazados', compact('recibos', 'totalRecibos'));
    }

    //marcar sin cobrar
    public function marcarSinCobrar($id)
    {
        $recibo = Recibo::find($id);

        if (!$recibo) {
            return response()->json([
                'error' => 'Recibo no encontrado.'
            ], 404);
        }

        $recibo->id_estado = 3;
        $recibo->fechaReparacion = Carbon::now();
        $recibo->save();

        return response()->json([
            'message' => 'El recibo ha sido marcado como completado sin cobrar.'
        ]);
    }

    
   
    public function pdfImprimir($id)
    {
        $recibo = Recibo::find($id);
    
        if (!$recibo) {
            // Manejo de error si el recibo no se encuentra
            abort(404, 'Recibo no encontrado');
        }
    
        // Contar la cantidad de equipos en el recibo
        $tipoEquipos = $recibo->tipoEquipo;
        $cantidadTiposEquipo = $tipoEquipos->count();
    
        $pdf = PDF::loadView('recibos.pdf', ['recibo' => $recibo, 'cantidadTiposEquipo' => $cantidadTiposEquipo])
            ->setPaper(array(0,0,612.00,792.00), 'portrait');
    
        // Ruta donde se guardará el PDF temporalmente
        $rutaPDF = public_path('pdfs/unico.pdf');
    
        try {
            // Guardar el PDF sobreescribiendo el existente
            $pdf->save($rutaPDF);
    
            // Nombre de la impresora específica a la que deseas enviar la impresión
            $nombreImpresora = "EPSON L365 Series";
    
            // Ruta al ejecutable SumatraPDF
            $sumatraPath = 'C:\\Users\\Soluciones\\AppData\\Local\\SumatraPDF\\SumatraPDF.exe';
    
            // Comando para imprimir el PDF
            $comando = "\"$sumatraPath\" -print-to \"$nombreImpresora\" \"$rutaPDF\"";
    
            // Ejecutar el comando y obtener la salida
            $resultado = shell_exec($comando);
    
            // Si hay más de 3 tipos de equipo, imprimir el PDF nuevamente
            if ($cantidadTiposEquipo > 3) {
                $resultado = shell_exec($comando);
            }
    
            // Si el comando de impresión no arroja errores, redirige con un mensaje de éxito
            return redirect('/home')->with('success', 'Equipo registrado con éxito y la impresión se realizó correctamente.');
        } catch (\Exception $e) {
            // En caso de error, redirige con un mensaje de error
            return redirect('/home')->with('error', 'Error al imprimir el recibo: ' . $e->getMessage());
        }
    }
    
    
    
 
    public function pdf($id)
    {
        $recibo = Recibo::find($id);
        $tipoEquipos = $recibo->tipoEquipo;
        $cantidadTiposEquipo = $tipoEquipos->count();
        if (!$recibo) {
            // Manejo de error si el tipo de equipo no se encuentra
            abort(404, 'Tipo de equipo no encontrado');
        }
        
        // Definir el tamaño personalizado de la página (mitad de carta)
        
        $pdf = PDF::loadView('recibos.pdf', ['recibo' => $recibo,'cantidadTiposEquipo'=>$cantidadTiposEquipo])->setPaper(array(0,0,612.00,792.00), 'portrait');
        
        return $pdf->stream();
    }

   


 // return $pdf->download($tipoEquipo->cliente->nombre . '-' . date('d-m-Y', strtotime($tipoEquipo->fecha)) . '.pdf');

   
    /**
     * Show the form for creating a new resource.
     
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     
    public function show(Recibo $recibo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     
    public function edit(Recibo $recibo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     
    public function update(Request $request, Recibo $recibo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     
    public function destroy(Recibo $recibo)
    {
        //
    }
    */
}
