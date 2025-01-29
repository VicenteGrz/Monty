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

function getAirQuality($lat, $lon) {
    $url = "https://api.waqi.info/feed/geo:{$lat};{$lon}/?token=3ac34e57f4dbb207a9e22e6c7167013c1fccfb08";
    $response = file_get_contents($url);
    return json_decode($response, true);
}

function getDetailedAirQuality($stationId) {
    $url = "https://api.waqi.info/feed/@" . $stationId . "/?token=3ac34e57f4dbb207a9e22e6c7167013c1fccfb08";
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
        
        // Obtener la calidad del aire básica
        $airQualityData = getAirQuality($lat, $lon);
        $aqi = isset($airQualityData['data']['aqi']) ? $airQualityData['data']['aqi'] : 'N/A';
        $aqiLevel = getAQILevel($aqi);

        // Obtener datos detallados de calidad del aire
        $stationId = isset($airQualityData['data']['idx']) ? $airQualityData['data']['idx'] : null;
        $detailedAirQuality = $stationId ? getDetailedAirQuality($stationId) : null;

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
                .weather-details, .air-quality-details {
                    margin-top: 20px;
                }
                .weather-details p, .air-quality-details p {
                    margin: 5px 0;
                }
                img {
                    width: 100px;
                }
                .air-quality-container {
                    margin-top: 20px;
                    padding: 15px;
                    background-color: #f8f9fa;
                    border-radius: 8px;
                }
                .pollutant-table {
                    width: 100%;
                    margin-top: 15px;
                    border-collapse: collapse;
                }
                .pollutant-table td {
                    padding: 8px;
                    border: 1px solid #ddd;
                }
                .aqi-level {
                    padding: 5px 10px;
                    border-radius: 4px;
                    display: inline-block;
                    margin: 5px 0;
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
                
                <div class='air-quality-container'>
                    <h2>Calidad del Aire</h2>
                    <div class='air-quality-details'>";
        
        if ($detailedAirQuality && $detailedAirQuality['status'] === 'ok') {
            $data = $detailedAirQuality['data'];
            $bgColor = getAQIColor($aqi);
            echo "<p>Índice de Calidad del Aire (AQI): <span class='aqi-level' style='background-color: {$bgColor}'>{$aqi} - {$aqiLevel}</span></p>";
            
            if (isset($data['iaqi'])) {
                echo "<table class='pollutant-table'>
                        <tr>
                            <td><strong>Contaminante</strong></td>
                            <td><strong>Valor</strong></td>
                        </tr>";
                
                $pollutants = [
                    'pm25' => 'PM<sub>2.5</sub>',
                    'pm10' => 'PM<sub>10</sub>',
                    'o3' => 'Ozono',
                    'no2' => 'Dióxido de nitrógeno',
                    'so2' => 'Dióxido de azufre',
                    'co' => 'Monóxido de carbono'
                ];
                
                foreach ($pollutants as $key => $name) {
                    if (isset($data['iaqi'][$key])) {
                        $value = $data['iaqi'][$key]['v'];
                        echo "<tr>
                                <td>{$name}</td>
                                <td>{$value}</td>
                             </tr>";
                    }
                }
                
                echo "</table>";
            }
            
            if (isset($data['city'])) {
                echo "<p>Estación: {$data['city']['name']}</p>";
            }
            
        } else {
            echo "<p>Calidad del Aire (AQI): {$aqi} - {$aqiLevel}</p>";
        }
        
        echo "    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    } else {
        echo "<div id='weather-container'><p>Error al obtener datos del clima.</p></div>";
    }
}

function getAQILevel($aqi) {
    if ($aqi <= 50) return "Buena";
    if ($aqi <= 100) return "Moderada";
    if ($aqi <= 150) return "Insalubre para grupos sensibles";
    if ($aqi <= 200) return "Insalubre";
    if ($aqi <= 300) return "Muy Insalubre";
    return "Peligrosa";
}

function getAQIColor($aqi) {
    if ($aqi <= 50) return "#009966";
    if ($aqi <= 100) return "#ffde33";
    if ($aqi <= 150) return "#ff9933";
    if ($aqi <= 200) return "#cc0033";
    if ($aqi <= 300) return "#660099";
    return "#7e0023";
}
?>
<?php include 'navbar.php'; ?>
<?php
renderWeatherPage("Monterrey");
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">