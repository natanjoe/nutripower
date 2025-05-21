<?php
require_once __DIR__ . '/load_env.php';

function logErro($mensagem) {
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $arquivo = $logDir . '/frete_error.log';
    $dataHora = date('Y-m-d H:i:s');
    error_log("[$dataHora] $mensagem\n", 3, $arquivo);
}

function calcularFreteMelhorEnvio($cep_destino, $produtos) {
    $cep_origem = '61658080'; // Seu CEP de origem

    $payload = [
        'from' => ['postal_code' => $cep_origem],
        'to' => ['postal_code' => $cep_destino],
        'products' => $produtos,
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
        logErro("Erro CURL: $curlError");
        return null;
    }

    $data = json_decode($response, true);

    if ($httpCode !== 200) {
        logErro("HTTP $httpCode - Erro da API: " . print_r($data, true));
        return null;
    }

    return $data;
}

// ================== EXECUÇÃO ====================
try {
    $cepDestino = $_GET['cep'] ?? '01001000';

    $produtos = [[
        'weight' => 1,
        'width' => 15,
        'height' => 10,
        'length' => 20,
        'insurance_value' => 100.00,
        'quantity' => 1
    ]];

    $fretes = calcularFreteMelhorEnvio($cepDestino, $produtos);

    if ($fretes) {
        $resultado = ["rates" => []];

        foreach ($fretes as $frete) {
            $descricaoOriginal = strtolower($frete['name']);

            if ($descricaoOriginal === '.com') {
                $descricao = 'Envio expresso';
            } elseif ($descricaoOriginal === '.package') {
                $descricao = 'Envio normal';
            } else {
                $descricao = $frete['name'];
            }

            $resultado["rates"][] = [
                "cost" => floatval($frete['price']),
                "description" => $descricao,
                "guaranteed_days_to_delivery" => intval($frete['delivery_time'] ?? 0),
                "id" => $frete['id'],
                "iconUrl" => $frete['company']['picture'] ?? null,
                "name" => $frete['company']['name'] ?? null,
            ];
        }

        file_put_contents(__DIR__ . '/frete.json', json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    } else {
        logErro("Erro ao calcular o frete. Nenhum dado retornado.");
    }
} catch (Throwable $e) {
    logErro("Exceção capturada: " . $e->getMessage());
}
