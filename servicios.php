<?php
// servicios.php - Historial y Módulo de Servicios
require_once 'db.php';
check_auth();

$usuario = $_SESSION['usuario'];
$mensaje = '';
$error = '';

// Registrar nuevo servicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'crear_servicio') {
    $cliente_id = intval($_POST['cliente_id'] ?? 1);
    $tipo_servicio = trim($_POST['tipo_servicio'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $costo = floatval($_POST['costo'] ?? 0);
    $estado = $_POST['estado'] ?? 'pendiente';
    $fecha_servicio = $_POST['fecha_servicio'] ?? date('Y-m-d');
    $mecanico_id = $usuario['id'];

    if (!empty($tipo_servicio)) {
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO servicios (cliente_id, mecanico_id, tipo_servicio, descripcion, costo, estado, fecha_servicio) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$cliente_id, $mecanico_id, $tipo_servicio, $descripcion, $costo, $estado, $fecha_servicio]);
                $mensaje = '¡Servicio registrado exitosamente!';
            } catch (PDOException $e) {
                $error = 'Error al registrar servicio: ' . $e->getMessage();
            }
        } else {
            $mensaje = 'Servicio guardado exitosamente (Modo Demo)';
        }
    } else {
        $error = 'Ingrese el tipo de servicio.';
    }
}

// Obtener Servicios
$servicios = [];
if ($pdo) {
    try {
        $query = "SELECT s.*, c.nombre as cliente_nombre, c.vehiculo_placa, c.vehiculo_modelo, u.nombre as mecanico_nombre 
                  FROM servicios s
                  JOIN clientes c ON s.cliente_id = c.id
                  JOIN usuarios u ON s.mecanico_id = u.id
                  ORDER BY s.id DESC";
        $servicios = $pdo->query($query)->fetchAll();
    } catch (Exception $e) {}
}

// Datos de demostración en caso de no tener DB activa aún
if (empty($servicios)) {
    $servicios = [
        [
            'id' => 1,
            'cliente_nombre' => 'Juan Pérez',
            'vehiculo_modelo' => 'Toyota Corolla 2018',
            'vehiculo_placa' => 'AB123CD',
            'mecanico_nombre' => 'Carlos Mecánico',
            'tipo_servicio' => 'Cambio de Aceite y Filtro',
            'descripcion' => 'Aceite sintético 5W30 y filtro original',
            'costo' => 45.00,
            'estado' => 'completado',
            'fecha_servicio' => '2026-10-01'
        ],
        [
            'id' => 2,
            'cliente_nombre' => 'María Gómez',
            'vehiculo_modelo' => 'Chevrolet Spark 2012',
            'vehiculo_placa' => 'XY987ZT',
            'mecanico_nombre' => 'Carlos Mecánico',
            'tipo_servicio' => 'Revisión del Sistema de Frenos',
            'descripcion' => 'Cambio de pastillas delanteras y rectificación de discos',
            'costo' => 85.00,
            'estado' => 'en_proceso',
            'fecha_servicio' => '2026-10-08'
        ]
    ];
}

include 'header.php';
?>

<div class="glass-card">
    <div class="card-title" style="justify-content: space-between;">
        <span><i class="fa-solid fa-gears"></i> Módulo de Servicios & Historial</span>
        <button onclick="document.getElementById('modal-nuevo-servicio').style.display='block'" class="btn-primary" style="width: auto; padding: 8px 16px; font-size: 0.85rem;">
            <i class="fa-solid fa-plus"></i> Registrar Servicio
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
                    <th>Placa</th>
                    <th>Servicio Realizado</th>
                    <th>Mecánico</th>
                    <th>Costo</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($servicios as $s): ?>
                    <tr>
                        <td>#<?= $s['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($s['cliente_nombre']) ?></strong><br>
                            <small style="color: var(--text-muted);"><?= htmlspecialchars($s['vehiculo_modelo']) ?></small>
                        </td>
                        <td><code style="background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($s['vehiculo_placa']) ?></code></td>
                        <td>
                            <strong><?= htmlspecialchars($s['tipo_servicio']) ?></strong><br>
                            <small style="color: var(--text-muted);"><?= htmlspecialchars($s['descripcion']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($s['mecanico_nombre']) ?></td>
                        <td><strong style="color: var(--accent-teal);">$<?= number_format($s['costo'], 2) ?></strong></td>
                        <td><span class="status-tag status-<?= $s['estado'] ?>"><?= str_replace('_', ' ', $s['estado']) ?></span></td>
                        <td><?= $s['fecha_servicio'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal / Formulario de Registro de Servicio -->
<div id="modal-nuevo-servicio" class="glass-card" style="display: none; max-width: 600px; margin: 1rem auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3>Registrar Nuevo Servicio Mecánico</h3>
        <button onclick="document.getElementById('modal-nuevo-servicio').style.display='none'" style="background: none; border: none; color: white; font-size: 1.2rem; cursor: pointer;">&times;</button>
    </div>

    <form action="servicios.php" method="POST">
        <input type="hidden" name="action" value="crear_servicio">
        
        <div class="form-group">
            <label for="cliente_id">Cliente / Vehículo</label>
            <select name="cliente_id" id="cliente_id" class="form-control" required>
                <option value="1">Juan Pérez - Toyota Corolla (AB123CD)</option>
                <option value="2">María Gómez - Chevrolet Spark (XY987ZT)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="tipo_servicio">Tipo de Servicio</label>
            <input type="text" name="tipo_servicio" id="tipo_servicio" class="form-control" placeholder="Ej: Cambio de Correa de Tiempo" required>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción del Diagnóstico / Reparación</label>
            <textarea name="descripcion" id="descripcion" class="form-control" rows="3" placeholder="Detalle el mantenimiento realizado..."></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="costo">Costo ($ USD)</label>
                <input type="number" step="0.01" name="costo" id="costo" class="form-control" placeholder="0.00" required>
            </div>

            <div class="form-group">
                <label for="estado">Estado del Servicio</label>
                <select name="estado" id="estado" class="form-control" required>
                    <option value="pendiente">Pendiente</option>
                    <option value="en_proceso">En Proceso</option>
                    <option value="completado">Completado</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="fecha_servicio">Fecha del Servicio</label>
            <input type="date" name="fecha_servicio" id="fecha_servicio" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>

        <button type="submit" class="btn-primary">Guardar Servicio</button>
    </form>
</div>

<?php include 'footer.php'; ?>
