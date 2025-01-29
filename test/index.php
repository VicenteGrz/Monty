<?php
// Ruta al archivo JSON (ajusta la ruta según la ubicación de tu archivo)
$json_file = 'noticias.json';

// Comprobar si el archivo existe
if (!file_exists($json_file)) {
    die('El archivo JSON no existe.');
}

// Leer el contenido del archivo JSON
$json_data = file_get_contents($json_file);

// Decodificar el JSON a un array de PHP
$data = json_decode($json_data, true);

// Verificar si la clave 'noticias' existe en el array
if (isset($data['noticias']) && is_array($data['noticias'])) {
    echo "<h1>Noticias</h1>";

    // Recorrer las noticias y mostrar los detalles
    foreach ($data['noticias'] as $index => $noticia) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
        echo "<h2>" . htmlspecialchars($noticia['titulo']) . "</h2>";
        echo "<p><strong>Descripción:</strong> " . htmlspecialchars($noticia['descripcion']) . "</p>";
        echo "<p><strong>Fecha:</strong> " . htmlspecialchars($noticia['fecha']) . "</p>";
        echo "<p><strong>Hora:</strong> " . htmlspecialchars($noticia['hora']) . "</p>";
        echo "</div>";
    }
} else {
    echo "<p>No se encontraron noticias.</p>";
}

?>
