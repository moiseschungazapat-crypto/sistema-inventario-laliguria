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
require_once __DIR__ . '/../config/supabase.php';

$resCategorias = supabase_request('categorias?select=nombre,productos(count)') ['data'] ?? [];
$resMovimientos = supabase_request('movimientos?select=tipo,cantidad') ['data'] ?? [];

$totalEntradas = 0;
$totalSalidas = 0;

foreach ($resMovimientos as $m) {
    if ($m['tipo'] === 'Entrada') {
        $totalEntradas += $m['cantidad'];
    } elseif ($m['tipo'] === 'Salida') {
        $totalSalidas += $m['cantidad'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes - LA LIGURIA S.A.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light p-4">
    <div class="container bg-white p-4 rounded shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Reportes Generales</h3>
            <a href="../dashboard.php" class="btn btn-secondary btn-sm">Volver</a>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h5 class="text-center">Resumen de Movimientos Total</h5>
                <canvas id="chartMovimientos"></canvas>
            </div>
        </div>
    </div>

    <script>
    new Chart(document.getElementById('chartMovimientos'), {
        type: 'pie',
        data: {
            labels: ['Total Entradas', 'Total Salidas'],
            datasets: [{
                data: [<?= $totalEntradas ?>, <?= $totalSalidas ?>],
                backgroundColor: ['#198754', '#dc3545']
            }]
        }
    });
    </script>
</body>
</html>