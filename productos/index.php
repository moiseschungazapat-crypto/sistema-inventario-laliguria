<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../config/supabase.php';

// Procesar formulario de creación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevoProducto = [
        'nombre' => $_POST['nombre'],
        'categoria_id' => !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null,
        'stock' => (int)$_POST['stock'],
        'min_stock' => (int)$_POST['min_stock'],
        'unidad' => $_POST['unidad']
    ];
    supabase_request('productos', 'POST', $nuevoProducto);
    header('Location: index.php');
    exit;
}

// Obtener productos y categorías con consultas a Supabase
$productos = supabase_request('productos?select=*,categorias(nombre)')['data'] ?? [];
$categorias = supabase_request('categorias?select=id,nombre')['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos - LA LIGURIA S.A.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light p-4">
    <div class="container bg-white p-4 rounded shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Gestión de Productos</h3>
            <a href="../dashboard.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Volver</a>
        </div>

        <!-- Formulario para agregar -->
        <form method="POST" class="row g-3 mb-4 border p-3 rounded">
            <div class="col-md-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Categoría</label>
                <select name="categoria_id" class="form-select">
                    <option value="">Seleccionar...</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Stock inicial</label>
                <input type="number" name="stock" class="form-control" value="0" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Stock Mínimo</label>
                <input type="number" name="min_stock" class="form-control" value="5" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-plus"></i> Guardar</button>
            </div>
        </form>

        <!-- Tabla de datos -->
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Stock</th>
                    <th>Mínimo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['categorias']['nombre'] ?? 'Sin categoría') ?></td>
                    <td><?= $p['stock'] ?></td>
                    <td><?= $p['min_stock'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>