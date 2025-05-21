<?php

header('Content-Type: application/json');

// Caminho do log
$logFile = __DIR__ . '/log_frete.txt';

function logMsg($msg) {
    global $logFile;
    error_log("[" . date('Y-m-d H:i:s') . "] $msg\n", 3, $logFile);
}

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    logMsg("Método inválido: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Lê corpo JSON
$rawInput = file_get_contents('php://input');
logMsg("Raw input: $rawInput");

$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    logMsg("Erro ao decodificar JSON: " . json_last_error_msg());
    echo json_encode(['error' => 'JSON inválido']);
    exit;
}

// Verificações básicas
if (!isset($input['items']) || !isset($input['shippingAddress']['postalCode'])) {
    http_response_code(400);
    logMsg("Dados incompletos: " . print_r($input, true));
    echo json_encode(['error' => 'Dados incompletos para cálculo de frete']);
    exit;
}

$cepDestino = preg_replace('/[^0-9]/', '', $input['shippingAddress']['postalCode']);
$itens = $input['items'];

logMsg("CEP destino: $cepDestino");
logMsg("Itens recebidos: " . print_r($itens, true));

// Monta o array de produtos
$produtos = [];

foreach ($itens as $item) {
    $produto = [
        'weight' => $item['weight'] ?? 1,
        'width' => $item['width'] ?? 11,
        'height' => $item['height'] ?? 17,
        'length' => $item['length'] ?? 20,
        'insurance_value' => ($item['price'] ?? 100) / 100, // preço em centavos
        'quantity' => $item['quantity'] ?? 1
    ];
    $produtos[] = $produto;
}

logMsg("Produtos montados: " . print_r($produtos, true));

// Chamada à função de cálculo
require_once __DIR__ . '/melhorenvio/calcular_frete.php';

$fretes = calcularFreteMelhorEnvio($cepDestino, $produtos);

if ($fretes) {
    $rates = [];

    foreach ($fretes as $frete) {
        $rates[] = [
            'name' => $frete['name'],
            'provider' => 'Melhor Envio',
            'cost' => floatval($frete['price']),
            'description' => $frete['name'],
            'id' => strtolower(str_replace(' ', '_', $frete['name'])),
            'userDefinedId' => strtolower(str_replace(' ', '_', $frete['name'])),
            'guaranteedDaysToDelivery' => intval($frete['delivery_time']),
            'iconUrl' => $frete['company']['picture'] ?? null
        ];
    }

    logMsg("Fretes retornados: " . print_r($rates, true));
    echo json_encode(['rates' => $rates]);
} else {
    logMsg("Nenhum frete retornado.");
    echo json_encode(['rates' => []]);
}
