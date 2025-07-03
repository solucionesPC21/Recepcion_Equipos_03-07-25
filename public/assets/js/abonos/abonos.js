$(document).ready(function() {
    // Configuración inicial
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Variables de estado
    let productoIndex = 1;
    let clienteSeleccionado = null;
    // Agrega esto en la sección de variables de estado
    let nuevoClienteModal = null;

    // Agrega esto después del DOM ready
    nuevoClienteModal = new bootstrap.Modal(document.getElementById('nuevoClienteModal'));
    // ==============================================
    // BUSCADOR DE CLIENTES EN TABLA PRINCIPAL
    // ==============================================
    
    //REGISTRO DE NUEVOS CLIENTES
    //===============================================
    // Mostrar modal para nuevo cliente
$('#btnNuevoCliente').click(function() {
    $('#nuevoClienteForm')[0].reset();
    nuevoClienteModal.show();
});

// Guardar nuevo cliente
// Guardar nuevo cliente
$('#btnGuardarCliente').click(function() {
    const form = $('#nuevoClienteForm');
    const btn = $(this);
    
    // Validación simple
    if (form.find('[name="nombre"]').val().trim() === '') {
        Swal.fire('Error', 'El nombre del cliente es requerido', 'error');
        return;
    }
    
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    $.ajax({
        url: '/clientesAbono',
        method: 'POST',
        data: form.serialize(),
        success: function(response) {
            // Cerrar modal de nuevo cliente
            nuevoClienteModal.hide();
            
            // Mostrar mensaje de éxito
            Swal.fire({
                title: '¡Éxito!',
                text: response.message,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
            
            // Seleccionar automáticamente el nuevo cliente
            clienteSeleccionado = {
                id: response.cliente.id,
                nombre: response.cliente.nombre
            };
            
            $('#cliente_id').val(clienteSeleccionado.id);
            $('#buscarCliente').val(clienteSeleccionado.nombre).prop('readonly', true);
            $('#nombre-cliente').text(clienteSeleccionado.nombre);
            $('#resultadosClientes').addClass('d-none');
            $('#cliente-seleccionado').removeClass('d-none');
            $('#buscarCliente').removeClass('is-invalid');
            
            // Eliminamos esta línea que hacía la búsqueda automática
            // buscarClientes(clienteSeleccionado.nombre);
        },
        error: function(xhr) {
            let message = 'Error al registrar el cliente';

            if (xhr.status === 409 && xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                message = Object.values(xhr.responseJSON.errors).join('<br>');
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }

            Swal.fire('Error', message, 'error');
        },
        complete: function() {
            btn.prop('disabled', false).html('Guardar');
        }
    });
});

    // ==============================================
    // BUSCADOR DE CLIENTES
    // ==============================================
    
    // Configuración del buscador
    $('#buscarCliente').on('input', function() {
        if (!clienteSeleccionado) {
            buscarClientes($(this).val());
        }
    });

    $('#btnBuscarCliente').click(function() {
        if (!clienteSeleccionado) {
            buscarClientes($('#buscarCliente').val());
        }
    });

    function buscarClientes(termino) {
        if (termino.length < 2) {
            $('#resultadosClientes').addClass('d-none');
            return;
        }

        $.ajax({
            url: '/buscar-clientes',
            method: 'GET',
            data: { termino: termino },
            success: function(response) {
                const lista = $('#listaClientes');
                lista.empty();

                if (response.length > 0) {
                    response.forEach(cliente => {
                        lista.append(`
                            <a href="#" class="list-group-item list-group-item-action cliente-item" 
                               data-id="${cliente.id}" 
                               data-nombre="${cliente.nombre}">
                                ${cliente.nombre}
                            </a>
                        `);
                    });
                    $('#resultadosClientes').removeClass('d-none');
                } else {
                    lista.append('<div class="list-group-item">No se encontraron clientes</div>');
                    $('#resultadosClientes').removeClass('d-none');
                }
            }
        });
    }

    // Seleccionar cliente de los resultados
    $(document).on('click', '.cliente-item', function(e) {
        e.preventDefault();
        clienteSeleccionado = {
            id: $(this).data('id'),
            nombre: $(this).data('nombre')
        };
        
        $('#cliente_id').val(clienteSeleccionado.id);
        $('#buscarCliente').val(clienteSeleccionado.nombre).prop('readonly', true);
        $('#nombre-cliente').text(clienteSeleccionado.nombre);
        $('#resultadosClientes').addClass('d-none');
        $('#cliente-seleccionado').removeClass('d-none');
        $('#buscarCliente').removeClass('is-invalid');
    });

    // Cambiar cliente seleccionado
    $('#cambiar-cliente').click(function() {
        clienteSeleccionado = null;
        $('#cliente_id').val('');
        $('#buscarCliente').val('').prop('readonly', false).focus();
        $('#cliente-seleccionado').addClass('d-none');
    });

    // ==============================================
    // GESTIÓN DE PRODUCTOS CON PRECIO MANUAL
    // ==============================================
    
    // Agregar nuevo producto
    $('#btn-add-producto').click(function() {
        const newItem = $('.producto-item').first().clone();
        const newIndex = $('.producto-item').length;
        
        // Limpiar valores y actualizar nombres
        newItem.find('.producto-nombre').val('').attr('name', `productos[${newIndex}][nombre]`);
        newItem.find('.producto-precio').val('').attr('name', `productos[${newIndex}][precio]`);
        newItem.find('.cantidad').val(1).attr('name', `productos[${newIndex}][cantidad]`);
        newItem.find('.subtotal').val('');
        newItem.find('.btn-remove-producto').prop('disabled', false);
        
        $('#productos-container').append(newItem);
        
        // Habilitar botón de eliminar en el primer item si hay más de uno
        if ($('.producto-item').length > 1) {
            $('.producto-item').first().find('.btn-remove-producto').prop('disabled', false);
        }
    });
    
    // Calcular subtotal cuando cambia precio o cantidad
    $(document).on('input', '.producto-precio, .cantidad', function() {
        calcularSubtotal($(this).closest('.producto-item'));
        calcularTotal();
    });

    function calcularSubtotal(item) {
        const precio = parseFloat(item.find('.producto-precio').val()) || 0;
        const cantidad = parseInt(item.find('.cantidad').val()) || 0;
        const subtotal = precio * cantidad;
        
        item.find('.subtotal').val('$' + subtotal.toFixed(2));
    }
    
    // Eliminar producto
    $(document).on('click', '.btn-remove-producto', function() {
        if ($('.producto-item').length > 1) {
            $(this).closest('.producto-item').remove();
            calcularTotal();
            
            // Si queda solo un item, deshabilitar su botón de eliminar
            if ($('.producto-item').length === 1) {
                $('.producto-item').first().find('.btn-remove-producto').prop('disabled', true);
            }
        }
    });
    
    // Calcular total general
    function calcularTotal() {
        let total = 0;
        
        $('.producto-item').each(function() {
            const subtotalText = $(this).find('.subtotal').val().replace('$', '') || '0';
            total += parseFloat(subtotalText);
        });
        
        $('#total-venta').text('$' + total.toFixed(2));
    }

    // ==============================================
    // GUARDAR VENTA CON PRECIOS MANUALES
    // ==============================================
    
    $('#btnGuardarVenta').click(function() {
    // Validar cliente seleccionado
    if (!clienteSeleccionado) {
        $('#buscarCliente').addClass('is-invalid');
        $('#buscarCliente').focus();
        return false;
    }

    // Validar productos
    let productosValidos = true;
    $('.producto-item').each(function() {
        const nombre = $(this).find('.producto-nombre').val().trim();
        const precio = $(this).find('.producto-precio').val();
        const cantidad = $(this).find('.cantidad').val();

        if (nombre === '') {
            $(this).find('.producto-nombre').addClass('is-invalid');
            productosValidos = false;
        } else {
            $(this).find('.producto-nombre').removeClass('is-invalid');
        }

        if (precio === '' || parseFloat(precio) <= 0) {
            $(this).find('.producto-precio').addClass('is-invalid');
            productosValidos = false;
        } else {
            $(this).find('.producto-precio').removeClass('is-invalid');
        }

        if (cantidad === '' || parseInt(cantidad) <= 0) {
            $(this).find('.cantidad').addClass('is-invalid');
            productosValidos = false;
        } else {
            $(this).find('.cantidad').removeClass('is-invalid');
        }
    });

    if (!productosValidos) {
        Swal.fire('Error', 'Por favor complete correctamente todos los campos de productos', 'error');
        return false;
    }

    const abonoInicial = parseFloat($('#abono_inicial').val()) || 0;
    const tipoPagoId = $('select[name="tipo_pago_id"]').val();

    if (abonoInicial > 0 && !tipoPagoId) {
        Swal.fire('Error', 'Por favor seleccione un método de pago para el abono inicial', 'error');
        $('select[name="tipo_pago_id"]').addClass('is-invalid');
        return false;
    } else {
        $('select[name="tipo_pago_id"]').removeClass('is-invalid');
    }

    // Preparar datos
    const formData = {
        cliente_id: $('#cliente_id').val(),
        productos: [],
        abono_inicial: abonoInicial,
        tipo_pago_id: tipoPagoId
    };

    $('.producto-item').each(function(index) {
        formData.productos.push({
            nombre: $(this).find('.producto-nombre').val(),
            precio: parseFloat($(this).find('.producto-precio').val()),
            cantidad: parseInt($(this).find('.cantidad').val())
        });
    });

    // Mostrar confirmación antes de enviar
    let detalleProductos = '';
    formData.productos.forEach((p, i) => {
        detalleProductos += `<p><strong>Producto ${i + 1}:</strong> ${p.nombre} | $${p.precio.toFixed(2)} x ${p.cantidad}</p>`;
    });

    Swal.fire({
        title: 'Confirmar venta',
        html: `
            <div class="text-left">
                <p>¿Está seguro de guardar esta venta?</p>
                ${abonoInicial > 0 ? `<p><strong>Abono inicial:</strong> $${abonoInicial.toFixed(2)}</p>
                <p><strong>Método de pago:</strong> ${$('select[name="tipo_pago_id"] option:selected').text()}</p>` : ''}
                ${detalleProductos}
            </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const btn = $('#btnGuardarVenta');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

            $.ajax({
                url: '/ventas-abonos',
                method: 'POST',
                data: formData,
                success: function(response) {
                   Swal.fire({
                    title: '¡Éxito!',
                    text: response.message,
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false
                });

                setTimeout(() => {
                    $('#nuevaVentaModal').modal('hide');
                    location.reload();
                }, 3000);

                },
                error: function(xhr) {
                    let message = 'Error al procesar la solicitud';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        message = Object.values(xhr.responseJSON.errors).join('<br>');
                    }
                    Swal.fire('Error', message, 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html('Guardar');
                }
            });
        }
    });
});


    // ==============================================
    // GESTIÓN DE ABONOS
    // ==============================================
    
    // Mostrar modal para registrar abono
    $(document).on('click', '.btn-abonar', function() {
        const ventaId = $(this).data('venta-id');
        const cliente = $(this).data('cliente');
        const productos = $(this).data('productos');
        const saldo = parseFloat($(this).data('saldo'));
        
        // Asignar valores al formulario de abono
        $('#venta_id_abono').val(ventaId);
        $('#abono_cliente').val(cliente);
        $('#abono_productos').val(productos);
        $('#abono_saldo').val('$' + saldo.toFixed(2));
        $('#abono_monto').attr('max', saldo).val('');
        
        // Mostrar modal
        const abonoModal = new bootstrap.Modal(document.getElementById('abonoModal'));
        abonoModal.show();
    });

    // Registrar abono
  $('#btnRegistrarAbono').click(function() {
    const form = $('#abonoForm');
    const formData = form.serialize();
    const btn = $(this);

    const monto = parseFloat($('#abono_monto').val());
    const saldo = parseFloat($('#abono_saldo').val().replace('$', ''));

    // Validar que se haya seleccionado un tipo de pago
    if ($('select[name="tipo_pago_id1"]').val() === '') {
        Swal.fire('Error', 'Por favor seleccione un método de pago', 'error');
        $('select[name="tipo_pago_id1"]').addClass('is-invalid');
        return;
    }

    if (monto > saldo) {
        Swal.fire('Error', 'El monto no puede ser mayor al saldo restante', 'error');
        return;
    }

    // Confirmación antes de enviar el abono
    Swal.fire({
        title: 'Confirmar Abono',
        html:
            '<div class="text-left">' +
            '<p>¿Está seguro de registrar este abono?</p>' +
            '<p><strong>Monto:</strong> $' + monto.toFixed(2) + '</p>' +
            '<p><strong>Saldo restante:</strong> $' + saldo.toFixed(2) + '</p>' +
            '</div>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Deshabilitar botón y mostrar loader
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

            // Enviar AJAX
            $.ajax({
                url: '/abonos',
                method: 'POST',
                data: formData,
                success: function(response) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: response.message || 'Abono registrado correctamente',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        $('#abonoModal').modal('hide');
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let message = 'Error al registrar el abono';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        message = Object.values(xhr.responseJSON.errors).join('<br>');
                        if (xhr.responseJSON.errors.tipo_pago_id1) {
                            $('select[name="tipo_pago_id1"]').addClass('is-invalid');
                        }
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', message, 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html('Registrar');
                }
            });
        }
    });
});

    // Mostrar historial de abonos
    $(document).on('click', '.btn-historial', function() {
        const ventaId = $(this).data('venta-id');
        const row = $(this).closest('tr');
        const modal = new bootstrap.Modal(document.getElementById('historialModal'));
        
        $('#historialCliente').text('Cliente: ' + row.find('td:eq(1)').text());
        $('#historialProductos').html('Productos:' + row.find('td:eq(3)').text().replace(/\n/g, '<br>'));
        $('#historialTotal').text(row.find('td:eq(4)').text());
        $('#historialSaldo').text(row.find('td:eq(5)').text());
        
        $('#historialBody').html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
        
        $.get('/abonos/' + ventaId)
            .done(function(response) {
                let html = '';
                if (response.length > 0) {
                    response.forEach((abono, index) => {
                        const fecha = new Date(abono.fecha_abono);
                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${fecha.toLocaleDateString()} ${fecha.toLocaleTimeString()}</td>
                                <td class="font-weight-bold">$${parseFloat(abono.monto).toFixed(2)}</td>
                                <td>
                                    ${abono.tipo_pago ? abono.tipo_pago.tipoPago : 'No especificado'}
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger btn-eliminar-abono" data-abono-id="${abono.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="4" class="text-center">No hay abonos registrados</td></tr>';
                }
                $('#historialBody').html(html);
            })
            .fail(function(xhr) {
                $('#historialBody').html('<tr><td colspan="4" class="text-center text-danger">Error al cargar el historial</td></tr>');
                console.error('Error:', xhr.responseText);
            });
        
        modal.show();
    });

    // Eliminar abono
    $(document).on('click', '.btn-eliminar-abono', function() {
        const abonoId = $(this).data('abono-id');
        
        Swal.fire({
            title: '¿Eliminar este abono?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/abonos/' + abonoId,
                    method: 'DELETE',
                    success: function(response) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: response.message || 'El abono ha sido eliminado',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            $('#historialModal').modal('hide');
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let message = 'No se pudo eliminar el abono';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', message, 'error');
                    }
                });
            }
        });
    });
});