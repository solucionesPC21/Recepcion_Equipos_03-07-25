// Función para buscar recibos
function buscarRecibos() {
    var searchTerm = document.getElementById('searchInput').value;
    var url = '/buscarTicket';
    
    $.ajax({
        url: url,
        type: 'GET',
        data: { search: searchTerm },
        success: function(response) {
            $('#recibosBody').html(response.recibosBodyHtml);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log('Error en la conexión con el controlador:', textStatus, errorThrown);
        }
    });
}


// Versión simplificada - permite cualquier entrada
function setupConceptoValidation(inputElement, errorElement) {
    // Eliminamos todas las validaciones de teclado
    // Solo mantenemos el estilo básico para mostrar/ocultar el error
    
    inputElement.addEventListener('input', function() {
        // Esta versión no valida el contenido, solo muestra/oculta el elemento de error
        // según si hay texto o no (como ejemplo básico)
        errorElement.style.display = this.value.trim() === '' ? 'block' : 'none';
    });
}

// Función para mostrar sugerencias
function displaySuggestions(suggestions, container) {
    let suggestionHtml = '';
    
    if (suggestions.length > 0) {
        suggestions.forEach(suggestion => {
            const isInventoryItem = suggestion.id_categoria == 2;
            const isAvailable = isInventoryItem ? suggestion.cantidad > 0 : true;
            
            // Mostrar nombre, modelo y marca
            const displayText = `${suggestion.nombre} ${suggestion.marca ? '| ' + suggestion.marca : ''} ${suggestion.modelo ? '| ' + suggestion.modelo : ''} | $${suggestion.precio}`;
            
            suggestionHtml += `
                <div class="suggestion-item ${!isAvailable ? 'disabled text-danger' : ''}" 
                    data-id="${suggestion.id}"
                    data-nombre="${suggestion.nombre}"
                    data-precio="${suggestion.precio}"
                    data-categoria="${suggestion.id_categoria}"
                    data-cantidad="${suggestion.cantidad}"
                    data-marca="${suggestion.marca || ''}"
                    data-modelo="${suggestion.modelo || ''}">
                    ${displayText}
                    ${isInventoryItem ? 
                      (isAvailable ? 
                        `<span class="badge bg-success">Stock: ${suggestion.cantidad}</span>` : 
                        `<span class="badge bg-danger">AGOTADO</span>`) : 
                      `<span class="badge bg-info">Servicio</span>`}
                </div>
            `;
        });
    }
    
    container.innerHTML = suggestionHtml;
    container.style.display = 'block';
}

// Función auxiliar para mostrar error de stock
function mostrarErrorStock(inputElement, disponible) {
    inputElement.classList.add('is-invalid');
    const errorElement = inputElement.nextElementSibling || document.createElement('div');
    errorElement.className = 'invalid-feedback text-danger fw-bold';
    errorElement.textContent = `STOCK INSUFICIENTE (Disponible: ${disponible})`;
    inputElement.parentNode.appendChild(errorElement);
}

// Función auxiliar para limpiar error de stock
function limpiarErrorStock(inputElement) {
    inputElement.classList.remove('is-invalid');
    if (inputElement.nextElementSibling) {
        inputElement.nextElementSibling.textContent = '';
    }
}

