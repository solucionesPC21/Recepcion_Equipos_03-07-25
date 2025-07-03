function buscarRecibos() {
    // Obtener el valor del campo de entrada con el id 'searchInput'
    var searchTerm = document.getElementById('searchInput').value;
    
    // Definir la URL a la que se enviará la solicitud AJAX
    var url = '/buscarCompleto'; // Utilizar la URL absoluta o relativa según corresponda
    
    // Realizar una solicitud AJAX al servidor
    $.ajax({
        // Especificar la URL a la que se enviará la solicitud
        url: url,
        
        // Especificar el método HTTP que se utilizará para la solicitud
        type: 'GET',
        
        // Especificar los datos que se enviarán con la solicitud
        data: { search: searchTerm }, // En este caso, se envía el término de búsqueda
        
        // Manejar la respuesta exitosa de la solicitud
        success: function(response) {
            // Actualizar el contenido del elemento HTML con el id 'recibosBody'
            // con el HTML devuelto por el servidor en la propiedad 'recibosBodyHtml' de la respuesta
            $('#recibosBody').html(response.recibosBodyHtml);
            
            // Actualizar el contenido del elemento HTML con el id 'totalTiposEquipo'
            // con el texto que indica el total de tipos de equipo recibidos,
            // utilizando el valor devuelto por el servidor en la propiedad 'totalTiposEquipo' de la respuesta
           
        },
        
        // Manejar cualquier error que ocurra durante la solicitud
        error: function(jqXHR, textStatus, errorThrown) {
            // Imprimir un mensaje de error en la consola con los detalles del error
            console.log('Error en la conexión con el controlador:', textStatus, errorThrown);
        }
    });
}

//alerta model success y error
document.addEventListener('DOMContentLoaded', function() {
    var successAlertModal = document.getElementById('success-alert-modal');
    var errorAlertModal = document.getElementById('error-alert-modal');
    var errorAlert = document.getElementById('error-alert');

    var successProgressBar = document.getElementById('success-progress-bar');
    var errorProgressBarModal = document.getElementById('error-progress-bar');
    var errorProgressBarAlert = document.getElementById('error-progress-bar');

    if (successAlertModal && successProgressBar) {
        setTimeout(function () {
            successProgressBar.style.width = '100%';
        }, 10); // Retraso para permitir la renderización inicial

        setTimeout(function () {
            successAlertModal.classList.add('hidden');
            setTimeout(function () {
                successAlertModal.style.display = 'none';
            }, 500); // 0.5 segundos para la transición de opacidad
        }, 2000); // 2 segundos para que la barra se llene
    }

    if (errorAlertModal && errorProgressBarModal) {
        setTimeout(function () {
            errorProgressBarModal.style.width = '100%';
        }, 10); // Retraso para permitir la renderización inicial

        setTimeout(function () {
            errorAlertModal.classList.add('hidden');
            setTimeout(function () {
                errorAlertModal.style.display = 'none';
            }, 500); // 0.5 segundos para la transición de opacidad
        }, 2000); // 2 segundos para que la barra se llene
    }

    if (errorAlert && errorProgressBarAlert) {
        setTimeout(function () {
            errorProgressBarAlert.style.width = '100%';
        }, 10); // Retraso para permitir la renderización inicial

        setTimeout(function () {
            errorAlert.classList.add('hidden');
            setTimeout(function () {
                errorAlert.style.display = 'none';
            }, 500); // 0.5 segundos para la transición de opacidad
        }, 2000); // 2 segundos para que la barra se llene
    }
});
