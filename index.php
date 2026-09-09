<?php
require_once __DIR__ . '/config/supabase.php';

// Prueba de conexión rápida a Supabase
$conexionOk = false;
$mensaje = "";

$res = supabase_request('sedes?select=count');
if (isset($res['code']) && $res['code'] === 200) {
    $conexionOk = true;
} else {
    $mensaje = "Error de conexión con Supabase. Revisa las claves.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LA LIGURIA S.A. - Sistema de Inventario</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            max-width: 420px;
            margin: 80px auto;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .brand-header {
            background-color: #1b365d;
            color: #ffffff;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 30px 20px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card login-card">
        <div class="brand-header">
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-boxes-stacked me-2"></i>LA LIGURIA S.A.</h3>
            <p class="small mb-0 opacity-75">Gestión e Inventario Multi-Sede</p>
        </div>
        <div class="card-body p-4">

            <?php if ($conexionOk): ?>
                <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                    <i class="fa-solid fa-circle-check me-2 fs-5"></i>
                    <div>Conexión exitosa con Supabase</div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2 fs-5"></i>
                    <div><?= htmlspecialchars($mensaje) ?></div>
                </div>
            <?php endif; ?>

            <form action="login_process.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label font-weight-bold">Correo Electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="usuario@laliguria.com" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock text-muted"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" style="background-color: #1b365d; border: none;">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Iniciar Sesión
                </button>
            </form>
        </div>
        <div class="card-footer text-center text-muted py-3 small bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
            &copy; <?= date('Y') ?> LA LIGURIA S.A. Todos los derechos reservados.
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>