<?php
require_once '../config.php';
require_once '../functions.php';
session_start();

// Verificar autenticación
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Obtener todos los avisos
$sql = "SELECT a.*, GROUP_CONCAT(CONCAT(r.ruta, ' - ', r.ramal) SEPARATOR ', ') as rutas_afectadas 
        FROM avisos a 
        LEFT JOIN avisos_rutas ar ON a.id = ar.aviso_id 
        LEFT JOIN rutas r ON ar.ruta_id = r.id 
        GROUP BY a.id 
        ORDER BY a.fecha DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Panel de Control</h1>
        <a href="crear_aviso.php" class="btn btn-primary mb-3">Crear Nuevo Aviso</a>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Titulo</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Rutas Afectadas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($aviso = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($aviso['titulo']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($aviso['fecha'])); ?></td>
                        <td>
                            <span class="badge <?php echo $aviso['estado'] ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $aviso['estado'] ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($aviso['rutas_afectadas']); ?></td>
                        <td>
                            <a href="editar_aviso.php?id=<?php echo $aviso['id']; ?>" 
                               class="btn btn-sm btn-warning">Editar</a>
                            <a href="eliminar_aviso.php?id=<?php echo $aviso['id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('¿Está seguro?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>