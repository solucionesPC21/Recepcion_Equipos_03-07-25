<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\BusquedaClientesController;
use App\Http\Controllers\BuscarColoniasController;
use App\Http\Controllers\ColoniasController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\RegistroEquipoCliente;
use App\Http\Controllers\ReciboController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ConceptoController;
use App\Http\Controllers\FinalizadoController;
use App\Http\Controllers\BusquedaRecibo;
use App\Http\Controllers\buscarTicket;
use App\Http\Controllers\BusquedaCompleto;
use App\Http\Controllers\BuscarCliente;
use App\Http\Controllers\BuscarUsuario;
use App\Http\Controllers\BusquedaConcepto;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RechazadoController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\BuscarProducto;
use Illuminate\Http\Request;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\buscarClienteVenta;
use App\Http\Controllers\PagosController;
use App\Http\Controllers\TicketPagoController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\AbonosController;
use App\Models\Marca; // Importa el modelo correctamente
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//Route::get('/', function () {
  // return view('auth.login');
//})->name('login');

/*Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home.index');
    }
    return view('auth.login');
})->name('login');*/

Route::get('/', function () {
  return view('auth.login');
})->name('login')->middleware('guest');


Route::post('/register',[RegisterController::class,'register'])->middleware('auth');

Route::get('/register',[RegisterController::class,'show'])->middleware('auth');

Route::get('/login', [LoginController::class, 'show'])->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
// Rutas protegidas por el middleware 'auth'
//Route::middleware('auth')->group(function () {
  //  Route::get('/home', [HomeController::class, 'index'])->name('home.index');
    //Route::post('/login',[LoginController::class,'login']);
//});


Route::get('/home',[HomeController::class,'index'])->name('home.index')->middleware('auth');
Route::get('/logout',[LogoutController::class,'logout'])->middleware('auth');
 
Route::middleware(['auth', 'admin'])->group(function () {
  Route::resource('users', UserController::class)
      ->except(['create', 'show'])
      ->names('users');
});

Route::resource('clientes', ClientesController::class)
->except(['create', 'show'])
->middleware('auth');

Route::resource('colonias', ColoniasController::class)
->except(['create', 'show'])
->middleware('auth');

Route::resource('marcas', MarcaController::class)
->except(['create', 'show'])
->middleware('auth');


Route::resource('tipo_equipos', EquipoController::class)
->except(['create', 'show'])
->middleware('auth');

Route::resource('recibos', ReciboController::class)
->except(['create', 'store', 'show', 'edit', 'update', 'destroy'])
->middleware('auth');

Route::resource('ticket', TicketController::class)
->except(['create', 'store', 'show', 'edit', 'update', 'destroy'])
->middleware('auth');

Route::resource('conceptos', ConceptoController::class)
->except(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])
->middleware('auth');

Route::post('/conceptos', [ConceptoController::class, 'guardar'])->name('conceptos.guardar')->middleware('auth');


// Ruta para buscar clientes en tiempo real
Route::get('/home/buscar', [BusquedaClientesController::class, 'buscar'])->middleware('auth');

// Ruta para seleccionar un cliente específico y cargar su información
Route::get('/home/seleccionarCliente/{id}', [BusquedaClientesController::class, 'seleccionarCliente'])->middleware('auth');

Route::get('/buscarUsuario', [BuscarUsuario::class, 'buscar'])->middleware('auth');
Route::get('/buscarCliente', [BuscarCliente::class, 'buscar'])->middleware('auth');
Route::get('/buscarCompleto', [BusquedaCompleto::class, 'buscar'])->middleware('auth');
Route::get('/buscarTicket', [buscarTicket::class, 'buscar'])->middleware('auth');
Route::get('/buscarConcepto', [BusquedaConcepto::class, 'buscar'])->middleware('auth');
Route::get('/buscarRecibo', [BusquedaRecibo::class, 'buscar'])->name('recibos.buscar')->middleware('auth');
Route::get('/buscarRechazado', [RechazadoController::class, 'buscar'])->middleware('auth');


Route::get('recibos/pdf/{id}', [ReciboController::class, 'pdf'])->name('recibos.pdf')->middleware('auth');
Route::get('recibos/pdfImprimir/{id}', [ReciboController::class, 'pdfImprimir'])->name('pdfImprimir.pdfImprimir')->middleware('auth');


