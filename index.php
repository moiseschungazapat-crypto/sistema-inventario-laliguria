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
        // Credencial fija asignada
        if ($email === 'moiseschungazapata@gmail.com' && $password === 'moises987654123') {
            $_SESSION['usuario'] = [
                'id' => 1,
                'nombre' => 'Moises Chunga',
                'email' => $email,
                'rol' => 'Administrador'
            ];
            header('Location: dashboard.php');
            exit;
        }

        // Validación con Supabase
        $res = supabase_request("usuarios?email=eq." . urlencode($email) . "&select=*");
        
        if (isset($res['data']) && count($res['data']) > 0) {
            $user = $res['data'][0];
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
            background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 16px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            background: #ffffff;
        }
        .login-header {
            background: #1a2a3a;
            color: #ffffff;
            padding: 30px 25px 25px 25px;
            text-align: center;
            border-bottom: 3px solid #d4a373;
        }
        .brand-logo-container {
            width: 100px;
            height: 100px;
            margin: 0 auto 12px auto;
            border-radius: 50%;
            border: 3px solid #d4a373;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 5px;
        }
        .brand-logo {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .login-body {
            padding: 28px 28px 20px 28px;
        }
        .form-label {
            font-weight: 600;
            color: #2b2b2b;
            font-size: 13px;
        }
        .input-group-text {
            background-color: #f4f6f8;
            border-right: none;
            color: #555;
            border-radius: 8px 0 0 8px;
        }
        .form-control {
            border-left: none;
            padding: 11px 12px;
            font-size: 14px;
            background-color: #f4f6f8;
        }
        .form-control:focus {
            box-shadow: none;
            background-color: #ffffff;
            border-color: #ced4da;
        }
        .toggle-password {
            background-color: #f4f6f8;
            border-left: none;
            color: #6c757d;
            cursor: pointer;
            border-radius: 0 8px 8px 0;
            transition: color 0.2s;
        }
        .toggle-password:hover {
            color: #1a2a3a;
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #1a2a3a 0%, #2c5364 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            padding: 12px;
            border-radius: 8px;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(26, 42, 58, 0.25);
        }
        .btn-primary-custom:hover {
            opacity: 0.95;
            color: #ffffff;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <div class="brand-logo-container">
            <img src="assets/img/logo.png" alt="LA LIGURIA S.A." class="brand-logo">
        </div>
        <h4 class="fw-bold m-0" style="letter-spacing: 0.5px;">LA LIGURIA S.A.</h4>
        <p class="m-0 text-white-50 small mt-1">Gestión e Inventario Multi-Sede</p>
    </div>

    <div class="login-body">
        <?php if ($conexion_status): ?>
            <div class="alert alert-success d-flex align-items-center py-2 px-3 mb-3" role="alert" style="font-size: 13px; border-radius: 8px;">
                <i class="fa-solid fa-circle-check me-2"></i>
                <div>Conexión exitosa con Supabase</div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning d-flex align-items-center py-2 px-3 mb-3" role="alert" style="font-size: 13px; border-radius: 8px;">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <div>Sin conexión directa con la base de datos</div>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 px-3 mb-3" role="alert" style="font-size: 13px; border-radius: 8px;">
                <i class="fa-solid fa-circle-xmark me-2"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php" autocomplete="off">
            <div class="mb-3">
                <label for="email" class="form-label">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Ingrese su correo electrónico" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                    <span class="input-group-text toggle-password" id="btnTogglePassword">
                        <i class="fa-regular fa-eye" id="eyeIcon"></i>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary-custom w-100 mb-2">
                <i class="fa-solid fa-right-to-bracket me-2"></i> Iniciar Sesión
            </button>
        </form>

        <div class="text-center mt-3 pt-3 border-top">
            <small class="text-muted" style="font-size: 11px;">
                &copy; <?= date('Y') ?> LA LIGURIA S.A. Todos los derechos reservados.
            </small>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('btnTogglePassword').addEventListener('click', function () {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
});
</script>
</body>
</html>