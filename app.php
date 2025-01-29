<?php

 require_once('conexion.php');


 $stmt = $conn->prepare("SELECT geolocalizacion.latitud, geolocalizacion.longitud, geolocalizacion.nombre, geolocalizacion.direccion FROM geolocalizacion order by direccion desc");
 $stmt->execute();

 $conn = null;

?> 