<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\TipoEquipo;
use App\Models\Clientes;
use App\Models\Marca;
use App\Models\Equipo;
use App\Models\Recibo;
use App\Models\Estado;
use App\Models\ReciboController;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log; // Asegúrate de tener este import


class RegistroEquipoCliente extends Controller
{
    public function recepcion(Request $request)
    {
         // Validar los datos del formulario
    $validator = Validator::make($request->all(), [
        'tipo_equipo' => 'required|array',
        'tipo_equipo.*' => 'required|integer',
        'marca' => 'required|array',
        'marca.*' => 'required|string',
        'nueva_marca' => 'nullable|array',
        'nueva_marca.*' => 'nullable|string|regex:/^[a-zA-Z0-9\s]*$/',
        'modelo' => 'required|array',
        'modelo.*' => 'required|string|regex:/^[a-zA-Z0-9\s\-]+$/',
        'ns' => 'nullable|array',
        'ns.*' => 'nullable|string|regex:/^[0-9]*$/',
        'falla' => 'required|array',
        'falla.*' => 'required|string|regex:/^[a-zA-Z0-9,\s\-ñÑáéíóúÁÉÍÓÚ.:$]*$/',
        'accesorios' => 'nullable|array',
        'accesorios.*' => 'nullable|string|regex:/^[a-zA-Z0-9,\s\n.\-]*$/',
    ]);

    // Si la validación falla, retornar errores
    if ($validator->fails()) {
        if ($request->ajax()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return back()->withErrors($validator)->withInput();
    }

        try {
            DB::transaction(function () use ($request, &$ultimoReciboId) {
                // Obtener el ID del cliente
                $cliente_id = Clientes::where('nombre', $request->input('nombre_cliente'))->value('id');
    
                // Crear un nuevo objeto Recibo
                $recibo = new Recibo();
                $recibo->id_estado = 1; // Ajustar según la lógica de tu aplicación
                $recibo->save();
                $ultimoReciboId = $recibo->id;
    
                // Iterar sobre los datos del formulario y guardar cada equipo en la base de datos
                foreach ($request->tipo_equipo as $key => $value) {
                    $marcaId = $request->marca[$key];
    
                    // Si se seleccionó "Agregar nueva marca", crear la nueva marca
                    // Si se seleccionó "Agregar nueva marca", verificar y seleccionar la existente si corresponde
                if ($marcaId === 'nueva_marca') {
                    $nuevaMarcaNombre = $request->nueva_marca[$key];

                    // Verificar si la nueva marca ya existe
                    $marcaExistente = Marca::where('marca', $nuevaMarcaNombre)->first();

                    if ($marcaExistente) {
                        $marcaId = $marcaExistente->id; // Usar el ID de la marca existente
                    } else {
                        // Si no existe, crear una nueva marca
                        $nuevaMarca = new Marca();
                        $nuevaMarca->marca = $nuevaMarcaNombre;
                        $nuevaMarca->save();
                        $marcaId = $nuevaMarca->id;
                    }
                }
                    
    
                    // Validar que marcaId no sea null
                    if (empty($marcaId)) {
                        throw new \Exception('Debe seleccionar o proporcionar una marca válida.');
                    }
    
                    // Crear el objeto TipoEquipo y guardarlo en la base de datos
                    $equipo = new TipoEquipo();
                    $equipo->id_cliente = $cliente_id;
                    $equipo->id_equipo = $request->tipo_equipo[$key];
                    $equipo->id_marca = $marcaId;
                    $equipo->modelo = $request->modelo[$key];
                    $equipo->ns = $request->ns[$key] ?? null;
                    $equipo->falla = $request->falla[$key];
                    $equipo->accesorio = $request->accesorios[$key] ?? null;
                    $equipo->usuario = Auth::user()->nombre;
                    $equipo->fecha = now()->toDateString();
                    $equipo->hora = now()->toTimeString();
                    $equipo->id_recibo = $recibo->id;
                    $equipo->save();
                }
            });
    
            // Preparar la respuesta JSON para AJAX
           // Preparar la respuesta JSON para AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Equipos registrados con éxito',
                'redirect_url' => route('pdfImprimir.pdfImprimir', ['id' => $ultimoReciboId])
            ]);
        }

            // Redirigir a la ruta de impresión con el ID del último recibo
            return redirect()->route('pdfImprimir.pdfImprimir', ['id' => $ultimoReciboId]);
        } catch (\Exception $e) {

            Log::error('Error en el método recepcion:', [
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->withErrors(['error_general' => 'Error al registrar el equipo: ' . $e->getMessage()])->withInput();
        }
    }
    
             
    public function estado($id)
    {
        // Buscar el TipoEquipo por su ID
        $recibo = Recibo::find($id);
    
        // Verificar si se encontró el TipoEquipo
        if ($recibo) {
            // Actualizar el campo id_estado a 2
            $recibo->id_estado = 2;

            if ($recibo->id_estado == 2) {
                // Establecer la fecha actual en el campo fechaReparacion
               // Obtiene la fecha actual en formato 'YYYY-MM-DD'
                $recibo->fechaReparacion = Carbon::now()->toDateString(); 
            }
            
            // Guardar los cambios en la base de datos
            $recibo->save();
            
            // Devolver el ID del TipoEquipo actualizado junto con un mensaje de éxito
            return response()->json(['message' => 'Estado Del Recibo actualizado correctamente'], 200);
           
        } else {
            // Si no se encuentra el TipoEquipo, devolver un mensaje de error con información adicional
            return response()->json(['error' => 'No se encontró el TipoEquipo con el ID proporcionado: ' . $id], 404);
        }
        
    }
    //cancelar cancelado
    public function cancelarCancelado($id)
    {
        // Buscar el Recibo por su ID
        $recibo = Recibo::find($id);

        // Verificar si se encontró el Recibo
        if ($recibo) {
            // Actualizar el campo id_estado a 4 (Estado de cancelación, según lo entendido)
            $recibo->id_estado = 1;
            $recibo->save();

            // Devolver una respuesta JSON con un mensaje de éxito
            return response()->json(['message' => 'Estado del recibo actualizado correctamente'], 200);
        } else {
            // Si no se encuentra el Recibo, devolver una respuesta JSON con un mensaje de error
            return response()->json(['error' => 'No se encontró el recibo con el ID proporcionado: ' . $id], 404);
        }
    }

    //cancelar recibo
    public function cancelado($id)
    {
        // Buscar el Recibo por su ID
        $recibo = Recibo::find($id);

        // Verificar si se encontró el Recibo
        if ($recibo) {
            // Actualizar el campo id_estado a 4 (Estado de cancelación, según lo entendido)
            $recibo->id_estado = 4;
            $recibo->save();

            // Devolver una respuesta JSON con un mensaje de éxito
            return response()->json(['message' => 'Estado del recibo actualizado correctamente'], 200);
        } else {
            // Si no se encuentra el Recibo, devolver una respuesta JSON con un mensaje de error
            return response()->json(['error' => 'No se encontró el recibo con el ID proporcionado: ' . $id], 404);
        }
    }
   

}
