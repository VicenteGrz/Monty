<!-- navbar.php -->
<nav class="top-navbar">
    <div class="nav-left">
        <button id="menu-toggle" class="menu-button">
            <i class="fas fa-bars"></i>
        </button>
        <div class="logo">
            <i class="fas fa-bus"></i>
            <span>Monty+</span>
        </div>
    </div>


    <div class="nav-right">
        <span></span>
        <i class="fas fa-star"></i>
    </div>
</nav>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-content">
        <a href="#" class="sidebar-item">
            <i class="fas fa-home"></i>
            <span>Inicio</span>
        </a>
        <a href="../noticias  " class="sidebar-item">
            <i class="fas fa-city"></i>
            <span>Noticias</span>
        </a>       
        <a href="../pronostico" class="sidebar-item">
            <i class="fas fa-city"></i>
            <span>Pronostico</span>
        </a>
        <a href="../ruta" class="sidebar-item">
            <i class="fas fa-city"></i>
            <span>Rutas</span>
        </a>
        <a href="../Avisos" class="sidebar-item">
            <i class="fas fa-star"></i>
            <span>Avisos</span>
        </a>
        <a href="../reportes" class="sidebar-item">
            <i class="fas fa-star"></i>
            <span>Realizar Reporte</span>
        </a>
        <a href="../trafico" class="sidebar-item">
            <i class="fas fa-star"></i>
            <span>Trafico</span>
        </a>
    </div>
</div>

<div class="overlay" id="overlay"></div>

<style>
/* Estilos del navbar */
.top-navbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 60px;
    background: white;
    display: flex;
    align-items: center;
    padding: 0 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    z-index: 1000;
}

.nav-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.menu-button {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: #666;
}

.logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #2196F3;
    font-weight: bold;
}

.search-container {
    flex: 1;
    margin: 0 1rem;
    position: relative;
}

.search-input {
    width: 100%;
    padding: 0.5rem 1rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    outline: none;
}

.search-input:focus {
    border-color: #2196F3;
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
}

/* Estilos del sidebar */
.sidebar {
    position: fixed;
    left: -280px;
    top: 0;
    bottom: 0;
    width: 280px;
    background: white;
    box-shadow: 2px 0 4px rgba(0,0,0,0.1);
    transition: 0.3s ease-in-out;
    z-index: 1001;
}

.sidebar.active {
    left: 0;
}

.sidebar-content {
    padding-top: 60px;
}

.sidebar-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    color: #666;
    text-decoration: none;
    transition: 0.2s;
}

.sidebar-item:hover {
    background: #f5f5f5;
}

/* Overlay */
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    opacity: 0;
    visibility: hidden;
    transition: 0.3s;
    z-index: 1000;
}

.overlay.active {
    opacity: 1;
    visibility: visible;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    menuToggle.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);

    function toggleSidebar() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
});
</script>