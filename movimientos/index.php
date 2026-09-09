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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productoId = (int)$_POST['producto_id'];
    $tipo = $_POST['tipo'];
    $cantidad = (int)$_POST['cantidad'];
    $sedeId = (int)$_POST['sede_id'];

    // 1. Registrar el movimiento
    $nuevoMovimiento = [
        'producto_id' => $productoId,
        'sede_id' => $sedeId,
        'tipo' => $tipo,
        'cantidad' => $cantidad,
        'usuario_id' => $_SESSION['usuario']['id'] ?? null
    ];
    supabase_request('movimientos', 'POST', $nuevoMovimiento);

    // 2. Actualizar stock del producto
    $prodActual = supabase_request("productos?id=eq.$productoId&select=stock")['data'][0] ?? null;
    if ($prodActual) {
        $nuevoStock = ($tipo === 'Entrada') 
            ? $prodActual['stock'] + $cantidad 
            : $prodActual['stock'] - $cantidad;
        
        supabase_request("productos?id=eq.$productoId", 'PATCH', ['stock' => max(0, $nuevoStock)]);
    }

    header('Location: index.php');
    exit;
}

$movimientos = supabase_request('movimientos?select=*,productos(nombre),sedes(nombre)&order=fecha.desc')['data'] ?? [];
$productos = supabase_request('productos?select=id,nombre')['data'] ?? [];
$sedes = supabase_request('sedes?select=id,nombre')['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Movimientos - LA LIGURIA S.A.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light p-4">
    <div class="container bg-white p-4 rounded shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Entradas y Salidas de Inventario</h3>
            <a href="../dashboard.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Volver</a>
        </div>

        <form method="POST" class="row g-3 mb-4 border p-3 rounded">
            <div class="col-md-3">
                <label class="form-label">Producto</label>
                <select name="producto_id" class="form-select" required>
                    <?php foreach ($productos as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Sede</label>
                <select name="sede_id" class="form-select" required>
                    <?php foreach ($sedes as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select" required>
                    <option value="Entrada">Entrada</option>
                    <option value="Salida">Salida</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Cantidad</label>
                <input type="number" name="cantidad" class="form-control" min="1" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100"><i class="fa-solid fa-check"></i> Registrar</button>
            </div>
        </form>

        <table class="table table-sm align-middle">
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
                <?php foreach ($movimientos as $m): ?>
                <tr>
                    <td><?= date('Y-m-d H:i', strtotime($m['fecha'])) ?></td>
                    <td><?= htmlspecialchars($m['productos']['nombre'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($m['sedes']['nombre'] ?? 'N/A') ?></td>
                    <td>
                        <span class="badge bg-<?= $m['tipo'] === 'Entrada' ? 'success' : 'danger' ?>">
                            <?= $m['tipo'] ?>
                        </span>
                    </td>
                    <td><?= $m['cantidad'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>