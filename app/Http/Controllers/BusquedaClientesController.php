<?php

namespace App\Http\Controllers;
use App\Models\Clientes;
use App\Models\Colonias;
use App\Models\Equipo; 
use App\Models\Marca;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BusquedaClientesController extends Controller
{
    public function buscar(Request $request){
        $term = $request->input('term');
    
        $clientes = Clientes::where('nombre', 'like', "%{$term}%")->take(12)->get();
        
        
        // Retornar un JSON con las coincidencias
        return response()->json($clientes);
    }

    public function seleccionarCliente(Request $request, $id) {
        $cliente = Clientes::find($id);
        
        if ($cliente) {
            // Obtener el nombre de la colonia del cliente
            $colonia = Colonias::find($cliente->id_colonia);
            $nombre_colonia = ($colonia) ? $colonia->colonia : 'Sin colonia registrada';
            
            // Guardar la información del cliente en la sesión
            $clienteData = [
                'nombre' => $cliente->nombre,
                'telefono' => $cliente->telefono,
                'rfc' => $cliente->rfc,
                'nombre_colonia' => $nombre_colonia,
            ];
            $request->session()->put('cliente', $clienteData);
            
            // Obtener la lista de equipos y marcas
            $equipos = Equipo::all();
            $marcas = Marca::all();
        
            // Redirigir a la vista con la información del cliente, equipos y marcas
            return view('clientes.busqueda', compact('cliente', 'equipos', 'marcas'));
        } else {
            // No se encontró ningún cliente
            $mensajeError = "No se encontraron clientes con el término de búsqueda '{$term}'.";
            return view('clientes.busqueda', compact('mensajeError'));
        }
    }
    
}
 