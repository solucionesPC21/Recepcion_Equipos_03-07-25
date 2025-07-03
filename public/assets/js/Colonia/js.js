document.addEventListener('DOMContentLoaded', function () {
    var registrarColoniaBtn = document.getElementById('registrarColoniaBtn');
    var modalRegistro = document.getElementById('modalRegistrarColonia');
    var cerrarModalBtn = document.getElementById('cerrarModal');
    var cerrarModalEditarBtn = document.getElementById('cerrarModalEditar');
    var modalEditarColonia = document.getElementById('modalEditarColonia');

    registrarColoniaBtn.addEventListener('click', function () {
        modalRegistro.style.display = 'block';
    });

    cerrarModalBtn.addEventListener('click', function () {
        limpiarInputs('modalRegistrarColonia'); // Llamar a la función para limpiar los inputs
        modalRegistro.style.display = 'none';
    });

    cerrarModalEditarBtn.addEventListener('click', function () {
        limpiarInputs('modalEditarColonia'); // Llamar a la función para limpiar los inputs
        modalEditarColonia.style.display = 'none';
    });

    window.addEventListener('click', function (event) {
        if (event.target == modalRegistro) {
            limpiarInputs('modalRegistrarColonia'); // Llamar a la función para limpiar los inputs
            modalRegistro.style.display = 'none';
        }
        if (event.target == modalEditarColonia) {
            limpiarInputs('modalEditarColonia'); // Llamar a la función para limpiar los inputs
            modalEditarColonia.style.display = 'none';
        }
    });

    // Limpia los inputs cuando la página se ha cargado completamente
    limpiarInputs('modalRegistrarColonia');
    limpiarInputs('modalEditarColonia');
});

var camposTexto = document.querySelectorAll('input[type="text"]');

camposTexto.forEach(function(campo) {
    campo.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
});

function limpiarInputs(modalId) {
    var modal = document.getElementById(modalId);
    var inputs = modal.querySelectorAll('input[type="text"]');
    inputs.forEach(function(input) {
        input.value = '';
    });
}

function editarColonia(event, coloniaId) {
    event.preventDefault();
    console.log('Editando colonia con ID:', coloniaId);
    
    var modalRegistro = document.getElementById('modalRegistrarColonia');
    console.log('Modal de registro:', modalRegistro);
    
    if (modalRegistro.style.display === 'block') {
        console.log('Cerrando modal de registro');
        limpiarInputs('modalRegistrarColonia'); // Llamar a la función para limpiar los inputs
        modalRegistro.style.display = 'none';
    }

    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/colonias/' + coloniaId + '/edit', true);
    xhr.onload = function () { 
        if (xhr.status === 200) {
            console.log('Solicitud AJAX exitosa');
            var colonia = JSON.parse(xhr.responseText);
            console.log('Datos de la colonia:', colonia);
            document.getElementById('nombre').value = colonia.colonia;
            console.log('Valor del campo nombre:', colonia.colonia);
            document.getElementById('formEditarColonia').action = '/colonias/' + colonia.id;
            console.log('Acción del formulario:', document.getElementById('formEditarColonia').action);
            var modalEditarColonia = document.getElementById('modalEditarColonia');
            console.log('Modal de edición:', modalEditarColonia);
            modalEditarColonia.style.display = 'block';
        } else {
            console.error('Error en la solicitud AJAX. Estado:', xhr.status);
        }
    };
    xhr.onerror = function () {
        console.error('Error de red al realizar la solicitud AJAX');
    };
    xhr.send();
}
