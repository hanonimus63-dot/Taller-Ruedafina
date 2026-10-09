<?php
// index.php - Dashboard Principal
require_once 'db.php';
include 'header.php';

$usuario = $_SESSION['usuario'] ?? null;

// Métricas de prueba
$total_servicios = 12;
$servicios_proceso = 4;
$total_cotizaciones = 8;
$ingresos_estimados = "$1,450.00";

if ($pdo) {
    try {
        $stmt1 = $pdo->query("SELECT COUNT(*) FROM servicios");
        $total_servicios = $stmt1->fetchColumn();

        $stmt2 = $pdo->query("SELECT COUNT(*) FROM servicios WHERE estado = 'en_proceso'");
        $servicios_proceso = $stmt2->fetchColumn();

        $stmt3 = $pdo->query("SELECT COUNT(*) FROM cotizaciones");
        $total_cotizaciones = $stmt3->fetchColumn();

        $stmt4 = $pdo->query("SELECT SUM(costo) FROM servicios WHERE estado = 'completado'");
        $sum = $stmt4->fetchColumn();
        $ingresos_estimados = "$" . number_format($sum ?: 0, 2);
    } catch (Exception $e) {
        // En caso de usar datos mock
    }
}
?>

<div class="welcome-section glass-card">
    <h2>¡Bienvenido a Taller <span style="color: var(--accent-pink);">RuedaFina</span>! 🛠️</h2>
    <p style="color: var(--text-muted); margin-top: 6px;">
        Sistema de Control de Mecánica, Historial de Servicios y Cotizaciones.
    </p>
    <?php if ($usuario): ?>
        <div style="margin-top: 1rem;">
            <span class="status-tag status-completado">Sesión Activa: <?= htmlspecialchars($usuario['nombre']) ?> (<?= strtoupper(htmlspecialchars($usuario['rol'])) ?>)</span>
        </div>
    <?php else: ?>
        <div style="margin-top: 1.2rem; display: flex; gap: 1rem;">
            <a href="login.php" class="nav-btn" style="text-decoration: none; padding: 10px 20px;">Iniciar Sesión</a>
            <a href="registro.php" style="text-decoration: none; color: white; border: 1px solid var(--border-color); padding: 10px 20px; border-radius: 10px;">Registrarse</a>
        </div>
    <?php endif; ?>
</div>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-gears"></i></div>
        <div>
            <div class="stat-number"><?= $total_servicios ?></div>
            <div class="stat-label">Servicios Registrados</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6, #14b8a6);"><i class="fa-solid fa-spinner"></i></div>
        <div>
            <div class="stat-number"><?= $servicios_proceso ?></div>
            <div class="stat-label">Servicios En Proceso</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #ec4899);"><i class="fa-solid fa-file-invoice"></i></div>
        <div>
            <div class="stat-number"><?= $total_cotizaciones ?></div>
            <div class="stat-label">Cotizaciones Creadas</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);"><i class="fa-solid fa-dollar-sign"></i></div>
        <div>
            <div class="stat-number"><?= $ingresos_estimados ?></div>
            <div class="stat-label">Ingresos por Servicios</div>
        </div>
    </div>
</div>

<div class="glass-card">
    <div class="card-title">
        <i class="fa-solid fa-star"></i> Módulos Disponibles en el Sistema
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
        <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
            <h3 style="color: var(--accent-purple); margin-bottom: 8px;"><i class="fa-solid fa-wrench"></i> 1. Historial de Servicios</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Registro completo de reparaciones, mantenimiento de vehículos, asignación de mecánico, costos y estado del servicio.</p>
            <a href="servicios.php" style="display: inline-block; margin-top: 1rem; color: var(--accent-pink); font-weight: 700; text-decoration: none;">Acceder a Servicios &rarr;</a>
        </div>

        <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
            <h3 style="color: var(--accent-pink); margin-bottom: 8px;"><i class="fa-solid fa-calculator"></i> 2. Módulo de Cotizaciones</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Generación de presupuestos estimados para clientes, detalles de mano de obra y repuestos antes de la reparación.</p>
            <a href="cotizaciones.php" style="display: inline-block; margin-top: 1rem; color: var(--accent-purple); font-weight: 700; text-decoration: none;">Acceder a Cotizaciones &rarr;</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
