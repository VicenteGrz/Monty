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
    $url = "https://api.waqi.info/feed/geo:{$lat};{$lon}/?token=3ac34e57f4dbb207a9e22e6c7167013c1fccfb08"; // Reemplaza YOUR_AQICN_TOKEN con tu token de AQICN
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
        
        // Obtener la calidad del aire
        $airQualityData = getAirQuality($lat, $lon);
        $aqi = isset($airQualityData['data']['aqi']) ? $airQualityData['data']['aqi'] : 'N/A';
        $aqiLevel = getAQILevel($aqi);

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
                .search-container {
                    margin-top: 20px;
                    text-align: center;
                }
                .result {
                    margin-top: 20px;
                    border-collapse: collapse;
                    width: 100%;
                }
                .result td {
                    border: 1px solid #ccc;
                    padding: 8px;
                }
            </style>
            <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
            <script>
                var tokenId = '3ac34e57f4dbb207a9e22e6c7167013c1fccfb08'; // Reemplaza con tu token

                function init(tokenId, inputId, outputId) {
                    var input = $(inputId);
                    var timer = null;
                    var output = $(outputId);

                    input.on('keyup', function () {
                        if (timer) clearTimeout(timer);
                        timer = setTimeout(function () {
                            search(input.val(), output);
                        }, 250);
                    });
                }

                function search(keyword, output) {
                    var info = token() == 'demo' ? '(based on demo token)' : '';
                    output.html('<h2>Resultados de busqueda ' + info + ':</h2>');
                    output.append($('<div/>').html('Loading...'));
                    output.append($('<div/>').addClass('cp-spinner cp-meter'));

                    let url =
                        'https://api.waqi.info/v2/search/?token=' +
                        token() +
                        '&keyword=' +
                        keyword;
                    fetch(url)
                        .then((x) => x.json())
                        .then((result) => {
                            var info = token() == 'demo' ? '(based on demo token)' : '';
                            output.html('<h2>Resultados de busqueda ' + info + ':</h2>');
                            if (!result || result.status != 'ok') {
                                throw result.data;
                            }

                            if (result.data.length == 0) {
                                output.append('Sorry, there is no result for your query!');
                                return;
                            }

                            var table = $('<table/>').addClass('result');
                            output.append(table);

                            output.append($('<div/>').html('Click para obtener mas informacion'));

                            var stationInfo = $('<div/>');
                            output.append(stationInfo);

                            result.data.forEach(function (station, i) {
                                var tr = $('<tr>');
                                tr.append($('<td>').html(station.station.name));
                                tr.append($('<td>').html(colorize(station.aqi)));
                                tr.append($('<td>').html(station.time.stime));
                                tr.on('click', function () {
                                    showStation(station, stationInfo);
                                });
                                table.append(tr);
                                if (i == 0) showStation(station, stationInfo);
                            });
                        })
                        .catch((e) => {
                            output.html('<div class=\'ui negative message\'>' + e + '</div>');
                        });
                }

                function showStation(station, output) {
                    output.html('<h2>Contaminantes y condiciones meteorológicas:</h2>');
                    output.append($('<div/>').html('Cargando...'));
                    output.append($('<div/>').addClass('cp-spinner cp-meter'));

                    let url =
                        'https://api.waqi.info/feed/@' + station.uid + '/?token=' + token();
                    fetch(url)
                        .then((x) => x.json())
                        .then((result) => {
                            output.html('<h2>Contaminantes y condiciones meteorológicas:</h2>');
                            if (!result || result.status != 'ok') {
                                output.append('Sorry, something wrong happened: ');
                                if (result.data) output.append($('<code>').html(result.data));
                                return;
                            }

                            var names = {
                                pm25: 'PM<sub>2.5</sub>',
                                pm10: 'PM<sub>10</sub>',
                                o3: 'Ozono',
                                no2: 'Dióxido de nitrógeno',
                                so2: 'Dióxido de azufre',
                                co: 'Monóxido de carbono',
                                t: 'Temperatura',
                                w: 'Viento',
                                r: 'Lluvia (precipitacion)',
                                h: 'Humedad relativa',
                                d: 'Rocío',
                                p: 'Presion atmosferica',
                            };

                            output.append($('<div>').html('Estacion: ' + result.data.city.name + ' on ' + result.data.time.s));

                            var table = $('<table/>').addClass('result');
                            output.append(table);

                            for (var specie in result.data.iaqi) {
                                var aqi = result.data.iaqi[specie].v;
                                var tr = $('<tr>');
                                tr.append($('<td>').html(names[specie] || specie));
                                tr.append($('<td>').html(colorize(aqi, specie)));
                                table.append(tr);
                            }
                        })
                        .catch((e) => {
                            output.html('<h2>Sorry, something went wrong</h2>' + e);
                        });
                }

                function token() {
                    return tokenId || 'demo';
                }

                function colorize(aqi, specie) {
                    specie = specie || 'aqi';
                    if (['pm25', 'pm10', 'no2', 'so2', 'co', 'o3', 'aqi'].indexOf(specie) < 0)
                        return aqi;

                    var spectrum = [
                        { a: 0, b: '#cccccc', f: '#ffffff' },
                        { a: 50, b: '#009966', f: '#ffffff' },
                        { a: 100, b: '#ffde33', f: '#000000' },
                        { a: 150, b: '#ff9933', f: '#000000' },
                        { a: 200, b: '#cc0033', f: '#ffffff' },
                        { a: 300, b: '#660099', f: '#ffffff' },
                        { a: 500, b: '#7e0023', f: '#ffffff' },
                    ];

                    var i = 0;
                    for (i = 0; i < spectrum.length - 2; i++) {
                        if (aqi == '-' || aqi <= spectrum[i].a) break;
                    }
                    return $('<div/>')
                        .html(aqi)
                        .css('font-size', '120%')
                        .css('min-width', '30px')
                        .css('text-align', 'center')
                        .css('background-color', spectrum[i].b)
                        .css('color', spectrum[i].f);
                }

                $(document).ready(function() {
                    init(tokenId, '#search-input', '#search-output');
                });
            </script>
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
                    <p>Calidad del Aire (AQI): {$aqi} - {$aqiLevel}</p>
                </div>
                
                <div class='search-container'>
                    <h2>Calidad del Aire</h2>
                    <input type='text' id='search-input' placeholder='Ingresa nombre de ciudad o estación' />
                    <div id='search-output'></div>
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
?>

<?php
// Llamar a la función renderWeatherPage con el nombre de la ciudad
renderWeatherPage("Monterrey");
?>
