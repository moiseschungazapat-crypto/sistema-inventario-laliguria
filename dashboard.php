<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config/supabase.php';

function obtenerArrayData($endpoint) {
    $res = supabase_request($endpoint);
    if (isset($res['data']) && is_array($res['data'])) {
        return $res['data'];
    }
    return [];
}

$dataProductos = obtenerArrayData('productos?select=id');
$totalProductos = count($dataProductos);

$productosBajos = obtenerArrayData('productos?stock=lte.10&select=id,nombre,stock,min_stock,unidad');
$totalAlertas = count($productosBajos);

$movimientosRecientes = obtenerArrayData('movimientos?select=id,tipo,cantidad,fecha,producto:productos(nombre),usuario:usuarios(nombre),sede:sedes(nombre)&order=fecha.desc&limit=5');

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
        
        /* Estilo circular para el logo */
        .sidebar-logo { 
            width: 38px; 
            height: 38px; 
            border-radius: 50%; 
            object-fit: cover; 
            border: 2px solid #d4a373; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
            flex-shrink: 0;
        }

        .sidebar-menu a { color: #495057; text-decoration: none; display: flex; align-items: center; padding: 10px 18px; font-weight: 500; border-radius: 6px; margin: 4px 10px; font-size: 14px; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: #0d233a; color: #ffffff; }
        .stat-card { border: none; border-radius: 10px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .card-custom { border: none; border-radius: 10px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .badge-critic { background-color: #d9534f; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 11px; }

        .chart-container {
            position: relative;
            height: 220px;
            width: 100%;
        }

        /* Ajustes específicos para móviles */
        @media (max-width: 767.98px) {
            .main-content { padding: 15px !important; }
            .chart-container { height: 180px; }
        }
    </style>
</head>
<body>

<!-- Menú Desplegable Offcanvas para Móviles -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
    <div class="offcanvas-header border-bottom">
        <div class="d-flex align-items-center gap-2">
            <img src="PEGA_AQUI_TU_TEXTO_BASE64" alt="Logo" class="sidebar-logo">
            <h6 class="offcanvas-title fw-bold text-dark m-0" id="mobileSidebarLabel">LA LIGURIA S.A.</h6>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
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
</div>

<div class="container-fluid">
    <div class="row">
        <!-- Barra Lateral Desktop (Se oculta en celulares d-none d-md-block) -->
        <div class="col-md-3 col-lg-2 sidebar p-0 d-none d-md-block">
            <div class="sidebar-brand d-flex align-items-center justify-content-center gap-2">
                <img src="PEGA_AQUI_TU_TEXTO_BASE64" alt="Logo" class="sidebar-logo">
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
        <div class="col-12 col-md-9 col-lg-10 p-4 main-content">
            <!-- Header Superior Responsive -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-2">
                    <!-- Botón Hamburguesa visible solo en celular -->
                    <button class="btn btn-white border d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                        <i class="fa-solid fa-bars fs-5"></i>
                    </button>
                    <h5 class="fw-bold m-0 d-none d-sm-block"><i class="fa-solid fa-bars me-2 d-none d-md-inline"></i> SISTEMA DE INVENTARIO</h5>
                    <h6 class="fw-bold m-0 d-sm-none">INVENTARIO</h6>
                </div>
                <div class="dropdown">
                    <button class="btn btn-white border dropdown-toggle fw-semibold text-truncate" type="button" data-bs-toggle="dropdown" style="max-width: 180px;">
                        <i class="fa-solid fa-circle-user text-primary me-1"></i> <?= htmlspecialchars($_SESSION['usuario']['nombre'] ?? 'Admin') ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>

            <h4 class="fw-bold text-dark mb-3 fs-5">Dashboard de Inventario</h4>

            <!-- Tarjetas de Resumen -->
            <div class="row g-2 g-md-3 mb-3 mb-md-4">
                <div class="col-6 col-md-3">
                    <div class="stat-card p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 10px;">Total Productos</small>
                            <h3 class="fw-bold m-0 text-dark"><?= number_format($totalProductos) ?></h3>
                        </div>
                        <div class="p-2 p-md-3 bg-light rounded-circle text-primary fs-5"><i class="fa-solid fa-box"></i></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 10px;">Stock Bajo</small>
                            <h3 class="fw-bold m-0 text-danger"><?= $totalAlertas ?></h3>
                        </div>
                        <div class="p-2 p-md-3 bg-danger bg-opacity-10 text-danger rounded-circle fs-5"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 10px;">Entradas (Hoy)</small>
                            <h3 class="fw-bold m-0 text-success"><?= $totalEntradas ?> <span class="fs-6">kg</span></h3>
                        </div>
                        <div class="p-2 p-md-3 bg-success bg-opacity-10 text-success rounded-circle fs-5"><i class="fa-solid fa-arrow-down"></i></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 10px;">Salidas (Hoy)</small>
                            <h3 class="fw-bold m-0 text-warning"><?= $totalSalidas ?> <span class="fs-6">kg</span></h3>
                        </div>
                        <div class="p-2 p-md-3 bg-warning bg-opacity-10 text-warning rounded-circle fs-5"><i class="fa-solid fa-arrow-up"></i></div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <div class="card-custom p-3">
                        <h6 class="fw-bold text-dark mb-3" style="font-size: 12px;">ENTRADAS VS SALIDAS</h6>
                        <div class="chart-container">
                            <canvas id="chartBarras"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="card-custom p-3">
                        <h6 class="fw-bold text-dark mb-3" style="font-size: 12px;">EVOLUCIÓN DEL CONSUMO</h6>
                        <div class="chart-container">
                            <canvas id="chartLineas"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tablas de Información -->
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="card-custom p-3">
                        <h6 class="fw-bold text-dark mb-3" style="font-size: 12px;">STOCK BAJO (ALERTAS)</h6>
                        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
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

                <div class="col-12 col-md-5">
                    <div class="card-custom p-3">
                        <h6 class="fw-bold text-dark mb-3" style="font-size: 12px;">MOVIMIENTOS RECIENTES</h6>
                        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                            <table class="table table-sm align-middle text-nowrap">
                                <thead>
                                    <tr><th>Hora</th><th>Producto</th><th>Cant.</th><th>Sede</th></tr>
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

                <div class="col-12 col-md-3">
                    <div class="card-custom p-3">
                        <h6 class="fw-bold text-dark mb-3" style="font-size: 12px;">CATEGORÍAS PRINCIPALES</h6>
                        <div class="chart-container">
                            <canvas id="chartDona"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center text-muted mt-4 mb-2 small">
                &copy; <?= date('Y') ?> LA LIGURIA S.A.
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
        scales: { y: { beginAtZero: true, max: 10 } }
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
        scales: { y: { beginAtZero: true, max: 10 } }
    }
});

new Chart(document.getElementById('chartDona'), {
    type: 'doughnut',
    data: {
        labels: ['Sin categorías'],
        datasets: [{ data: [1], backgroundColor: ['#e0e0e0'] }]
    },
    options: { responsive: true, maintainAspectRatio: false }
});
</script>
</body>
</html>