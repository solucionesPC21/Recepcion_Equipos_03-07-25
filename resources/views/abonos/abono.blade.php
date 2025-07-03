@extends('layouts.abonos.app-master')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-4"><i class="fas fa-hand-holding-usd text-primary"></i> Gestión de Abonos</h1>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevaVentaModal">
                <i class="fas fa-plus"></i> Nueva Venta A Abonos
            </button>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="card-title"><i class="fas fa-list"></i> Historial de Ventas a Abonos</h3>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="ventasTable">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Telefono</th>
                            <th>Productos</th>
                            <th>Total</th>
                            <th>Saldo</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ventas as $venta)
                        <tr>
                            <td>{{ $venta->id }}</td>
                            <td>{{ $venta->cliente->nombre }}</td>
                            <td>{{ $venta->cliente->telefono }}</td>

                            <td>
                                @foreach($venta->detalles as $detalle)
                                    {{ $detalle->cantidad }} - {{ $detalle->concepto->nombre }} (${{ number_format($detalle->precio_unitario, 2) }})<br>
                                @endforeach
                            </td>
                            <td class="font-weight-bold">${{ number_format($venta->total, 2) }}</td>
                            <td>
                                <span class="px-2 py-1 rounded text-white bg-{{ $venta->saldo_restante > 0 ? 'warning' : 'success' }}">
                                    ${{ number_format($venta->saldo_restante, 2) }}
                                </span>
                            </td>
                            <td>{{ $venta->fecha_venta->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="px-2 py-1 rounded text-white bg-{{ $venta->estado->id == 1 ? 'danger' : ($venta->estado->id == 2 ? 'success' : 'secondary') }}">
                                    {{ $venta->estado->id == 1 ? 'Pendiente' : ($venta->estado->id == 2 ? 'Pagado' : $venta->estado->nombre) }}
                                </span>
                            </td>
                            <td>
                                @if($venta->saldo_restante > 0)
                                <button class="btn btn-sm btn-primary btn-abonar" 
                                        data-venta-id="{{ $venta->id }}"
                                        data-saldo="{{ $venta->saldo_restante }}"
                                        data-cliente="{{ $venta->cliente->nombre }}"
                                        data-productos="{{ $venta->detalles->map(function($item) {
                                            return $item->cantidad.' - '.$item->concepto->nombre;
                                        })->implode(', ') }}">
                                    <i class="fas fa-money-bill-wave"></i> Abonar
                                </button>
                                @endif
                                <button class="btn btn-sm btn-info btn-historial text-white" 
                                        data-venta-id="{{ $venta->id }}">
                                    <i class="fas fa-history"></i> Historial
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-4">
                    {{ $ventas->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para nueva venta a crédito -->
<!-- Modal para nueva venta a crédito -->
<div class="modal fade" id="nuevaVentaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nueva Venta a Abonos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="nuevaVentaForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Cliente</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="buscarCliente" placeholder="Buscar cliente..." required>
                            <input type="hidden" name="cliente_id" id="cliente_id">
                            <button class="btn btn-outline-primary" type="button" id="btnNuevoCliente">
                                <i class="fas fa-plus"></i> Nuevo
                            </button>
                        </div>
                        <div id="resultadosClientes" class="mt-2 d-none">
                            <div class="list-group" id="listaClientes">
                                <!-- Resultados de búsqueda aparecerán aquí -->
                            </div>
                        </div>
                        <div class="invalid-feedback" id="cliente-feedback">
                            Por favor seleccione un cliente de los resultados
                        </div>
                        <div id="cliente-seleccionado" class="mt-2 d-none">
                            <div class="alert alert-success py-2">
                                <strong>Cliente seleccionado: </strong>
                                <span id="nombre-cliente"></span>
                                <button type="button" class="btn btn-sm btn-link float-end" id="cambiar-cliente">
                                    <i class="fas fa-times"></i> Cambiar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Resto del formulario (productos, abono inicial, etc.) -->
                    <div class="mb-3">
                        <label class="form-label">Productos</label>
                        <div id="productos-container">
                            <div class="row producto-item mb-2">
                                <div class="col-md-5">
                                    <input type="text" class="form-control producto-nombre" name="productos[0][nombre]" placeholder="Nombre del producto" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" step="0.01" min="0" class="form-control producto-precio" name="productos[0][precio]" placeholder="Precio" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control cantidad" name="productos[0][cantidad]" min="1"  placeholder="Cantidad" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control subtotal" placeholder="Subtotal" readonly>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-remove-producto" disabled>X</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary mt-2" id="btn-add-producto">
                            <i class="fas fa-plus"></i> Agregar otro producto
                        </button>
                    </div>
                                        
                    <div class="mb-3">
                        <label class="form-label">Abono Inicial (Opcional)</label>
                        <input type="number" class="form-control" name="abono_inicial" id="abono_inicial" step="0.01" min="0">
                    </div>
                    
                   <!-- Nuevo campo para tipo de pago -->
                        <div class="mb-3">
                            <label class="form-label">Método de Pago</label>
                            <select class="form-select" name="tipo_pago_id" required>
                                <option value="">Seleccione método</option>
                                @foreach($tiposPago as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->tipoPago }}</option>
                                @endforeach
                            </select>
                        </div>

                    <div class="mb-3">
                        <h5>Total: <span id="total-venta">$0.00</span></h5>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarVenta">Guardar</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal para registrar nuevo cliente -->
<div class="modal fade" id="nuevoClienteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Registrar Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="nuevoClienteForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" name="telefono">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarCliente">Guardar</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal para registrar abono -->
<div class="modal fade" id="abonoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Registrar Abono</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="abonoForm">
                    @csrf
                    <input type="hidden" name="venta_id" id="venta_id_abono">
                    
                    <div class="mb-3">
                        <label class="form-label">Cliente</label>
                        <input type="text" class="form-control" id="abono_cliente" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Productos</label>
                        <input type="text" class="form-control" id="abono_productos" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Saldo Restante</label>
                        <input type="text" class="form-control" id="abono_saldo" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Monto a Abonar</label>
                        <input type="number" class="form-control" name="monto" id="abono_monto" step="0.01" min="0.01" required>
                    </div>

                    <!-- Nuevo campo para tipo de pago -->
                        <div class="mb-3">
                            <label class="form-label">Método de Pago</label>
                            <select class="form-select" name="tipo_pago_id1" required>
                                <option value="">Seleccione método</option>
                                @foreach($tiposPago as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->tipoPago }}</option>
                                @endforeach
                            </select>
                        </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnRegistrarAbono">Registrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para historial de abonos -->
<div class="modal fade" id="historialModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Historial de Abonos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h5 id="historialCliente"></h5>
                        <p id="historialProductos" class="text-muted"></p>
                    </div>
                    <div class="col-md-6 text-right">
                        <h5>Total: <span id="historialTotal" class="font-weight-bold"></span></h5>
                        <h5>Saldo: <span id="historialSaldo" class="font-weight-bold"></span></h5>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped" id="historialTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Metodo Pago</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="historialBody">
                            <!-- Aquí se cargarán los abonos via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection