<?php
// authorize.php

$config = require 'config.php';

// Etapa 1: Redirecionar para obter o código de autorização
$query = http_build_query([
    'response_type' => 'code',
    'client_id' => $config['client_id'],
    'redirect_uri' => $config['redirect_uri'],
    'scope' => 'shipping-calculate shipping-generate',
]);

// Redireciona o navegador para a URL de autorização
header('Location: ' . $config['base_url'] . '/oauth/authorize?' . $query);
exit;