Route::middleware(['auth', 'admin'])->group(function () {
  Route::post('pagos/reporte', [ReporteController::class, 'generarReporte'])->name('generar.reporte')->middleware('auth');
});
                                                       

Route::get('recibos/cancelarCancelado/{id}', [RegistroEquipoCliente::class, 'cancelarCancelado'])
    ->name('recibos.cancelar')
    ->middleware(['auth', 'admin']);  // Requiere autenticación Y ser admin                                                                  
Route::get('recibos/estado/{id}', [RegistroEquipoCliente::class, 'estado'])->name('recibos.estado')->middleware('auth');
Route::get('recibos/cancelado/{id}', [RegistroEquipoCliente::class, 'cancelado'])
    ->name('recibos.cancelado')
    ->middleware(['auth', 'admin']);
Route::get('recibos/rechazado', [ReciboController::class, 'rechazado'])->name('recibos.rechazado')->middleware('auth');

Route::get('/imprimir', [ConceptoController::class, 'imprimir'])->middleware('auth');


Route::get('/home/buscarColonia', [BuscarColoniasController::class, 'buscarColonia'])->middleware('auth');

Route::post('/home/registroEquipoCliente', [RegistroEquipoCliente::class, 'recepcion'])->middleware('auth');
Route::post('/home/validarMarca', function (Request $request) {
  $marcaExiste = Marca::where('marca', $request->marca)->exists();
  return response()->json(['exists' => $marcaExiste]);
});
Route::get('completados',[FinalizadoController::class,'index'])->name('completados.index')->middleware('auth');

Route::get('completados/pdf/{id}',[FinalizadoController::class,'pdf'])->name('completados.pdf')->middleware('auth');

//Route::get('recibos/nota', [NotaController::class, 'guardarNota'])->name('guardarNota');

Route::get('/recibos/nota/{id}', [NotaController::class, 'obtenerNota']);
Route::get('/recibos/agregarnota{id}', [NotaController::class, 'guardarNota']);

//Productos

Route::resource('productos', ProductoController::class)->middleware('auth');
Route::post('/productos/validar', [ProductoController::class, 'validarProducto'])
    ->middleware('auth')
    ->name('productos.validar');


//Route::get('/productos/buscar', [ProductoController::class, 'buscar']);
// routes/web.php
Route::get('/buscarProducto', [BuscarProducto::class, 'buscar'])->name('buscar.producto');

Route::resource('ventas', VentaController::class)->middleware('auth');

Route::resource('pagos', PagosController::class)->middleware('auth');

Route::resource('gastos', GastoController::class)->middleware('auth');

Route::middleware(['auth'])->group(function () {
  // Rutas para abonos
  Route::get('/abonos', [AbonosController::class, 'index'])->name('abonos.index');
  Route::post('/abonos', [AbonosController::class, 'store'])->name('abonos.store');
  Route::get('/abonos/{ventaId}', [AbonosController::class, 'getAbonos'])->name('abonos.historial');
  Route::delete('/abonos/{abono}', [AbonosController::class, 'destroy'])->name('abonos.destroy');
  
  // Rutas para ventas a crédito
  Route::post('/ventas-abonos', [AbonosController::class, 'storeVenta'])->name('ventas-abonos.store');
  Route::get('/buscar-clientes', [AbonosController::class, 'buscarClientes'])->name('clientes.buscar');
  // Nueva ruta para búsqueda AJAX
  Route::post('/clientesAbono', [AbonosController::class, 'storeCliente'])->name('clientes.store');
});

Route::patch('/pagos/{id}/cancelar', [PagosController::class, 'cancelar'])
     ->name('pagos.cancelar');


//ticket pagos
Route::get('/pagos/pdf/{id}', [TicketPagoController::class, 'pdf'])
     ->name('pagos.pdf');

// Ruta para registrar un cliente
Route::post('/ventas/registrar', [VentaController::class, 'crearCliente'])->name('clientes.registrar');
Route::get('/buscar-productos', [VentaController::class, 'buscarProducto'])->name('productos.buscar');

Route::post('/ventas/realizar-cobro', [VentaController::class, 'realizarCobro'])->name('venta.realizar');;

Route::get('/buscarClienteVentas', [BuscarClienteVenta::class, 'buscar'])->middleware('auth');


// Ruta para seleccionar un cliente específico y cargar su información
Route::get('/seleccionarClienteVenta/{id}', [BuscarClienteVenta::class, 'seleccionarCliente'])->middleware('auth');


