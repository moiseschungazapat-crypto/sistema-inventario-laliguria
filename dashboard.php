<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config/supabase.php';

// Consultas rápidas para los contadores de la empresa
$resProductos = supabase_request('productos?select=count');
$resSedes = supabase_request('sedes?select=count');
$resMovimientos = supabase_request('movimientos?select=count');

$totalProductos = $resProductos['data'][0]['count'] ?? 0;
$totalSedes = $resSedes['data'][0]['count'] ?? 0;
$totalMovimientos = $resMovimientos['data'][0]['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LA LIGURIA S.A.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background-color: #1b365d; color: #fff; }
        .sidebar a { color: #d0dceb; text-decoration: none; display: block; padding: 12px 20px; font-weight: 500; }
        .sidebar a:hover, .sidebar a.active { background-color: #2c4d75; color: #fff; }
        .stat-card { border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Menú Lateral -->
        <div class="col-md-3 col-lg-2 sidebar p-0">
            <div class="p-3 text-center border-bottom border-secondary">
                <h5 class="fw-bold m-0"><i class="fa-solid fa-boxes-stacked me-2"></i>LA LIGURIA S.A.</h5>
            </div>
            <div class="py-3">
                <a href="dashboard.php" class="active"><i class="fa-solid fa-chart-line me-2"></i> Dashboard</a>
                <a href="#productos"><i class="fa-solid fa-box me-2"></i> Productos</a>
                <a href="#sedes"><i class="fa-solid fa-building me-2"></i> Sedes</a>
                <a href="#movimientos"><i class="fa-solid fa-right-left me-2"></i> Movimientos</a>
                <a href="#proveedores"><i class="fa-solid fa-truck me-2"></i> Proveedores</a>
                <hr class="dropdown-divider bg-secondary my-3">
                <a href="logout.php" class="text-danger"><i class="fa-solid fa-power-off me-2"></i> Cerrar Sesión</a>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Panel de Control</h2>
                <span class="badge bg-primary fs-6 py-2 px-3">
                    <i class="fa-solid fa-user me-2"></i><?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>
                </span>
            </div>

            <!-- Tarjetas de Estadísticas -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card stat-card p-3 bg-white">
                        <div class="d-flex align-items-center">
                            <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-3 fs-3 me-3">
                                <i class="fa-solid fa-box"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Productos</h6>
                                <h3 class="fw-bold mb-0"><?= $totalProductos ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card p-3 bg-white">
                        <div class="d-flex align-items-center">
                            <div class="p-3 bg-success bg-opacity-10 text-success rounded-3 fs-3 me-3">
                                <i class="fa-solid fa-building"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Sedes Activas</h6>
                                <h3 class="fw-bold mb-0"><?= $totalSedes ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card p-3 bg-white">
                        <div class="d-flex align-items-center">
                            <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-3 fs-3 me-3">
                                <i class="fa-solid fa-right-left"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Movimientos Registrados</h6>
                                <h3 class="fw-bold mb-0"><?= $totalMovimientos ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de trabajo -->
            <div class="card border-0 shadow-sm p-4 bg-white">
                <h4><i class="fa-solid fa-list-check me-2"></i>Bienvenido al Sistema de Inventario</h4>
                <p class="text-muted">Desde este panel de control podrás gestionar el stock multi-sede, registrar auditorías, entradas y salidas de inventario en tiempo real.</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>