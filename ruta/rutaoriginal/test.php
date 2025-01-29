<?php
class RutaAutobus {
    private $nombre;
    private $tipoPago;
    private $costo;
    private $horario;
    private $tiemporuta;
    private $imagenBus;
    
    public function __construct() {
        $this->nombre = "Ruta 527 Santa Maria Apodaca";
        $this->tipoPago = "Urbani";
        $this->costo = "14.50";
        $this->horario = "Horario";
        $this->tiemporuta = "1 hora 45 minutos";
        $this->imagenBus = "/imagenes/527.jpg"; 
    }
    
    public function mostrarInformacion() {
        echo '<div class="informacion-ruta">';
        echo '<h1>' . htmlspecialchars($this->nombre) . '</h1>';
        
        // Mostrar el mapa (aquí irá tu implementación del mapa)
        echo '<div id="mapa" style="width: 100%; height: 400px; margin-bottom: 20px;"></div>';
        
        // Información de la ruta
        echo '<h2>Información de la ruta</h2>';
        echo '<div class="detalles-ruta">';
        
        // Imagen del autobús
        if (file_exists($this->imagenBus)) {
            echo '<div class="imagen-bus">';
            echo '<img src="' . htmlspecialchars($this->imagenBus) . '" alt="Imagen del autobús" style="max-width: 300px;">';
            echo '</div>';
        }
        
        // Detalles
        echo '<div class="detalles">';
        echo '<p><strong>Tipo de pago:</strong> ' . htmlspecialchars($this->tipoPago) . '</p>';
        echo '<p><strong>Costo:</strong> ' . htmlspecialchars($this->costo) . '</p>';
        echo '<p><strong>Horario:</strong> ' . htmlspecialchars($this->horario) . '</p>';
        echo '<p><strong>Duracion promedio:</strong> ' . htmlspecialchars($this->tiemporuta) . '</p>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
    }
    
    // Métodos setter para actualizar la información
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
    
    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }
    
    public function setImagenBus($imagenBus) {
        $this->imagenBus = $imagenBus;
    }
}

// Estilos CSS
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

// Uso de la clase
$ruta = new RutaAutobus();
$ruta->mostrarInformacion();
?>