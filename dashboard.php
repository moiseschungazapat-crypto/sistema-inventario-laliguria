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

// Alertas de stock bajo comparando dinámicamente con min_stock
$productosBajos = obtenerArrayData('productos?stock=lte.min_stock&select=id,nombre,stock,min_stock,unidad');
$totalAlertas = count($productosBajos);

$movimientosRecientes = obtenerArrayData('movimientos?select=id,tipo,cantidad,fecha,producto:productos(nombre),usuario:usuarios(nombre),sede:sedes(nombre)&order=fecha.desc&limit=5');

// Fecha con timestamp ISO 8601 compatible con Supabase
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
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background-color: #ffffff; border-right: 1px solid #e0e0e0; }
        .sidebar-brand { padding: 20px 15px; text-align: left; border-bottom: 1px solid #f0f0f0; }
        .sidebar-brand img { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; }
        .sidebar-menu a { color: #555; text-decoration: none; display: flex; align-items: center; padding: 12px 20px; font-weight: 600; border-radius: 8px; margin: 4px 10px; font-size: 14px; }
        .sidebar-menu a:hover { background-color: #f0f4f8; color: #0d233a; }
        .sidebar-menu a.active { background-color: #0d233a; color: #ffffff; }
        .stat-card { border: none; border-radius: 12px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .card-custom { border: none; border-radius: 12px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .badge-critic { background-color: #dc3545; color: #fff; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: bold; }
        .chart-container { position: relative; height: 250px; width: 100%; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar p-0">
            <div class="sidebar-brand d-flex align-items-center gap-2 px-3">
                <img src="logo.jpg" alt="Logo" onerror="this.style.display='none'">
                <span class="fw-bold text-dark fs-6">LA LIGURIA S.A.</span>
            </div>
            <div class="sidebar-menu mt-3">
                <a href="dashboard.php" class="active"><i class="fa-solid fa-chart-pie me-3"></i> Dashboard</a>
                <a href="productos.php"><i class="fa-solid fa-boxes-stacked me-3"></i> Productos</a>
                <a href="movimientos.php"><i class="fa-solid fa-right-left me-3"></i> Movimientos</a>
                <a href="sedes.php"><i class="fa-solid fa-building me-3"></i> Sedes</a>
                <a href="usuarios.php"><i class="fa-solid fa-users me-3"></i> Usuarios</a>
                <a href="logout.php" class="text-danger mt-5"><i class="fa-solid fa-right-from-bracket me-3"></i> Cerrar Sesión</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-dark mb-1">Panel del Control</h3>
                    <p class="text-muted small mb-0">Resumen general del inventario y movimientos recientes.</p>
                </div>
                <div>
                    <span class="badge bg-white text-dark border py-2 px-3 rounded-3 shadow-sm">
                        <i class="fa-regular fa-calendar me-1"></i> <?php echo date('d/m/Y'); ?>
                    </span>
                </div>
            </div>

            <!-- Cards -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary">
                                <i class="fa-solid fa-box fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted small mb-1">Total Productos</h6>
                                <h4 class="fw-bold mb-0"><?php echo $totalProductos; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3 text-danger">
                                <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted small mb-1">Alertas Stock</h6>
                                <h4 class="fw-bold mb-0"><?php echo $totalAlertas; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3 text-success">
                                <i class="fa-solid fa-arrow-down fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted small mb-1">Entradas de Hoy</h6>
                                <h4 class="fw-bold mb-0"><?php echo $totalEntradas; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3 text-warning">
                                <i class="fa-solid fa-arrow-up fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted small mb-1">Salidas de Hoy</h6>
                                <h4 class="fw-bold mb-0"><?php echo $totalSalidas; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico y Alertas -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-lg-7">
                    <div class="card card-custom p-3 h-100">
                        <h6 class="fw-bold text-dark mb-3">Flujo del Día (Entradas vs Salidas)</h6>
                        <div class="chart-container">
                            <canvas id="flujoChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="card card-custom p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0">Stock Bajo / Crítico</h6>
                            <a href="productos.php" class="text-decoration-none small">Ver todo</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light fs-7">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Stock</th>
                                        <th class="text-center">Mínimo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($productosBajos)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted small py-3">Sin alertas de stock.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($productosBajos as $pb): ?>
                                            <tr>
                                                <td class="fw-medium small"><?php echo htmlspecialchars($pb['nombre'] ?? ''); ?></td>
                                                <td class="text-center"><span class="badge badge-critic"><?php echo $pb['stock'] ?? 0; ?></span></td>
                                                <td class="text-center text-muted small"><?php echo $pb['min_stock'] ?? 0; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla Movimientos -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-custom p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0">Últimos Movimientos Registrados</h6>
                            <a href="movimientos.php" class="text-decoration-none small">Ver historial completo</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 fs-7">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Producto</th>
                                        <th class="text-center">Cantidad</th>
                                        <th>Sede</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($movimientosRecientes)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted small py-3">No hay movimientos recientes registrados.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($movimientosRecientes as $mov): ?>
                                            <tr>
                                                <td class="small text-muted"><?php echo isset($mov['fecha']) ? date('d/m/Y H:i', strtotime($mov['fecha'])) : '-'; ?></td>
                                                <td>
                                                    <?php if (($mov['tipo'] ?? '') === 'Entrada'): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success">Entrada</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning bg-opacity-10 text-warning">Salida</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="fw-medium"><?php echo htmlspecialchars($mov['producto']['nombre'] ?? 'Desconocido'); ?></td>
                                                <td class="text-center fw-bold"><?php echo $mov['cantidad'] ?? 0; ?></td>
                                                <td class="small"><?php echo htmlspecialchars($mov['sede']['nombre'] ?? 'N/A'); ?></td>
                                                <td class="small text-muted"><?php echo htmlspecialchars($mov['usuario']['nombre'] ?? 'Sistema'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('flujoChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Entradas de Hoy', 'Salidas de Hoy'],
            datasets: [{
                label: 'Cantidad',
                data: [<?php echo $totalEntradas; ?>, <?php echo $totalSalidas; ?>],
                backgroundColor: ['rgba(25, 135, 84, 0.8)', 'rgba(255, 193, 7, 0.8)'],
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>
</body>
</html>