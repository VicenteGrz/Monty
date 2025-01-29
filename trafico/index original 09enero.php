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

    <meta property="og:title" content="Monty+">
    <meta property="og:description" content="Descubre todo sobre el transporte público: pronósticos, alertas y noticias importantes">
    <meta property="og:url" content="https://montyplus.com/">
    <meta property="og:image" content="https://montyplus.com/monty.png">

    <style>
        html, body, #map {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        #map {
            margin-top: 60px;
            height: calc(100vh - 60px);
            width: 100%;
        }

        .layers-control {
            position: absolute;
            top: 80px;
            right: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
            width: 250px;
            z-index: 1000;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
        }

        .layers-header {
            padding: 12px 16px;
            border-bottom: 1px solid #e0e0e0;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .layers-content {
            padding: 8px 0;
        }

        .layer-item {
            padding: 8px 16px;
            display: flex;
            align-items: center;
            transition: background-color 0.2s;
        }

        .layer-item:hover {
            background-color: #f5f5f5;
        }

        .layer-item input[type="checkbox"] {
            margin-right: 12px;
        }

        .layer-item label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            width: 100%;
            margin: 0;
        }

        .layer-item i {
            width: 20px;
            text-align: center;
            color: #666;
        }

        a.mapboxgl-ctrl-logo {
            display: none !important;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div id="map"></div>
    
    <div class="layers-control">
        <div class="layers-header">
            <i class="fas fa-layers"></i>
            <span>Filtros</span>
        </div>
        <div class="layers-content">
            <div class="layer-item">
                <input type="checkbox" id="trafficToggle" checked>
                <label for="trafficToggle">
                    <i class="fas fa-car"></i>
                    Tráfico
                </label>
            </div>
            <div class="layer-item">
                <input type="checkbox" id="oxxoLayer" checked>
                <label for="oxxoLayer">
                    <i class="fas fa-store"></i>
                Oxxo
                </label>
            </div>
        </div>
    </div>

    <script src='https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js'></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    
    <script>
        mapboxgl.accessToken = 'pk.eyJ1IjoidmljZW50ZXJleWVzIiwiYSI6ImNtMnBtbjBxZDBza2YyanB1YWRzcDBjOGMifQ.R5Q04DQjFAmkTjmbLpuOAw';

        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/outdoors-v12',
            center: [-100.3045, 25.6397],
            zoom: 12,
            attributionControl: false
        });
        const layerControls = {
            traffic: {
                id: 'traffic-layer',
                visible: true
            },
            oxxo: {
                id: 'oxxo',
                visible: true
            }
        };

        // Inicialización del mapa y capa de tráfico
        map.on('load', () => {
            // Agregar fuente de tráfico
            map.addSource('traffic', {
                'type': 'vector',
                'url': 'mapbox://mapbox.mapbox-traffic-v1'
            });

            // Agregar capa de tráfico
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

            // Cargar datos GeoJSON iniciales
            loadGeoJSON(["data/oxxo.geojson"]);
        });

        // Event listeners para los controles de capas
        document.getElementById('trafficToggle').addEventListener('change', (e) => {
            layerControls.traffic.visible = e.target.checked;
            map.setLayoutProperty(
                'traffic-layer',
                'visibility',
                e.target.checked ? 'visible' : 'none'
            );
        });

        // Función para cargar archivos GeoJSON
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
                                'line-cap': 'round',
                                'visibility': layerControls[layerId]?.visible ? 'visible' : 'none'
                            },
                            'paint': {
                                'line-color': '#00aae4',
                                'line-width': 7
                            }
                        });
                    });
            });
        }
    </script>
</body>
</html>