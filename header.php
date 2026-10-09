<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$usuario_actual = $_SESSION['usuario'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taller RuedaFina - Gestión de Servicios Mecánicos</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <a href="index.php" class="brand-logo">
                <div class="logo-icon">
                    <i class="fa-solid fa-wrench"></i>
                </div>
                <div class="logo-text">
                    <span class="brand-title">Rueda<span class="highlight">Fina</span></span>
                    <span class="brand-subtitle">TALLER MECÁNICO</span>
                </div>
            </a>

            <nav class="main-nav">
                <?php if ($usuario_actual): ?>
                    <a href="index.php" class="nav-link"><i class="fa-solid fa-chart-pie"></i> Inicio</a>
                    <a href="servicios.php" class="nav-link"><i class="fa-solid fa-gears"></i> Historial de Servicios</a>
                    <a href="cotizaciones.php" class="nav-link"><i class="fa-solid fa-file-invoice-dollar"></i> Cotizaciones</a>
                <?php else: ?>
                    <a href="index.php" class="nav-link"><i class="fa-solid fa-house"></i> Inicio</a>
                    <a href="login.php" class="nav-link"><i class="fa-solid fa-right-to-bracket"></i> Iniciar Sesión</a>
                    <a href="registro.php" class="nav-link nav-btn"><i class="fa-solid fa-user-plus"></i> Registrarse</a>
                <?php endif; ?>
            </nav>

            <?php if ($usuario_actual): ?>
                <div class="user-badge">
                    <div class="user-avatar">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    <div class="user-info">
                        <span class="user-name"><?= htmlspecialchars($usuario_actual['nombre']) ?></span>
                        <span class="user-role badge-<?= htmlspecialchars($usuario_actual['rol']) ?>">
                            <?= strtoupper(htmlspecialchars($usuario_actual['rol'])) ?>
                        </span>
                    </div>
                    <a href="logout.php" class="btn-logout" title="Cerrar Sesión">
                        <i class="fa-solid fa-power-off"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>
    <main class="main-content">
