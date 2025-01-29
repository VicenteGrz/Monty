<?php 
// Configuración
$base_url = "https://newsapi.org/v2/everything";
$api_key = "f632de0cff7741488b69ec92374da6b3";

// Obtener el término de búsqueda desde el formulario
$search_query = isset($_GET['q']) ? $_GET['q'] : "Mexico"; // Valor por defecto si no se pasa un término de búsqueda

// Parámetros de la solicitud
$params = [
    "q" => $search_query,
    "from" => "2024-12-18",
    "sortBy" => "popularity",
    "apiKey" => $api_key,
];

// Construir la URL con los parámetros
$query = http_build_query($params);
$url = "{$base_url}?{$query}";

// Inicializar cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Configurar el encabezado User-Agent
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "User-Agent: Chaconitoxz/1.0 (https://cachonitox.com)"
]);

// Ejecutar la solicitud
$response = curl_exec($ch);

// Manejar errores de cURL
if (curl_errno($ch)) {
    echo "Error en cURL: " . curl_error($ch);
    curl_close($ch);
    exit;
}

// Cerrar cURL
curl_close($ch);

// Procesar la respuesta
$data = json_decode($response, true);

// Cabecera de la página HTML
echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Modelo de búsqueda de Noticias</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }";
echo ".article { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; }";
echo ".article h3 { margin: 0 0 10px 0; }";
echo ".article p { margin: 5px 0; }";
echo ".article a { color: #007bff; text-decoration: none; }";
echo ".article a:hover { text-decoration: underline; }";
echo "form { margin-bottom: 20px; }";
echo "input[type='text'] { padding: 8px; font-size: 16px; width: 200px; }";
echo "input[type='submit'] { padding: 8px 16px; font-size: 16px; background-color: #007bff; color: white; border: none; cursor: pointer; }";
echo "input[type='submit']:hover { background-color: #0056b3; }";
echo "</style>";
echo "</head>";
echo "<body>";

// Formulario de búsqueda
echo "<h2>Buscar Noticias</h2>";
echo "<form method='GET' action=''>";
echo "<input type='text' name='q' placeholder='Buscar noticias...' value='" . htmlspecialchars($search_query) . "'>";
echo "<input type='submit' value='Buscar'>";
echo "</form>";

if ($data && isset($data['status']) && $data['status'] == "ok") {
    echo "<h3>Resultados para: <strong>" . htmlspecialchars($search_query) . "</strong></h3>";
    
    // Recorrer los artículos y mostrarlos en formato HTML
    foreach ($data['articles'] as $article) {
        echo "<div class='article'>";
        echo "<h3>" . $article['title'] . "</h3>";
        echo "<p><strong>Fuente:</strong> " . $article['source']['name'] . "</p>";
        echo "<p><a href='" . $article['url'] . "' target='_blank'>Leer más...</a></p>";
        echo "</div>";
    }
} else {
    echo "<p><strong>Error en la solicitud:</strong> " . ($data['message'] ?? "Respuesta inválida.") . "</p>";
}

echo "</body>";
echo "</html>";
?>
