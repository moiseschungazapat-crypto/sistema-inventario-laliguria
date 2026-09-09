<?php
$resCategorias = obtenerArrayData('categorias?select=nombre');
$resMovimientos = obtenerArrayData('movimientos?select=tipo,cantidad');

$totalEntradas = 0;
$totalSalidas = 0;

// Se verifica que $resMovimientos sea un array y cada elemento contenga las llaves antes del acceso
if (is_array($resMovimientos)) {
    foreach ($resMovimientos as $m) {
        if (is_array($m) && isset($m['tipo'], $m['cantidad']) && is_numeric($m['cantidad'])) {
            if ($m['tipo'] === 'Entrada') {
                $totalEntradas += (int)$m['cantidad'];
            } elseif ($m['tipo'] === 'Salida') {
                $totalSalidas += (int)$m['cantidad'];
            }
        }
    }
}
?>
<div class="card-custom p-4">
    <h4 class="fw-bold mb-4">Reportes de Inventario</h4>
    <div class="row">
        <div class="col-md-6">
            <h6>Consumo Total (Entradas vs Salidas)</h6>
            <div style="height: 250px;">
                <canvas id="chartReporteMovimientos"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
new Chart(document.getElementById('chartReporteMovimientos'), {
    type: 'pie',
    data: {
        labels: ['Entradas', 'Salidas'],
        datasets: [{
            data: [<?= $totalEntradas ?>, <?= $totalSalidas ?>],
            backgroundColor: ['#198754', '#dc3545']
        }]
    },
    options: { responsive: true, maintainAspectRatio: false }
});
</script>