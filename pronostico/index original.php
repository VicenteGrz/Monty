<?php
function getWeatherData($city = "Monterrey,MX", $units = "metric") {
    $apiKey = "a731dc10f2c8fc16abe7b3b240197106";
    $url = "http://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&units=" . $units . "&appid=" . $apiKey;
    
    $response = file_get_contents($url);
    return json_decode($response, true);
}

function getUVIndex($lat, $lon) {
    $apiKey = "a731dc10f2c8fc16abe7b3b240197106";
    $url = "http://api.openweathermap.org/data/2.5/uvi?lat={$lat}&lon={$lon}&appid={$apiKey}";
    
    $response = file_get_contents($url);
    return json_decode($response, true);
}

function renderWeatherPage($city = "Monterrey") {
    $data = getWeatherData($city);
    
    if ($data['cod'] == 200) {
        $temperature = round($data['main']['temp']);
        $feelsLike = round($data['main']['feels_like']);
        $weatherDescription = ucfirst($data['weather'][0]['description']);
        $weatherIcon = $data['weather'][0]['icon'];
        $humidity = $data['main']['humidity'];
        $windSpeed = round($data['wind']['speed'], 1);
        $windDirection = $data['wind']['deg'];
        $pressure = $data['main']['pressure'];
        $visibility = isset($data['visibility']) ? $data['visibility'] / 1000 : 'N/A';
        $dewPoint = round($data['main']['temp'] - ((100 - $humidity) / 5));
        $rain = isset($data['rain']) ? $data['rain']['1h'] : 0;
        $rainText = $rain > 0 ? "{$rain} mm" : "Sin Lluvia";
        $tempMin = round($data['main']['temp_min']);
        $tempMax = round($data['main']['temp_max']);
        
        $lat = $data['coord']['lat'];
        $lon = $data['coord']['lon'];
        $uvData = getUVIndex($lat, $lon);
        $uvIndex = isset($uvData['value']) ? round($uvData['value'], 1) : 'N/A';

        $directions = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
        $windDir = $directions[round($windDirection / 45) % 8];

        $date = date('M j, h:ia', $data['dt']);
        
        echo "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Clima en {$data['name']}</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    color: #333;
                    margin: 0;
                    padding: 20px;
                }
                #weather-container {
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                    padding: 20px;
                    max-width: 600px;
                    margin: auto;
                }
                .weather-header {
                    text-align: center;
                }
                .weather-main {
                    text-align: center;
                }
                .weather-details {
                    margin-top: 20px;
                }
                .weather-details p {
                    margin: 5px 0;
                }
                img {
                    width: 100px;
                }
            </style>
        </head>
        <body>
            <div id='weather-container'>
                <div class='weather-header'>
                    <h1>Clima en {$data['name']}, {$data['sys']['country']}</h1>
                    <span class='date'>{$date}</span>
                </div>
                <div class='weather-main'>
                    <img src='http://openweathermap.org/img/wn/{$weatherIcon}@2x.png' alt='{$weatherDescription}'>
                    <span class='temperature'>{$temperature}°C</span>
                </div>
                <p class='weather-description'>Sensación Térmica: {$feelsLike} °C - {$weatherDescription}</p>
                <div class='weather-details'>
                    <p>↓ {$windSpeed} m/s {$windDir}</p>
                    <p>🎐 {$pressure} hPa</p>
                    <p>Humedad: {$humidity}%</p>
                    <p>Temp Mín: {$tempMin}°C</p>
                    <p>Temp Máx: {$tempMax}°C</p>
                    <p>Índice UV: {$uvIndex}</p>
                    <p>Punto de Rocío: {$dewPoint}°C</p>
                    <p>Visibilidad: {$visibility} km</p>
                    <p>Pronóstico: {$rainText}</p>
                </div>
            </div>
        </body>
        </html>
        ";
    } else {
        echo "<div id='weather-container'><p>Error al obtener datos del clima.</p></div>";
    }
}
?>

<?php
// Llamar a la función renderWeatherPage con el nombre de la ciudad
renderWeatherPage("Monterrey");
?>
