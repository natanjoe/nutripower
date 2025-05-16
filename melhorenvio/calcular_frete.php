<?php
require_once __DIR__ . '/load_env.php';

function calcularFreteMelhorEnvio($cep_destino) {
    $cep_origem = '61658080';

    $payload = [
        'from' => ['postal_code' => $cep_origem],
        'to' => ['postal_code' => $cep_destino],
        'products' => [[
            'weight' => 1,
            'width' => 15,
            'height' => 10,
            'length' => 20,
            'insurance_value' => 100,
            'quantity' => 1
        ]],
        'services' => [],
        'options' => [
            'receipt' => false,
            'own_hand' => false,
            'collect' => false
        ]
    ];

    $base_url = getenv('MELHOR_ENVIO_API_URL');
    $access_token = getenv('ACCESS_TOKEN');

    $ch = curl_init("$base_url/api/v2/me/shipment/calculate");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $access_token",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if (!$response) {
        error_log("❌ Erro CURL: $curlError");
        return null;
    }

    $data = json_decode($response, true);

    if ($httpCode !== 200) {
        error_log("❌ HTTP $httpCode - Erro da API: " . print_r($data, true));
        return null;
    }

    return $data;
}

// ========== EXECUÇÃO ==========
$cepDestino = $_GET['cep'] ?? '01001000'; // CEP de teste padrão

$fretes = calcularFreteMelhorEnvio($cepDestino);

if ($fretes) {
    $resultado = ["rates" => []];

    foreach ($fretes as $frete) {
        $resultado["rates"][] = [
            "cost" => floatval($frete['price']),
            "description" => $frete['name'],
            "guaranteed_days_to_delivery" => intval($frete['delivery_time'])
        ];
    }

    file_put_contents(__DIR__ . '/frete.json', json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "✅ frete.json atualizado com sucesso!";
} else {
    echo "❌ Erro ao calcular o frete. Veja os logs.";
}
