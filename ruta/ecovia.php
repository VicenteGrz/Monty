<?php 
class RutaAutobus {
    private $nombre;
    private $tipoPago;
    private $costo;
    private $horario;
    private $tiemporuta;
    private $imagenBus;

    public function __construct() {
        $this->nombre = "Ecovia";
        $this->tipoPago = "Urbani - Efectivo";
        $this->costo = "16.20";
        $this->horario = "4:00 - 24:00";
        $this->tiemporuta = "1 hora 45 minutos";
        $this->imagenBus = "/imagenes/527.jpg"; 
    }
    
    public function mostrarInformacion() {
        echo '<div class="informacion-ruta">';
        echo '<h1>' . htmlspecialchars($this->nombre) . '</h1>';
        echo '<div id="mapa" style="width: 100%; height: 400px; margin-bottom: 20px;"></div>';
        echo '<h2>Información de la ruta</h2>';
        echo '<div class="detalles-ruta">';
        
        if (file_exists($this->imagenBus)) {
            echo '<div class="imagen-bus">';
            echo '<img src="' . htmlspecialchars($this->imagenBus) . '" alt="Imagen del autobús" style="max-width: 300px;">';
            echo '</div>';
        }
        
        echo '<div class="detalles">';
        echo '<p><strong>Tipo de pago:</strong> ' . htmlspecialchars($this->tipoPago) . '</p>';
        echo '<p><strong>Costo:</strong> ' . htmlspecialchars($this->costo) . '</p>';
        echo '<p><strong>Horario:</strong> ' . htmlspecialchars($this->horario) . '</p>';
        echo '<p><strong>Duracion promedio:</strong> ' . htmlspecialchars($this->tiemporuta) . '</p>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
        
        echo '<script>
            var map = L.map("mapa").setView([25.7495, -100.2254], 12);
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                maxZoom: 19,
                attribution: "© OpenStreetMap"
            }).addTo(map);

            fetch("ecovia.geojson") 
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Network response was not ok");
                    }
                    return response.json();
                })
                .then(data => {
                    L.geoJSON(data).addTo(map);
                })
                .catch(error => console.error("Error cargando el GeoJSON:", error));
        </script>';
    }
    
    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }
    
    public function setTipoPago($tipoPago) {
        $this->tipoPago = $tipoPago;
    }
    
    public function setCosto($costo) {
        $this->costo = $costo;
    }
    
    public function setHorario($horario) {
        $this->horario = $horario;
    }
    
    public function setImagenBus($imagenBus) {
        $this->imagenBus = $imagenBus;
    }
}

echo '<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />';
echo '<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>';
echo '<style>
    .informacion-ruta {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        font-family: Arial, sans-serif;
    }
    
    .detalles-ruta {
        display: flex;
        gap: 30px;
        margin-top: 20px;
    }
    
    .imagen-bus {
        flex: 0 0 300px;
    }
    
    .detalles {
        flex: 1;
    }
    
    .detalles p {
        margin: 10px 0;
        font-size: 16px;
    }
    
    h1, h2 {
        color: #333;
    }
</style>';

$ruta = new RutaAutobus();
$ruta->mostrarInformacion();
?>
<?php include 'navbar.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">