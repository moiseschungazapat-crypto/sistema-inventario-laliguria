<?php
ini_set('session.cookie_lifetime', 86400);
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);
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

// 1. Alertas de stock bajo comparando dinámicamente con la columna min_stock
$productosBajos = obtenerArrayData('productos?stock=lte.min_stock&select=id,nombre,stock,min_stock,unidad');
$totalAlertas = count($productosBajos);

$movimientosRecientes = obtenerArrayData('movimientos?select=id,tipo,cantidad,fecha,producto:productos(nombre),usuario:usuarios(nombre),sede:sedes(nombre)&order=fecha.desc&limit=5');

// 2. Formato ISO para Supabase
$hoy = date('Y-m-d\T00:00:00');

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

$nombreUsuario = $_SESSION['usuario']['nombre'] ?? 'Moises Chunga';
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
        body { background-color: #f0f2f5; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
        .sidebar { min-height: 100vh; background-color: #ffffff; border-right: 1px solid #e2e8f0; }
        .sidebar-brand { padding: 18px 20px; border-bottom: 1px solid #edf2f7; display: flex; align-items: center; gap: 12px; }
        .sidebar-logo { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 1px solid #d4a373; }
        
        .sidebar-menu { padding: 10px 0; }
        .sidebar-menu a { color: #4a5568; text-decoration: none; display: flex; align-items: center; padding: 11px 22px; font-weight: 600; font-size: 14px; transition: all 0.2s; }
        .sidebar-menu a i { width: 22px; margin-right: 10px; font-size: 16px; text-align: center; }
        .sidebar-menu a:hover { color: #1a202c; background-color: #f7fafc; }
        .sidebar-menu a.active { background-color: #0b1e36; color: #ffffff; border-radius: 6px; margin: 0 12px; }
        .sidebar-menu a.active i { color: #ffffff; }
        .sidebar-menu a.logout { color: #e53e3e; margin-top: 40px; }

        .top-navbar { height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; background-color: transparent; }
        .stat-card { border: none; border-radius: 12px; background: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .stat-icon { width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .card-custom { border: none; border-radius: 12px; background: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .chart-box { position: relative; height: 230px; width: 100%; }
        .badge-danger-soft { background-color: #fed7d7; color: #9b2c2c; font-weight: bold; border-radius: 12px; padding: 4px 10px; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-md-3 col-lg-2 sidebar p-0">
            <div class="sidebar-brand">
                <img src="https://ui-avatars.com/api/?name=La+Liguria&background=d4a373&color=fff" alt="Logo" class="sidebar-logo">
                <span class="fw-bold text-dark fs-6">LA LIGURIA S.A.</span>
            </div>
            <div class="sidebar-menu">
                <a href="dashboard.php" class="active"><i class="fa-solid fa-house"></i> Dashboard</a>
                <a href="productos.php"><i class="fa-solid fa-box-archive"></i> Productos</a>
                <a href="categorias.php"><i class="fa-solid fa-tag"></i> Categorías</a>
                <a href="proveedores.php"><i class="fa-solid fa-truck"></i> Proveedores</a>
                <a href="sedes.php"><i class="fa-solid fa-building"></i> Sedes</a>
                <a href="inventario.php"><i class="fa-solid fa-warehouse"></i> Inventario</a>
                <a href="movimientos.php"><i class="fa-solid fa-arrow-right-arrow-left"></i> Movimientos</a>
                <a href="reportes.php"><i class="fa-solid fa-chart-line"></i> Reportes</a>
                <a href="usuarios.php"><i class="fa-solid fa-users"></i> Usuarios</a>
                <a href="logout.php" class="logout"><i class="fa-solid fa-power-off"></i> Cerrar Sesión</a>
            </div>
        </div>

        <!-- Main Workspace -->
        <div class="col-md-9 col-lg-10 p-0">
            <!-- Header Bar -->
            <div class="top-navbar">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa-solid fa-bars fs-5 text-secondary"></i>
                    <h5 class="fw-bold m-0 text-dark">SISTEMA DE INVENTARIO</h5>
                </div>
                <div class="dropdown">
                    <button class="btn btn-white border-0 dropdown-toggle text-dark fw-medium" type="button" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-circle-user text-primary me-1"></i> <?php echo htmlspecialchars($nombreUsuario); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-class dropdown-item text-danger" href="logout.php"><i class="fa-solid fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>

            <div class="px-4 pb-4">
                <h4 class="fw-bold text-dark mb-4">Dashboard de Inventario</h4>

                <!-- Top Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card stat-card p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted fw-bold style-sub small">TOTAL PRODUCTOS</span>
                                    <h2 class="fw-bold m-0 mt-1"><?php echo $totalProductos; ?></h2>
                                </div>
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary fs-5">
                                    <i class="fa-solid fa-box"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card stat-card p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted fw-bold style-sub small">STOCK BAJO</span>
                                    <h2 class="fw-bold text-danger m-0 mt-1"><?php echo $totalAlertas; ?></h2>
                                </div>
                                <div class="stat-icon bg-danger bg-opacity-10 text-danger fs-5">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card stat-card p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted fw-bold style-sub small">ENTRADAS (HOY)</span>
                                    <h2 class="fw-bold text-success m-0 mt-1"><?php echo $totalEntradas; ?> <span class="fs-6 text-muted fw-normal">kg</span></h2>
                                </div>
                                <div class="stat-icon bg-success bg-opacity-10 text-success fs-5">
                                    <i class="fa-solid fa-arrow-down"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card stat-card p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted fw-bold style-sub small">SALIDAS (HOY)</span>
                                    <h2 class="fw-bold text-warning m-0 mt-1"><?php echo $totalSalidas; ?> <span class="fs-6 text-muted fw-normal">kg</span></h2>
                                </div>
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning fs-5">
                                    <i class="fa-solid fa-arrow-up"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-lg-6">
                        <div class="card card-custom p-3 h-100">
                            <h6 class="fw-bold text-dark mb-3">ENTRADAS VS SALIDAS</h6>
                            <div class="chart-box">
                                <canvas id="chartEntradasSalidas"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="card card-custom p-3 h-100">
                            <h6 class="fw-bold text-dark mb-3">EVOLUCIÓN DEL CONSUMO</h6>
                            <div class="chart-box">
                                <canvas id="chartConsumo"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Grid -->
                <div class="row g-3">
                    <div class="col-12 col-lg-4">
                        <div class="card card-custom p-3 h-100">
                            <h6 class="fw-bold text-dark mb-3">STOCK BAJO (ALERTAS)</h6>
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle mb-0 fs-7">
                                    <thead>
                                        <tr class="border-bottom text-muted">
                                            <th>Producto</th>
                                            <th class="text-center">Stock</th>
                                            <th class="text-center">Mínimo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($productosBajos)): ?>
                                            <tr><td colspan="3" class="text-center text-muted py-3">Sin alertas de stock</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($productosBajos as $pb): ?>
                                                <tr>
                                                    <td class="fw-medium"><?php echo htmlspecialchars($pb['nombre'] ?? ''); ?></td>
                                                    <td class="text-center"><span class="badge-danger-soft"><?php echo $pb['stock'] ?? 0; ?></span></td>
                                                    <td class="text-center text-muted"><?php echo $pb['min_stock'] ?? 0; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <div class="card card-custom p-3 h-100">
                            <h6 class="fw-bold text-dark mb-3">MOVIMIENTOS RECIENTES</h6>
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle mb-0 fs-7">
                                    <tbody>
                                        <?php if (empty($movimientosRecientes)): ?>
                                            <tr><td class="text-center text-muted py-3">No hay registros recientes</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($movimientosRecientes as $m): ?>
                                                <tr class="border-bottom">
                                                    <td>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($m['producto']['nombre'] ?? 'Producto'); ?></div>
                                                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($m['fecha'] ?? 'now')); ?></small>
                                                    </td>
                                                    <td class="text-end fw-bold <?php echo ($m['tipo'] ?? '') === 'Entrada' ? 'text-success' : 'text-warning'; ?>">
                                                        <?php echo (($m['tipo'] ?? '') === 'Entrada' ? '+' : '-') . ($m['cantidad'] ?? 0); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <div class="card card-custom p-3 h-100">
                            <h6 class="fw-bold text-dark mb-3">CATEGORÍAS PRINCIPALES</h6>
                            <div class="d-flex align-items-center justify-content-center text-muted h-75">
                                <span class="small">Sin datos de categorías</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Gráfico 1: Entradas vs Salidas por Día
    const ctx1 = document.getElementById('chartEntradasSalidas').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            datasets: [
                { label: 'Entradas', data: [0, 0, <?php echo $totalEntradas; ?>, 0, 0, 0, 0], backgroundColor: '#1e40af' },
                { label: 'Salidas', data: [0, 0, <?php echo $totalSalidas; ?>, 0, 0, 0, 0], backgroundColor: '#d97706' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, max: 10 } }
        }
    });

    // Gráfico 2: Evolución del Consumo
    const ctx2 = document.getElementById('chartConsumo').getContext('2d');
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
            datasets: [{
                label: 'Consumo',
                data: [0, 0, 0, 0, 0, 0],
                borderColor: '#1e40af',
                tension: 0.1,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, max: 10 } }
        }
    });
</script>
</body>
</html>