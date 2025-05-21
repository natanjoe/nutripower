<?php
// config.php

$environment = 'sandbox'; // Altere para 'production' ao ir para produção

$base_url = $environment === 'sandbox'
    ? 'https://sandbox.melhorenvio.com.br'
    : 'https://www.melhorenvio.com.br';

return [
    'client_id' => '6091',
    'client_secret' => 'LoVG5JPJuIwm73zcV8gIr3PjdMOJAGEWBvbnHLHN',
    'redirect_uri' => 'https://6cac-2804-7fa8-9081-e600-908e-2f0c-30ee-469b.ngrok-free.app/melhorenvio_auth/callback.php',
    'base_url' => $base_url,
];
