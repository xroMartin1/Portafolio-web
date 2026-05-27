<?php
/**
 * Panel de Administración Principal (Dashboard) - Modularizado
 * Portafolio Web Profesional - Martín Valdebenito
 */
$page = 'dashboard';
include 'header.php';

$total_proyectos = 0;
$total_habilidades = 0;
$total_tecnologias = 0;
$total_mensajes = 0;
$mensajes = [];
$error_bd = '';

// Obtener métricas y mensajes de forma segura desde la BD
try {
    $total_proyectos = $pdo->query("SELECT COUNT(*) FROM proyectos")->fetchColumn();
    $total_habilidades = $pdo->query("SELECT COUNT(*) FROM habilidades")->fetchColumn();
    $total_tecnologias = $pdo->query("SELECT COUNT(*) FROM tecnologias")->fetchColumn();
    $total_mensajes = $pdo->query("SELECT COUNT(*) FROM mensajes")->fetchColumn();
    
    // Obtener los últimos mensajes (mostramos los últimos 5)
    $stmt_mensajes = $pdo->query("SELECT * FROM mensajes ORDER BY fecha_envio DESC LIMIT 5");
    $mensajes = $stmt_mensajes->fetchAll();
} catch (\PDOException $e) {
    $error_bd = "Aviso: No se pudo conectar a todas las tablas de la base de datos. Asegúrate de haber importado el archivo 'bd.sql' en phpMyAdmin.";
}
?>

<!-- Encabezado de Sección -->
<div class="admin-title-section">
    <h1>Dashboard</h1>
    <p>Resumen general del sistema</p>
</div>

<!-- Alertas de Base de Datos -->
<?php if (!empty($error_bd)): ?>
    <div class="alert alert-warning border-0 rounded-3 py-3 shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-exclamation-circle text-warning fs-5"></i>
            <strong class="text-dark">Base de datos no sincronizada</strong>
        </div>
        <p class="mt-2 mb-0 small text-secondary"><?php echo $error_bd; ?></p>
    </div>
<?php endif; ?>

<!-- Fila de 5 Tarjetas de Métricas (Según Imagen) -->
<section class="stat-card-row">
    <!-- Tarjeta Biografía (Azul) -->
    <div class="stat-card card-blue">
        <div class="stat-card-icon">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="stat-card-info">
            <span class="stat-card-title">Biografía</span>
            <span class="stat-card-value">1</span>
            <span class="stat-card-label">Registro</span>
        </div>
    </div>

    <!-- Tarjeta Habilidades (Verde) -->
    <div class="stat-card card-green">
        <div class="stat-card-icon">
            <i class="far fa-star"></i>
        </div>
        <div class="stat-card-info">
            <span class="stat-card-title">Habilidades</span>
            <span class="stat-card-value"><?php echo $total_habilidades; ?></span>
            <span class="stat-card-label">Registros</span>
        </div>
    </div>

    <!-- Tarjeta Tecnologías (Amarillo) -->
    <div class="stat-card card-yellow">
        <div class="stat-card-icon">
            <i class="fas fa-code"></i>
        </div>
        <div class="stat-card-info">
            <span class="stat-card-title">Tecnologías</span>
            <span class="stat-card-value"><?php echo $total_tecnologias; ?></span>
            <span class="stat-card-label">Registros</span>
        </div>
    </div>

    <!-- Tarjeta Proyectos (Morado) -->
    <div class="stat-card card-purple">
        <div class="stat-card-icon">
            <i class="fas fa-briefcase"></i>
        </div>
        <div class="stat-card-info">
            <span class="stat-card-title">Proyectos</span>
            <span class="stat-card-value"><?php echo $total_proyectos; ?></span>
            <span class="stat-card-label">Registros</span>
        </div>
    </div>

    <!-- Tarjeta Mensajes (Rojo) -->
    <div class="stat-card card-red">
        <div class="stat-card-icon">
            <i class="far fa-envelope"></i>
        </div>
        <div class="stat-card-info">
            <span class="stat-card-title">Mensajes</span>
            <span class="stat-card-value"><?php echo $total_mensajes; ?></span>
            <span class="stat-card-label">Recibidos</span>
        </div>
    </div>
</section>

<!-- Sección: Gestión Rápida (Según Imagen) -->
<section class="mb-5">
    <h3 class="section-header-title">Gestión Rápida</h3>
    <div class="quick-manage-row">
        <!-- Editar Biografía -->
        <div class="quick-card">
            <i class="fas fa-user-circle"></i>
            <span>Editar Biografía</span>
            <a href="biografia.php" class="btn-manage-outline">Gestionar</a>
        </div>
        <!-- Gestionar Habilidades -->
        <div class="quick-card">
            <i class="far fa-star"></i>
            <span>Gestionar Habilidades</span>
            <a href="habilidades.php" class="btn-manage-outline">Gestionar</a>
        </div>
        <!-- Gestionar Tecnologías -->
        <div class="quick-card">
            <i class="fas fa-code"></i>
            <span>Gestionar Tecnologías</span>
            <a href="tecnologias.php" class="btn-manage-outline">Gestionar</a>
        </div>
        <!-- Gestionar Proyectos -->
        <div class="quick-card">
            <i class="fas fa-briefcase"></i>
            <span>Gestionar Proyectos</span>
            <a href="proyectos.php" class="btn-manage-outline">Gestionar</a>
        </div>
    </div>
</section>

<!-- Sección: Últimos Mensajes de Contacto (Según Imagen) -->
<section class="contact-table-card">
    <div class="contact-table-card-header">
        <h3 class="table-card-title">Últimos Mensajes de Contacto</h3>
    </div>

    <?php if (count($mensajes) > 0): ?>
        <div class="table-responsive">
            <table class="table dashboard-table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Asunto</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mensajes as $msg): ?>
                        <tr>
                            <td class="fw-semibold text-dark"><?php echo htmlspecialchars($msg['nombre']); ?></td>
                            <td class="text-secondary"><?php echo htmlspecialchars($msg['correo']); ?></td>
                            <td class="text-dark"><?php echo htmlspecialchars($msg['asunto']); ?></td>
                            <td class="badge-date-td text-secondary">
                                <?php echo date('d/m/Y', strtotime($msg['fecha_envio'])); ?>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="mensajes.php" class="btn-action-icon" title="Ver mensaje">
                                        <i class="far fa-eye"></i>
                                    </a>
                                    <a href="mensajes.php?eliminar=<?php echo $msg['id']; ?>" 
                                       class="btn-action-icon delete-btn text-danger text-decoration-none" 
                                       title="Eliminar mensaje"
                                       onclick="return confirm('¿Estás seguro de que deseas eliminar este mensaje?');">
                                        <i class="far fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="far fa-envelope-open text-secondary mb-3" style="font-size: 2.5rem; opacity: 0.4;"></i>
            <p class="text-secondary mb-0 fw-semibold">No hay mensajes aún</p>
            <p class="text-muted small mt-1">Cuando alguien te contacte desde el sitio público, sus mensajes aparecerán aquí.</p>
        </div>
    <?php endif; ?>

    <div class="centered-btn-wrapper">
        <a href="mensajes.php" class="btn-manage-outline">Ver todos los mensajes</a>
    </div>
</section>

<?php
include 'footer.php';
?>
