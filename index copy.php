<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monty+</title>

    <!-- Estilos CSS -->
    <link rel="stylesheet" href="css/leaflet.min.css" />
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="css/weather_widget.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

    <style>
        html, body, #map {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        .menu-container {
            position: absolute;
            top: 900px;
            left: 10px;
            z-index: 1000;
            background: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body>
    <?php
        include 'clima.php';
        echo renderWeatherWidget("Monterrey");
    ?>

    <div class="menu-container">
        <label for="geojson-select">Mostrar:</label>
        <select id="geojson-select">
            <option value="data/rutas.geojson">Estaciones</option>
            <option value="data/universidades.geojson">Universidades</option>
            <option value="data/comercial.geojson">Comercial</option>
        </select>
        <button id="load-geojson">Mostrar</button>
    </div>

    <div id="map"></div>

    <script>
        const map = L.map('map').setView([25.6397, -100.3045], 11);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; Monty+ / OpenStreetMaps'
        }).addTo(map);

        const customIcon = L.icon({
            iconUrl: './bus.png',
            iconSize: [10, 10],
            iconAnchor: [15, 30],
            popupAnchor: [0, -32]
        });

        map.invalidateSize();

        // Cargar GeoJSON
        function loadGeoJSON(file) {
            $.getJSON(file, function(data) {
                L.geoJSON(data, {
                    style: () => ({
                        color: '#00aae4',
                        weight: 7,
                        opacity: 1
                    }),
                    onEachFeature: (feature, layer) => {
                        const popupContent = `
                            <strong>Operador:</strong> ${feature.properties.operator}<br>
                            <strong>Estacion:</strong> ${feature.properties.name}<br>
                            <strong>ETA:</strong> ${feature.properties.description}
                        `;
                        layer.bindPopup(popupContent);
                    }
                }).addTo(map);
            });
        }

        // Cargar el GeoJSON por defecto
        loadGeoJSON("nada.geojson");

        $('#load-geojson').click(function() {
            const selectedFile = $('#geojson-select').val();
            map.eachLayer(layer => {
                if (layer instanceof L.GeoJSON) {
                    map.removeLayer(layer);
                }
            });
            loadGeoJSON(selectedFile);
        });

        // Obtener marcadores
        function getMarkers() {
            $.ajax({
                url: "get_markers.php",
                dataType: "json",
                success: function(data) {
                    map.eachLayer(layer => {
                        if (layer instanceof L.Marker) {
                            map.removeLayer(layer);
                        }
                    });

                    $.each(data, function(_, val) {
                        const marker = L.marker([val.latitud, val.longitud], {icon: customIcon}).addTo(map);
                        marker.bindPopup(`
                            <div id="content">
                                <h2 class="firstHeading">${val.nombre}</h2>
                                <h3>Dirección: ${val.direccion}</h3>
                            </div>
                        `);
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

        // Agregar control de geocodificación
        const geocoder = L.Control.Geocoder.nominatim();
        L.Control.geocoder({ geocoder }).addTo(map);
    </script>

    <div class="col-md-12">
        <h2 class="h2s" align="center" style="font-size:35px;"></h2>
        <?php include('./app.php'); ?> 
    </div>
</body>
</html>
