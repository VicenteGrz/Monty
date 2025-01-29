<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba Comandos</title>
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
            border-radius: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 350px;
            padding: 20px;
            text-align: center;
        }

        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            margin: 10px;
            font-size: 16px;
            border-radius: 50px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        button:hover:enabled {
            background-color: #45a049;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        #result {
            font-size: 18px;
            color: #333;
            font-weight: bold;
            margin-top: 10px;
        }

        #coordinates {
            font-size: 16px;
            color: #555;
            margin-top: 10px;
        }

        h3 {
            font-size: 18px;
            color: #555;
            margin-top: 20px;
        }

        .buttons-container {
            display: flex;
            justify-content: space-around;
        }

        #start-recognition, #stop-recognition {
            font-size: 24px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Comandos de voz</h1>

        <!-- Botones para iniciar y detener reconocimiento -->
        <div class="buttons-container">
            <button id="start-recognition">Activar</button>
            <button id="stop-recognition" disabled>Desactivar</button>
        </div>

        <h3>Texto detectado</h3>
        <p id="result"></p>
        <h3>Resultado mas cercano</h3>
        <p id="coordinates"></p>
    </div>

    <script>
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
