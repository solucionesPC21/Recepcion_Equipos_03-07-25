<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipo;
use App\Models\Marca;
use App\Models\Colonias;

class HomeController extends Controller
{
    public function index()
    {
        $marcas = Marca::all(); 
        $equipos = Equipo::all();
        $colonias = Colonias::orderBy('colonia', 'asc')->get(); // Obtener todas las colonias ordenadas descendentemente
        return view('home.index', compact('equipos','marcas','colonias'));
    }

}
