<!DOCTYPE html>
<html>
<head>
    <title>Mi ruta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.min.css" />
    <link rel="stylesheet" href="css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
   <!-- Con el siguiente style establezco el tamaño inicial del mapa al tamaño de la ventana -->
    <style>
        html, body, #map {
            height: 100%;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <!--En este div se cargará el mapa con openstreetmap -->
    <div id="map"></div>

    <script>

    // centrado del mada y nivel de zoom ajustado para centrar el municipio de escobedo
    var map = L.map('map').setView([25.6397, -100.3045], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {

        // Esta liena pone mi nombre en la esquina inferior derecha del mapa
        attribution: '&copy; Grupo Reyes'
    }).addTo(map);

    //Cambiar el icono del marcador por uno personalizado
    var customIcon = L.icon({
    iconUrl: './ubicacion.png',
    iconSize: [52, 52], // tamaño del icono
    iconAnchor: [16, 32], // punto de anclaje del icono (la parte inferior del centro)
    popupAnchor: [0, -32] // punto donde se abrirá la ventana de información (arriba del centro)
});

    // Establecer el tamaño del mapa al 100% del contenedor
    map.invalidateSize();

    // Función para cargar los marcadores
    function getMarkers() {
        $.ajax({
            url: "get_markers.php",
            dataType: "json",
            success: function(data) {
                // Eliminar los marcadores antiguos
                map.eachLayer(function(layer) {
                    if (layer instanceof L.Marker) {
                        map.removeLayer(layer);
                    }
                });

                // Añadir los nuevos marcadores
                $.each(data, function(key, val) {
                    var marker = L.marker([val.latitud, val.longitud], {icon: customIcon}).addTo(map);
                    marker.bindPopup(
                        '<div id="content">' +
                          '<div id="siteNotice">' +
                          "</div>" +
                          '<h2 id="firstHeading" class="firstHeading">'+ val.nombre +  '</h2>' +
                          '</center>'+
                          '<div id="bodyContent">' +
                          '<h3>'+  "direccion:" + ' ' + val.direccion + '</h3>' +
                          "</p>" +
                          "</div>" +
                          "</div>");
                    marker.bindTooltip(val.nombre, {
                    permanent: true,
                    direction: 'top',
                    offset: [0, -25]
                });
                });
            }
        });
    }

    // Llamar a la función cada 5 segundos
    setInterval(getMarkers, 5000);

    // Cargar los marcadores al principio
    getMarkers();
    </script>
       

        <div class="col-md-12">

<h2 class="h2s" align=center style="font-size:35px;"></h2>

<!-- Archivo PHP con la lógica para mostrar una tabla con las ubicaciones -->
<?php include('./app.php'); ?> 

</div>
</body>
</html>
