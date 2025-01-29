<?php
require_once '../config.php';
require_once '../functions.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$rutas = getRutas($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $fecha = $_POST['fecha'];
    $estado = isset($_POST['estado']) ? 1 : 0;
    $rutas_seleccionadas = isset($_POST['rutas']) ? $_POST['rutas'] : [];

    // Manejar la subida de imagen
    $imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['size'] > 0) {
        $imagen = uploadImage($_FILES['imagen']);
    }

    // Insertar el aviso
    $sql = "INSERT INTO avisos (titulo, descripcion, fecha, estado, imagen) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssis", $titulo, $descripcion, $fecha, $estado, $imagen);

    if ($stmt->execute()) {
        $aviso_id = $conn->insert_id;

        // Insertar rutas afectadas
        if (!empty($rutas_seleccionadas)) {
            $sql = "INSERT INTO avisos_rutas (aviso_id, ruta_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            foreach ($rutas_seleccionadas as $ruta_id) {
                $stmt->bind_param("ii", $aviso_id, $ruta_id);
                if (!$stmt->execute()) {
                    // Manejo de errores: log o muestra un mensaje
                    error_log("Error insertando ruta: " . $stmt->error);
                }
            }
        }

        header("Location: index.php");
        exit;
    } else {
        // Manejo de errores: log o muestra un mensaje
        error_log("Error insertando aviso: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Aviso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Crear Nuevo Aviso</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Título:</label>
                <input type="text" name="titulo" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Descripción:</label>
                <textarea name="descripcion" class="form-control" rows="4" required></textarea>
            </div>
            <div class="mb-3">
                <label>Fecha:</label>
                <input type="date" name="fecha" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Imagen:</label>
                <input type="file" name="imagen" class="form-control" accept="image/*">
            </div>
            <div class="mb-3">
                <label>Rutas Afectadas:</label>
                <select name="rutas[]" class="form-control" multiple>
                    <?php foreach($rutas as $ruta): ?>
                        <option value="<?php echo htmlspecialchars($ruta['id']); ?>">
                            <?php echo htmlspecialchars($ruta['ruta'] . ' - ' . $ruta['ramal']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>
                    <input type="checkbox" name="estado" checked>
                    Activo
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>
