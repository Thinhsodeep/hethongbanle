<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/hethongbanle/public/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email' => 'cashier.q1@retailchain.vn',
    'password' => 'Abc@12345'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
echo "Login Response:\n" . $response;
