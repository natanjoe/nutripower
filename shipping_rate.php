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

foreach ($freteData['rates'] as $frete) {
    // Normaliza o nome para comparação
    $nomeOriginal = strtolower(trim($frete['description'] ?? ''));
    
    // Substitui os nomes específicos
    if ($nomeOriginal === '.com') {
        $nomeSubstituto = 'Envio normal';
    } elseif ($nomeOriginal === '.package') {
        $nomeSubstituto = 'Envio expresso';
    } else {
        $nomeSubstituto = $frete['name'] ?? 'Frete';
    }

    // Monta o nome completo e o ID base
    $descricaoOriginal = $frete['description'] ?? '';
    $nomeCompleto = trim($nomeSubstituto . ' ' . $descricaoOriginal);
    $idBase = strtolower(str_replace([' ', '.', ','], '_', $nomeCompleto));
    $tipo = trim($frete['tipo'] ?? '');

    $rates[] = [
        'name' => $nomeCompleto,
        'provider' => $frete['provider'] ?? 'Frete',
        'cost' => (float)($frete['cost'] ?? 0),
        'description' => $nomeCompleto,
        'id' => $idBase,
        'userDefinedId' => $idBase,
        'guaranteedDaysToDelivery' => (int)($frete['guaranteed_days_to_delivery'] ?? 0),
        "delivery_range" => [
            "min" => intval($frete['delivery_range']['min'] ?? 0),
            "max" => intval($frete['delivery_range']['max'] ?? 0)
        ],
        'iconUrl' => $frete['iconUrl'] ?? null
    ];
}


// Retorna o JSON formatado para o Snipcart
echo json_encode(['rates' => $rates]);
