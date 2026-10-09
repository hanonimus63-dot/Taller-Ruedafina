<?php
// registro.php
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? 'mecanico';

    if (!empty($nombre) && !empty($email) && !empty($password)) {
        if (!in_array($rol, ['admin', 'mecanico'])) {
            $rol = 'mecanico';
        }

        if ($pdo) {
            try {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nombre, $email, $hash, $rol]);
                $success = '¡Registro exitoso! Ya puedes iniciar sesión con tus credenciales.';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = 'El correo electrónico ya se encuentra registrado.';
                } else {
                    $error = 'Error al registrar el usuario: ' . $e->getMessage();
                }
            }
        } else {
            $success = '¡Registro exitoso (Modo Demostración)! Ya puedes iniciar sesión.';
        }
    } else {
        $error = 'Por favor complete todos los campos requeridos.';
    }
}

include 'header.php';
?>

<div class="auth-container">
    <div class="glass-card">
        <div class="auth-header">
            <h2>Crear nueva cuenta</h2>
            <p>Registro de personal para Taller RuedaFina</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form action="registro.php" method="POST">
            <div class="form-group">
                <label for="nombre"><i class="fa-solid fa-user"></i> Nombre Completo</label>
                <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Ej: Pedro Pérez" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="email"><i class="fa-solid fa-envelope"></i> Correo Electrónico</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="pedro@ruedafina.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock"></i> Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label for="rol"><i class="fa-solid fa-id-badge"></i> Rol de Usuario</label>
                <select id="rol" name="rol" class="form-control" required>
                    <option value="mecanico">Mecánico</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>

            <button type="submit" class="btn-primary" style="margin-top: 1rem;">
                <i class="fa-solid fa-user-plus"></i> Registrar Usuario
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: var(--text-muted);">
            ¿Ya tienes una cuenta? <a href="login.php" style="color: var(--accent-pink); font-weight: 700; text-decoration: none;">Inicia sesión</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
