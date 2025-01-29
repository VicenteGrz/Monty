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
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css' rel='stylesheet' />
    <link href='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-directions/v4.1.1/mapbox-gl-directions.css' rel='stylesheet' />
    <link href='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.css' rel='stylesheet' />
    <script src='https://unpkg.com/@turf/turf@6/turf.min.js'></script>
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

        .map-overlay {
            position: absolute;
            bottom: 40px;
            right: 10px;
            background: rgba(255, 255, 255, 0.9);
            padding: 10px;
            border-radius: 3px;
            z-index: 1000;
        }

        .search-box {
            position: absolute;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1;
            width: 50%;
            max-width: 600px;
        }

        .mapboxgl-ctrl-geocoder {
            width: 100% !important;
            max-width: none !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .navigation-control {
            position: absolute;
            top: 80px;
            left: 10px;
            background: white;
            padding: 10px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            z-index: 1;
        }

        .current-location {
            position: absolute;
            top: 150px;
            right: 10px;
            background: white;
            padding: 10px;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            z-index: 1;
            cursor: pointer;
        }

        .route-info {
            position: absolute;
            top: 80px;
            right: 10px;
            background: white;
            padding: 15px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            z-index: 1;
            display: none;
        }

        .directions-control {
            position: absolute;
            top: 80px;
            left: 10px;
            background: white;
            padding: 15px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            z-index: 1;
            width: 300px;
            display: none;
        }

        a.mapboxgl-ctrl-logo {
            display: none !important;
        }
    </style>
</head>
<body>
    <div id="map"></div>
    
    <!-- Controles del mapa -->
    <div class="search-box">
        <div id="geocoder" class="geocoder"></div>
    </div>
    
    <div class="current-location">
        <i class="fas fa-crosshairs"></i>
    </div>
    
    <div class="directions-control" id="directions-panel">
        <h4>Direcciones</h4>
        <div class="input-group mb-3">
            <input type="text" id="origin" class="form-control" placeholder="Origen">
            <button class="btn btn-outline-secondary" type="button" id="use-current-location">
                <i class="fas fa-crosshairs"></i>
            </button>
        </div>
        <input type="text" id="destination" class="form-control mb-3" placeholder="Destino">
        <div class="btn-group w-100">
            <button class="btn btn-primary" id="get-directions">Obtener ruta</button>
            <button class="btn btn-secondary" id="clear-route">Limpiar</button>
        </div>
    </div>
    
    <div class="route-info" id="route-info">
        <h5>Información de la ruta</h5>
        <div id="route-distance"></div>
        <div id="route-duration"></div>
    </div>

    <div class="map-overlay">
        <div>
            <select id="mapStyle" class="form-control mb-2">
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

    <!-- Scripts -->
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js'></script>
    <script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-directions/v4.1.1/mapbox-gl-directions.js'></script>
    <script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min.js'></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        mapboxgl.accessToken = 'pk.eyJ1IjoidmljZW50ZXJleWVzIiwiYSI6ImNtMnBtbjBxZDBza2YyanB1YWRzcDBjOGMifQ.R5Q04DQjFAmkTjmbLpuOAw';

        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/outdoors-v12',
            center: [-100.3045, 25.6397],
            zoom: 12,
            attributionControl: false
        });

        // Inicializar el control de direcciones
        const directions = new MapboxDirections({
            accessToken: mapboxgl.accessToken,
            unit: 'metric',
            profile: 'mapbox/driving',
            alternatives: true,
            language: 'es',
            steps: true,
            controls: {
                inputs: false,
                instructions: true
            }
        });

        // Inicializar el geocodificador
        const geocoder = new MapboxGeocoder({
            accessToken: mapboxgl.accessToken,
            mapboxgl: mapboxgl,
            placeholder: 'Buscar ubicación...',
            language: 'es',
            countries: 'mx'
        });

        // Añadir controles al mapa
        document.getElementById('geocoder').appendChild(geocoder.onAdd(map));
        map.addControl(new mapboxgl.NavigationControl(), 'top-right');

        // Variables globales
        let userLocation = null;
        let navigationActive = false;
        let watchId = null;
        let userMarker = null;
        let destinationMarker = null;
        let routeLayer = null;

        // Función para obtener la ubicación actual
        function getCurrentLocation() {
            return new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(
                    position => {
                        userLocation = [position.coords.longitude, position.coords.latitude];
                        resolve(userLocation);
                    },
                    error => {
                        reject(error);
                    },
                    {
                        enableHighAccuracy: true
                    }
                );
            });
        }

        // Iniciar seguimiento de ubicación en tiempo real
        function startLocationTracking() {
            if (watchId) return;
            
            watchId = navigator.geolocation.watchPosition(
                position => {
                    const newLocation = [position.coords.longitude, position.coords.latitude];
                    
                    if (!userMarker) {
                        userMarker = new mapboxgl.Marker({
                            color: '#2196F3'
                        })
                        .setLngLat(newLocation)
                        .addTo(map);
                    } else {
                        userMarker.setLngLat(newLocation);
                    }

                    if (navigationActive) {
                        map.setCenter(newLocation);
                        updateRouteToDestination(newLocation);
                    }
                },
                error => console.error('Error tracking location:', error),
                {
                    enableHighAccuracy: true,
                    maximumAge: 0,
                    timeout: 5000
                }
            );
        }

        // Detener seguimiento de ubicación
        function stopLocationTracking() {
            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }
            if (userMarker) {
                userMarker.remove();
                userMarker = null;
            }
        }

        // Actualizar ruta al destino
        function updateRouteToDestination(currentLocation) {
            if (!destinationMarker) return;
            
            const destination = destinationMarker.getLngLat();
            
            // Obtener nueva ruta desde la ubicación actual
            directions.setOrigin(currentLocation);
            directions.setDestination([destination.lng, destination.lat]);
        }

        // Event Listeners
        document.querySelector('.current-location').addEventListener('click', async () => {
            try {
                const location = await getCurrentLocation();
                map.flyTo({
                    center: location,
                    zoom: 15
                });
                startLocationTracking();
            } catch (error) {
                console.error('Error getting location:', error);
                alert('No se pudo obtener tu ubicación. Por favor, verifica los permisos de ubicación.');
            }
        });

        // Cambio de estilo del mapa
        document.getElementById('mapStyle').addEventListener('change', (e) => {
            const style = e.target.value;
            const currentTrafficVisibility = map.getLayoutProperty('traffic-layer', 'visibility');
            
            map.setStyle(style);
            
            map.once('style.load', () => {
                // Recargar capas después del cambio de estilo
                initializeTrafficLayer();
                if (navigationActive) {
                    directions.setOrigin(userLocation);
                }
            });
        });

        // Toggle de tráfico
        document.getElementById('trafficToggle').addEventListener('change', (e) => {
            map.setLayoutProperty(
                'traffic-layer',
                'visibility',
                e.target.checked ? 'visible' : 'none'
            );
        });

        // Inicializar capa de tráfico
        function initializeTrafficLayer() {
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
        }

        // Geocoder events
        geocoder.on('result', (e) => {
            const coords = e.result.center;
            
            if (destinationMarker) {
                destinationMarker.remove();
            }
            
            destinationMarker = new mapboxgl.Marker({
                color: '#FF0000'
            })
            .setLngLat(coords)
            .addTo(map);

            document.getElementById('directions-panel').style.display = 'block';
            document.getElementById('destination').value = e.result.place_name;
            
            if (userLocation) {
                updateRouteToDestination(userLocation);
            }
        });

        // Inicialización del mapa
        map.on('load', () => {
            initializeTrafficLayer();
            
            // Cargar GeoJSON inicial
            loadGeoJSON(["data/nada.geojson"]);
        });

        // Función de carga de GeoJSON (mantenida del código original)
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
// Event listeners para el panel de direcciones (continuación)
document.getElementById('use-current-location').addEventListener('click', async () => {
    try {
        const location = await getCurrentLocation();
        document.getElementById('origin').value = 'Mi ubicación';
        if (userMarker) {
            userMarker.setLngLat(location);
        } else {
            userMarker = new mapboxgl.Marker({
                color: '#2196F3'
            })
            .setLngLat(location)
            .addTo(map);
        }
        startLocationTracking();
    } catch (error) {
        console.error('Error getting location:', error);
        alert('No se pudo obtener tu ubicación actual');
    }
});

