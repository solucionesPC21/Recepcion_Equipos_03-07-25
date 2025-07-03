<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Clientes;
use Illuminate\Support\Facades\View;

class BuscarCliente extends Controller
{

    public function buscar(Request $request)
    {
            $searchTerm = $request->input('search');

            // Buscar por nombre O teléfono
            $clientes = Clientes::where('nombre', 'like', '%'.$searchTerm.'%')
                                ->orWhere('telefono', 'like', '%'.$searchTerm.'%')
                                ->paginate(5);

            // Cargar la vista parcial con los resultados
            $recibosBodyHtml = View::make('clientes.clientes-body', compact('clientes'))->render();

            // Retornar la respuesta JSON con el HTML generado
            return response()->json(['recibosBodyHtml' => $recibosBodyHtml]);
    }

}
