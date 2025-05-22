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

/**
 * Valida e formata os dados para a API do Melhor Envio
 */
function prepararDadosFrete($cep_destino, $produtos) {
    // Validações básicas
    if (empty($cep_destino)) {
        throw new Exception("CEP destino não informado");
    }

    $cep_origem = '61658080'; // Substitua pelo seu CEP de origem
    $cep_destino = preg_replace('/[^0-9]/', '', $cep_destino);

    // Valores mínimos aceitos pelo Melhor Envio
    $dimensoes_minimas = [
        'width' => 11,  // Largura mínima 11cm
        'height' => 2,   // Altura mínima 2cm
        'length' => 16   // Comprimento mínimo 16cm
    ];

    $produtos_formatados = [];
    foreach ($produtos as $item) {
        // Campos obrigatórios com valores padrão
        $peso = max(0.1, floatval($item['weight'] ?? 0.3)); // Mínimo 0.1kg (100g)
        $largura = max($dimensoes_minimas['width'], floatval($item['width'] ?? 11));
        $altura = max($dimensoes_minimas['height'], floatval($item['height'] ?? 5));
        $comprimento = max($dimensoes_minimas['length'], floatval($item['length'] ?? 16));
        $valor = floatval($item['price'] ?? 0);

        $produtos_formatados[] = [
            'id' => $item['id'] ?? uniqid(),
            'weight' => $peso,
            'width' => $largura,
            'height' => $altura,
            'length' => $comprimento,
            'insurance_value' => $valor,
            'quantity' => intval($item['quantity'] ?? 1)
        ];
    }

    return [
        'from' => ['postal_code' => $cep_origem],
        'to' => ['postal_code' => $cep_destino],
        'products' => $produtos_formatados,
        'options' => [
            'receipt' => false,
            'own_hand' => false,
            'collect' => false,
            'insurance_value' => array_sum(array_column($produtos_formatados, 'insurance_value'))
        ]
    ];
}

/**
 * Função principal para cálculo de frete
 */
function calcularFreteMelhorEnvio($cep_destino, $produtos) {
    try {
        $payload = prepararDadosFrete($cep_destino, $produtos);

        $base_url = getenv('MELHOR_ENVIO_API_URL');
        $access_token = getenv('ACCESS_TOKEN');

        if (empty($base_url) || empty($access_token)) {
            throw new Exception("Configurações da API não encontradas");
        }

        $ch = curl_init("$base_url/api/v2/me/shipment/calculate");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $access_token",
                "Content-Type: application/json",
                "Accept: application/json",
                "User-Agent: SeuEcommerce/1.0"
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Tratamento de erros
        if ($curlError) {
            throw new Exception("Erro na requisição: $curlError");
        }

        if ($httpCode !== 200) {
            $errorMsg = json_decode($response, true)['message'] ?? 'Erro desconhecido';
            throw new Exception("API retornou HTTP $httpCode: $errorMsg");
        }

        $data = json_decode($response, true);

        // Verifica estrutura da resposta
        if (!is_array($data)) {
            throw new Exception("Resposta da API em formato inválido");
        }

        return $data;

    } catch (Exception $e) {
        logErro("Erro ao calcular frete: " . $e->getMessage());
        logErro("Payload enviado: " . json_encode($payload ?? []));
        return null;
    }
}