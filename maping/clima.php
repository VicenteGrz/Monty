<?php
$apiKey = "a731dc10f2c8fc16abe7b3b240197106";
$city = "Monterrey";
$units = "metric";

$url = "http://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&units=" . $units . "&appid=" . $apiKey;

$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data['cod'] == 200) {
    $cityName = $data['name'];
    $temperature = $data['main']['temp'];
    $feelsLike = $data['main']['feels_like'];
    $tempMin = $data['main']['temp_min'];
    $tempMax = $data['main']['temp_max'];
    $weatherMain = $data['weather'][0]['main'];
    $weatherDescription = $data['weather'][0]['description'];
    $weatherIcon = $data['weather'][0]['icon'];
    $humidity = $data['main']['humidity'];
    $pressure = $data['main']['pressure'];
    $visibility = $data['visibility'];
    $windSpeed = $data['wind']['speed'];
    $windDeg = $data['wind']['deg'];
    $clouds = $data['clouds']['all'];
    $country = $data['sys']['country'];
    $sunrise = date('H:i:s', $data['sys']['sunrise']);
    $sunset = date('H:i:s', $data['sys']['sunset']);
    
    echo "<h1>$cityName, $country</h1>";
    echo "<p>Descripción: $weatherMain - $weatherDescription</p>";
    echo "<p>Temperatura: $temperature °C</p>";
    echo "<p>Sensación Térmica: $feelsLike °C</p>";
    echo "<p>Temperatura Mínima: $tempMin °C</p>";
    echo "<p>Temperatura Máxima: $tempMax °C</p>";
    echo "<p>Humedad: $humidity%</p>";
    echo "<p>Presión: $pressure hPa</p>";
    echo "<p>Visibilidad: $visibility metros</p>";
    echo "<p>Velocidad del viento: $windSpeed m/s</p>";
    echo "<p>Dirección del viento: $windDeg grados</p>";
    echo "<p>Nubes: $clouds%</p>";
    echo "<p>Salida del sol: $sunrise</p>";
    echo "<p>Puesta del sol: $sunset</p>";
} else {
    echo "<p>Error: " . $data['message'] . "</p>";
}
?>
