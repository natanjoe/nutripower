<?php
header('Content-Type: application/json');

// Configuração de logs
$logFile = __DIR__ . '/logs/snipcart_frete.log';
function logMsg($msg) {
    global $logFile;
    error_log("[" . date('Y-m-d H:i:s') . "] $msg\n", 3, $logFile);
}

// Verifica método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Processa input
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido']);
    exit;
}

// Valida campos obrigatórios
if (empty($input['shippingAddress']['postalCode']) || empty($input['items'])) {
    http_response_code(400);
    echo json_encode(['error' => 'CEP ou itens faltando']);
    exit;
}

// Prepara dados
$cep_destino = preg_replace('/[^0-9]/', '', $input['shippingAddress']['postalCode']);
$produtos = [];

foreach ($input['items'] as $item) {
    $produtos[] = [
        'id' => $item['id'] ?? $item['uniqueId'],
        'weight' => $item['weight'] ?? 1, // Peso dinâmico (kg)
        'width' => $item['width'] ?? 11,  // Largura dinâmica (cm)
        'height' => $item['height'] ?? 17, // Altura dinâmica (cm)
        'length' => $item['length'] ?? 20, // Comprimento dinâmico (cm)
        'insurance_value' => $item['price'] ?? 100, // Valor unitário
        'quantity' => $item['quantity'] ?? 1
    ];
}

// Calcula fretes
require_once __DIR__ . '/melhorenvio/calcular_frete.php';
$fretes = calcularFreteMelhorEnvio($cep_destino, $produtos);

// Formata resposta para o Snipcart
$response = ['rates' => []];

if ($fretes && is_array($fretes)) {
    foreach ($fretes as $frete) {
        $response['rates'][] = [
            'id' => $frete['id'] ?? strtolower(str_replace(' ', '_', $frete['name'])),
            'name' => $frete['name'] ?? 'Transportadora',
            'cost' => floatval($frete['price']),
            'description' => $frete['name'] ?? 'Entrega padrão',
            'guaranteedDaysToDelivery' => intval($frete['delivery_time'] ?? 0),
            'provider' => 'Melhor Envio',
            'userDefinedId' => $frete['id'] ?? null
        ];
    }
}

echo json_encode($response);