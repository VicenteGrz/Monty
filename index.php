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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <meta property="og:title" content="Monty+">
    <meta property="og:description" content="Descubre todo sobre el transporte público: pronósticos, alertas y noticias importantes">
    <meta property="og:url" content="https://montyplus.cloud/">
    <meta property="og:image" content="https://montyplus.cloud/monty.png">


    <?php include 'navbar.php'; ?>
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
        #map {
        margin-top: 60px; 
         height: calc(100vh - 60px); 
         width: 100%;
}
    </style>
</head>
<body>
    <div id="map"></div>

    <div class="menu-container">
        <select id="geojson-select">
            <option value="data/oxxo.geojson">Puntos de Recarga</option>
            <option value="data/alertas.geojson">Alertas</option>
            <option value="data/eventos.geojson">Eventos</option>
        </select>
        <button id="load-geojson" class="btn btn-primary">Mostrar</button>
    </div>


    <script>
        const map = L.map('map').setView([25.6397, -100.3045], 12);

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
                            <strong>Lugar:</strong> ${feature.properties.name}<br>
                            <strong>Recargas:</strong> ${feature.properties.recargas !== "no" ? feature.properties.recargas : "No disponible"}<br>
                        `;
                        layer.bindPopup(popupContent);
                    }
                }).addTo(map);
            });
        }

        loadGeoJSON("data/nada.geojson");

        $('#load-geojson').click(function() {
            const selectedFile = $('#geojson-select').val();
            map.eachLayer(layer => {
                if (layer instanceof L.GeoJSON) {
                    map.removeLayer(layer);
                }
            });
            loadGeoJSON(selectedFile);
        });

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
        const geocoder = L.Control.Geocoder.nominatim();
        L.Control.geocoder({ geocoder }).addTo(map);
    </script>
    </div>
</body>
</html>
