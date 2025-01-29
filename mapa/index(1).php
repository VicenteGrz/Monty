<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monty+</title>

    <!-- Estilos CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="css/weather_widget.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Mapbox CSS -->
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css' rel='stylesheet' />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />

    <!-- Scripts -->
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js'></script>
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
        
        .map-overlay {
            position: absolute;
            bottom: 40px;
            right: 10px;
            background: rgba(255, 255, 255, 0.9);
            padding: 10px;
            border-radius: 3px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div id="map"></div>
    <div class="map-overlay">
        <div>
            <input type="checkbox" id="trafficToggle" checked>
            <label for="trafficToggle">Mostrar Tráfico</label>
        </div>
    </div>

    <div class="menu-container">
        <select id="geojson-select">
            <option value="data/uvmunido.geojson">UVM</option>
            <option value="data/ecovia2.geojson">Ecovia</option>
            <option value="data/rutapuntos.geojson">Ecovia</option>
            <option value="data/527apodaca.geojson">527 Apodaca</option>
        </select>
        <button id="load-geojson" class="btn btn-primary">Mostrar Ruta</button>
    </div>

    <script>
        // Reemplaza con tu token de Mapbox
        mapboxgl.accessToken = 'pk.eyJ1IjoidmljZW50ZXJleWVzIiwiYSI6ImNtMnBtbjBxZDBza2YyanB1YWRzcDBjOGMifQ.R5Q04DQjFAmkTjmbLpuOAw';

        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [-100.3045, 25.6397],
            zoom: 12,
            attributionControl: false // Deshabilita el logo de Mapbox
        });

        map.on('load', () => {
            // Agregar capa de tráfico
            map.addSource('traffic', {
                'type': 'vector',
                'url': 'mapbox://mapbox.mapbox-traffic-v1'
            });

            map.addLayer({
                'id': 'traffic-layer',
                'type': 'line',
                'source': 'traffic',
                'source-layer': 'traffic',
                'paint': {
                    'line-width': 2,
                    'line-color': [
                        'match',
                        ['get', 'congestion'],
                        'low', '#00ff00',
                        'moderate', '#ffff00',
                        'heavy', '#ff0000',
                        'severe', '#800000',
                        '#ffffff'
                    ]
                }
            });
        });

        // Control para mostrar/ocultar tráfico
        document.getElementById('trafficToggle').addEventListener('change', (e) => {
            map.setLayoutProperty(
                'traffic-layer',
                'visibility',
                e.target.checked ? 'visible' : 'none'
            );
        });

        // Función para cargar GeoJSON
        function loadGeoJSON(file) {
            fetch(file)
                .then(response => response.json())
                .then(data => {
                    if (map.getSource('route')) {
                        map.removeLayer('route');
                        map.removeSource('route');
                    }

                    map.addSource('route', {
                        type: 'geojson',
                        data: data
                    });

                    map.addLayer({
                        'id': 'route',
                        'type': 'line',
                        'source': 'route',
                        'layout': {
                            'line-join': 'round',
                            'line-cap': 'round'
                        },
                        'paint': {
                            'line-color': '#00aae4',
                            'line-width': 7
                        }
                    });
                });
        }

        // Cargar el GeoJSON por defecto
        loadGeoJSON("data/nada.geojson");

        document.getElementById('load-geojson').addEventListener('click', () => {
            const selectedFile = document.getElementById('geojson-select').value;
            loadGeoJSON(selectedFile);
        });

        // Función para obtener marcadores
        function getMarkers() {
            fetch("get_markers.php")
                .then(response => response.json())
                .then(data => {
                    // Eliminar marcadores existentes
                    document.querySelectorAll('.marker').forEach(marker => marker.remove());

                    data.forEach(val => {
                        const el = document.createElement('div');
                        el.className = 'marker';
                        el.style.backgroundImage = 'url(./bus.png)';
                        el.style.width = '10px';
                        el.style.height = '10px';
                        el.style.backgroundSize = '100%';

                        new mapboxgl.Marker(el)
                            .setLngLat([val.longitud, val.latitud])
                            .setPopup(new mapboxgl.Popup().setHTML(`
                                <div id="content">
                                    <h2 class="firstHeading">${val.nombre}</h2>
                                    <h3>Dirección: ${val.direccion}</h3>
                                </div>
                            `))
                            .addTo(map);
                    });
                });
        }

        setInterval(getMarkers, 60000);
        getMarkers();
    </script>
</body>
</html>
