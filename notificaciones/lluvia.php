<?php
$url = "https://www.appcreator24.com/intra/app_envios_confirm_guardando.php?idioma=es&idsesion=grcx8wlth35kq04pfodv61m7njusy9z2ieba&idapp=3323756&titulo=%F0%9F%8C%A4%EF%B8%8FLluvia+prueba%F0%9F%8C%A4%EF%B8%8F&subtitulo=%C2%A1Aviso+de+lluvia%21+Se+espera+precipitaci%C3%B3n&idabrir=0&idseccabrir=32830824&web=&sustituir=0&cod_img=&enviar_a=&codigo=&mensaje_chat=";

$response = file_get_contents($url);
if ($response !== false) {
    echo $response; // Aquí puedes manejar la respuesta como desees
} else {
    echo "Error al abrir la URL.";
}
?>
