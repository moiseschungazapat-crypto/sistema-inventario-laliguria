<?php
// Configuración de credenciales de Supabase
$supabaseUrl = getenv('SUPABASE_URL') ?: 'https://jahyuyjupzumdkdvcely.supabase.co';
$supabaseApiKey = getenv('SUPABASE_ANON_KEY') ?: 'sb_publishable_kYEx4NdpkPuTDalujzz-LQ_AQlprrt6';

function supabase_request($endpoint, $method = 'GET', $data = null) {
    global $supabaseUrl, $supabaseApiKey;

    // Limpieza estricta de la URL base
    $baseUrl = preg_replace('/\/rest\/v1\/?$/', '', rtrim($supabaseUrl, '/'));
    $url = $baseUrl . '/rest/v1/' . ltrim($endpoint, '/');
    
    $ch = curl_init($url);

    $headers = [
        'apikey: ' . $supabaseApiKey,
        'Authorization: Bearer ' . $supabaseApiKey,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'data' => json_decode($response, true)
    ];
}