<?php
require_once __DIR__ . '/../config/supabase.php';
require_once __DIR__ . '/../header.php';

// Consultar productos en Supabase
$response = supabase_request('productos?select=*,categorias(nombre),sedes(nombre)', 'GET');
$productos = $response['data'] ?? [];
?>

<div class="contenedor-pagina">
    <div class="encabezado-seccion">
        <h1>Inventario de Productos</h1>
        <a href="/productos/crear.php" class="btn btn-primario">+ Nuevo Producto</a>
    </div>

    <table class="tabla-datos">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Sede</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($productos)): ?>
                <tr><td colspan="6">No se encontraron productos registrados.</td></tr>
            <?php else: ?>
                <?php foreach ($productos as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['codigo'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['nombre'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['categorias']['nombre'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($item['sedes']['nombre'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($item['stock'] ?? 0) ?></td>
                        <td>
                            <a href="/productos/editar.php?id=<?= $item['id'] ?>">Editar</a>
                            <a href="/productos/eliminar.php?id=<?= $item['id'] ?>" class="text-rojo">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>