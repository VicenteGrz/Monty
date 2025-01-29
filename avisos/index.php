<?php
require_once 'config.php';
require_once 'functions.php';

$sql = "SELECT a.*, GROUP_CONCAT(CONCAT(r.ruta, ' - ', r.ramal) SEPARATOR ', ') as rutas_afectadas 
        FROM avisos a 
        LEFT JOIN avisos_rutas ar ON a.id = ar.aviso_id 
        LEFT JOIN rutas r ON ar.ruta_id = r.id 
        WHERE a.estado = 1 
        GROUP BY a.id 
        ORDER BY a.fecha DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avisos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .card {
            height: 100%;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            h1 {
                font-size: 1.8rem;
                margin-bottom: 1rem;
            }

            .card-img-top {
                height: 180px;
            }

            .card-title {
                font-size: 1.2rem;
            }

            .card-text {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .col-md-4 {
                padding: 0 10px;
            }

            .card {
                margin-bottom: 15px;
            }

            .card-img-top {
                height: 160px;
            }
        }

        .rutas-afectadas {
            max-height: 100px;
            overflow-y: auto;
            padding: 5px;
            background-color: #f8f9fa;
            border-radius: 4px;
            margin-top: 10px;
        }

        .img-loading {
            position: relative;
            min-height: 200px;
            background: #f0f0f0;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container mt-4">
        <h1 class="mb-4">Avisos</h1>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            <?php while($aviso = $result->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100">
                        <?php if(!empty($aviso['imagen'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($aviso['imagen']); ?>" 
                                 class="card-img-top lazy" 
                                 alt="Imagen del aviso"
                                 loading="lazy">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($aviso['titulo']); ?></h5>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($aviso['descripcion'])); ?></p>
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt"></i>
                                    <?php echo date('d/m/Y', strtotime($aviso['fecha'])); ?>
                                </small>
                            </p>
                            <?php if(!empty($aviso['rutas_afectadas'])): ?>
                                <div class="rutas-afectadas">
                                    <p class="card-text mb-1"><strong>
                                        <i class="fas fa-route"></i> Rutas afectadas:
                                    </strong></p>
                                    <p class="card-text small">
                                        <?php echo htmlspecialchars($aviso['rutas_afectadas']); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const lazyImages = document.querySelectorAll("img.lazy");
            lazyImages.forEach(img => {
                img.parentElement.classList.add('img-loading');
                img.onload = function() {
                    img.parentElement.classList.remove('img-loading');
                }
            });
        });
    </script>
</body>
</html> 
