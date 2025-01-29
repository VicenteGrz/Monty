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
        <h3>Coordenadas</h3>
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

        // Lista de palabras clave
        const keywords = ["Fundidora", "Apodaca", "Monterrey", "Pesquería"];

        // Función para obtener las coordenadas usando la API de OpenStreetMap
        async function getCoordinates(city) {
            const apiUrl = `https://nominatim.openstreetmap.org/search?city=${encodeURIComponent(city)}&format=json&addressdetails=1&limit=1`;
            try {
                const response = await fetch(apiUrl);
                const data = await response.json();
                if (data.length > 0) {
                    const { lat, lon } = data[0];
                    coordinatesElement.textContent = `Lat: ${lat}, Lon: ${lon}`;
                } else {
                    coordinatesElement.textContent = "No se encontraron coordenadas.";
                }
            } catch (error) {
                coordinatesElement.textContent = "Error al obtener las coordenadas.";
            }
        }

        // Función que se ejecuta cuando el reconocimiento de voz detecta texto
        recognition.addEventListener('result', (event) => {
            const transcript = event.results[0][0].transcript;
            resultElement.textContent = transcript;

            // Buscar palabras clave en el texto detectado
            const foundCities = keywords.filter(city => transcript.toLowerCase().includes(city.toLowerCase()));

            if (foundCities.length > 0) {
                // Si se encuentra alguna palabra clave, obtener coordenadas
                getCoordinates(foundCities[0]);
            } else {
                coordinatesElement.textContent = "No se detecto algun lugar";
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
