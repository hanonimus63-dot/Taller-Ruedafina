<?php
// db.php - Configuración de conexión compatible con Railway y local MySQL
session_start();

$host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '3306';
$user = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: '';
$dbname = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'ruedafina_db';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    // Si falla la conexión local por falta de DB, mostramos mensaje amigable
    $pdo = null;
    $db_error = $e->getMessage();
}

function check_auth($roles_permitidos = []) {
    if (!isset($_SESSION['usuario'])) {
        header('Location: login.php');
        exit;
    }
    if (!empty($roles_permitidos) && !in_array($_SESSION['usuario']['rol'], $roles_permitidos)) {
        header('Location: index.php?error=acceso_denegado');
        exit;
    }
}
?>
