<?php
$movimientos = obtenerArrayData('movimientos?select=id,fecha,tipo,cantidad,productos(nombre),sedes(nombre)&order=fecha.desc');
$productos = obtenerArrayData('productos?select=id,nombre');
$sedes = obtenerArrayData('sedes?select=id,nombre');
?>
<div class="card-custom p-4">
    <h4 class="fw-bold mb-4">Entradas y Salidas de Inventario</h4>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Sede</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movimientos)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">No hay movimientos registrados.</td></tr>
                <?php else: ?>
                    <?php foreach ($movimientos as $m): ?>
                        <?php if (is_array($m)): ?>
                        <tr>
                            <td><?= isset($m['fecha']) ? date('Y-m-d H:i', strtotime($m['fecha'])) : '-' ?></td>
                            <td><?= htmlspecialchars($m['productos']['nombre'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($m['sedes']['nombre'] ?? 'N/A') ?></td>
                            <td>
                                <span class="badge bg-<?= (isset($m['tipo']) && $m['tipo'] === 'Entrada') ? 'success' : 'danger' ?>">
                                    <?= htmlspecialchars($m['tipo'] ?? 'Desconocido') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($m['cantidad'] ?? 0) ?></td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>