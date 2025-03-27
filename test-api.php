<?php

$baseUrl = 'http://127.0.0.1:8000/api';

// 1. Login isteği (POST /login)
$loginUrl = $baseUrl . '/login';
$loginData = [
    'email'    => 'admin@example.com',
    'password' => 'password'
];

$ch = curl_init($loginUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($loginData)
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "Login URL: $loginUrl\n";
echo "HTTP Code: $httpCode\n";

if ($curlError) {
    echo "cURL Error: $curlError\n";
}

echo "Login response:\n$response\n\n";

if ($httpCode !== 200) {
    echo "Login failed! Lütfen API sunucusunun çalıştığından, doğru URL kullanıldığından ve route cache'inin temizlendiğinden emin olun.\n";
    exit;
}

$decodedResponse = json_decode($response, true);
if (!isset($decodedResponse['token'])) {
    echo "Login failed: token alınamadı.\n";
    exit;
}

$token = $decodedResponse['token'];
echo "Login successful. Token: $token\n\n";

// 2. Diğer endpoint'leri test edelim
$endpoints = [
    ['method' => 'GET', 'path' => '/user'],
    ['method' => 'GET', 'path' => '/dashboard/stats'],
    ['method' => 'GET', 'path' => '/questions'],
    ['method' => 'GET', 'path' => '/games'],
    ['method' => 'GET', 'path' => '/categories'],
];

foreach ($endpoints as $endpoint) {
    $url = $baseUrl . $endpoint['path'];
    $method = $endpoint['method'];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        "Authorization: Bearer $token"
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
    } elseif ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    echo "$method $url => HTTP $httpCode\n";
    if ($curlError) {
        echo "cURL Error: $curlError\n\n";
        continue;
    }
    if (strlen($response) > 150) {
        echo "Response (ilk 150 karakter): " . substr($response, 0, 150) . "...\n\n";
    } else {
        echo "Response: $response\n\n";
    }
}