// Función auxiliar para mostrar mensaje de error general
function mostrarMensajeError(mensaje) {
    // Eliminar mensajes anteriores
    document.querySelectorAll('.alert-error-ticket').forEach(el => el.remove());
    
    const errorAlert = document.createElement('div');
    errorAlert.className = 'alert alert-danger position-fixed top-0 start-50 translate-middle-x mt-3 alert-error-ticket';
    errorAlert.style.zIndex = '9999';
    errorAlert.innerHTML = `
        <strong>¡Error!</strong> ${mensaje}
        <button type="button" class="btn-close float-end" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(errorAlert);
    
    setTimeout(() => {
        errorAlert.remove();
    }, 5000);
}

// Configurar eventos para un grupo de concepto
function setupConceptoGroup(conceptoGroup) {
    const conceptoInput = conceptoGroup.querySelector('.concepto-input');
    const precioInput = conceptoGroup.querySelector('.precio-input');
    const categoriaSelect = conceptoGroup.querySelector('.categoria-select');
    const cantidadInput = conceptoGroup.querySelector('.cantidad-input');
    const totalInput = conceptoGroup.querySelector('.total');
    const conceptoError = conceptoGroup.querySelector('.concepto-error');
    const suggestionsContainer = conceptoGroup.querySelector('.suggestions-container');
    const conceptoIdInput = conceptoGroup.querySelector('.concepto-id') || createHiddenInput();

    function createHiddenInput() {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'concepto_id[]';
        input.className = 'concepto-id';
        conceptoGroup.appendChild(input);
        return input;
    }

    // Validación básica del concepto
    conceptoInput.addEventListener('input', function() {
        conceptoError.style.display = this.value.trim() === '' ? 'block' : 'none';
        this.value = this.value.toUpperCase();
        
        // Si el usuario está escribiendo manualmente, resetear el ID
        if (this.value.trim().length > 0 && !suggestionsContainer.querySelector('.suggestion-item:hover')) {
            conceptoIdInput.value = '';
            precioInput.removeAttribute('readonly');
            categoriaSelect.value = '';
        }
    });

    // Búsqueda de sugerencias con debounce
    let searchTimeout;
    conceptoInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length >= 3 && !conceptoIdInput.value) {
            suggestionsContainer.innerHTML = '<div class="suggestion-item">Buscando...</div>';
            suggestionsContainer.style.display = 'block';
            
            searchTimeout = setTimeout(() => {
                fetch(`/buscarConcepto?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => displaySuggestions(data, suggestionsContainer))
                    .catch(() => {
                        suggestionsContainer.innerHTML = '<div class="suggestion-item">Error al cargar sugerencias</div>';
                    });
            }, 300);
        } else {
            suggestionsContainer.innerHTML = '';
            suggestionsContainer.style.display = 'none';
        }
    });

    // Selección de sugerencia
    suggestionsContainer.addEventListener('click', function(event) {
        const item = event.target.closest('.suggestion-item');
        if (!item || item.classList.contains('disabled')) return;
        
        const { id, nombre, precio, categoria, cantidad } = item.dataset;
        
        // Llenar campos
        conceptoInput.value = nombre;
        precioInput.value = precio;
        categoriaSelect.value = categoria;
        conceptoIdInput.value = id;
        
        // Configurar cantidad disponible
        if (cantidadInput) {
            cantidadInput.dataset.disponible = categoria == 2 ? cantidad : '9999';
            cantidadInput.value = '1';
            cantidadInput.dispatchEvent(new Event('input'));
        }
        
        suggestionsContainer.innerHTML = '';
        suggestionsContainer.style.display = 'none';
    });

    // Manejo de precio
    precioInput.addEventListener('input', function() {
        // Validar formato numérico
        this.value = this.value.replace(/[^0-9.]/g, '');
        
        // Limitar a 2 decimales
        if (this.value.includes('.')) {
            const parts = this.value.split('.');
            if (parts[1].length > 2) {
                this.value = parseFloat(this.value).toFixed(2);
            }
        }
        
        // Validar stock si es producto
        if (categoriaSelect.value == 2 && cantidadInput) {
            const cantidad = parseInt(cantidadInput.value) || 0;
            const disponible = parseInt(cantidadInput.dataset.disponible || 0);
            
            if (cantidad > disponible) {
                mostrarErrorStock(cantidadInput, disponible);
            } else {
                limpiarErrorStock(cantidadInput);
            }
        }
        
        calcularTotalConcepto(conceptoGroup);
        calcularTotalGeneral();
    });

    // Manejo de cantidad
    cantidadInput.addEventListener('input', function() {
        // Forzar número entero positivo
        this.value = Math.max(1, parseInt(this.value) || 1);
        
        if (categoriaSelect.value == 2) {
            const cantidad = parseInt(this.value);
            const disponible = parseInt(this.dataset.disponible || 0);
            
            if (cantidad > disponible) {
                mostrarErrorStock(this, disponible);
            } else {
                limpiarErrorStock(this);
            }
        }
        
        calcularTotalConcepto(conceptoGroup);
        calcularTotalGeneral();
    });

    // Cambio de categoría
    categoriaSelect.addEventListener('change', function() {
        if (this.value == 2 && !conceptoIdInput.value) {
            // Si es nueva mercancía, inicializar stock
            cantidadInput.dataset.disponible = '0';
        }
        calcularTotalConcepto(conceptoGroup);
    });

    // Cerrar sugerencias al hacer clic fuera
    document.addEventListener('click', function(event) {
        if (!conceptoGroup.contains(event.target) && 
            !event.target.closest('.suggestions-container')) {
            suggestionsContainer.style.display = 'none';
        }
    });
}

