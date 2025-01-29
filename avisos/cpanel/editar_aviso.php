<?php
require_once '../config.php';
require_once '../functions.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;
$rutas = getRutas($conn);

// Obtener datos del aviso
$sql = "SELECT * FROM avisos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$aviso = $stmt->get_result()->fetch_assoc();

if (!$aviso) {
    header("Location: index.php");
    exit;
}

$rutas_seleccionadas = getAvisoRutas($conn, $id);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $fecha = $_POST['fecha'];
    $estado = isset($_POST['estado']) ? 1 : 0;
    $rutas_nuevas = isset($_POST['rutas']) ? $_POST['rutas'] : [];
    
    // Manejar la subida de imagen
    $imagen = $aviso['imagen'];
    if (isset($_FILES['imagen']) && $_FILES['imagen']['size'] > 0) {
        $nueva_imagen = uploadImage($_FILES['imagen']);
        if ($nueva_imagen) {
            // Eliminar imagen anterior si existe
            if ($imagen && file_exists("../uploads/" . $imagen)) {
                unlink("../uploads/" . $imagen);
            }
            $imagen = $nueva_imagen;
        }
    }
    
    // Actualizar el aviso
    $sql = "UPDATE avisos SET titulo = ?, descripcion = ?, fecha = ?, estado = ?, imagen = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssisi", $titulo, $descripcion, $fecha, $estado, $imagen, $id);
    
    if ($stmt->execute()) {
        // Actualizar rutas afectadas
        $sql = "DELETE FROM avisos_rutas WHERE aviso_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        if (!empty($rutas_nuevas)) {
            $sql = "INSERT INTO avisos_rutas (aviso_id, ruta_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            foreach ($rutas_nuevas as $ruta_id) {
                $stmt->bind_param("ii", $id, $ruta_id);
                $stmt->execute();
            }
        }
        
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Aviso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Editar Aviso</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Título:</label>
                <input type="text" name="titulo" class="form-control" 
                       value="<?php echo htmlspecialchars($aviso['titulo']); ?>" required>
            </div>
            <div class="mb-3">
                <label>Descripción:</label>
                <textarea name="descripcion" class="form-control" rows="4" required>
                    <?php echo htmlspecialchars($aviso['descripcion']); ?>
                </textarea>
            </div>
            <div class="mb-3">
                <label>Fecha:</label>
                <input type="date" name="fecha" class="form-control" 
                       value="<?php echo $aviso['fecha']; ?>" required>
            </div>
            <div class="mb-3">
                <label>Imagen:</label>
                <?php if($aviso['imagen']): ?>
                    <div class="mb-2">
                        <img src="../uploads/<?php echo htmlspecialchars($aviso['imagen']); ?>" 
                             style="max-width: 200px;">
                    </div>
                <?php endif; ?>
                <input type="file" name="imagen" class="form-control" accept="image/*">
            </div>
            <div class="mb-3">
                <label>Rutas Afectadas:</label>
                <select name="rutas[]" class="form-control" multiple>
                    <?php foreach($rutas as $ruta): ?>
                        <option value="<?php echo $ruta['id']; ?>"
                                <?php echo in_array($ruta['id'], $rutas_seleccionadas) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ruta['ruta'] . ' - ' . $ruta['ramal']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>
                    <input type="checkbox" name="estado" 
                           <?php echo $aviso['estado'] ? 'checked' : ''; ?>>
                    Activo
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>
