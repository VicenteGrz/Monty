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
            top: 20px;
            left: 10px;
            z-index: 1000;
            background: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            width: 250px;
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

        .map-overlay select {
            margin-bottom: 10px;
            width: 200px;
            padding: 5px;
        }

        a.mapboxgl-ctrl-logo {
            display: none !important;
        }
    </style>
</head>
<body>
    <div id="map"></div>
    <div class="map-overlay">
        <div>
            <select id="mapStyle" class="form-control">
                <option value="mapbox://styles/mapbox/outdoors-v12">Outdoors</option>
                <option value="mapbox://styles/mapbox/dark-v11">Dark</option>
                <option value="mapbox://styles/mapbox/satellite-v9">Satélite</option>
                <option value="mapbox://styles/mapbox/satellite-streets-v12">Satélite con Calles</option>
                <option value="mapbox://styles/mapbox/navigation-day-v1">Navegación (Día)</option>
                <option value="mapbox://styles/mapbox/navigation-night-v1">Navegación (Noche)</option>
            </select>
        </div>
        <div>
            <input type="checkbox" id="trafficToggle" checked>
            <label for="trafficToggle">Mostrar Tráfico</label>
        </div>
    </div>
    <script>
        mapboxgl.accessToken = 'pk.eyJ1IjoidmljZW50ZXJleWVzIiwiYSI6ImNtMnBtbjBxZDBza2YyanB1YWRzcDBjOGMifQ.R5Q04DQjFAmkTjmbLpuOAw';

        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/outdoors-v12',
            center: [-100.3045, 25.6397],
            zoom: 12,
            attributionControl: false 
        });

        // Map style change handler
        document.getElementById('mapStyle').addEventListener('change', (e) => {
            const style = e.target.value;
            const currentTrafficVisibility = map.getLayoutProperty('traffic-layer', 'visibility');
            
            map.setStyle(style);
            
            // Re-add traffic layer after style change
            map.once('style.load', () => {
                map.addSource('traffic', {
                    'type': 'vector',
                    'url': 'mapbox://mapbox.mapbox-traffic-v1'
                });

                map.addLayer({
                    'id': 'traffic-layer',
                    'type': 'line',
                    'source': 'traffic',
                    'source-layer': 'traffic',
                    'layout': {
                        'visibility': currentTrafficVisibility
                    },
                    'paint': {
                        'line-width': 2.5,
                        'line-color': [
                            'match',
                            ['get', 'congestion'],
                            'low', '#16e299',
                            'moderate', '#ffd143',
                            'heavy', '#f75043',
                            'severe', '#a92727',
                            '#ffffff'
                        ]
                    }
                });
                
                // Reload GeoJSON layers
                loadGeoJSON(["data/nada.geojson"]);
            });
        });

        map.on('load', () => {
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
                    'line-width': 2.5,
                    'line-color': [
                        'match',
                        ['get', 'congestion'],
                        'low', '#16e299',
                        'moderate', '#ffd143',
                        'heavy', '#f75043',
                        'severe', '#a92727',
                        '#ffffff'
                    ]
                }
            });
        });

        document.getElementById('trafficToggle').addEventListener('change', (e) => {
            map.setLayoutProperty(
                'traffic-layer',
                'visibility',
                e.target.checked ? 'visible' : 'none'
            );
        });

        function loadGeoJSON(files) {
            files.forEach(file => {
                fetch(file)
                    .then(response => response.json())
                    .then(data => {
                        const layerId = file.split('/').pop().split('.')[0]; 
                        if (map.getLayer(layerId)) {
                            map.removeLayer(layerId);
                            map.removeSource(layerId);
                        }

                        map.addSource(layerId, {
                            type: 'geojson',
                            data: data
                        });
                        map.addLayer({
                            'id': layerId,
                            'type': 'line',
                            'source': layerId,
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
            });
        }
        
        document.getElementById('load-geojson').addEventListener('click', () => {
            const selectedFiles = Array.from(document.getElementById('geojson-select').selectedOptions)
                                        .map(option => option.value);
            loadGeoJSON(selectedFiles);
        });
        loadGeoJSON(["data/nada.geojson"]);
    </script>
</body>
</html>