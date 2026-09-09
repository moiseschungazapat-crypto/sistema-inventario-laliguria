<?php
// config/supabase.php

function supabase_request($endpoint, $method = 'GET', $data = null) {
    $url = "https://jahyuyjupzumdkdvcely.supabase.co/rest/v1/" . $endpoint;
    $apiKey = "sb_publishable_kYEx4NdpkPuTDalujzz-LQ_AQlprrt6";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    $headers = [
        "apikey: {$apiKey}",
        "Authorization: Bearer {$apiKey}",
        "Content-Type: application/json",
        "Prefer: return=representation"
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    curl_close($ch);

    $decoded = json_decode($response, true);

    // Garantiza que la respuesta sea siempre un array estructurado
    if (is_array($decoded)) {
        return ['data' => $decoded];
    }

    return ['data' => []];
}