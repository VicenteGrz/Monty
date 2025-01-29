<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Speech API con PHP</title>
</head>
<body>
    <h1>Prueba de Web Speech API (Español de México)</h1>

    <button id="start-recognition">Iniciar Reconocimiento de Voz</button>
    <button id="stop-recognition" disabled>Detener Reconocimiento</button>

    <h3>Texto Reconocido:</h3>
    <p id="result"></p>

    <h3>Sintetizar Texto:</h3>
    <input type="text" id="text-to-speak" placeholder="Escribe algo...">
    <button id="speak-button">Hablar</button>

    <script>
        // Variables
        const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
        const synth = window.speechSynthesis;
        const resultElement = document.getElementById('result');
        const startButton = document.getElementById('start-recognition');
        const stopButton = document.getElementById('stop-recognition');
        const speakButton = document.getElementById('speak-button');
        const textInput = document.getElementById('text-to-speak');
        
        // Configurar Speech Recognition para español de México (es-MX)
        recognition.lang = 'es-MX';  // Cambiar a español de México
        recognition.interimResults = false;  // Solo resultados finales

        // Iniciar reconocimiento
        startButton.addEventListener('click', () => {
            recognition.start();
            startButton.disabled = true;
            stopButton.disabled = false;
        });

        // Detener reconocimiento
        stopButton.addEventListener('click', () => {
            recognition.stop();
            startButton.disabled = false;
            stopButton.disabled = true;
        });

        // Manejo del resultado del reconocimiento
        recognition.addEventListener('result', (event) => {
            const transcript = event.results[0][0].transcript;
            resultElement.textContent = transcript;
        });

        recognition.addEventListener('end', () => {
            startButton.disabled = false;
            stopButton.disabled = true;
        });

        // Sintetizar voz en español de México (es-MX)
        speakButton.addEventListener('click', () => {
            const text = textInput.value;
            if (text) {
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'es-MX';  // Cambiar a español de México
                synth.speak(utterance);
            }
        });
    </script>
</body>
</html>
