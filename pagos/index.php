<?php

require 'vendor/autoload.php'; // Asegúrate de que el autoloader de Composer esté incluido

// Configura la clave secreta de tu API de Conekta
Conekta::setApiKey("key_IPRKJ1TXvY2rtp4uiLhzGJa");

try {
    // Crea un cliente
    $customer = Conekta\Customer::create([
        'name' => 'Juan Pérez',
        'email' => 'juan.perez@example.com',
        'phone' => '+521234567890',
        'payment_sources' => [
            [
                'type' => 'card',
                'token_id' => 'tok_test_visa_4242', // Este es un token de tarjeta de prueba
            ],
        ],
    ]);

    // Crea un cargo
    $charge = Conekta\Order::create([
        'currency' => 'MXN',
        'line_items' => [
            [
                'name' => 'Producto de prueba',
                'unit_price' => 1000, // Precio en centavos (1000 = 10.00 MXN)
                'quantity' => 1,
            ],
        ],
        'customer_info' => [
            'customer_id' => $customer->id,
        ],
        'charges' => [
            [
                'payment_method' => [
                    'type' => 'card',
                    'token_id' => 'tok_test_visa_4242',
                ],
            ],
        ],
    ]);

    // Imprimir el resultado
    echo "Cargo exitoso. ID de la orden: " . $charge->id;

} catch (Conekta\ProcessingError $error) {
    // Manejo de errores
    echo "Error en el procesamiento: " . $error->getMessage();
} catch (Conekta\ParameterValidationError $error) {
    // Manejo de errores de validación
    echo "Error de validación: " . $error->getMessage();
} catch (Conekta\ConektaError $error) {
    // Manejo de otros errores
    echo "Error de Conekta: " . $error->getMessage();
}
?>
