-- ==========================================
-- Base de Datos para Taller RuedaFina
-- Compatible con MySQL / DBeaver / Railway
-- ==========================================

CREATE DATABASE IF NOT EXISTS `ruedafina_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ruedafina_db`;

-- Tabla de Usuarios (Roles: Admin / Mecánico)
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `rol` ENUM('admin', 'mecanico') NOT NULL DEFAULT 'mecanico',
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Clientes
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `telefono` VARCHAR(30),
  `email` VARCHAR(120),
  `vehiculo_modelo` VARCHAR(100) NOT NULL,
  `vehiculo_placa` VARCHAR(20) NOT NULL UNIQUE,
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Servicios / Historial de Servicios
CREATE TABLE IF NOT EXISTS `servicios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `cliente_id` INT NOT NULL,
  `mecanico_id` INT NOT NULL,
  `tipo_servicio` VARCHAR(150) NOT NULL,
  `descripcion` TEXT,
  `costo` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `estado` ENUM('pendiente', 'en_proceso', 'completado', 'cancelado') DEFAULT 'pendiente',
  `fecha_servicio` DATE NOT NULL,
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`mecanico_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Cotizaciones
CREATE TABLE IF NOT EXISTS `cotizaciones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `cliente_id` INT NOT NULL,
  `mecanico_id` INT NOT NULL,
  `detalles` TEXT NOT NULL,
  `total_estimado` DECIMAL(10,2) NOT NULL,
  `estado` ENUM('borrador', 'enviada', 'aprobada', 'rechazada') DEFAULT 'borrador',
  `fecha` DATE NOT NULL,
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`mecanico_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Datos Semilla (Usuarios de prueba)
-- Password por defecto para pruebas: "123456" (usando hash bcrypt)
INSERT INTO `usuarios` (`nombre`, `email`, `password`, `rol`) VALUES
('Miguel Admin', 'admin@ruedafina.com', '$2y$10$e8w.p4fH8X/Yy.X.9Zq3x.98v8cQ8mU9E3E8Xq3x.98v8cQ8mU9E3', 'admin'),
('Carlos Mecánico', 'mecanico@ruedafina.com', '$2y$10$e8w.p4fH8X/Yy.X.9Zq3x.98v8cQ8mU9E3E8Xq3x.98v8cQ8mU9E3', 'mecanico')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Datos Semilla (Clientes)
INSERT INTO `clientes` (`nombre`, `telefono`, `email`, `vehiculo_modelo`, `vehiculo_placa`) VALUES
('Juan Pérez', '0414-1234567', 'juan@gmail.com', 'Toyota Corolla 2018', 'AB123CD'),
('María Gómez', '0424-7654321', 'maria@gmail.com', 'Chevrolet Spark 2012', 'XY987ZT');

-- Datos Semilla (Servicios)
INSERT INTO `servicios` (`cliente_id`, `mecanico_id`, `tipo_servicio`, `descripcion`, `costo`, `estado`, `fecha_servicio`) VALUES
(1, 2, 'Cambio de Aceite y Filtro', 'Aceite sintético 5W30 y filtro original', 45.00, 'completado', '2026-10-01'),
(2, 2, 'Revisión del Sistema de Frenos', 'Cambio de pastillas delanteras y rectificación de discos', 85.00, 'en_proceso', '2026-10-08');

-- Datos Semilla (Cotizaciones)
INSERT INTO `cotizaciones` (`cliente_id`, `mecanico_id`, `detalles`, `total_estimado`, `estado`, `fecha`) VALUES
(1, 2, 'Cambio de amortiguadores traseros y alineación', 190.00, 'enviada', '2026-10-09');
