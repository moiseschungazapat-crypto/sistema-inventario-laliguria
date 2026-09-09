<?php
session_start();
require_once __DIR__ . '/config/supabase.php';

$error = '';
$conexion_status = false;

// Verificación rápida de conexión con Supabase
try {
    $test = supabase_request('usuarios?select=id&limit=1');
    if (isset($test['data'])) {
        $conexion_status = true;
    }
} catch (Exception $e) {
    $conexion_status = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        $res = supabase_request("usuarios?email=eq." . urlencode($email) . "&select=*");
        
        if (isset($res['data']) && count($res['data']) > 0) {
            $user = $res['data'][0];
            // Verificación de contraseña (soporta texto plano o password_verify)
            if ($password === $user['password'] || password_verify($password, $user['password'])) {
                $_SESSION['usuario'] = [
                    'id' => $user['id'],
                    'nombre' => $user['nombre'] ?? 'Usuario',
                    'email' => $user['email'],
                    'rol' => $user['rol'] ?? 'Usuario'
                ];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Contraseña incorrecta.';
            }
        } else {
            $error = 'El correo electrónico no está registrado.';
        }
    } else {
        $error = 'Por favor, ingrese su correo y contraseña.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - LA LIGURIA S.A.</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            background: #ffffff;
        }
        .login-header {
            background-color: #1a365d;
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .login-header img {
            max-height: 50px;
            margin-bottom: 10px;
        }
        .login-body {
            padding: 25px 25px 20px 25px;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #1a365d;
        }
        .btn-primary-custom {
            background-color: #1a365d;
            border: none;
            color: #ffffff;
            font-weight: 600;
            padding: 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary-custom:hover {
            background-color: #122540;
            color: #ffffff;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
            color: #6c757d;
        }
        .form-control {
            border-left: none;
            padding: 10px 12px;
        }
        .input-group:focus-within .input-group-text {
            border-color: #1a365d;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
            <i class="fa-solid fa-boxes-stacked fs-2"></i>
            <h4 class="fw-bold m-0" style="letter-spacing: 0.5px;">LA LIGURIA S.A.</h4>
        </div>
        <p class="m-0 text-white-50 small">Gestión e Inventario Multi-Sede</p>
    </div>

    <div class="login-body">
        <?php if ($conexion_status): ?>
            <div class="alert alert-success d-flex align-items-center py-2 px-3 mb-3" role="alert" style="font-size: 13px;">
                <i class="fa-solid fa-circle-check me-2"></i>
                <div>Conexión exitosa con Supabase</div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning d-flex align-items-center py-2 px-3 mb-3" role="alert" style="font-size: 13px;">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <div>Sin conexión directa con la base de datos</div>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 px-3 mb-3" role="alert" style="font-size: 13px;">
                <i class="fa-solid fa-circle-xmark me-2"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <div class="mb-3">
                <label for="email" class="form-label fw-medium text-dark small">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="usuario@laliguria.com" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-medium text-dark small">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary-custom w-100 mb-3">
                <i class="fa-solid fa-right-to-bracket me-2"></i> Iniciar Sesión
            </button>
        </form>

        <div class="text-center mt-3 pt-2 border-top">
            <small class="text-muted" style="font-size: 12px;">
                &copy; <?= date('Y') ?> LA LIGURIA S.A. Todos los derechos reservados.
            </small>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>