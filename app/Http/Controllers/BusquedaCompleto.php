<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recibo;
use Illuminate\Support\Facades\View;

class BusquedaCompleto extends Controller
{
    public function buscar(Request $request)
    {
        $searchTerm = $request->input('search');
 
        // Realizar la búsqueda utilizando el nombre del cliente
        $recibos = Recibo::whereHas('tipoEquipo.cliente', function($query) use ($searchTerm) {
            $query->where('nombre', 'like', '%'.$searchTerm.'%');
        })->where('id_estado', 3)
        ->orderBy('fechaReparacion', 'DESC')
        ->paginate(5);
        

        // Cargar la vista parcial y pasar los datos de la búsqueda
        $recibosBodyHtml = View::make('completados.completados-body', compact('recibos'))->render();

        // Retornar la vista parcial como respuesta
        return response()->json([
            'recibosBodyHtml' => $recibosBodyHtml,
            
        ]);
    }

}
