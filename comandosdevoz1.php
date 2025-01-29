<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comandos de voz con mapa</title>
    
    <!-- Material Web (M3) CSS -->
    <link rel="stylesheet" href="https://unpkg.com/@material/web@latest/dist/material-web.min.css">
    
    <!-- Mapbox GL JS CSS -->
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.11.1/mapbox-gl.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }

        .container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
            width: 350px;
            padding: 24px;
            text-align: center;
            z-index: 1;
        }

        h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 16px;
        }

        .mdc-button {
            width: 48%;
            font-size: 16px;
            padding: 12px 16px;
            border-radius: 8px;
            margin-top: 12px;
        }

        .mdc-button:disabled {
            background-color: #e0e0e0;
            cursor: not-allowed;
        }

        .mdc-button:hover:enabled {
            background-color: #4CAF50;
        }

        .mdc-text-field {
            width: 100%;
            margin-bottom: 24px;
        }

        .mdc-text-field__input {
            font-size: 16px;
        }

        .output-text {
            font-size: 18px;
            color: #333;
            font-weight: bold;
            margin-top: 12px;
        }

        .output-subtext {
            font-size: 16px;
            color: #555;
        }

        h3 {
            font-size: 18px;
            color: #555;
            margin-top: 16px;
        }

        #map {
            width: 100%;
            height: 400px;
            margin-top: 16px;
        }

        .mdc-icon-button {
            font-size: 24px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Comandos de voz</h1>

        <div class="buttons-container">
            <button class="mdc-button mdc-button--raised" id="start-recognition">Activar</button>
            <button class="mdc-button mdc-button--raised" id="stop-recognition" disabled>Desactivar</button>
        </div>

        <h3>Texto detectado</h3>
        <p class="output-text" id="result"></p>
        
        <h3>Resultado más cercano</h3>
        <p class="output-subtext" id="coordinates"></p>
    </div>

    <!-- Map container -->
    <div id="map"></div>

    <!-- Mapbox GL JS Script -->
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.11.1/mapbox-gl.js"></script>

    <script>
        // Inicializar Mapbox
        mapboxgl.accessToken = 'pk.eyJ1IjoidmljZW50ZXJleWVzIiwiYSI6ImNtMnBtbjBxZDBza2YyanB1YWRzcDBjOGMifQ.R5Q04DQjFAmkTjmbLpuOAw'; // Reemplaza con tu token de Mapbox

        // Inicializar mapa
        const map = new mapboxgl.Map({
            container: 'map', 
            style: 'mapbox://styles/mapbox/streets-v11', 
            center: [-100.313821, 25.680522], 
            zoom: 11
        });

        // Variables globales
        const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
        const resultElement = document.getElementById('result');
        const coordinatesElement = document.getElementById('coordinates');
        const startButton = document.getElementById('start-recognition');
        const stopButton = document.getElementById('stop-recognition');

        recognition.lang = 'es-MX';
        recognition.interimResults = false;

        // Función para obtener la ubicación del usuario
        function getUserLocation() {
            return new Promise((resolve, reject) => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(resolve, reject);
                } else {
                    reject("Geolocalización no soportada");
                }
            });
        }

        // Función para calcular la distancia entre dos puntos geográficos (en kilómetros)
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radio de la Tierra en km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        // Función para obtener las coordenadas de un lugar usando OpenStreetMap
        async function getPlaceCoordinates(place) {
            const apiUrl = `https://nominatim.openstreetmap.org/search?city=${encodeURIComponent(place)}&format=json&addressdetails=1&limit=5`;
            try {
                const response = await fetch(apiUrl);
                const data = await response.json();
                return data.map(item => ({
                    name: item.display_name,
                    lat: parseFloat(item.lat),
                    lon: parseFloat(item.lon)
                }));
            } catch (error) {
                return [];
            }
        }

        // Función para obtener el lugar más cercano
        async function findClosestPlace(userLat, userLon, places) {
            let closestPlace = null;
            let minDistance = Infinity;

            for (const place of places) {
                const distance = calculateDistance(userLat, userLon, place.lat, place.lon);
                if (distance < minDistance) {
                    minDistance = distance;
                    closestPlace = place;
                }
            }

            return closestPlace;
        }

        // Función para obtener las indicaciones de ruta usando la API de Mapbox Directions
        async function getDirections(userLat, userLon, placeLat, placeLon) {
            const directionsUrl = `https://api.mapbox.com/directions/v5/mapbox/driving/${userLon},${userLat};${placeLon},${placeLat}?steps=true&geometries=geojson&access_token=${mapboxgl.accessToken}`;
            
            try {
                const response = await fetch(directionsUrl);
                const data = await response.json();

                // Comprobar si la respuesta tiene rutas disponibles
                if (data.routes && data.routes.length > 0) {
                    // Eliminar la capa si existe
                    if (map.getLayer('route')) {
                        map.removeLayer('route');
                    }

                    // Eliminar la fuente si existe
                    if (map.getSource('route')) {
                        map.removeSource('route');
                    }

                    // Dibujar la ruta en el mapa
                    const route = data.routes[0].geometry;
                    map.addSource('route', {
                        type: 'geojson',
                        data: {
                            type: 'Feature',
                            geometry: route
                        }
                    });
                    map.addLayer({
                        id: 'route',
                        type: 'line',
                        source: 'route',
                        paint: {
                            'line-color': '#3887be',
                            'line-width': 5
                        }
                    });

                    // Centrar el mapa en la ruta
                    const bounds = new mapboxgl.LngLatBounds();
                    route.coordinates.forEach(coord => bounds.extend(coord));
                    map.fitBounds(bounds, { padding: 20 });
                } else {
                    coordinatesElement.textContent = "No se encontraron rutas disponibles.";
                }
            } catch (error) {
                console.error("Error al obtener las indicaciones de ruta con tráfico", error);
                coordinatesElement.textContent = "Error al obtener las indicaciones de ruta.";
            }
        }

        // Función para eliminar el prefijo "quiero ir a "
        function removePrefix(text) {
            const prefix = "quiero ir a ";
            if (text.toLowerCase().startsWith(prefix)) {
                return text.slice(prefix.length);
            }
            return text;
        }

        recognition.addEventListener('result', async (event) => {
            const transcript = event.results[0][0].transcript;
            const cleanedTranscript = removePrefix(transcript);
            resultElement.textContent = cleanedTranscript;

            try {
                const position = await getUserLocation();
                const userLat = position.coords.latitude;
                const userLon = position.coords.longitude;

                const places = await getPlaceCoordinates(cleanedTranscript);

                if (places.length > 0) {
                    const closestPlace = await findClosestPlace(userLat, userLon, places);
                    if (closestPlace) {
                        coordinatesElement.textContent = `Resultado: ${closestPlace.name} (Lat: ${closestPlace.lat}, Lon: ${closestPlace.lon})`;
                        await getDirections(userLat, userLon, closestPlace.lat, closestPlace.lon);
                    } else {
                        coordinatesElement.textContent = "No se encontraron resultados cercanos.";
                    }
                } else {
                    coordinatesElement.textContent = "No se encontraron resultados.";
                }
            } catch (error) {
                coordinatesElement.textContent = "Error al obtener la ubicación o lugares.";
            }
        });

        recognition.addEventListener('end', () => {
            startButton.disabled = false;
            stopButton.disabled = true;
        });

        startButton.addEventListener('click', () => {
            recognition.start();
            startButton.disabled = true;
            stopButton.disabled = false;
        });

        stopButton.addEventListener('click', () => {
            recognition.stop();
            startButton.disabled = false;
            stopButton.disabled = true;
        });
    </script>
</body>
</html>
