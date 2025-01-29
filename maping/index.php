<!DOCTYPE html>
<html>
<head>
    <title>Mi ruta</title>
    <link rel="stylesheet" href="css/leaflet.min.css" />
    <link rel="stylesheet" href="css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/weather_widget.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>
    <style>
        html, body, #map {
            height: 100%;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
<?php
    include 'weather_widget.php';
    echo renderWeatherWidget("Monterrey");
?>
    <div id="map"></div>
    <script>
    var map = L.map('map').setView([25.6397, -100.3045], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; Grupo Reyes'
    }).addTo(map);

    var customIcon = L.icon({
        iconUrl: './ubicacion1.png',
        iconSize: [10, 10], 
        iconAnchor: [16, 32], 
        popupAnchor: [0, -32] 
    });

    map.invalidateSize();

    function getMarkers() {
        $.ajax({
            url: "get_markers.php",
            dataType: "json",
            success: function(data) {
                map.eachLayer(function(layer) {
                    if (layer instanceof L.Marker) {
                        map.removeLayer(layer);
                    }
                });

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
    setInterval(getMarkers, 60000);
    getMarkers();

    // Add the geocoder control to the map
    var geocoder = L.Control.Geocoder.nominatim();
    L.Control.geocoder({
        geocoder: geocoder
    }).addTo(map);

    // Variables globales para almacenar el control de enrutamiento y las coordenadas de la ruta
    var controlRouting;
    var routeCoordinates = [];

    // Función para actualizar la ruta
    function updateRoute() {
        if (routeCoordinates.length >= 2) {
            // Elimina la capa de ruta existente si hay alguna
            if (controlRouting) {
                map.removeControl(controlRouting);
            }

            // Añadir el control de enrutamiento al mapa
            controlRouting = L.Routing.control({
                waypoints: routeCoordinates.map(coord => L.latLng(coord.lat, coord.lng)),
                routeWhileDragging: true
            }).addTo(map);
        }
    }

    // Añadir una función para permitir al usuario seleccionar puntos para la ruta
    map.on('click', function(e) {
        // Agregar punto a la lista de coordenadas
        routeCoordinates.push({ lat: e.latlng.lat, lng: e.latlng.lng });
        
        // Actualizar la ruta con los nuevos puntos
        updateRoute();
    });

    </script>

    <div class="col-md-12">
        <h2 class="h2s" align=center style="font-size:35px;"></h2>
        <?php include('./app.php'); ?> 
    </div>
</body>
</html>
