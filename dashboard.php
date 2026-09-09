<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config/supabase.php';

// Función auxiliar para obtener datos como arreglo seguro
function obtenerArrayData($endpoint) {
    $res = supabase_request($endpoint);
    if (isset($res['data']) && is_array($res['data'])) {
        return $res['data'];
    }
    return [];
}

// Consultas seguras a Supabase
$dataProductos = obtenerArrayData('productos?select=id');
$totalProductos = count($dataProductos);

$productosBajos = obtenerArrayData('productos?stock=lte.10&select=id,nombre,stock,min_stock,unidad');
$totalAlertas = count($productosBajos);

$movimientosRecientes = obtenerArrayData('movimientos?select=id,tipo,cantidad,fecha,producto:productos(nombre),usuario:usuarios(nombre),sede:sedes(nombre)&order=fecha.desc&limit=5');

// Entradas y salidas del día
$hoy = date('Y-m-d');
$dataEntradas = obtenerArrayData("movimientos?tipo=eq.Entrada&fecha=gte.$hoy&select=cantidad");
$totalEntradas = 0;
foreach ($dataEntradas as $m) {
    if (isset($m['cantidad']) && is_numeric($m['cantidad'])) {
        $totalEntradas += $m['cantidad'];
    }
}

$dataSalidas = obtenerArrayData("movimientos?tipo=eq.Salida&fecha=gte.$hoy&select=cantidad");
$totalSalidas = 0;
foreach ($dataSalidas as $m) {
    if (isset($m['cantidad']) && is_numeric($m['cantidad'])) {
        $totalSalidas += $m['cantidad'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LA LIGURIA S.A.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #eef2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background-color: #ffffff; border-right: 1px solid #e0e0e0; }
        .sidebar-brand { padding: 15px; text-align: center; border-bottom: 1px solid #eeeeee; }
        .sidebar-logo { max-height: 45px; border-radius: 6px; }
        .sidebar-menu a { color: #495057; text-decoration: none; display: flex; align-items: center; padding: 10px 18px; font-weight: 500; border-radius: 6px; margin: 4px 10px; font-size: 14px; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: #0d233a; color: #ffffff; }
        .stat-card { border: none; border-radius: 10px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .card-custom { border: none; border-radius: 10px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .badge-critic { background-color: #d9534f; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 11px; }

        /* FIX DE ALTURA PARA CANVASES DE CHART.JS */
        .chart-container {
            position: relative;
            height: 230px;
            width: 100%;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Barra Lateral -->
        <div class="col-md-3 col-lg-2 sidebar p-0">
            <div class="sidebar-brand d-flex align-items-center justify-content-center gap-2">
                <img src="assets/img/logo.png" alt="Logo" class="sidebar-logo" onerror="this.style.display='none'">
                <span class="fw-bold text-dark fs-6">LA LIGURIA S.A.</span>
            </div>
            <div class="sidebar-menu py-3">
                <a href="dashboard.php" class="active"><i class="fa-solid fa-house me-3"></i> Dashboard</a>
                <a href="#productos"><i class="fa-solid fa-box me-3"></i> Productos</a>
                <a href="#categorias"><i class="fa-solid fa-tags me-3"></i> Categorías</a>
                <a href="#proveedores"><i class="fa-solid fa-truck me-3"></i> Proveedores</a>
                <a href="#sedes"><i class="fa-solid fa-building me-3"></i> Sedes</a>
                <a href="#inventario"><i class="fa-solid fa-boxes-stacked me-3"></i> Inventario</a>
                <a href="#movimientos"><i class="fa-solid fa-arrow-right-arrow-left me-3"></i> Movimientos</a>
                <a href="#reportes"><i class="fa-solid fa-chart-pie me-3"></i> Reportes</a>
                <a href="#usuarios"><i class="fa-solid fa-users me-3"></i> Usuarios</a>
                <hr class="my-3">
                <a href="logout.php" class="text-danger"><i class="fa-solid fa-power-off me-3"></i> Cerrar Sesión</a>
            </div>
        </div>

        <!-- Área Principal -->
        <div class="col-md-9 col-lg-10 p-4">
            <!-- Header Superior -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0"><i class="fa-solid fa-bars me-2"></i> SISTEMA DE INVENTARIO</h5>
                <div class="dropdown">
                    <button class="btn btn-white border dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-circle-user text-primary me-2"></i> <?= htmlspecialchars($_SESSION['usuario']['nombre'] ?? 'Administrador') ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>

            <h4 class="fw-bold text-dark mb-4">Dashboard de Inventario</h4>

            <!-- Tarjetas de Resumen (4 métricas) -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 11px;">Total Productos</small>
                            <h2 class="fw-bold m-0 text-dark"><?= number_format($totalProductos) ?></h2>
                        </div>
                        <div class="p-3 bg-light rounded-circle text-primary fs-4"><i class="fa-solid fa-box"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 11px;">Stock Bajo (Alertas)</small>
                            <h2 class="fw-bold m-0 text-danger"><?= $totalAlertas ?></h2>
                        </div>
                        <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-circle fs-4"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 11px;">Entradas (Hoy)</small>
                            <h2 class="fw-bold m-0 text-success"><?= $totalEntradas ?> <span class="fs-6">kg</span></h2>
                        </div>
                        <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle fs-4"><i class="fa-solid fa-arrow-down"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 11px;">Salidas (Hoy)</small>
                            <h2 class="fw-bold m-0 text-warning"><?= $totalSalidas ?> <span class="fs-6">kg</span></h2>
                        </div>
                        <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-circle fs-4"><i class="fa-solid fa-arrow-up"></i></div>
                    </div>
                </div>
            </div>

            <!-- Gráficos del Sistema con tamaño fijo -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card-custom p-3">
                        <h6 class="fw-bold text-dark mb-3" style="font-size: 13px;">Gráfico de Barras: ENTRADAS VS SALIDAS</h6>
                        <div class="chart-container">
                            <canvas id="chartBarras"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card-custom p-3">
                        <h6 class="fw-bold text-dark mb-3" style="font-size: 13px;">Gráfico de Líneas: EVOLUCIÓN DEL CONSUMO</h6>
                        <div class="chart-container">
                            <canvas id="chartLineas"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tablas de Información -->
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card-custom p-3">
                        <h6 class="fw-bold text-dark mb-3" style="font-size: 13px;">LISTADO DE PRODUCTOS CON STOCK BAJO</h6>
                        <div class="table-responsive" style="max-height: 230px; overflow-y: auto;">
                            <table class="table table-sm text-nowrap">
                                <thead>
                                    <tr><th>Producto</th><th class="text-end">Estado</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($productosBajos)): ?>
                                        <tr><td colspan="2" class="text-center text-muted py-3">Sin alertas de stock.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($productosBajos as $p): ?>
                                            <?php if (!empty($p['nombre'])): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($p['nombre']) ?> <small class="text-muted">(<?= $p['stock'] ?? 0 ?>/Min: <?= $p['min_stock'] ?? 0 ?>)</small></td>
                                                <td class="text-end"><span class="badge-critic">Crítico</span></td>
                                            </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card-custom p-3">
                        <h6 class="fw-bold text-dark mb-3" style="font-size: 13px;">RESUMEN DE MOVIMIENTOS RECIENTES</h6>
                        <div class="table-responsive" style="max-height: 230px; overflow-y: auto;">
                            <table class="table table-sm align-middle text-nowrap">
                                <thead>
                                    <tr><th>Fecha</th><th>Producto</th><th>Cant.</th><th>Sede</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($movimientosRecientes)): ?>
                                        <tr><td colspan="4" class="text-center text-muted py-3">No hay movimientos registrados.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($movimientosRecientes as $m): ?>
                                            <?php if (isset($m['producto']['nombre'])): ?>
                                            <tr>
                                                <td><small><?= isset($m['fecha']) ? date('H:i A', strtotime($m['fecha'])) : '--:--' ?></small></td>
                                                <td><?= htmlspecialchars($m['producto']['nombre']) ?></td>
                                                <td class="<?= (isset($m['tipo']) && $m['tipo'] === 'Entrada') ? 'text-success' : 'text-danger' ?> fw-bold">
                                                    <?= (isset($m['tipo']) && $m['tipo'] === 'Entrada') ? '+' : '-' ?><?= $m['cantidad'] ?? 0 ?>
                                                </td>
                                                <td><small><?= htmlspecialchars($m['sede']['nombre'] ?? 'Central') ?></small></td>
                                            </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card-custom p-3">
                        <h6 class="fw-bold text-dark mb-3" style="font-size: 13px;">CATEGORÍAS PRINCIPALES</h6>
                        <div class="chart-container">
                            <canvas id="chartDona"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center text-muted mt-4 mb-2 small">
                &copy; <?= date('Y') ?> LA LIGURIA S.A. Todos los derechos reservados.
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Configuración de Chart.js con restricciones de tamaño
new Chart(document.getElementById('chartBarras'), {
    type: 'bar',
    data: {
        labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
        datasets: [
            { label: 'Entradas', data: [0, 0, 0, 0, 0, 0, 0], backgroundColor: '#2b5c8f' },
            { label: 'Salidas', data: [0, 0, 0, 0, 0, 0, 0], backgroundColor: '#d9822b' }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true, max: 10 }
        }
    }
});

new Chart(document.getElementById('chartLineas'), {
    type: 'line',
    data: {
        labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
        datasets: [{ label: 'Consumo', data: [0, 0, 0, 0, 0, 0], borderColor: '#2b5c8f', fill: false }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true, max: 10 }
        }
    }
});

new Chart(document.getElementById('chartDona'), {
    type: 'doughnut',
    data: {
        labels: ['Sin categorías'],
        datasets: [{ data: [1], backgroundColor: ['#e0e0e0'] }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>
</body>
</html>