<?php
// procesar.php
header('Content-Type: application/json');

// Configuración de la base de datos
$config = [
    'host' => 'localhost',
    'dbname' => 'u814339862_montyplus',
    'user' => 'u814339862_montyplusadmin',
    'password' => 'Stafatima104!'
];

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8",
        $config['user'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Procesar la imagen si se subió una
    $foto_path = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        // Validar el tipo de archivo
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['foto']['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            throw new Exception('Tipo de archivo no permitido. Solo se aceptan PNG y JPG/JPEG.');
        }
        
        // Validar el tamaño del archivo (opcional, ajusta según tus necesidades)
        $max_size = 5 * 1024 * 1024; // 5MB
        if ($_FILES['foto']['size'] > $max_size) {
            throw new Exception('El archivo es demasiado grande.');
        }
        
        // Generar nombre único y mover el archivo
        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_name = uniqid() . '.' . $extension;
        $upload_dir = 'uploads/';
        
        // Crear directorio si no existe
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $foto_path = $upload_dir . $foto_name;
        
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path)) {
            throw new Exception('Error al subir la imagen');
        }
    }
    
    // Preparar y ejecutar la consulta
    $sql = "INSERT INTO registros (ruta, numero_unidad, ramal, fecha, hora, foto_path) 
            VALUES (:ruta, :unidad, :ramal, :fecha, :hora, :foto_path)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':ruta' => $_POST['ruta'],
        ':unidad' => $_POST['unidad'],
        ':ramal' => $_POST['ramal'],
        ':fecha' => $_POST['fecha'],
        ':hora' => $_POST['hora'],
        ':foto_path' => $foto_path
    ]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}