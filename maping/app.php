<?php

 require_once('conexion.php');


 $stmt = $conn->prepare("SELECT geolocalizacion.latitud, geolocalizacion.longitud, geolocalizacion.nombre, geolocalizacion.direccion FROM geolocalizacion order by direccion desc");
 $stmt->execute();


 echo "<div class='table-responsive'>";

 echo "<table class='table'>
         <thead class='thead-dark'>
           <tr>
             <th scope='col'></th>
             <th scope='col'></th>
           </tr>
           </thead>
           <tbody>";

 while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
     echo "<tr>";
     echo "<td scope='col'>" . $row['nombre'] . "</td>";
     echo "<td scope='col'>" . preg_replace('/\\\\u([\da-fA-F]{4})/', '&#x\1;', $row['direccion']) . "</td>";
   
     echo "</tr>";
 }
 echo "</tbody></table>";
 echo "</div>";

 $conn = null;

?> 