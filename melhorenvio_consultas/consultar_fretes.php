<?php

$curl = curl_init();

// Dados para o cálculo do frete (payload)
$payload = json_encode([
    "from" => [
        "postal_code" => "61658080" // CEP de origem (ex: Curitiba-PR)
    ],
    "to" => [
        "postal_code" => "90619-900"  // CEP de destino (ex: Rio de Janeiro-RJ)
    ],
    "package" => [
        "weight" => 0.3, // Peso em kg
        "height" => 2,   // Altura em cm
        "width" => 11,   // Largura em cm
        "length" => 16   // Comprimento em cm
    ]
]);

curl_setopt_array($curl, [
    CURLOPT_URL => "https://sandbox.melhorenvio.com.br/api/v2/me/shipment/calculate",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $payload, // Adiciona o payload aqui
    CURLOPT_HTTPHEADER => [
        "Accept: application/json",
        "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI2MDkxIiwianRpIjoiMGI5MDZkYTA3ZTgyMDViNjYxZTQ3YzZjY2Q5YmFhMmVmNWIzOTMwMmM1MmJkNjYxN2YyNTQ0MWQyNGMwNWQyNWQ2MGYwMzMzZjJhOTcwYTciLCJpYXQiOjE3NDc2MTYxMjIuNjE0NDk1LCJuYmYiOjE3NDc2MTYxMjIuNjE0NDk4LCJleHAiOjE3NTAyMDgxMjIuNTg4NzYsInN1YiI6IjllZTZmYjJkLWE0NGYtNDRlYS1hYTc1LTdiZWU4OWEwMmY4NiIsInNjb3BlcyI6WyJzaGlwcGluZy1jYWxjdWxhdGUiLCJzaGlwcGluZy1nZW5lcmF0ZSJdfQ.B2oe1OBYJhZSV0UFCM9cbaRDBT82FUxSbxZFVgIfU96Qwy8Ddgg1wiLGKdVHn6L6dRzexEZOoAoniPAWyApmlNCgXxX0RPcLK_D50T_OapoZqsonR7YGItampq11l0m2YjZ92iilreq6mnyt3yP_PWW5n52p25-Lof6Jf6xt1lzTjp2idKXo2HlLNbrRXewtSPPip2n53TJeRCniEgA2qsEB6L6nYhP6AMj3MLExZ-PU1JZ0i4NATGUJNce77r9z26ucx52r-aQCbybqniQxfTGRsukAEMQQ7_D-xEPrEutIMIFbPaoPrTuu0nGaGNw0cBUKGerlnlCazb1ox-xxPZiePuUDIK8AmLitYR2pdep51oLH7gGyWBt_zGG2HbRVuG-iOWcrvkD07N7h_h9aYHUcuzAihRPGObglroGCsNo1g_aMPuXoYtOl_SV3YWbQGMoJE45ZUaw0YBvhdcTSF6Xt_9Vk939ZOst8HJvJiHnur74COQ_q2U-f_GGc1ssotyJbPq1h4o85Tg_Btw8Q2yMYvmtGq06LhsIRg7NCP3qJUcDESVh3X-fMvCNQJkJW2LF4tDh082i6PyGJkvVTgxx8AzigV16-56ZLwxY2inw54uwaEGFvWD_vvK4hACE3ruEFZEI6uzMprdL6sK-ind15VMWkgvfrHkQmzgGAX_0", // Substitua pelo seu token
        "Content-Type: application/json",
        "User-Agent: Aplicação (produtosnutripower@gmail.com)"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    echo "cURL Error #:" . $err;
} else {
    echo $response;
}