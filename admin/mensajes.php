<?php
/**
 * Panel de Visualización de Mensajes de Contacto (Bandeja de Entrada)
 * Portafolio Web Profesional - Martín Valdebenito
 */
$page = 'mensajes';
include 'header.php';

$success_msg = '';
$error_msg = '';
$error_db = '';

// ====================================================================
// PROCESAR ELIMINACIÓN (GET)
// ====================================================================
if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    
    try {
        $stmtDel = $pdo->prepare("DELETE FROM mensajes WHERE id = :id");
        $stmtDel->execute([':id' => $id_eliminar]);
        
        if ($stmtDel->rowCount() > 0) {
            $success_msg = "Mensaje eliminado correctamente de la bandeja de entrada.";
        } else {
            $error_msg = "El mensaje especificado no existe o ya fue eliminado.";
        }
    } catch (\PDOException $e) {
        $error_msg = "Error al intentar eliminar el mensaje: " . $e->getMessage();
    }
}

// ====================================================================
// OBTENER TODOS LOS MENSAJES DE CONTACTO
// ====================================================================
$mensajes = [];
try {
    $stmt = $pdo->query("SELECT * FROM mensajes ORDER BY fecha_envio DESC");
    $mensajes = $stmt->fetchAll();
} catch (\PDOException $e) {
    $error_db = "No se pudieron cargar los mensajes de contacto. Asegúrate de tener importada la base de datos.";
}
?>

<!-- Encabezado de Sección -->
<div class="admin-title-section">
    <h1>Bandeja de Entrada</h1>
    <p>Revisa y responde a los mensajes que tus prospectos y clientes te han enviado desde el formulario de contacto de tu portafolio público</p>
</div>

<!-- Alerta de BD -->
<?php if (!empty($error_db)): ?>
    <div class="alert alert-danger border-0 rounded-3 py-3 shadow-sm mb-4" role="alert">
        <i class="fas fa-exclamation-circle text-danger me-2"></i> <?php echo $error_db; ?>
    </div>
<?php endif; ?>

<!-- Alerta de Éxito -->
<?php if (!empty($success_msg)): ?>
    <div class="alert alert-success border-0 rounded-3 py-3 shadow-sm mb-4 alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($success_msg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<!-- Alerta de Error -->
<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger border-0 rounded-3 py-3 shadow-sm mb-4 alert-dismissible fade show" role="alert">
        <i class="fas fa-times-circle me-2"></i> <?php echo htmlspecialchars($error_msg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<div class="card border border-light-subtle rounded-3 shadow-sm bg-white p-4">
    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
        <h3 class="fs-6 fw-bold text-secondary m-0 text-uppercase">Mensajes Recibidos</h3>
        <span class="badge bg-primary rounded-pill"><?php echo count($mensajes); ?> mensajes</span>
    </div>

    <?php if (count($mensajes) > 0): ?>
        <!-- Accordion Premium para visualizar mensajes sin saturar la pantalla -->
        <div class="accordion accordion-flush" id="messagesAccordion">
            <?php foreach ($mensajes as $index => $msg): ?>
                <div class="accordion-item border-bottom py-2">
                    <h2 class="accordion-header" id="heading-<?php echo $msg['id']; ?>">
                        <button class="accordion-button collapsed px-3 py-3 rounded-2" type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse-<?php echo $msg['id']; ?>" 
                                aria-expanded="false" 
                                aria-controls="collapse-<?php echo $msg['id']; ?>"
                                style="background-color: transparent; box-shadow: none;">
                            
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center w-100 gap-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 40px; height: 40px; flex-shrink: 0;">
                                        <i class="far fa-envelope-open"></i>
                                    </div>
                                    <div class="text-start">
                                        <span class="fw-bold text-dark d-block"><?php echo htmlspecialchars($msg['nombre']); ?></span>
                                        <span class="text-muted small"><?php echo htmlspecialchars($msg['correo']); ?></span>
                                    </div>
                                </div>
                                <div class="text-md-end d-flex flex-row flex-md-column align-items-center align-items-md-end gap-2">
                                    <span class="badge bg-primary-subtle text-primary border rounded-pill px-2.5 py-1">
                                        <?php echo htmlspecialchars($msg['asunto']); ?>
                                    </span>
                                    <span class="text-secondary small" style="font-size: 0.78rem;">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($msg['fecha_envio'])); ?>
                                    </span>
                                </div>
                            </div>
                        </button>
                    </h2>
                    
                    <div id="collapse-<?php echo $msg['id']; ?>" class="accordion-collapse collapse" 
                         aria-labelledby="heading-<?php echo $msg['id']; ?>" 
                         data-bs-parent="#messagesAccordion">
                         <div class="accordion-body bg-light-subtle rounded-3 p-4 border mt-2">
                            <h5 class="fs-6 fw-bold text-dark mb-2">Mensaje del Prospecto:</h5>
                            <p class="text-secondary mb-4" style="line-height: 1.6; white-space: pre-wrap; font-size: 0.95rem;">
                                <?php echo htmlspecialchars($msg['mensaje']); ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                <a href="mailto:<?php echo htmlspecialchars($msg['correo']); ?>?subject=Re: <?php echo rawurlencode($msg['asunto']); ?>" 
                                   class="btn btn-primary btn-sm fw-semibold" style="border-radius: 6px;">
                                    <i class="fas fa-paper-plane me-1"></i> Responder por Correo
                                </a>
                                <a href="mensajes.php?eliminar=<?php echo $msg['id']; ?>" 
                                   class="btn btn-outline-danger btn-sm fw-semibold text-decoration-none" 
                                   style="border-radius: 6px;"
                                   onclick="return confirm('¿Estás seguro de que deseas eliminar este mensaje de contacto permanentemente?');">
                                    <i class="far fa-trash-alt me-1"></i> Eliminar Mensaje
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state text-center py-5">
            <i class="far fa-envelope text-light mb-3" style="font-size: 3rem;"></i>
            <h5 class="text-secondary mb-1">Tu bandeja de entrada está vacía</h5>
            <p class="text-muted small">Los mensajes de contacto que te envíen en la web pública aparecerán aquí.</p>
        </div>
    <?php endif; ?>
</div>

<script>
// Auto-ocultar alertas después de 5 segundos
document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
    setTimeout(function() {
        var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        bsAlert.close();
    }, 5000);
});
</script>

<?php
include 'footer.php';
?>
