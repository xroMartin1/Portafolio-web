<?php
/**
 * Cabecera Modular del Panel Administrativo (Header Template)
 * Portafolio Web Profesional - Martín Valdebenito
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Control de acceso: si no está autenticado, redirigir al login en la raíz
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}

require_once '../config/db.php';

// Definir variable de página activa para evitar errores si no está configurada
if (!isset($page)) {
    $page = 'dashboard';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($page); ?> - Panel de Administración</title>
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Font Awesome 6.5.2 para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Vinculación de la hoja de estilos externa compartida -->
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="admin-body">

    <div class="admin-wrapper">
        <!-- Sidebar - Menú Lateral de Navegación -->
        <aside class="admin-sidebar">
            <!-- Bloque de Cabecera Azul Sólido -->
            <div class="sidebar-header-blue">
                <h5>Portafolio<br>Admin</h5>
                <div class="sidebar-profile">
                    <div class="sidebar-profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <p class="profile-name">Administrador</p>
                </div>
            </div>

            <!-- Listado de Menú con Resaltado Dinámico de Página Activa -->
            <ul class="sidebar-menu-list">
                <li class="sidebar-menu-item">
                    <a href="index.php" class="sidebar-menu-btn <?php echo ($page === 'dashboard') ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="biografia.php" class="sidebar-menu-btn <?php echo ($page === 'biografia') ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i>
                        <span>Biografía</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="habilidades.php" class="sidebar-menu-btn <?php echo ($page === 'habilidades') ? 'active' : ''; ?>">
                        <i class="fas fa-star"></i>
                        <span>Habilidades</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="tecnologias.php" class="sidebar-menu-btn <?php echo ($page === 'tecnologias') ? 'active' : ''; ?>">
                        <i class="fas fa-code"></i>
                        <span>Tecnologías</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="proyectos.php" class="sidebar-menu-btn <?php echo ($page === 'proyectos') ? 'active' : ''; ?>">
                        <i class="fas fa-briefcase"></i>
                        <span>Proyectos</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="mensajes.php" class="sidebar-menu-btn <?php echo ($page === 'mensajes') ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i>
                        <span>Mensajes</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="usuarios.php" class="sidebar-menu-btn <?php echo ($page === 'usuarios') ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span>Usuarios</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="configuracion.php" class="sidebar-menu-btn <?php echo ($page === 'configuracion') ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i>
                        <span>Configuración</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="logout.php" class="sidebar-menu-btn text-danger">
                        <i class="fas fa-sign-out-alt text-danger"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Contenedor Derecho Completo (Navbar + Área de Contenido) -->
        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
            
            <!-- Navbar Superior -->
            <nav class="admin-navbar">
                <button class="navbar-toggle-btn" id="sidebarToggleBtn">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="navbar-user-dropdown" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="navbar-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <span class="navbar-username">Administrador</span>
                    <i class="fas fa-chevron-down small text-secondary"></i>
                </div>
                <!-- Dropdown Menú de Bootstrap -->
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-light" aria-labelledby="userMenuDropdown">
                    <li><a class="dropdown-menu-item dropdown-item py-2" href="../index.php" target="_blank"><i class="fas fa-globe me-2 text-secondary"></i> Ver sitio público</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-menu-item dropdown-item py-2 text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a></li>
                </ul>
            </nav>

            <!-- Área de Contenido Principal -->
            <main class="admin-content-area">
