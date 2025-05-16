<?php
function loadEnv($path) {
    if (!file_exists($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || !strpos($line, '=')) continue;
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
        //echo "<b>🔍 ACCESS_TOKEN:</b> " . getenv('ACCESS_TOKEN') . "<br>";
        //echo "<b>🔍 MELHOR_ENVIO_API_URL:</b> " . getenv('MELHOR_ENVIO_API_URL') . "<br>";

    }
}

loadEnv(__DIR__ . '/../.env');

//temporario para saber se esta acessando corretamente.
//var_dump(getenv('ACCESS_TOKEN'));
//var_dump(getenv('MELHOR_ENVIO_API_URL'));
//exit;