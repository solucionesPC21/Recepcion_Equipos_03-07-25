@extends('layouts.app-master')

@section('content')

    <h1 class="titulo">Clientes</h1>

    @if (session('success'))
    <div id="success-alert-modal" class="modal-alert">
        <div class="modal-alert-content alert alert-success alert-dismissible fade-out custom-alert" role="alert">
            {{ session('success') }}
            <div class="progress-bar" id="success-progress-bar"></div>
        </div>
    </div>
@endif

@if ($errors->any())
    <div id="error-alert-modal" class="modal-alert">
        <div class="modal-alert-content custom-error-message">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
                <div class="progress-bar" id="error-progress-bar"></div>
            </ul>
        </div>
    </div>
@endif

@if (session('error'))
    <div id="error-alert" class="modal-alert alert alert-danger alert-dismissible fade-out custom-alert" role="alert">
        {{ session('error') }}
        <div class="progress-bar" id="error-progress-bar"></div>
    </div>
@endif



    <!-- Input para búsqueda en tiempo real con estilos de Bootstrap -->
    <div class="input-group mb-3" style="max-width: 700px;">
            <!-- Ajusta el ancho máximo según tus necesidades -->
            <input type="text" id="searchInput" class="form-control" placeholder="Buscar clientes..." onkeyup="buscarRecibos()">
    </div>
    <br>
        <table class="tabla">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Telefono</th>
                    <th>Telefono 2</th>
                    <th>RFC</th>
                    <th>Colonia</th>
                    <th>Acciones</th>
                    
                </tr>
            </thead>
            <tbody id="recibosBody">
                @foreach($clientes as $cliente)
                    <tr>
                        <td>{{ $cliente->id}}</td>
                        <td>{{ $cliente->nombre }}</td>
                        <td>{{ $cliente->telefono }}</td>
                        <td>{{ $cliente->telefono2 }}</td>
                        <td>{{ $cliente->rfc }}</td>
                        <td>
                             @if($cliente->colonia)
                                {{ $cliente->colonia->colonia }}
                            @else
                                Sin colonia registrada
                            @endif
                        </td>
                        
                        <td>
                            <form action="{{ url('/clientes/'.$cliente->id) }}" method="post" style="display: inline;">
                                @csrf
                                {{ method_field('DELETE') }}
                                <input type="submit" onclick="return confirm('¿Quieres Borrar?')" value="Borrar" class="btn btn-danger" >
                            </form>
                            <a class="editarClienteBtn btn btn-info" data-cliente-id="{{ $cliente->id }}" href="#" onclick="editarCliente(event, {{ $cliente->id }})">Editar</a>
                            
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <nav aria-label="...">
            <ul class="pagination">
                {{ $clientes->links() }}
            </ul>
        </nav>


      
    @include('clientes.form-editar')
@endsection
