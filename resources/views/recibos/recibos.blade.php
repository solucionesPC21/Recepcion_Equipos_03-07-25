@extends('layouts.recibos.app-master')

@section('content')

    <h1 class="titulo">Recibos</h1>

    <!-- Modal de éxito -->
    <div id="success-alert-modal" class="modal-alert hidden">
        <div class="modal-alert-content custom-alert">
            <span id="success-message"></span>
            <div class="progress-bar" id="success-progress-bar"></div>
        </div>
    </div>

    <!-- Modal de error -->
    <div id="error-alert-modal" class="modal-alert hidden">
        <div class="modal-alert-content custom-error-message">
            <span id="error-message"></span>
            <div class="progress-bar" id="error-progress-bar"></div>
        </div>
    </div>

    <!-- Filtro y búsqueda en tiempo real con estilos de Bootstrap -->
    <div class="d-flex justify-content-between mb-3" style="max-width: 900px;">
        <!-- Ajusta el ancho máximo según tus necesidades -->
        <div class="input-group" style="max-width: 700px;">
            <input type="text" id="searchInput" class="form-control" placeholder="Buscar recibo..." onkeyup="buscarRecibos()">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button">Buscar</button>
            </div>
        </div>

    </div>

    <strong><h2>Total De Ordenes De Reparación Recibidos: {{ $totalRecibos }}</h2></strong><br>
    <table class="tabla">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Fecha de Recibido</th>
                <th>Nota De Recepción</th>
                <th>Estado</th>
                <th>Comentarios</th>
            </tr>
        </thead>
        <tbody id="recibosBody">
            @foreach($recibos as $recibo)
            <tr class="recibo-row" data-estado="{{ $recibo->estado }}">
                <td>
                @if(isset($recibo->tipoEquipo[0]->cliente))
                    {{ $recibo->tipoEquipo[0]->cliente->nombre}}
                @else
                    Cliente No Encontrado
                @endif
                </td>
                <td>{{ date('d-m-Y', strtotime($recibo->created_at)) }}</td>
                <td>
                    <form action="{{ route('recibos.pdf', ['id' => $recibo->id]) }}" method="GET" target="_blank">
                        @csrf
                        <button type="submit" style="border: none; background-color: transparent; padding: 0;">
                            <img src="{{ url('assets/iconos/file-earmark-arrow-down-fill.svg') }}" width="24" height="24" style="display: block;">
                        </button>
                    </form> 
                </td>
                <td>
                    <button type="button" onclick="abrirModalConfirmacion({{ $recibo->id }})" style="border: none; background-color: transparent; padding: 0;">
                        <img src="{{ url('assets/iconos/tools.svg') }}" width="24" height="24" style="display: block;">
                    </button>
                </td>

                <td>
                    <button type="button"  onclick="abrirNotaModal({{ $recibo->id }})" style="border: none; background-color: transparent; padding: 0;">
                        <img src="{{ url('assets/iconos/journals.svg') }}" width="24" height="24" style="display: block;">
                    </button>   
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <nav aria-label="...">
        <ul class="pagination">
            {{ $recibos->links() }}
        </ul>
    </nav>

    <div id="confirmacionModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModalConfirmacion()">&times;</span>
            <h2>¿La Reparación Ya Fue Completada?</h2>
            <div class="botones">
                <button id="confirmarReparacionButton" onclick="confirmarReparacion()" data-id="">Confirmar Reparación</button>
                @auth
                    @if(auth()->user()->isAdmin())
                        <button class="cancelar" id="cancelarReparacionButton" onclick="confirmarCancelarReparacion()" data-id="">Cancelar Reparación</button>
                    @endif
                @endauth
            </div>
        </div>
    </div>

<!-- Modal para mostrar y editar la nota -->
<div class="modal fade" id="notaModal" tabindex="-1" role="dialog" aria-labelledby="notaModalLabel" aria-hidden="true">
    <div class="modal-dialog custom-modal-width" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h4 class="modal-title font-weight-bold" id="notaModalLabel">Nota del Recibo</h4>
            </div>
            <div class="modal-body">
                <p id="notaContent"></p>
                <textarea id="notaInput" style="display: none;"></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="cerrarNotaModal()">Cerrar</button>
                
                <button type="button" id="guardarNotaButton" class="btn btn-primary" style="display: none;" onclick="guardarNota()">Guardar</button>
                <button type="button" id="editNotaButton" class="btn btn-success" onclick="habilitarEdicionNota()">Editar</button>
            </div>
        </div>
    </div>
</div>


@endsection

