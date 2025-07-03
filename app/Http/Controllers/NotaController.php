<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\nota;
use App\Models\Recibo;

class NotaController extends Controller
{
    public function obtenerNota($reciboId)
{
    // Validar que el ID del recibo esté presente
    if (!$reciboId) {
        return response()->json(['error' => 'ID de recibo no proporcionado.'], 400);
    }

    // Buscar el recibo por ID y cargar la nota asociada
    $recibo = Recibo::find($reciboId);
    if (!$recibo || !$recibo->nota) {
        return response()->json(['nota' => '']);
    }

    // Retornar la nota en formato JSON
    return response()->json(['nota' => $recibo->nota->nota]);
}
    /**
     * Guarda la nota asociada a un recibo.
     */
    public function guardarNota(Request $request)
{
    // Validar los datos de entrada
    $validated = $request->validate([
        'id' => 'required|exists:recibos,id', // Asegúrate de que el ID del recibo exista
        'nota' => 'required|string'
    ]);

    // Buscar el recibo usando el ID proporcionado
    $recibo = Recibo::find($validated['id']);
    if (!$recibo) {
        return response()->json(['error' => 'Recibo no encontrado.'], 404);
    }

    // Obtener la nota asociada al recibo
    $nota = $recibo->nota; // Usar la relación belongsTo para obtener la nota

    // Si no hay una nota existente, crea una nueva y asígnala al recibo
    if (!$nota) {
        $nota = new Nota();
        $nota->nota = $validated['nota']; // Asignar el contenido de la nota
        $nota->save();

        // Asignar la nueva nota al recibo
        $recibo->id_nota = $nota->id;
        $recibo->save();
    } else {
        // Si ya existe una nota, simplemente actualizar el contenido
        $nota->nota = $validated['nota'];
        $nota->save();
    }

    // Retornar una respuesta exitosa
    return response()->json(['success' => true, 'nota' => $nota->contenido]);
}

}
