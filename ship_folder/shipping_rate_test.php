<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Teste básico de saída
echo "Teste inicial ok<br>";

// Seu código continua aqui
require_once __DIR__ . '/melhorenvio/calcular_frete.php';

// Depois de require
echo "Arquivo calcular_frete.php carregado<br>";

$response = calcularFreteMelhorEnvio('90619-900'); // teste com CEP fixo

if ($response === false) {
    echo "❌ Erro ao consultar a API do Melhor Envio";
} else {
    echo "<pre>";
    print_r($response);
    echo "</pre>";
}
