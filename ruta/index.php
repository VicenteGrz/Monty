<?php
$directorio = '.';

$archivos = [];
if ($handle = opendir($directorio)) {
    while (false !== ($archivo = readdir($handle))) {
        if ($archivo != "." && $archivo != ".." && pathinfo($archivo, PATHINFO_EXTENSION) === 'php' && $archivo != "index.php" && $archivo != "navbar.php") {
            $archivos[] = $archivo;
        }
    }
    closedir($handle);
    
    sort($archivos);
    
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Rutas</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                color: #333;
                margin: 0;
                padding: 20px;
            }
            h1 {
                text-align: center;
            }
            ul {
                list-style-type: none;
                padding: 0;
            }
            li {
                background: #fff;
                margin: 10px 0;
                padding: 15px;
                border-radius: 5px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                transition: background 0.3s;
            }
            li:hover {
                background: #e2e2e2;
            }
            a {
                text-decoration: none;
                color: #007BFF;
            }
            a:hover {
                text-decoration: underline;
            }
            @media (max-width: 600px) {
                body {
                    padding: 10px;
                }
                li {
                    padding: 10px;
                }
            }
        </style>
    </head>
    <body>
        <h1>Rutas</h1>
        <ul>";

    foreach ($archivos as $archivo) {
        $nombreArchivo = pathinfo($archivo, PATHINFO_FILENAME);
        echo "<li><a href=\"{$nombreArchivo}\">{$nombreArchivo}</a></li>";
    }

    echo "</ul>
        </body>
    </html>";
} else {
    echo "No se pudo abrir el directorio.";
}
?>
<?php include 'navbar.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
