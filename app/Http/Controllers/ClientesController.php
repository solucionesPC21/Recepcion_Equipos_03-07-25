<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use App\Models\Colonias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class ClientesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clientes = Clientes::paginate(5); // Obtener clientes paginados
        $colonias = Colonias::all(); // Obtener todas las colonias
        return view('clientes.clientes', compact('clientes', 'colonias'));
    }

    /**
     * Show the form for creating a new resource.
     
    public function create()
    {
       
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $campos=[
        'nombre' => 'required|string|regex:/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s.,\-]+$/|max:60|unique:clientes',
        'telefono'=>'required|numeric|digits:10',
        'telefono2'=>'nullable|numeric|digits:10',
        'rfc' => 'nullable|min:13|max:14|regex:/^[A-Za-z0-9]+$/|unique:clientes',
    ];

    $mensaje=[
        'required'=>'El :attribute es requerido',
        'digits'=>'Numero De Telefono Maximo 10 digitos',
        'numeric'=>'Solo se puede ingresar numeros al telefono',
        'nombre.regex'=>'El formato del nombre solo acepta letras y numeros',
        'nombre.unique' => 'Error El cliente con nombre :input ya está registrado en el sistema, Busque al cliente en el cuadro de búsqueda',
        'rfc.regex'=>'El formato del RFC solo acepta números y letras',
        'rfc.unique' => 'RFC ya registrado Ingrese un nuevo RFC',
        'min'=>'El tamaño mínimo de caracteres del RFC es de 13',
        'max'=>'El tamaño máximo de caracteres del RFC es de 14',
    ];  

    $this->validate($request, $campos, $mensaje);

    // Obtener la colonia seleccionada del formulario
    $coloniaId = $request->input('colonia');

    // Obtener los datos del cliente del formulario excepto el token CSRF y la colonia
    $datosClientes = $request->except(['_token', 'colonia']);

    // Buscar la colonia por su ID
    $colonia = Colonias::find($coloniaId);

    // Asignar el ID de la colonia al cliente si se encontró una colonia válida
    $datosClientes['id_colonia'] = optional($colonia)->id;

    // Insertar los datos del cliente en la tabla de clientes
    $cliente = Clientes::create($datosClientes);

    // Almacenar los datos del cliente en la sesión
    $clienteData = [
        'nombre' => $cliente->nombre,
        'telefono' => $cliente->telefono,
        'rfc' => $cliente->rfc,
        'nombre_colonia' => optional($colonia)->colonia ?? 'Sin colonia registrada'
    ];
    $request->session()->put('cliente', $clienteData);

    // Preparar la respuesta JSON para AJAX
    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Cliente agregado con éxito',
            'redirect_url' => route('home.index')
        ]);
    }

    // Redirigir a la vista home.index con un mensaje de éxito y el nombre de la colonia seleccionada
    return redirect()->route('home.index')->with('success', 'Cliente agregado con éxito');
}


 

    /**
     * Display the specified resource.
     
    public function show(Clientes $clientes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $cliente = Clientes::with('colonia')->findOrFail($id);
        return response()->json($cliente);
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    // Obtener los datos originales del cliente
    $cliente = Clientes::findOrFail($id);

    // Definir todas las reglas de validación
    $todasLasReglas = [
        'nombre' => [
        'required',
        'string',
        'max:60',
        Rule::unique('clientes')->ignore($id),
        'regex:/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s.]+$/',
    ],
        'telefono' => 'required|numeric|digits:10',
        'telefono2' => 'nullable|numeric|digits:10',
        'rfc' => 'nullable|min:13|max:14|regex:/^[A-Za-z0-9]+$/|unique:clientes,rfc,' . $id,
    ];

    // Definir los mensajes de error personalizados
    $mensajes = [
        'nombre.required' => 'El campo nombre es obligatorio.',
        'nombre.string' => 'El campo nombre debe ser una cadena de texto.',
        'nombre.regex' => 'El campo nombre solo puede contener letras, espacios y puntos.',
        'nombre.max' => 'El campo nombre no puede tener más de 60 caracteres.',
        'nombre.unique' => 'El nombre de cliente que ingreso ya esta registrado pruebe con uno diferente.',
        'telefono.required' => 'El campo teléfono es obligatorio.',
        'telefono.numeric' => 'El campo teléfono debe ser un número.',
        'telefono.digits' => 'El campo teléfono debe tener 10 dígitos.',
        'telefono2.numeric' => 'El campo teléfono 2 debe ser un número.',
        'telefono2.digits' => 'El campo teléfono 2 debe tener 10 dígitos.',
        'rfc.min' => 'El RFC debe tener al menos 13 caracteres.',
        'rfc.max' => 'El RFC no puede tener más de 14 caracteres.',
        'rfc.regex' => 'El RFC solo puede contener letras y números.',
        'rfc.unique' => 'El RFC ya está registrado.',
    ];

    // Crear un array de reglas de validación solo para los campos modificados
    $camposModificados = [];
    foreach ($todasLasReglas as $campo => $reglas) {
        if ($request->input($campo) !== $cliente->$campo) {
            $camposModificados[$campo] = $reglas;
        }
    }

    // Validar solo los campos modificados
    $validator = Validator::make($request->only(array_keys($camposModificados)), $camposModificados, $mensajes);

    // Si la validación falla, redirigir de nuevo con errores
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // Obtener los datos del formulario excepto los tokens y métodos
    $datosClientes = $request->except('_token', '_method');
    $nombre_colonia = $request->input('colonia');

    // Buscar la colonia en la tabla colonias
    $colonia = Colonias::where('colonia', $nombre_colonia)->first();
    if ($colonia) {
        // Si la colonia existe, actualizar el id_colonia del cliente
        $datosClientes['id_colonia'] = $colonia->id;
    } else {
        // Si la colonia no existe, puedes decidir qué hacer aquí
        // Por ejemplo, podrías insertar la nueva colonia en la tabla colonias
    }

    // Eliminar el campo 'colonia' del array $datosClientes
    unset($datosClientes['colonia']);

    // Actualizar los datos del cliente
    $cliente->update($datosClientes);

    // Redirigir a la página de clientes con un mensaje de éxito
    return redirect('clientes')->with('success', 'Los Datos Del Cliente Se Actualizaron correctamente');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Clientes::destroy($id);
        return redirect('clientes')->with ('success','Cliente Borrado Con Exito');
    } 

    


}
