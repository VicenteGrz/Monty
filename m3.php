<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comandos de voz</title>
    
    <!-- Material Web (M3) CSS -->
    <link rel="stylesheet" href="https://unpkg.com/@material/web@latest/dist/material-web.min.css">
    
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
        }

        .container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
            width: 350px;
            padding: 24px;
            text-align: center;
        }

        h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 16px;
        }

        /* Botones con Material Design */
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

        /* Entrada de texto con Material Design */
        .mdc-text-field {
            width: 100%;
            margin-bottom: 24px;
        }

        .mdc-text-field__input {
            font-size: 16px;
        }

        /* Estilos adicionales */
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

        .buttons-container {
            display: flex;
            justify-content: space-between;
        }

        #directions {
            margin-top: 16px;
        }

        .mdc-icon-button {
            font-size: 24px;
        }
    </style>

    <!-- Mapbox CSS -->
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.11.1/mapbox-gl.css" rel="stylesheet">
</head>
<body>

    <div class="container">
        <h1>Comandos de voz</h1>

        <!-- Botones de Material Design para iniciar y detener el reconocimiento -->
        <div class="buttons-container">
            <button class="mdc-button mdc-button--raised" id="start-recognition">Activar</button>
            <button class="mdc-button mdc-button--raised" id="stop-recognition" disabled>Desactivar</button>
        </div>

        <h3>Texto detectado</h3>
        <p class="output-text" id="result"></p>
        
        <h3>Resultado más cercano</h3>
        <p class="output-subtext" id="coordinates"></p>

        <h3>Indicaciones de ruta</h3>
        <p class="output-subtext" id="directions"></p>
    </div>

    <script>
        // Cargar Material Web
        const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
        const resultElement = document.getElementById('result');
        const coordinatesElement = document.getElementById('coordinates');
        const directionsElement = document.getElementById('directions');
        const startButton = document.getElementById('start-recognition');
        const stopButton = document.getElementById('stop-recognition');

        recognition.lang = 'es-MX';
        recognition.interimResults = false;

        // Tu Mapbox Access Token (cámbialo por el tuyo)
        const mapboxAccessToken = 'pk.eyJ1IjoidmljZW50ZXJleWVzIiwiYSI6ImNtMnBtbjBxZDBza2YyanB1YWRzcDBjOGMifQ.R5Q04DQjFAmkTjmbLpuOAw';

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

        // Función para obtener las indicaciones de ruta usando la API de direcciones de Mapbox
        async function getDirections(userLat, userLon, placeLat, placeLon) {
            const directionsUrl = `https://api.mapbox.com/directions/v5/mapbox/driving/${userLon},${userLat};${placeLon},${placeLat}?steps=true&geometries=geojson&language=es&access_token=${mapboxAccessToken}`;
            try {
                const response = await fetch(directionsUrl);
                const data = await response.json();
                const directions = data.routes[0].legs[0].steps.map(step => step.maneuver.instruction);
                directionsElement.innerHTML = directions.join('<br>');
            } catch (error) {
                directionsElement.textContent = "Error al obtener las indicaciones de ruta.";
            }
        }

        // Función que se ejecuta cuando el reconocimiento de voz detecta texto
        recognition.addEventListener('result', async (event) => {
            const transcript = event.results[0][0].transcript;
            resultElement.textContent = transcript;

            // Obtener la ubicación del usuario
            try {
                const position = await getUserLocation();
                const userLat = position.coords.latitude;
                const userLon = position.coords.longitude;

                // Buscar lugares relacionados con el texto detectado
                const places = await getPlaceCoordinates(transcript);

                if (places.length > 0) {
                    // Encontrar el lugar más cercano
                    const closestPlace = await findClosestPlace(userLat, userLon, places);
                    if (closestPlace) {
                        coordinatesElement.textContent = `Resultado: ${closestPlace.name} (Lat: ${closestPlace.lat}, Lon: ${closestPlace.lon})`;

                        // Obtener las indicaciones de ruta
                        await getDirections(userLat, userLon, closestPlace.lat, closestPlace.lon);
                    } else {
                        coordinatesElement.textContent = "No se encontraron resultados cercanos.";
                        directionsElement.textContent = "";
                    }
                } else {
                    coordinatesElement.textContent = "No se encontraron resultados.";
                    directionsElement.textContent = "";
                }
            } catch (error) {
                coordinatesElement.textContent = "Error al obtener la ubicación o lugares.";
                directionsElement.textContent = "";
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
