<?php
/**
 * Panel de Gestión de Tecnologías Dominadas
 * Portafolio Web Profesional - Martín Valdebenito
 */
$page = 'tecnologias';
include 'header.php';

$success_msg = '';
$error_msg = '';
$error_db = '';

// Variables para el control de la edición
$modo_edicion = false;
$id_editar = 0;
$edit_nombre = '';
$edit_porcentaje = 0;

// ====================================================================
// CARGAR DATOS PARA EDICIÓN (GET)
// ====================================================================
if (isset($_GET['editar'])) {
    $id_editar = (int)$_GET['editar'];
    try {
        $stmtEdit = $pdo->prepare("SELECT * FROM tecnologias WHERE id = :id");
        $stmtEdit->execute([':id' => $id_editar]);
        $tecEdit = $stmtEdit->fetch();
        
        if ($tecEdit) {
            $modo_edicion = true;
            $edit_nombre = $tecEdit['nombre'];
            $edit_porcentaje = $tecEdit['porcentaje'];
        } else {
            $error_msg = "La tecnología especificada para editar no existe.";
        }
    } catch (\PDOException $e) {
        $error_msg = "Error al obtener la tecnología para edición.";
    }
}

// ====================================================================
// PROCESAR ELIMINACIÓN (GET)
// ====================================================================
if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    
    try {
        $stmtDel = $pdo->prepare("DELETE FROM tecnologias WHERE id = :id");
        $stmtDel->execute([':id' => $id_eliminar]);
        
        if ($stmtDel->rowCount() > 0) {
            $success_msg = "Tecnología eliminada correctamente.";
        } else {
            $error_msg = "La tecnología especificada no existe o ya fue eliminada.";
        }
    } catch (\PDOException $e) {
        $error_msg = "No se pudo eliminar la tecnología debido a un error en la base de datos.";
    }
}

// ====================================================================
// PROCESAR FORMULARIO (POST: Agregar o Editar)
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $porcentaje = isset($_POST['porcentaje']) ? (int)$_POST['porcentaje'] : -1;
    $id_post = isset($_POST['id_editar']) ? (int)$_POST['id_editar'] : 0;
    
    if (empty($nombre) || $porcentaje < 0 || $porcentaje > 100) {
        $error_msg = "Todos los campos son obligatorios. El porcentaje debe estar entre 0 y 100.";
    } else {
        if ($id_post > 0) {
            // EDITAR
            try {
                $stmtUpdate = $pdo->prepare("UPDATE tecnologias SET nombre = :nombre, porcentaje = :porcentaje WHERE id = :id");
                $stmtUpdate->execute([
                    ':nombre'     => $nombre,
                    ':porcentaje' => $porcentaje,
                    ':id'         => $id_post
                ]);
                $success_msg = "Tecnología actualizada correctamente.";
                $modo_edicion = false;
            } catch (\PDOException $e) {
                $error_msg = "Error al actualizar la tecnología en la base de datos.";
            }
        } else {
            // AGREGAR
            try {
                $stmtInsert = $pdo->prepare("INSERT INTO tecnologias (nombre, porcentaje) VALUES (:nombre, :porcentaje)");
                $stmtInsert->execute([
                    ':nombre'     => $nombre,
                    ':porcentaje' => $porcentaje
                ]);
                
                $success_msg = "Tecnología agregada correctamente.";
            } catch (\PDOException $e) {
                $error_msg = "Error al guardar la tecnología. Asegúrate de que no esté duplicada.";
            }
        }
    }
}

// ====================================================================
// OBTENER TODAS LAS TECNOLOGÍAS
// ====================================================================
$tecnologias = [];
try {
    $stmt = $pdo->query("SELECT * FROM tecnologias ORDER BY id ASC");
    $tecnologias = $stmt->fetchAll();
} catch (\PDOException $e) {
    $error_db = "No se pudieron cargar las tecnologías. Asegúrate de tener importada la base de datos.";
}
?>

<!-- Encabezado de Sección -->
<div class="admin-title-section">
    <h1>Administrar Tecnologías Dominadas</h1>
    <p>Gestiona las tecnologías y el porcentaje de dominio que se representan en las barras de progreso de tu portafolio público</p>
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

