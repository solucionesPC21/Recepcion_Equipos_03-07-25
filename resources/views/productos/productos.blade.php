@extends('layouts.productos.app-master')
@section('content')

<!-- Modal de éxito -->
<div class="modal fade" id="notificationModal" data-bs-backdrop="false" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-bottom-right" role="document">
        <div >
            <div class="modal-body p-2">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show m-0" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show m-0" role="alert">
                        {{ session('error') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center">
    <h1 class="titulo mb-0">Registro de Productos</h1>
    <h4 class="ms-3">Total De Productos: <strong>{{ $totalProductos }}</strong></h4>
</div>
<br>

<input type="text" id="searchInput" class="form-control" placeholder="Buscar productos...">

<div class="d-flex justify-content-end mb-3">
    <button id="registrarProductoBtn" class="btn btn-primary">
        <i class="fas fa-plus"></i> Registrar Producto
    </button>
</div>

<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Cantidad</th>
                <th>Modelo</th>
                <th>Marca</th>
                <th>Descripción</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody id="productosTabla">
            @foreach($productos as $producto)
            <tr>
                <td>{{ ($productos->currentPage() - 1) * $productos->perPage() + $loop->iteration }}</td>
                <td>{{ $producto->nombre }}</td>
                <td>${{ number_format($producto->precio, 2) }}</td>
                <td>{{ $producto->cantidad }}</td>
                <td>{{ $producto->modelo }}</td>
                <td>{{ $producto->marca }}</td>
                <td>{{ $producto->descripcion }}</td>
                <td class="text-center">
                    <div class="btn-group" style="gap: 10px;">
                    <button class="btn btn-warning btn-sm edit-btn" data-id="{{ $producto->id }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16">
                            <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/>
                        </svg>
                    </button>
                        <form action="{{ url('/productos/'.$producto->id) }}" method="post" class="delete-form d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                 <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/>
                            </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        {{ $productos->links() }}
    </ul>
</nav>

@include('productos.form-registrar')
@include('productos.form-editar')


@endsection


