function abrirNotaModal(reciboId) {
    if (!reciboId) {
        console.error("El ID del recibo no se ha proporcionado correctamente.");
        return;
    }

    var url = '/recibos/nota/' + reciboId;

    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            document.getElementById('notaContent').innerText = response.nota || 'No hay nota disponible.';
            var modal = $('#notaModal');
            modal.attr('data-recibo-id', reciboId);
            console.log('ID del recibo asignado al modal:', reciboId); // Añade esta línea
            modal.modal({
                backdrop: 'static',
                keyboard: false
            });
            modal.modal('show');
        },
        error: function() {
            alert('Error al obtener la nota.');
        }
    });
}

function cerrarNotaModal() {
    $('#notaModal').modal('hide');
}

// Asegúrate de que el backdrop se elimine y el estado del cuerpo se restablezca
$('#notaModal').on('hidden.bs.modal', function () {
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    $('body').css('padding-right', '');

    // Restablecer contenido y estado del modal al cerrarse
    document.getElementById('notaContent').innerText = '';
    document.getElementById('notaInput').value = '';
    document.getElementById('notaInput').style.display = 'none';
    document.getElementById('guardarNotaButton').style.display = 'none';
    document.getElementById('editNotaButton').style.display = 'inline-block';
    document.getElementById('notaContent').style.display = 'block';
});

function habilitarEdicionNota() {
    var notaContent = document.getElementById('notaContent');
    var notaInput = document.getElementById('notaInput');
    var guardarNotaButton = document.getElementById('guardarNotaButton');
    var editNotaButton = document.getElementById('editNotaButton');

    notaInput.value = notaContent.innerText;
    notaInput.style.display = 'block';
    guardarNotaButton.style.display = 'inline-block';
    editNotaButton.style.display = 'none';
    notaContent.style.display = 'none';
}

function guardarNota() {
    var notaInput = document.getElementById('notaInput');
    if (!notaInput) {
        console.error('El elemento notaInput no se encontró.');
        return;
    }

    var reciboId = $('#notaModal').attr('data-recibo-id');
    console.log('ID del recibo recuperado del modal:', reciboId); // Añade esta línea

    if (!reciboId) {
        console.error('El ID del recibo no se ha proporcionado correctamente.');
        return;
    }

    $.ajax({
        url: '/recibos/agregarnota' + reciboId,
        type: 'GET',
        data: {
            id: reciboId,
            nota: notaInput.value,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            document.getElementById('notaContent').innerText = notaInput.value;
            cerrarNotaModal();
        },
        error: function() {
            alert('Error al guardar la nota.');
        }
    });
}
