<?php
//***FAZ A COTAÇÃO DO FRETE E GERA O ARQUIVO QUE DEVE SER LIDO PELO SHIPPING_RATES, 
// QUE ENVIA PARA O SNIPCART
// Arquivos de entrada (ajuste os caminhos se necessário)
$fretePath = 'calcular_frete.json';          // JSON com os rates
$transportadorasPath = 'transportadoras.json'; // JSON com os detalhes das transportadoras
$outputPath = 'fretes.json';                  // JSON final

// Carrega os arquivos
$freteJson = file_get_contents($fretePath);
$transportadorasJson = file_get_contents($transportadorasPath);

$fretes = json_decode($freteJson, true);
$transportadoras = json_decode($transportadorasJson, true);

// Mapeia nomes amigáveis (opcional)
$nomes_friendly = [
    '.Package' => 'Jadlog Package',
    '.Com' => 'Jadlog .Com'
];

// Monta o resultado
$dados_finais = [];

foreach ($fretes['rates'] as $frete) {
    foreach ($transportadoras as $transportadora) {
        if ($frete['description'] === $transportadora['name']) {
            $dados_finais[] = [
                'nome_servico' => $frete['description'],
                'nome_amigavel' => $nomes_friendly[$frete['description']] ?? $frete['description'],
                'preco' => $frete['cost'],
                'prazo_entrega' => $frete['guaranteed_days_to_delivery'],
                'nome_transportadora' => $transportadora['company']['name'],
                'logo_transportadora' => $transportadora['company']['picture'],
                'formato_pacote' => $transportadora['packages'][0]['format'],
                'peso' => $transportadora['packages'][0]['weight'],
                'dimensoes' => $transportadora['packages'][0]['dimensions'],
            ];
            break;
        }
    }
}

// Salva o JSON final
file_put_contents($outputPath, json_encode($dados_finais, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Retorna resposta de sucesso
header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'mensagem' => 'Arquivo fretes.json gerado com sucesso.', 'itens' => count($dados_finais)]);
