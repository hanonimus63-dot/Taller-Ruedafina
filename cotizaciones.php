<?php
// cotizaciones.php - Módulo CRUD de Cotizaciones
require_once 'db.php';
check_auth();

$usuario = $_SESSION['usuario'];
$mensaje = '';
$error = '';

// Procesar Acciones CRUD (Crear / Editar / Cambiar Estado / Eliminar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'crear_cotizacion') {
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
                    $error = 'Error al registrar cotización: ' . $e->getMessage();
                }
            } else {
                $mensaje = 'Cotización creada exitosamente (Modo Demo)';
            }
        } else {
            $error = 'Por favor ingrese los detalles y un monto válido.';
        }
    }

    if ($action === 'editar_cotizacion') {
        $id = intval($_POST['id'] ?? 0);
        $cliente_id = intval($_POST['cliente_id'] ?? 1);
        $detalles = trim($_POST['detalles'] ?? '');
        $total_estimado = floatval($_POST['total_estimado'] ?? 0);
        $estado = $_POST['estado'] ?? 'borrador';
        $fecha = $_POST['fecha'] ?? date('Y-m-d');

        if ($id > 0 && !empty($detalles) && $total_estimado > 0) {
            if ($pdo) {
                try {
                    $stmt = $pdo->prepare("UPDATE cotizaciones SET cliente_id = ?, detalles = ?, total_estimado = ?, estado = ?, fecha = ? WHERE id = ?");
                    $stmt->execute([$cliente_id, $detalles, $total_estimado, $estado, $fecha, $id]);
                    $mensaje = '¡Cotización actualizada con éxito!';
                } catch (PDOException $e) {
                    $error = 'Error al actualizar cotización: ' . $e->getMessage();
                }
            }
        }
    }

    if ($action === 'eliminar_cotizacion') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0 && $pdo) {
            try {
                $stmt = $pdo->prepare("DELETE FROM cotizaciones WHERE id = ?");
                $stmt->execute([$id]);
                $mensaje = '¡Cotización eliminada correctamente!';
            } catch (PDOException $e) {
                $error = 'Error al eliminar cotización: ' . $e->getMessage();
            }
        }
    }
}

// Obtener Clientes para selects de los modales
$clientes = [];
if ($pdo) {
    try {
        $clientes = $pdo->query("SELECT id, nombre, vehiculo_modelo FROM clientes ORDER BY nombre ASC")->fetchAll();
    } catch (Exception $e) {}
}
if (empty($clientes)) {
    $clientes = [
        ['id' => 1, 'nombre' => 'Juan Pérez', 'vehiculo_modelo' => 'Toyota Corolla 2018'],
        ['id' => 2, 'nombre' => 'María Gómez', 'vehiculo_modelo' => 'Chevrolet Spark 2012']
    ];
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

if (empty($cotizaciones)) {
    $cotizaciones = [
        [
            'id' => 1,
            'cliente_id' => 1,
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
                    <th>Acciones</th>
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
                        <td>
                            <button onclick="editarCotizacion(<?= htmlspecialchars(json_encode($q)) ?>)" class="btn-primary" style="padding: 4px 10px; font-size: 0.75rem; width: auto; background: #c89659; display: inline-block;">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="cotizaciones.php" method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar esta cotización?');">
                                <input type="hidden" name="action" value="eliminar_cotizacion">
                                <input type="hidden" name="id" value="<?= $q['id'] ?>">
                                <button type="submit" class="btn-primary" style="padding: 4px 10px; font-size: 0.75rem; width: auto; background: #ef4444; display: inline-block;">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Crear Cotización -->
<div id="modal-nueva-cotizacion" class="glass-card" style="display: none; max-width: 600px; margin: 1rem auto; position: fixed; top: 10%; left: 50%; transform: translateX(-50%); z-index: 1000; width: 90%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3>Crear Presupuesto / Cotización</h3>
        <button onclick="document.getElementById('modal-nueva-cotizacion').style.display='none'" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
    </div>

    <form action="cotizaciones.php" method="POST">
        <input type="hidden" name="action" value="crear_cotizacion">

        <div class="form-group">
            <label for="cliente_id_cot">Cliente / Vehículo</label>
            <select name="cliente_id" id="cliente_id_cot" class="form-control" required>
                <?php foreach ($clientes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?> - <?= htmlspecialchars($c['vehiculo_modelo']) ?></option>
                <?php endforeach; ?>
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
                    <option value="rechazada">Rechazada</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="fecha">Fecha</label>
            <input type="date" name="fecha" id="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>

        <button type="submit" class="btn-primary" style="margin-top: 1rem;">Guardar Cotización</button>
    </form>
</div>

<!-- Modal Editar Cotización -->
<div id="modal-editar-cotizacion" class="glass-card" style="display: none; max-width: 600px; margin: 1rem auto; position: fixed; top: 10%; left: 50%; transform: translateX(-50%); z-index: 1000; width: 90%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3>Editar Cotización</h3>
        <button onclick="document.getElementById('modal-editar-cotizacion').style.display='none'" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
    </div>

    <form action="cotizaciones.php" method="POST">
        <input type="hidden" name="action" value="editar_cotizacion">
        <input type="hidden" name="id" id="edit_cot_id">

        <div class="form-group">
            <label for="edit_cliente_id">Cliente / Vehículo</label>
            <select name="cliente_id" id="edit_cliente_id" class="form-control" required>
                <?php foreach ($clientes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?> - <?= htmlspecialchars($c['vehiculo_modelo']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="edit_detalles">Detalles de Repuestos y Mano de Obra</label>
            <textarea name="detalles" id="edit_detalles" class="form-control" rows="3" required></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="edit_total_estimado">Total Estimado ($ USD)</label>
                <input type="number" step="0.01" name="total_estimado" id="edit_total_estimado" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="edit_estado">Estado</label>
                <select name="estado" id="edit_estado" class="form-control" required>
                    <option value="borrador">Borrador</option>
                    <option value="enviada">Enviada al Cliente</option>
                    <option value="aprobada">Aprobada</option>
                    <option value="rechazada">Rechazada</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="edit_fecha">Fecha</label>
            <input type="date" name="fecha" id="edit_fecha" class="form-control" required>
        </div>

        <button type="submit" class="btn-primary" style="margin-top: 1rem;">Actualizar Cotización</button>
    </form>
</div>

<script>
function editarCotizacion(q) {
    document.getElementById('edit_cot_id').value = q.id;
    document.getElementById('edit_cliente_id').value = q.cliente_id;
    document.getElementById('edit_detalles').value = q.detalles;
    document.getElementById('edit_total_estimado').value = q.total_estimado;
    document.getElementById('edit_estado').value = q.estado;
    document.getElementById('edit_fecha').value = q.fecha;
    document.getElementById('modal-editar-cotizacion').style.display = 'block';
}
</script>

<?php include 'footer.php'; ?>
