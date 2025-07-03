<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Colonias;

class BuscarColoniasController extends Controller
{
        public function buscarColonia(Request $request)
        {
            $terminoBusqueda = $request->input('term');
            
            // Limitar el número de resultados a 10
            $colonias = Colonias::where('colonia', 'LIKE', '%' . $terminoBusqueda . '%')
                                ->take(10)
                                ->pluck('colonia');
            
            // Crear un arreglo asociativo con claves 'value' y 'label'
            $datos = $colonias->map(function($colonia) {
                return ['value' => $colonia, 'label' => $colonia];
            });
            
            // Devolver los datos como JSON
            return response()->json($datos);
        }
}
