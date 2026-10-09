<?php
// cotizaciones.php - Módulo de Cotizaciones
require_once 'db.php';
check_auth();

$usuario = $_SESSION['usuario'];
$mensaje = '';
$error = '';

// Registrar nueva cotización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'crear_cotizacion') {
    $cliente_id = intval($_POST['cliente_id'] ?? 1);
    $detalles = trim($_POST['detalles'] ?? '');
    $total_estimado = floatval($_POST['total_estimado'] ?? 0);
    $estado = $_POST['estado'] ?? 'borrador';
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $mecanico_id = $usuario['id'];

    if (!empty($detalles) && $total_estimado > 0) {
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO cotizaciones (cliente_id, mecanico_id, detalles, total_estimado, estado, fecha) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$cliente_id, $mecanico_id, $detalles, $total_estimado, $estado, $fecha]);
                $mensaje = '¡Cotización registrada correctamente!';
            } catch (PDOException $e) {
                $error = 'Error al registrar la cotización: ' . $e->getMessage();
            }
        } else {
            $mensaje = 'Cotización creada exitosamente (Modo Demo)';
        }
    } else {
        $error = 'Por favor complete todos los detalles y monto estimado.';
    }
}

// Obtener Cotizaciones
$cotizaciones = [];
if ($pdo) {
    try {
        $query = "SELECT q.*, c.nombre as cliente_nombre, c.vehiculo_modelo, u.nombre as mecanico_nombre 
                  FROM cotizaciones q
                  JOIN clientes c ON q.cliente_id = c.id
                  JOIN usuarios u ON q.mecanico_id = u.id
                  ORDER BY q.id DESC";
        $cotizaciones = $pdo->query($query)->fetchAll();
    } catch (Exception $e) {}
}

// Datos de demostración en caso de no tener DB activa aún
if (empty($cotizaciones)) {
    $cotizaciones = [
        [
            'id' => 1,
            'cliente_nombre' => 'Juan Pérez',
            'vehiculo_modelo' => 'Toyota Corolla 2018',
            'mecanico_nombre' => 'Carlos Mecánico',
            'detalles' => 'Cambio de amortiguadores traseros y alineación',
            'total_estimado' => 190.00,
            'estado' => 'enviada',
            'fecha' => '2026-10-09'
        ]
    ];
}

include 'header.php';
?>

<div class="glass-card">
    <div class="card-title" style="justify-content: space-between;">
        <span><i class="fa-solid fa-file-invoice-dollar"></i> Módulo de Cotizaciones & Presupuestos</span>
        <button onclick="document.getElementById('modal-nueva-cotizacion').style.display='block'" class="btn-primary" style="width: auto; padding: 8px 16px; font-size: 0.85rem;">
            <i class="fa-solid fa-plus"></i> Nueva Cotización
        </button>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th># ID</th>
                    <th>Cliente / Vehículo</th>
                    <th>Detalles del Presupuesto</th>
                    <th>Mecánico Emisor</th>
                    <th>Total Estimado</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cotizaciones as $q): ?>
                    <tr>
                        <td>#<?= $q['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($q['cliente_nombre']) ?></strong><br>
                            <small style="color: var(--text-muted);"><?= htmlspecialchars($q['vehiculo_modelo']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($q['detalles']) ?></td>
                        <td><?= htmlspecialchars($q['mecanico_nombre']) ?></td>
                        <td><strong style="color: var(--accent-pink);">$<?= number_format($q['total_estimado'], 2) ?></strong></td>
                        <td><span class="status-tag status-<?= $q['estado'] ?>"><?= strtoupper($q['estado']) ?></span></td>
                        <td><?= $q['fecha'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Registro de Cotización -->
<div id="modal-nueva-cotizacion" class="glass-card" style="display: none; max-width: 600px; margin: 1rem auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3>Crear Presupuesto / Cotización</h3>
        <button onclick="document.getElementById('modal-nueva-cotizacion').style.display='none'" style="background: none; border: border; color: white; font-size: 1.2rem; cursor: pointer;">&times;</button>
    </div>

    <form action="cotizaciones.php" method="POST">
        <input type="hidden" name="action" value="crear_cotizacion">

        <div class="form-group">
            <label for="cliente_id_cot">Cliente / Vehículo</label>
            <select name="cliente_id" id="cliente_id_cot" class="form-control" required>
                <option value="1">Juan Pérez - Toyota Corolla 2018</option>
                <option value="2">María Gómez - Chevrolet Spark 2012</option>
            </select>
        </div>

        <div class="form-group">
            <label for="detalles">Detalles de Repuestos y Mano de Obra</label>
            <textarea name="detalles" id="detalles" class="form-control" rows="3" placeholder="Ej: Kit de embrague $120, Mano de obra $50..." required></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="total_estimado">Total Estimado ($ USD)</label>
                <input type="number" step="0.01" name="total_estimado" id="total_estimado" class="form-control" placeholder="0.00" required>
            </div>

            <div class="form-group">
                <label for="estado_cot">Estado inicial</label>
                <select name="estado" id="estado_cot" class="form-control" required>
                    <option value="borrador">Borrador</option>
                    <option value="enviada">Enviada al Cliente</option>
                    <option value="aprobada">Aprobada</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="fecha">Fecha</label>
            <input type="date" name="fecha" id="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>

        <button type="submit" class="btn-primary">Guardar Cotización</button>
    </form>
</div>

<?php include 'footer.php'; ?>
