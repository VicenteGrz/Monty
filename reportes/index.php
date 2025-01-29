<!DOCTYPE html> 
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de unidad</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding-top: 70px;
            font-family: Arial, sans-serif;
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background-color: #333;
            color: white;
        }

        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        select {
            height: 40px;
            background-color: white;
        }

        select:disabled {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }

        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #45a049;
        }

        #preview {
            max-width: 200px;
            margin-top: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .error-message {
            color: #dc3545;
            font-size: 14px;
            display: none;
            margin-top: 5px;
            padding: 5px;
            border-radius: 4px;
        }

        .loading {
            display: none;
            text-align: center;
            margin: 10px 0;
        }

        .loading::after {
            content: "⌛";
            animation: loading 1s infinite;
        }

        @keyframes loading {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <?php
    $config = [
        'host' => 'localhost',
        'usuario' => 'u814339862_montyplusadmin',
        'password' => 'Stafatima104!',
        'database' => 'u814339862_montyplus'
    ];

    $conn = new mysqli($config['host'], $config['usuario'], $config['password'], $config['database']);
    
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    
    // Establecer charset
    $conn->set_charset("utf8mb4");

    // Obtener rutas únicas
    $rutas_query = "SELECT DISTINCT ruta FROM registrorutas ORDER BY ruta";
    $rutas_result = $conn->query($rutas_query);
    ?>

    <div class="form-container">
        <h2>Reporte de unidad</h2>
        <form id="registroForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="ruta">Ruta:</label>
                <select id="ruta" name="ruta" required>
                    <option value="">Selecciona una ruta</option>
                    <?php while($row = $rutas_result->fetch_assoc()) { ?>
                        <option value="<?php echo htmlspecialchars($row['ruta']); ?>">
                            <?php echo htmlspecialchars($row['ruta']); ?>
                        </option>
                    <?php } ?>
                </select>
                <div id="rutaLoading" class="loading">Cargando ramales...</div>
            </div>
            
            <div class="form-group">
                <label for="unidad">Número económico de unidad:</label>
                <input type="number" id="unidad" name="unidad" required min="1">
            </div>
            
            <div class="form-group">
                <label for="ramal">Ramal:</label>
                <select id="ramal" name="ramal" required disabled>
                    <option value="">Primero seleccione una ruta</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" required>
            </div>
            
            <div class="form-group">
                <label for="hora">Hora:</label>
                <input type="time" id="hora" name="hora" required>
            </div>
            
            <div class="form-group">
                <label for="foto">Foto (opcional):</label>
                <input type="file" id="foto" name="foto" accept="image/png, image/jpeg">
                <div id="errorMessage" class="error-message">Solo se permiten archivos PNG, JPG o JPEG</div>
                <img id="preview" style="display: none;">
            </div>
            
            <button type="submit" class="submit-btn">
                <span>Enviar Reporte</span>
            </button>
        </form>
    </div>

    <?php $conn->close(); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const fecha = document.getElementById('fecha');
            const hora = document.getElementById('hora');
            
            fecha.value = today.toISOString().split('T')[0];
            hora.value = today.getHours().toString().padStart(2, '0') + ':' + 
                        today.getMinutes().toString().padStart(2, '0');
        });

        document.getElementById('ruta').addEventListener('change', async function() {
            const ramalSelect = document.getElementById('ramal');
            const loadingDiv = document.getElementById('rutaLoading');
            const rutaSeleccionada = this.value;
            
            if (rutaSeleccionada) {
                try {
                    loadingDiv.style.display = 'block';
                    ramalSelect.disabled = true;
                    
                    const formData = new FormData();
                    formData.append('ruta', rutaSeleccionada);
                    
                    const response = await fetch('get_ramales.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    
                    const ramales = await response.json();
                    
                    if (ramales.error) {
                        throw new Error(ramales.error);
                    }
                    
                    ramalSelect.innerHTML = '<option value="">Selecciona un ramal</option>';
                    ramales.forEach(ramal => {
                        const option = document.createElement('option');
                        option.value = ramal;
                        option.textContent = ramal;
                        ramalSelect.appendChild(option);
                    });
                    
                    ramalSelect.disabled = false;
                    
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al cargar los ramales: ' + error.message);
                } finally {
                    loadingDiv.style.display = 'none';
                }
            } else {
                ramalSelect.innerHTML = '<option value="">Primero selecciona una ruta</option>';
                ramalSelect.disabled = true;
            }
        });

        // Manejador del evento de archivo
        document.getElementById('foto').addEventListener('change', function(e) {
            const preview = document.getElementById('preview');
            const errorMessage = document.getElementById('errorMessage');
            const file = e.target.files[0];
            
            if (file) {
                const validTypes = ['image/png', 'image/jpeg', 'image/jpg'];
                if (!validTypes.includes(file.type)) {
                    errorMessage.style.display = 'block';
                    preview.style.display = 'none';
                    this.value = '';
                    return;
                }

                if (file.size > 500000) { 
                    errorMessage.textContent = 'El archivo es demasiado grande. ';
                    errorMessage.style.display = 'block';
                    preview.style.display = 'none';
                    this.value = '';
                    return;
                }

                errorMessage.style.display = 'none';
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('registroForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('.submit-btn');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span>Enviando reporte...</span>';
            submitBtn.disabled = true;
            
            try {
                const formData = new FormData(this);
                
                const response = await fetch('procesar.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error('Sucedio un Error...');
                }
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Reporte realizado correctamente');
                    this.reset();
                    document.getElementById('preview').style.display = 'none';
                    document.getElementById('errorMessage').style.display = 'none';
                    const ramalSelect = document.getElementById('ramal');
                    ramalSelect.innerHTML = '<option value="">Selecciona una ruta</option>';
                    ramalSelect.disabled = true;
                    
                    const today = new Date();
                    document.getElementById('fecha').value = today.toISOString().split('T')[0];
                    document.getElementById('hora').value = 
                        today.getHours().toString().padStart(2, '0') + ':' + 
                        today.getMinutes().toString().padStart(2, '0');
                } else {
                    throw new Error(result.message || 'Error al guardar el reporte');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error: ' + error.message);
            } finally {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>