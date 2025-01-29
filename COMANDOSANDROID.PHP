<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reconocimiento de voz y Síntesis</title>
</head>
<body>
    <h1>Reconocimiento de voz y Síntesis</h1>

    <button id="start-recognition">Iniciar Reconocimiento</button>
    <button id="stop-recognition" disabled>Detener Reconocimiento</button>

    <div id="result">Aquí se mostrará el texto reconocido</div>

    <h3>Texto a hablar</h3>
    <input type="text" id="text-to-speak" placeholder="Escribe algo para hablar">
    <button id="speak-button">Hablar</button>

    <script>
        // Iniciar el reconocimiento de voz
        const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
        const synth = window.speechSynthesis;

        const resultElement = document.getElementById('result');
        const startButton = document.getElementById('start-recognition');
        const stopButton = document.getElementById('stop-recognition');
        const speakButton = document.getElementById('speak-button');
        const textInput = document.getElementById('text-to-speak');

        recognition.lang = 'es-MX';  // Establece el idioma del reconocimiento de voz a español (México)
        recognition.interimResults = false;
        recognition.continuous = true;  // Mantener el reconocimiento continuo (nunca se detiene)

        // Iniciar el reconocimiento cuando se hace clic en "Iniciar Reconocimiento"
        startButton.addEventListener('click', () => {
            recognition.start();  // Inicia el reconocimiento de voz
            startButton.disabled = true;
            stopButton.disabled = false;
        });

        // Detener el reconocimiento cuando se hace clic en "Detener Reconocimiento"
        stopButton.addEventListener('click', () => {
            recognition.stop();  // Detiene el reconocimiento de voz
            startButton.disabled = false;
            stopButton.disabled = true;
        });

        // Escuchar el resultado del reconocimiento de voz
        recognition.addEventListener('result', (event) => {
            const transcript = event.results[0][0].transcript;
            resultElement.textContent = transcript;
        });

        // Reiniciar el reconocimiento de voz después de que el micrófono se haya detenido
        recognition.addEventListener('speechend', () => {
            recognition.start();  // Reinicia el reconocimiento automáticamente
        });

        // Para no detener el reconocimiento una vez que se haya activado
        recognition.addEventListener('end', () => {
            recognition.start();  // Reinicia automáticamente después de que el reconocimiento termine
        });

        // Cuando se hace clic en el botón de "Hablar", convertir el texto a voz
        speakButton.addEventListener('click', () => {
            const text = textInput.value;
            if (text) {
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'es-MX';  // Establece el idioma a español (México)
                synth.speak(utterance);    // Hablar el texto
            }
        });
    </script>
</body>
</html>