// Función para calcular el total de un concepto individual
function calcularTotalConcepto(group) {
    const cantidad = parseFloat(group.querySelector('.cantidad-input').value) || 0;
    const precio = parseFloat(group.querySelector('.precio-input').value) || 0;
    const total = cantidad * precio;
    group.querySelector('.total').value = '$' + total.toFixed(2);
}

// Función para calcular el total general
function calcularTotalGeneral() {
    let totalGeneral = 0;
    document.querySelectorAll('.concepto-group').forEach(group => {
        const totalText = group.querySelector('.total').value;
        const totalValue = parseFloat(totalText.replace('$', '')) || 0;
        totalGeneral += totalValue;
    });
    
    const totalGeneralInput = document.getElementById('total_general');
    if (totalGeneralInput) {
        totalGeneralInput.value = '$' + totalGeneral.toFixed(2);
    }
}

// Función para validar inventario
function validarInventario() {
    let isValid = true;
    let errorMessage = '';
    const conceptos = document.querySelectorAll('.concepto-group');
    
    conceptos.forEach(grupo => {
        const categoria = grupo.querySelector('.categoria-select').value;
        const cantidadInput = grupo.querySelector('.cantidad-input');
        const precioInput = grupo.querySelector('.precio-input');
        const conceptoInput = grupo.querySelector('.concepto-input');
        
        // Validar campos obligatorios
        if (!conceptoInput.value.trim() || !precioInput.value.trim() || cantidadInput.value.trim() === '') {
            isValid = false;
            errorMessage = 'Todos los campos de concepto son obligatorios.';
            return;
        }
        
        // Validar stock para productos (categoría 2)
        if (categoria == 2) {
            const cantidad = parseInt(cantidadInput.value) || 0;
            const disponible = parseInt(cantidadInput.dataset.disponible || 0);
            
            if (cantidad > disponible) {
                mostrarErrorStock(cantidadInput, disponible);
                isValid = false;
                errorMessage = 'La cantidad solicitada para uno o más productos excede el stock disponible.';
            }
        }
    });
    
    if (!isValid) {
        mostrarMensajeError(errorMessage);
    }
    
    return isValid;
}

// Función para restablecer el contenido de la modal
function resetearModal() {
    document.getElementById('recibos_id').value = '';
    
    var conceptoContainer = document.getElementById('conceptoContainer');
    // Eliminar todos los conceptos excepto el primero
    while (conceptoContainer.children.length > 1) {
        conceptoContainer.removeChild(conceptoContainer.lastChild);
    }
    
    // Limpiar el primer concepto
    var firstGroup = conceptoContainer.firstElementChild;
    firstGroup.querySelectorAll('input').forEach(input => {
        input.value = '';
        input.removeAttribute('readonly');
    });
    firstGroup.querySelector('select').value = '';
    
    // Ocultar sugerencias y limpiar errores
    firstGroup.querySelector('.suggestions-container').innerHTML = '';
    firstGroup.querySelector('.suggestions-container').style.display = 'none';
    limpiarErrorStock(firstGroup.querySelector('.cantidad-input'));
    
    // Restablecer total general
    document.getElementById('total_general').value = '$0.00';
}

