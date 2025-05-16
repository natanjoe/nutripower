<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/melhorenvio/calcular_frete.php';

$response = calcularFrete([
    'from' => [
        'postal_code' => '61656245'  // CEP de origem (Ex: Florianópolis/SC)
    ],
    'to' => [
        'postal_code' => '61658080'  // CEP de destino (Ex: São Paulo/SP) 90619-900
    ],
    'products' => [
        [
            'weight' => 0.3,
            'width' => 11,
            'height' => 17,
            'length' => 20,
            'insurance_value' => 100,
            'quantity' => 1
        ]
    ]
]);

if ($response === false) {
    echo "❌ Erro ao consultar a API do Melhor Envio";
} else {
    echo "<pre>";
    print_r($response);
    echo "</pre>";
}

