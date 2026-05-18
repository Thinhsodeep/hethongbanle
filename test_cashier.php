<?php
require_once 'config/config.php';
require_once 'app/Models/User.php';

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

// Now request /pos/search
curl_setopt($ch, CURLOPT_URL, 'http://localhost/hethongbanle/public/pos/search?q=SKU-EL-001&cat=');
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Code: $httpcode\n";
echo "Response Body:\n";
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$body = substr($response, $header_size);
echo $body;

curl_close($ch);