document.getElementById('get-directions').addEventListener('click', async () => {
    const origin = document.getElementById('origin').value;
    const destination = document.getElementById('destination').value;
    
    if (!origin || !destination) {
        alert('Por favor ingresa un origen y destino');
        return;
    }

    try {
        let originCoords;
        if (origin === 'Mi ubicación') {
            originCoords = userLocation;
        } else {
            // Geocodificar el origen si no es la ubicación actual
            const response = await fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(origin)}.json?access_token=${mapboxgl.accessToken}&country=mx`);
            const data = await response.json();
            if (data.features.length > 0) {
                originCoords = data.features[0].center;
            } else {
                throw new Error('No se encontró la ubicación de origen');
            }
        }

        // Configurar la ruta
        directions.setOrigin(originCoords);
        directions.setDestination(destination);
        
        // Activar navegación
        navigationActive = true;
        document.getElementById('route-info').style.display = 'block';
        
        // Ajustar el mapa para mostrar la ruta completa
        const bounds = new mapboxgl.LngLatBounds()
            .extend(originCoords)
            .extend(destinationMarker.getLngLat());
        
        map.fitBounds(bounds, {
            padding: 100
        });

        // Comenzar seguimiento en tiempo real
        startLocationTracking();
        
        // Actualizar información de la ruta
        updateRouteInfo();
    } catch (error) {
        console.error('Error getting directions:', error);
        alert('Error al obtener la ruta. Por favor intenta de nuevo.');
    }
});

document.getElementById('clear-route').addEventListener('click', () => {
    // Limpiar la ruta y marcadores
    directions.removeRoutes();
    if (destinationMarker) {
        destinationMarker.remove();
        destinationMarker = null;
    }
    
    // Detener navegación
    navigationActive = false;
    stopLocationTracking();
    
    // Limpiar campos
    document.getElementById('origin').value = '';
    document.getElementById('destination').value = '';
    
    // Ocultar paneles
    document.getElementById('route-info').style.display = 'none';
    document.getElementById('directions-panel').style.display = 'none';
});

// Función para actualizar la información de la ruta
function updateRouteInfo() {
    directions.on('route', (event) => {
        const route = event.route[0];
        if (route) {
            const distance = (route.distance / 1000).toFixed(1);
            const duration = Math.round(route.duration / 60);
            
            document.getElementById('route-distance').textContent = 
                `Distancia: ${distance} km`;
            document.getElementById('route-duration').textContent = 
                `Tiempo estimado: ${duration} min`;
        }
    });
}

// Función para manejar errores de navegación
function handleNavigationError(error) {
    console.error('Error de navegación:', error);
    alert('Se produjo un error durante la navegación. Por favor, intenta de nuevo.');
    navigationActive = false;
    stopLocationTracking();
}

// Añadir control de escala al mapa
map.addControl(new mapboxgl.ScaleControl({
    maxWidth: 100,
    unit: 'metric'
}), 'bottom-left');

// Función para actualizar el bearing del mapa basado en el movimiento del usuario
function updateMapBearing(oldPos, newPos) {
    if (!oldPos) return;
    
    const bearing = turf.bearing(
        turf.point([oldPos.coords.longitude, oldPos.coords.latitude]),
        turf.point([newPos.coords.longitude, newPos.coords.latitude])
    );
    
    map.easeTo({
        bearing: bearing,
        duration: 1000
    });
}

// Sistema de recálculo de ruta cuando el usuario se desvía
let lastKnownRoute = null;
const ROUTE_DEVIATION_THRESHOLD = 50; // metros

function checkRouteDeviation(userPosition, route) {
    if (!route || !userPosition) return false;
    
    const userPoint = turf.point([userPosition[0], userPosition[1]]);
    const routeLine = turf.lineString(route.geometry.coordinates);
    
    const distance = turf.pointToLineDistance(userPoint, routeLine, {units: 'meters'});
    
    return distance > ROUTE_DEVIATION_THRESHOLD;
}

// Función para actualizar información de tráfico en tiempo real
function updateTrafficInfo() {
    const bounds = map.getBounds();
    const url = `https://api.mapbox.com/traffic/v1/mapbox/flow/tile/...`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            // Actualizar la capa de tráfico con nueva información
            if (map.getSource('traffic')) {
                map.getSource('traffic').setData(data);
            }
        })
        .catch(error => console.error('Error actualizando información de tráfico:', error));
}

// Actualizar el tráfico cada 5 minutos
setInterval(updateTrafficInfo, 300000);

// Añadir botón de modo de navegación
const navModeButton = document.createElement('button');
navModeButton.className = 'btn btn-primary navigation-mode';
navModeButton.innerHTML = '<i class="fas fa-compass"></i> Modo Navegación';
navModeButton.style.position = 'absolute';
navModeButton.style.top = '200px';
navModeButton.style.right = '10px';
document.body.appendChild(navModeButton);

// Toggle del modo de navegación
let navigationMode = false;
navModeButton.addEventListener('click', () => {
    navigationMode = !navigationMode;
    if (navigationMode) {
        map.setPitch(60);
        map.setBearing(0);
        navModeButton.classList.add('active');
    } else {
        map.setPitch(0);
        map.setBearing(0);
        navModeButton.classList.remove('active');
    }
});