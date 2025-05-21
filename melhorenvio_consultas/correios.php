<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function calcularFreteCorreios($cepOrigem, $cepDestino, $peso, $comprimento, $altura, $largura, $servico) {
    $url = "https://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx?" . http_build_query([
        'nCdEmpresa' => '',
        'sDsSenha' => '',
        'nCdServico' => $servico, // 04510 = PAC, 04014 = SEDEX
        'sCepOrigem' => $cepOrigem,
        'sCepDestino' => $cepDestino,
        'nVlPeso' => $peso,
        'nCdFormato' => 1,
        'nVlComprimento' => $comprimento,
        'nVlAltura' => $altura,
        'nVlLargura' => $largura,
        'nVlDiametro' => 0,
        'sCdMaoPropria' => 'N',
        'nVlValorDeclarado' => 0,
        'sCdAvisoRecebimento' => 'N',
        'StrRetorno' => 'xml'
    ]);

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    if ($response === false) {
        return ['erro' => 'Erro ao consultar os Correios: ' . curl_error($curl)];
    }
    curl_close($curl);

    $xml = simplexml_load_string($response);
    if ($xml === false) {
        return ['erro' => 'Erro ao interpretar XML dos Correios'];
    }

    // Garante que os campos existam
    return [
        'servico' => isset($xml->cServico->Codigo) ? (string) $xml->cServico->Codigo : '',
        'valor' => isset($xml->cServico->Valor) ? (string) $xml->cServico->Valor : '',
        'prazo' => isset($xml->cServico->PrazoEntrega) ? (string) $xml->cServico->PrazoEntrega : '',
        'erro' => isset($xml->cServico->MsgErro) ? (string) $xml->cServico->MsgErro : ''
    ];
}

// Dados de exemplo
$cepOrigem = '01001-000'; // CEP de origem (ex: São Paulo)
$cepDestino = '20040-000'; // CEP de destino (ex: Rio de Janeiro)
$peso = 1;          // peso em kg
$comprimento = 20;  // em cm
$altura = 10;       // em cm
$largura = 15;      // em cm

// Códigos dos serviços dos Correios
$servicos = [
    'SEDEX' => '04014',
    'PAC' => '04510'
];

$resultados = [];

foreach ($servicos as $nome => $codigoServico) {
    $frete = calcularFreteCorreios($cepOrigem, $cepDestino, $peso, $comprimento, $altura, $largura, $codigoServico);

    // Se houver erro, informe no resultado
    if (!empty($frete['erro'])) {
        $resultados[] = [
            'tipo' => $nome,
            'erro' => $frete['erro'],
            'valor' => null,
            'prazo_dias' => null
        ];
    } else {
        $resultados[] = [
            'tipo' => $nome,
            'valor' => $frete['valor'],
            'prazo_dias' => $frete['prazo'],
            'erro' => null
        ];
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($resultados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit;
