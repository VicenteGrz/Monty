<?php
require_once('conexion.php');

if ($conn) {
    $sql = "SELECT geolocalizacion.latitud, geolocalizacion.tipo, geolocalizacion.longitud, geolocalizacion.nombre, geolocalizacion.direccion FROM geolocalizacion ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $markers = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $markers[] = $row;
    }

    echo json_encode($markers);
} else {
    echo "Error: no se pudo establecer la conexión a la base de datos.";
}

?>





