<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Obtener información de la imagen
    $sql = "SELECT imagen FROM avisos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $aviso = $result->fetch_assoc();
    
    // Eliminar la imagen si existe
    if ($aviso['imagen'] && file_exists("../uploads/" . $aviso['imagen'])) {
        unlink("../uploads/" . $aviso['imagen']);
    }
    
    // Eliminar registros relacionados en avisos_rutas
    $sql = "DELETE FROM avisos_rutas WHERE aviso_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Eliminar el aviso
    $sql = "DELETE FROM avisos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: index.php");
exit;
