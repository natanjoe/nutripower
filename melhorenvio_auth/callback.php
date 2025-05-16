<?php
// callback.php

$config = require 'config.php';

if (!isset($_GET['code'])) {
    echo "Código de autorização não encontrado.";
    exit;
}

$code = $_GET['code'];

$data = [
    'grant_type' => 'authorization_code',
    'client_id' => $config['client_id'],
    'client_secret' => $config['client_secret'],
    'code' => $code,
    'redirect_uri' => $config['redirect_uri'],
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
    ],
];

$context  = stream_context_create($options);
$response = file_get_contents($config['base_url'] . '/oauth/token', false, $context);

if ($response === false) {
    echo "Erro ao obter o token.";
    exit;
}

file_put_contents('token.json', $response);

echo "✅ Token salvo com sucesso em <code>token.json</code><br><br>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";
