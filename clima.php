<?php
function getWeatherData($city = "Monterrey,MX", $units = "metric") {
    $apiKey = "a731dc10f2c8fc16abe7b3b240197106";
    $url = "http://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&units=" . $units . "&appid=" . $apiKey;
    
    $response = file_get_contents($url);
    return json_decode($response, true);
}

function renderWeatherWidget($city = "Monterrey") {
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
        $dewPoint = round($data['main']['temp'] - ((100 - $humidity) / 5)); // Aproximación del punto de rocío
        
        // Datos del pronóstico de lluvia
        $rain = isset($data['rain']) ? $data['rain']['1h'] : 0; // Precipitación en la última hora
        $rainText = $rain > 0 ? "{$rain} mm" : "sin Lluvia";

        $directions = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
        $windDir = $directions[round($windDirection / 45) % 8];

        $date = date('M j, h:ia', $data['dt']);
        
        $html = "
        <div id='weather-widget-container'>
            <div class='weather-widget'>
                <div class='weather-header'>
                    <span class='date'>{$date}</span>
                    <h3>{$data['name']}, {$data['sys']['country']}</h3>
                </div>
                <div class='weather-main'>
                    <img src='http://openweathermap.org/img/wn/{$weatherIcon}@2x.png' alt='{$weatherDescription}'>
                    <span class='temperature'>{$temperature}°C</span>
                </div>
                <p class='weather-description'>Sensacion Termica {$feelsLike} °C {$weatherDescription}</p>
                <div class='weather-details'>
                    <p>↓ {$windSpeed}m/s {$windDir}</p>
                    <p>🎐 {$pressure}hPa</p>
                    <p>Humedad: {$humidity}%</p>
                    <p>Indice UV: 2</p>
                    <p>Punto de rocío: {$dewPoint}°C</p>
                    <p>Visibilidad: {$visibility}km</p>
                    <p>Pronostico: {$rainText}</p>
                </div>
            </div>
        </div>
        ";
        
        return $html;
    } else {
        return "<div id='weather-widget-container'><p>Error al obtener datos del clima.</p></div>";
    }
}
?>
