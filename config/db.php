<?php
/**
 * Conexión segura a la Base de Datos usando PDO
 * Portafolio Web Profesional - Martín Valdebenito
 */

$host = 'localhost';
$db   = 'portafolio_db';
$user = 'root';
$pass = ''; // Por defecto en XAMPP es vacío
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanzar excepciones en caso de error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devolver arreglos asociativos por defecto
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Desactivar emulación para mayor seguridad frente a inyecciones SQL
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // En producción se debe ocultar el mensaje exacto por seguridad, pero en desarrollo local es muy útil
    die("Error crítico de conexión a la base de datos: " . $e->getMessage());
}
?>
