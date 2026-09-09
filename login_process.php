<?php
session_start();
require_once __DIR__ . '/config/supabase.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Verificación de credenciales por defecto para LA LIGURIA S.A.
    if ($email === 'admin@laliguria.com' && $password === 'admin123') {
        $_SESSION['usuario'] = [
            'nombre' => 'Administrador General',
            'email' => $email,
            'rol' => 'Administrador'
        ];
        header('Location: dashboard.php');
        exit;
    }

    // Consulta en la tabla usuarios de Supabase
    $res = supabase_request("usuarios?email=eq." . urlencode($email));

    if (isset($res['code']) && $res['code'] === 200 && !empty($res['data'])) {
        $usuario = $res['data'][0];
        // Verificación básica
        if ($usuario['password'] === $password || password_verify($password, $usuario['password'])) {
            $_SESSION['usuario'] = [
                'id' => $usuario['id'],
                'nombre' => $usuario['nombre'],
                'email' => $usuario['email'],
                'rol' => $usuario['rol'] ?? 'Usuario'
            ];
            header('Location: dashboard.php');
            exit;
        }
    }

    // Si falla, redirige con alerta
    header('Location: index.php?error=1');
    exit;
}