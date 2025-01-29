<?php 
// Configuración
$base_url = "https://newsapi.org/v2/everything";
$api_key = "f632de0cff7741488b69ec92374da6b3";
$params = [
    "q" => "Mexico",
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
echo "<title>Modelo de busqueada</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }";
echo ".article { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; }";
echo ".article h3 { margin: 0 0 10px 0; }";
echo ".article p { margin: 5px 0; }";
echo ".article a { color: #007bff; text-decoration: none; }";
echo ".article a:hover { text-decoration: underline; }";
echo "</style>";
echo "</head>";
echo "<body>";

if ($data && isset($data['status']) && $data['status'] == "ok") {
    echo "<h2>Modelo Noticias</h2>";
    
    // Recorrer los artículos y mostrarlos en formato HTML
    foreach ($data['articles'] as $article) {
        echo "<div class='article'>";
        echo "<h3>" . $article['title'] . "</h3>";
        echo "<p><strong>Fuente:</strong> " . $article['source']['name'] . "</p>";
        echo "<p><a href='" . $article['url'] . "' target='_blank'>Enlace...</a></p>";
        echo "</div>";
    }
} else {
    echo "<p><strong>Error en la solicitud:</strong> " . ($data['message'] ?? "Respuesta inválida.") . "</p>";
}

echo "</body>";
echo "</html>";
?>
