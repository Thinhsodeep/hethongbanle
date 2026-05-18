<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/hethongbanle/public/pos/search');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
echo $response;