// Evento para confirmar generación de ticket
function confirmarGenerarTicket(idRecibos) {
    if (confirm('¿Estás seguro de generar el ticket?')) {
        document.getElementById('myModal').style.display = 'block';
        document.getElementById('recibos_id').value = idRecibos;
    } else {
        mostrarMensajeError('Se canceló la generación del ticket.');
    }
}

// Evento cuando el DOM está cargado
document.addEventListener('DOMContentLoaded', function() {
    var conceptoContainer = document.getElementById('conceptoContainer');
    var agregarConceptoBtn = document.getElementById('agregarConcepto');
    var ticketForm = document.getElementById('ticketForm');

    // Configurar el primer grupo de concepto
    if (conceptoContainer && conceptoContainer.firstElementChild) {
        setupConceptoGroup(conceptoContainer.firstElementChild);
    }

    // Evento para agregar nuevo concepto
    if (agregarConceptoBtn) {
        agregarConceptoBtn.addEventListener('click', function() {
            if (!conceptoContainer.firstElementChild) return;
            
            // Clonar el primer grupo
            var newConceptoGroup = conceptoContainer.firstElementChild.cloneNode(true);
            
            // Limpiar valores
            newConceptoGroup.querySelector('.concepto-input').value = '';
            newConceptoGroup.querySelector('.cantidad-input').value = '1';
            newConceptoGroup.querySelector('.precio-input').value = '';
            newConceptoGroup.querySelector('.precio-input').removeAttribute('readonly');
            newConceptoGroup.querySelector('.total').value = '$0.00';
            newConceptoGroup.querySelector('.categoria-select').value = '';
            
            // Limpiar sugerencias y errores
            newConceptoGroup.querySelector('.suggestions-container').innerHTML = '';
            newConceptoGroup.querySelector('.suggestions-container').style.display = 'none';
            limpiarErrorStock(newConceptoGroup.querySelector('.cantidad-input'));
            
            // Agregar botón de eliminar
            var deleteButton = document.createElement('button');
            deleteButton.textContent = 'Eliminar Concepto';
            deleteButton.className = 'eliminar-concepto btn btn-danger btn-sm';
            newConceptoGroup.appendChild(deleteButton);
            
            // Agregar al contenedor
            conceptoContainer.appendChild(newConceptoGroup);
            
            // Configurar eventos para el nuevo grupo
            setupConceptoGroup(newConceptoGroup);
            
            // Enfocar el campo de concepto
            newConceptoGroup.querySelector('.concepto-input').focus();
        });
    }

    // Evento para eliminar concepto
    if (conceptoContainer) {
        conceptoContainer.addEventListener('click', function(event) {
            if (event.target.classList.contains('eliminar-concepto')) {
                // No permitir eliminar el último concepto
                if (conceptoContainer.children.length > 1) {
                    event.target.closest('.concepto-group').remove();
                    calcularTotalGeneral();
                } else {
                    mostrarMensajeError('Debe haber al menos un concepto.');
                }
            }
        });
    }

    // Evento para calcular totales
    if (ticketForm) {
        ticketForm.addEventListener('input', function() {
            document.querySelectorAll('.concepto-group').forEach(group => {
                calcularTotalConcepto(group);
            });
            calcularTotalGeneral();
        });
    }

    // Evento para validar formulario
    if (ticketForm) {
        ticketForm.addEventListener('submit', function(event) {
            if (!validarInventario()) {
                event.preventDefault();
            }
        });
    }

    // Evento para cerrar modal
    var closeModal = document.querySelector('.close');
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            document.getElementById('myModal').style.display = 'none';
            resetearModal();
        });
    }

    // Evento para cerrar modal al hacer clic fuera
    window.addEventListener('click', function(event) {
        if (event.target == document.getElementById('myModal')) {
            document.getElementById('myModal').style.display = 'none';
            resetearModal();
        }
    });

    // Evento para convertir a mayúsculas
    document.addEventListener('input', function(event) {
        if (event.target.nodeName === 'INPUT' && event.target.type === 'text') {
            event.target.value = event.target.value.toUpperCase();
        }
    });
});