<?php
// Habilitar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtener la ruta solicitada
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Si se pide la raíz, cargar index.php principal
if ($uri === '/' || $uri === '') {
    require __DIR__ . '/../index.php';
    exit;
}

// Buscar si el archivo PHP existe en la raíz
$file = __DIR__ . '/..' . $uri;
if (file_exists($file) && !is_dir($file)) {
    require $file;
    exit;
}

// Si no existe, intentar agregar .php
if (file_exists($file . '.php')) {
    require $file . '.php';
    exit;
}

// Si nada coincide, cargar el index principal
require __DIR__ . '/../index.php';