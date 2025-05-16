<?php

header('Content-Type: application/json');

// GET para checar endpoint
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    http_response_code(200);
    echo json_encode(['message' => '🚚 Shipping endpoint online']);
    exit;
}

// Só aceita POST para retornar opções de frete
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Caminho do arquivo JSON com os fretes
$freteJsonPath = __DIR__ . '/melhorenvio/frete.json';

if (!file_exists($freteJsonPath)) {
    echo json_encode(['rates' => []]);
    exit;
}

// Lê e decodifica JSON
$freteData = json_decode(file_get_contents($freteJsonPath), true);

if (!$freteData || !isset($freteData['rates']) || !is_array($freteData['rates'])) {
    echo json_encode(['rates' => []]);
    exit;
}

$rates = [];

// Mapeia cada opção para o formato esperado pelo Snipcart
foreach ($freteData['rates'] as $frete) {
    $rates[] = [
        'name' => $frete['name'] ?? 'Frete',
        'provider' => $frete['provider'] ?? 'Frete',
        'cost' => (float)($frete['cost'] ?? 0),
        'description' => $frete['description'] ?? '',
        'id' => $frete['id'] ?? strtolower(str_replace(' ', '_', $frete['name'] ?? 'frete')),
        'userDefinedId' => $frete['userDefinedId'] ?? strtolower(str_replace(' ', '_', $frete['name'] ?? 'frete')),
        'guaranteedDaysToDelivery' => (int)($frete['guaranteedDaysToDelivery'] ?? 0),
        'iconUrl' => $frete['iconUrl'] ?? null
    ];
}

// Retorna o JSON formatado para o Snipcart
echo json_encode(['rates' => $rates]);
