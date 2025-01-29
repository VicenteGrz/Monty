<?php
function uploadImage($file) {
    $target_dir = "../uploads/";
    $fileExtension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $newFileName = uniqid() . '.' . $fileExtension;
    $target_file = $target_dir . $newFileName;
    
    // Verificar que sea una imagen
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($fileExtension, $allowedTypes)) {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $newFileName;
    }
    return false;
}

function getRutas($conn) {
    $sql = "SELECT * FROM rutas ORDER BY ruta, ramal";
    $result = $conn->query($sql);
    $rutas = [];
    while($row = $result->fetch_assoc()) {
        $rutas[] = $row;
    }
    return $rutas;
}

function getAvisoRutas($conn, $avisoId) {
    $sql = "SELECT ruta_id FROM avisos_rutas WHERE aviso_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $avisoId);
    $stmt->execute();
    $result = $stmt->get_result();
    $rutas = [];
    while($row = $result->fetch_assoc()) {
        $rutas[] = $row['ruta_id'];
    }
    return $rutas;
}