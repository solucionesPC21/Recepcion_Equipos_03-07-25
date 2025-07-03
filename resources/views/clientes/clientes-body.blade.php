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