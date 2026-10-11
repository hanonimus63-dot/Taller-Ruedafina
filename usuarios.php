<?php
// usuarios.php - Módulo CRUD de Usuarios (Solo Administradores)
require_once 'db.php';
check_auth(['admin']);

$usuario_sesion = $_SESSION['usuario'];
$mensaje = '';
$error = '';

// Procesar Acciones POST (Crear / Editar / Eliminar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'crear_usuario') {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $rol = $_POST['rol'] ?? 'mecanico';

        if (!empty($nombre) && !empty($email) && !empty($password)) {
            if ($pdo) {
                try {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$nombre, $email, $hash, $rol]);
                    $mensaje = '¡Usuario creado exitosamente!';
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000 || strpos($e->getMessage(), '1062') !== false) {
                        $error = 'El correo electrónico ya se encuentra registrado.';
                    } else {
                        $error = 'Error en la base de datos: ' . $e->getMessage();
                    }
                }
            }
        } else {
            $error = 'Por favor llene todos los campos del formulario.';
        }
    }

    if ($action === 'editar_usuario') {
        $id = intval($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $rol = $_POST['rol'] ?? 'mecanico';
        $password = trim($_POST['password'] ?? '');

        if ($id > 0 && !empty($nombre) && !empty($email)) {
            if ($pdo) {
                try {
                    if (!empty($password)) {
                        $hash = password_hash($password, PASSWORD_BCRYPT);
                        $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ?, rol = ?, password = ? WHERE id = ?");
                        $stmt->execute([$nombre, $email, $rol, $hash, $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id = ?");
                        $stmt->execute([$nombre, $email, $rol, $id]);
                    }
                    $mensaje = '¡Usuario actualizado correctamente!';
                } catch (PDOException $e) {
                    $error = 'Error al actualizar usuario: ' . $e->getMessage();
                }
            }
        }
    }

    if ($action === 'eliminar_usuario') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            if ($id === $usuario_sesion['id']) {
                $error = 'No puedes eliminar tu propia cuenta en sesión.';
            } else if ($pdo) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
                    $stmt->execute([$id]);
                    $mensaje = '¡Usuario eliminado con éxito!';
                } catch (PDOException $e) {
                    $error = 'Error al eliminar usuario: ' . $e->getMessage();
                }
            }
        }
    }
}

// Obtener Usuarios
$usuarios = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, nombre, email, rol, creado_en FROM usuarios ORDER BY id DESC");
        $usuarios = $stmt->fetchAll();
    } catch (Exception $e) {}
}

if (empty($usuarios)) {
    $usuarios = [
        ['id' => 1, 'nombre' => 'Miguel Admin', 'email' => 'admin@ruedafina.com', 'rol' => 'admin', 'creado_en' => date('Y-m-d H:i:s')],
        ['id' => 2, 'nombre' => 'Carlos Mecánico', 'email' => 'mecanico@ruedafina.com', 'rol' => 'mecanico', 'creado_en' => date('Y-m-d H:i:s')]
    ];
}

include 'header.php';
?>

<div class="glass-card">
    <div class="card-title" style="justify-content: space-between;">
        <span><i class="fa-solid fa-users-gear"></i> Gestión de Usuarios & Personal</span>
        <button onclick="document.getElementById('modal-crear-usuario').style.display='block'" class="btn-primary" style="width: auto; padding: 8px 16px; font-size: 0.85rem;">
            <i class="fa-solid fa-user-plus"></i> Nuevo Usuario
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
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo Electrónico</th>
                    <th>Rol</th>
                    <th>Fecha Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td>#<?= $u['id'] ?></td>
                        <td><strong><?= htmlspecialchars($u['nombre']) ?></strong></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="user-role badge-<?= htmlspecialchars($u['rol']) ?>"><?= strtoupper(htmlspecialchars($u['rol'])) ?></span></td>
                        <td><?= $u['creado_en'] ?></td>
                        <td>
                            <button onclick="editarUsuario(<?= htmlspecialchars(json_encode($u)) ?>)" class="btn-primary" style="padding: 4px 10px; font-size: 0.75rem; width: auto; background: #c89659; display: inline-block;">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <?php if ($u['id'] !== $usuario_sesion['id']): ?>
                                <form action="usuarios.php" method="POST" style="display: inline;" onsubmit="return confirm('¿Confirma eliminar este usuario?');">
                                    <input type="hidden" name="action" value="eliminar_usuario">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn-primary" style="padding: 4px 10px; font-size: 0.75rem; width: auto; background: #ef4444; display: inline-block;">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Crear Usuario -->
<div id="modal-crear-usuario" class="glass-card" style="display: none; max-width: 500px; margin: 1rem auto; position: fixed; top: 10%; left: 50%; transform: translateX(-50%); z-index: 1000; width: 90%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3>Registrar Nuevo Usuario</h3>
        <button onclick="document.getElementById('modal-crear-usuario').style.display='none'" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
    </div>
    <form action="usuarios.php" method="POST">
        <input type="hidden" name="action" value="crear_usuario">
        <div class="form-group">
            <label>Nombre Completo</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Correo Electrónico</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Rol</label>
            <select name="rol" class="form-control" required>
                <option value="mecanico">Mecánico</option>
                <option value="admin">Administrador</option>
            </select>
        </div>
        <button type="submit" class="btn-primary" style="margin-top: 1rem;">Guardar Usuario</button>
    </form>
</div>

<!-- Modal Editar Usuario -->
<div id="modal-editar-usuario" class="glass-card" style="display: none; max-width: 500px; margin: 1rem auto; position: fixed; top: 10%; left: 50%; transform: translateX(-50%); z-index: 1000; width: 90%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3>Editar Usuario</h3>
        <button onclick="document.getElementById('modal-editar-usuario').style.display='none'" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
    </div>
    <form action="usuarios.php" method="POST">
        <input type="hidden" name="action" value="editar_usuario">
        <input type="hidden" name="id" id="edit_id">
        <div class="form-group">
            <label>Nombre Completo</label>
            <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Correo Electrónico</label>
            <input type="email" name="email" id="edit_email" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Nueva Contraseña (Opcional)</label>
            <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para conservar contraseña actual">
        </div>
        <div class="form-group">
            <label>Rol</label>
            <select name="rol" id="edit_rol" class="form-control" required>
                <option value="mecanico">Mecánico</option>
                <option value="admin">Administrador</option>
            </select>
        </div>
        <button type="submit" class="btn-primary" style="margin-top: 1rem;">Actualizar Usuario</button>
    </form>
</div>

<script>
function editarUsuario(u) {
    document.getElementById('edit_id').value = u.id;
    document.getElementById('edit_nombre').value = u.nombre;
    document.getElementById('edit_email').value = u.email;
    document.getElementById('edit_rol').value = u.rol;
    document.getElementById('modal-editar-usuario').style.display = 'block';
}
</script>

<?php include 'footer.php'; ?>
