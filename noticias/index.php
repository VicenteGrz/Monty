<?php
class TheNewsAPIClient {
    private string $apiKey;
    private string $baseUrl = 'https://api.thenewsapi.com/v1/news';

    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;
    }

    public function getTopNews(array $params = []): array {
        return $this->makeRequest('/top', $params);
    }

    private function makeRequest(string $endpoint, array $params = []): array {
        $params['api_token'] = $this->apiKey;
        
        $url = $this->baseUrl . $endpoint . '?' . http_build_query($params);
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            throw new Exception("Error en la petición cURL: $err");
        }

        return json_decode($response, true);
    }
}

// Configuración
$apiKey = 'cM6lahabTxmtcp6gc2tThhkgKBAm3RBWeJRYV4Ov';
$newsApi = new TheNewsAPIClient($apiKey);

try {
    $news = $newsApi->getTopNews([
        'locale' => 'mx',
        'language' => 'es',
        'limit' => 12,
        'search' => 'Nuevo León' 
    ]);
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Montyplus.com</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4 text-center">Noticias de Nuevo León</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($news['data'] as $article): ?>
                    <div class="col">
                        <div class="card h-100">
                            <?php if (!empty($article['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($article['image_url']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($article['title']); ?>"
                                     onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($article['title']); ?>
                                </h5>
                                
                                <p class="card-text">
                                    <?php echo htmlspecialchars($article['description'] ?? ''); ?>
                                </p>
                                
                                <div class="text-muted small mb-2">
                                    <?php 
                                        $fecha = new DateTime($article['published_at']);
                                        echo $fecha->format('d/m/Y H:i'); 
                                    ?>
                                </div>
                                
                                <?php if (!empty($article['categories'])): ?>
                                    <div class="mb-2">
                                        <?php foreach ($article['categories'] as $category): ?>
                                            <span class="badge bg-secondary me-1">
                                                <?php echo htmlspecialchars($category); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-footer">
                                <a href="<?php echo htmlspecialchars($article['url']); ?>" 
                                   class="btn btn-primary btn-sm"
                                   target="_blank">
                                    Leer más
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <?php include 'navbar.php'; ?>