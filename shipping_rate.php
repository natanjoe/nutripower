<?php
// shipping_rate.php - Versão Final Otimizada

header('Content-Type: application/json');

// Configuração de logs
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/snipcart_frete.log';

function logMsg($msg) {
    global $logFile;
    error_log("[" . date('Y-m-d H:i:s') . "] $msg\n", 3, $logFile);
}

try {
    // 1. Validação do método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método não permitido", 405);
    }

    // 2. Processamento do input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON inválido", 400);
    }

    // 3. Debug: Salva o request completo
    file_put_contents($logDir . '/last_request.json', json_encode($input, JSON_PRETTY_PRINT));

    // 4. Extração do CEP
    $cep_bruto = $input['shippingAddressPostalCode'] ?? 
                ($input['content']['shippingAddress']['postalCode'] ?? null);
    $cep_destino = preg_replace('/[^0-9]/', '', $cep_bruto ?? '');

    if (strlen($cep_destino) !== 8) {
        throw new Exception("CEP inválido: deve conter 8 dígitos", 400);
    }

    // 5. Extração dos itens
    $itens = $input['items'] ?? $input['content']['items'] ?? [];
    if (empty($itens)) {
        throw new Exception("Nenhum item no carrinho", 400);
    }

    // 6. Processamento dos produtos
    $produtos = [];
    foreach ($itens as $item) {
        if (isset($item['shippable']) && !$item['shippable']) {
            continue;
        }

        // Validação rigorosa das dimensões
        $requiredFields = ['weight', 'width', 'height', 'length'];
        foreach ($requiredFields as $field) {
            if (!isset($item[$field])) {
                throw new Exception("Campo obrigatório faltando: $field", 400);
            }
        }

        $produtos[] = [
            'id' => $item['id'] ?? $item['uniqueId'],
            'weight' => floatval($item['weight']), // Já deve vir em kg do Snipcart
            'width' => floatval($item['width']),
            'height' => floatval($item['height']),
            'length' => floatval($item['length']),
            'price' => floatval($item['price'] ?? 0),
            'quantity' => intval($item['quantity'] ?? 1)
        ];
    }

    if (empty($produtos)) {
        throw new Exception("Nenhum produto válido para cálculo", 400);
    }

    // 7. Log dos dados processados
    logMsg("Dados enviados para cálculo: " . json_encode([
        'cep_destino' => $cep_destino,
        'produtos' => $produtos,
        'total_itens' => count($produtos)
    ]));

    // 8. Cálculo do frete
    require_once __DIR__ . '/melhorenvio/calcular_frete.php';
    $fretes = calcularFreteMelhorEnvio($cep_destino, $produtos);

    // 9. Formatação da resposta
    $response = ['rates' => []];
    if ($fretes && is_array($fretes)) {
        foreach ($fretes as $frete) {
            if (!isset($frete['id'], $frete['price'])) continue;
            
            $response['rates'][] = [
                'id' => $frete['id'],
                'name' => $frete['name'] ?? $frete['company']['name'] ?? 'Transportadora',
                'cost' => floatval($frete['price']),
                'description' => $frete['name'] ?? 'Entrega padrão',
                'guaranteedDaysToDelivery' => intval($frete['delivery_time'] ?? 0),
                'provider' => 'Melhor Envio',
                'userDefinedId' => $frete['id'],
                'iconUrl' => $frete['company']['picture'] ?? null
            ];
        }
    }

    // 10. Fallback se nenhum frete disponível
    if (empty($response['rates'])) {
        $response['rates'][] = [
            'id' => 'manual-correios-pac',
            'name' => 'Correios PAC',
            'cost' => 15.90,
            'description' => 'Entrega econômica',
            'guaranteedDaysToDelivery' => 5,
            'provider' => 'Manual'
        ];
        logMsg("Usando fallback manual para fretes");
    }

    logMsg("Resposta enviada: " . json_encode($response));
    echo json_encode($response);

} catch (Exception $e) {
    $code = $e->getCode() >= 400 ? $e->getCode() : 500;
    http_response_code($code);
    
    $errorResponse = [
        'error' => $e->getMessage(),
        'rates' => [],
        'debug' => [
            'cep' => $cep_destino ?? null,
            'itens' => count($itens ?? []),
            'produtos_validos' => count($produtos ?? [])
        ]
    ];
    
    logMsg("ERRO: " . $e->getMessage());
    logMsg("DEBUG: " . print_r($errorResponse['debug'], true));
    
    echo json_encode($errorResponse);
}