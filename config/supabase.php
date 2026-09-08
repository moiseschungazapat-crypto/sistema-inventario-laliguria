<?php
// Cargar variables de entorno desde Vercel o archivo de configuración
$supabaseUrl = getenv('SUPABASE_URL') ?: 'https://tu-id-de-proyecto.supabase.co';
$supabaseApiKey = getenv('SUPABASE_ANON_KEY') ?: 'tu-clave-anonima-supabase';

/**
 * Función genérica para consultar Supabase REST API
 */
function supabase_request($endpoint, $method = 'GET', $data = null) {
    global $supabaseUrl, $supabaseApiKey;

    $url = rtrim($supabaseUrl, '/') . '/rest/v1/' . ltrim($endpoint, '/');
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