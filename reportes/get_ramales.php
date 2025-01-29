<?php
header('Content-Type: application/json');

$config = [
    'host' => 'localhost',
    'usuario' => 'u814339862_montyplusadmin',
    'password' => 'Stafatima104!',
    'database' => 'u814339862_montyplus'
];

try {
    $conn = new mysqli($config['host'], $config['usuario'], $config['password'], $config['database']);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");

    if (!isset($_POST['ruta'])) {
        throw new Exception("No se especifico ruta");
    }

    // Preparar y ejecutar la consulta
    $query = "SELECT DISTINCT ramal FROM registrorutas WHERE ruta = ? ORDER BY ramal";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conn->error);
    }

    $ruta = $_POST['ruta'];
    $stmt->bind_param("s", $ruta);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }

    $result = $stmt->get_result();
    
    $ramales = [];
    while ($row = $result->fetch_assoc()) {
        $ramales[] = $row['ramal'];
    }

    echo json_encode($ramales);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}