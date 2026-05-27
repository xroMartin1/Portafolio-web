-- =========================================================================
-- Script de Generación de Base de Datos - Portafolio Web Profesional
-- Evaluación N°3 - Martín Valdebenito
-- =========================================================================

CREATE DATABASE IF NOT EXISTS `portafolio_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `portafolio_db`;

-- 1. TABLA: Usuarios (Autenticación del Administrador)
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario` VARCHAR(50) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. TABLA: Biografía Profesional
CREATE TABLE IF NOT EXISTS `biografia` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre_completo` VARCHAR(150) NOT NULL,
    `presentacion_breve` VARCHAR(255) NOT NULL,
    `descripcion_personal` TEXT NOT NULL,
    `foto_avatar` VARCHAR(255) DEFAULT 'uploads/default-avatar.png',
    `cv_url` VARCHAR(255) DEFAULT NULL,
    `github_url` VARCHAR(255) DEFAULT NULL,
    `linkedin_url` VARCHAR(255) DEFAULT NULL,
    `correo_contacto` VARCHAR(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 3. TABLA: Habilidades y Herramientas (Íconos de Bootstrap/FontAwesome)
CREATE TABLE IF NOT EXISTS `habilidades` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `icono_class` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. TABLA: Tecnologías Dominadas (Porcentajes para barras de progreso)
CREATE TABLE IF NOT EXISTS `tecnologias` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `porcentaje` INT NOT NULL CHECK (`porcentaje` BETWEEN 0 AND 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. TABLA: Proyectos Realizados
CREATE TABLE IF NOT EXISTS `proyectos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titulo` VARCHAR(150) NOT NULL,
    `descripcion` TEXT NOT NULL,
    `imagen_url` VARCHAR(255) DEFAULT 'uploads/default-proyecto.png',
    `demo_url` VARCHAR(255) DEFAULT NULL,
    `github_url` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. TABLA: Mensajes de Contacto (Bandeja de Entrada del Dashboard)
CREATE TABLE IF NOT EXISTS `mensajes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `correo` VARCHAR(100) NOT NULL,
    `asunto` VARCHAR(150) NOT NULL,
    `mensaje` TEXT NOT NULL,
    `fecha_envio` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =========================================================================
-- INSERT DE DATOS SEMILLA (Información inicial requerida por la rúbrica)
-- =========================================================================

-- Usuario Administrador: 'admin' / Clave: 'admin123' 
INSERT INTO `usuarios` (`usuario`, `password_hash`) VALUES 
('admin', '$2y$10$mC7p6jGByUa04oR6VjEaUuB8pC4m5TlzY7b/eQY9WoxvV/QhO4Z2.');

-- Datos de Biografía Iniciales
INSERT INTO `biografia` (`nombre_completo`, `presentacion_breve`, `descripcion_personal`) VALUES 
('Martín Valdebenito', 'Estudiante de Técnico en Informática / IT Technician', 'Desarrollador web en formación enfocado en tecnologías backend y frontend. Apasionado por la optimización de sistemas y soluciones digitales funcionales de alto rendimiento.');

-- Habilidades Requeridas Obligatorias
INSERT INTO `habilidades` (`nombre`, `icono_class`) VALUES 
('HTML5', 'bi bi-filetype-html'),
('CSS3', 'bi bi-filetype-css'),
('JavaScript', 'bi bi-filetype-js'),
('PHP', 'bi bi-filetype-php'),
('MySQL', 'bi bi-database'),
('Bootstrap', 'bi bi-bootstrap'),
('GitHub', 'bi bi-github'),
('IA Aplicada', 'bi bi-cpu');

-- Tecnologías Dominadas (Barras de progreso por defecto)
INSERT INTO `tecnologias` (`nombre`, `porcentaje`) VALUES 
('Frontend (HTML/CSS/JS)', 80),
('Backend (PHP)', 70),
('Bases de Datos (MySQL)', 75),
('Control de Versiones (Git)', 85);

-- Proyecto de Muestra Inicial
INSERT INTO `proyectos` (`titulo`, `descripcion`, `demo_url`, `github_url`) VALUES 
('Proyecto E-commerce Dinámico', 'Desarrollo de una tienda en línea administrable utilizando arquitectura PHP clásica y persistencia de datos en MySQL.', 'https://teclab.uct.cl/~usuario/demo1', 'https://github.com/usuario/ecommerce-php');