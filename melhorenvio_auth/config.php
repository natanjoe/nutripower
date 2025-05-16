<?php
// config.php

$environment = 'sandbox'; // Altere para 'production' ao ir para produção

$base_url = $environment === 'sandbox'
    ? 'https://sandbox.melhorenvio.com.br'
    : 'https://www.melhorenvio.com.br';

return [
    'client_id' => '6064',
    'client_secret' => 'ICA4PSBWGpKASNySoZYZ4BibXwnufyoOAylqdJxZ',
    'redirect_uri' => 'https://db53-2804-7fa8-9081-e600-328c-f8b6-6e13-4b2c.ngrok-free.app/melhorenvio_auth/callback.php',
    'base_url' => $base_url,
];
