<?php
require_once __DIR__ . '/melhorenvio/calcular_frete.php';

$request = file_get_contents('php://input');
$data = json_decode($request, true);

if (!isset($data['shippingAddress']['postalCode'])) {
    http_response_code(400);
    echo json_encode(['message' => 'CEP de destino não informado']);
    exit;
}

file_put_contents('log.txt', print_r($data, true));  // antes de calcularFreteMelhorEnvio


$cep_destino = preg_replace('/[^0-9]/', '', $data['shippingAddress']['postalCode']);
$fretes = calcularFreteMelhorEnvio($cep_destino);


if (!$fretes || !is_array($fretes)) {
    file_put_contents('log.txt', "Erro ao obter fretes:\n" . print_r($fretes, true), FILE_APPEND);
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao consultar Melhor Envio']);
    exit;
}

$shipping_rates = [];

foreach ($fretes as $frete) {
    if (in_array($frete['id'], [1, 2, 3, 4])) {
        $shipping_rates[] = [
            'cost' => floatval($frete['price']),
            'description' => $frete['name'] . ' - entrega em até ' . $frete['delivery_time'] . ' dias',
            'guaranteedDaysToDelivery' => (int)$frete['delivery_time']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($shipping_rates);