<div class="row g-4">
    <!-- Columna Izquierda: Listado de Tecnologías -->
    <div class="col-lg-8">
        <div class="card border border-light-subtle rounded-3 shadow-sm bg-white p-4">
            <h3 class="fs-6 fw-bold text-secondary mb-3 text-uppercase">Tecnologías Registradas</h3>
            
            <?php if (count($tecnologias) > 0): ?>
                <div class="table-responsive">
                    <table class="table dashboard-table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>Tecnología</th>
                                <th style="width: 100px; text-align: center;">Porcentaje</th>
                                <th>Dominio Visual (Progreso)</th>
                                <th style="width: 120px; text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tecnologias as $tec): ?>
                                <tr class="<?php echo ($modo_edicion && $id_editar === $tec['id']) ? 'table-primary-subtle' : ''; ?>">
                                    <td class="text-secondary"><?php echo $tec['id']; ?></td>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($tec['nombre']); ?></td>
                                    <td class="text-center fw-bold text-primary"><?php echo $tec['porcentaje']; ?>%</td>
                                    <td>
                                        <!-- Barra de progreso en miniatura -->
                                        <div class="progress" style="height: 12px; border-radius: 20px; background-color: #f0f2f5;">
                                            <div class="progress-bar bg-primary" role="progressbar" 
                                                 style="width: <?php echo $tec['porcentaje']; ?>%; border-radius: 20px;" 
                                                 aria-valuenow="<?php echo $tec['porcentaje']; ?>" 
                                                 aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="action-btns justify-content-center gap-2">
                                            <a href="tecnologias.php?editar=<?php echo $tec['id']; ?>" 
                                               class="btn-action-icon edit-btn text-primary text-decoration-none" 
                                               title="Editar Tecnología">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <a href="tecnologias.php?eliminar=<?php echo $tec['id']; ?>" 
                                               class="btn-action-icon delete-btn text-danger text-decoration-none" 
                                               title="Eliminar Tecnología"
                                               onclick="return confirm('¿Estás seguro de que deseas eliminar la tecnología &quot;<?php echo htmlspecialchars($tec['nombre']); ?>&quot;?');">
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
                <div class="empty-state text-center py-5">
                    <i class="fas fa-sliders-h text-light mb-3" style="font-size: 3rem;"></i>
                    <h5 class="text-secondary mb-1">Sin tecnologías dominadas</h5>
                    <p class="text-muted small mb-0">Comienza agregando tu primera tecnología en el formulario de la derecha.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Columna Derecha: Formulario de Adición / Edición -->
    <div class="col-lg-4">
        <div class="card border border-light-subtle rounded-3 shadow-sm bg-white p-4">
            <h3 class="fs-6 fw-bold text-secondary mb-3 text-uppercase">
                <?php echo $modo_edicion ? '<i class="fas fa-edit text-primary me-1"></i> Editar Tecnología' : '<i class="fas fa-plus text-primary me-1"></i> Agregar Tecnología'; ?>
            </h3>
            
            <form action="tecnologias.php" method="POST">
                <?php if ($modo_edicion): ?>
                    <input type="hidden" name="id_editar" value="<?php echo $id_editar; ?>">
                <?php endif; ?>

                <!-- Nombre de la tecnología -->
                <div class="mb-3">
                    <label for="nombre" class="form-label fw-semibold text-secondary small">Nombre de la Tecnología</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                           value="<?php echo htmlspecialchars($modo_edicion ? $edit_nombre : ''); ?>" 
                           placeholder="Ej. PHP Avanzado" required>
                </div>

                <!-- Porcentaje de Dominio -->
                <div class="mb-4">
                    <label for="porcentaje" class="form-label fw-semibold text-secondary small">Porcentaje de Dominio (%)</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="porcentaje" name="porcentaje" min="0" max="100" 
                               value="<?php echo htmlspecialchars($modo_edicion ? $edit_porcentaje : ''); ?>" 
                               placeholder="Ej. 85" required>
                        <span class="input-group-text bg-light text-secondary">%</span>
                    </div>
                    <div class="form-text text-muted small mt-2">
                        Debe ser un valor numérico entero entre <strong>0 y 100</strong>. Representará el tamaño de la barra de progreso en tu sitio.
                    </div>
                </div>

                <!-- Botones de Envío -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1 fw-semibold" style="border-radius: 6px;">
                        <i class="fas fa-save me-1"></i> <?php echo $modo_edicion ? 'Guardar Cambios' : 'Agregar Tecnología'; ?>
                    </button>
                    <?php if ($modo_edicion): ?>
                        <a href="tecnologias.php" class="btn btn-outline-secondary fw-semibold" style="border-radius: 6px;">
                            Cancelar
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
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
