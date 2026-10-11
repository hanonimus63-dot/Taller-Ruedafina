<?php
// login.php
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                // Soporta claves encriptadas con password_hash() o texto plano (agregadas en DBeaver)
                if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
                    $_SESSION['usuario'] = [
                        'id' => $user['id'],
                        'nombre' => $user['nombre'],
                        'email' => $user['email'],
                        'rol' => $user['rol']
                    ];
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Credenciales incorrectas. Verifique su correo y contraseña.';
                }
            } catch (Exception $e) {
                $error = 'Error al consultar la base de datos: ' . $e->getMessage();
            }
        } else {
            $error = 'No hay conexión activa con la base de datos. ' . ($db_error ?? '');
        }
    } else {
        $error = 'Por favor complete todos los campos.';
    }
}

include 'header.php';
?>

<div class="auth-container">
    <div class="glass-card">
        <div class="auth-header">
            <h2>Acceso a Taller RuedaFina</h2>
            <p>Ingrese sus credenciales según su rol (Admin / Mecánico)</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email"><i class="fa-solid fa-envelope"></i> Correo Electrónico</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="admin@ruedafina.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock"></i> Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-primary" style="margin-top: 1rem;">
                <i class="fa-solid fa-right-to-bracket"></i> Iniciar Sesión
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: var(--text-muted);">
            ¿No tienes cuenta? <a href="registro.php" style="color: var(--accent-pink); font-weight: 700; text-decoration: none;">Regístrate aquí</a>
        </div>
        
        <div style="margin-top: 1.5rem; padding: 12px; background: rgba(255,255,255,0.03); border-radius: 8px; font-size: 0.8rem; color: var(--text-muted);">
            <strong>Credenciales Demo:</strong><br>
            • Admin: <code>admin@ruedafina.com</code> | Pass: <code>123456</code><br>
            • Mecánico: <code>mecanico@ruedafina.com</code> | Pass: <code>123456</code>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
