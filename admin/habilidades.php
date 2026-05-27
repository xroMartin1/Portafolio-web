<?php
/**
 * Panel de Gestión de Habilidades
 * Portafolio Web Profesional - Martín Valdebenito
 */
$page = 'habilidades';
include 'header.php';

$success_msg = '';
$error_msg = '';
$error_db = '';

// Variables para control de edición
$modo_edicion = false;
$id_editar = 0;
$edit_nombre = '';
$edit_icono = '';

// ====================================================================
// CARGAR DATOS PARA EDICIÓN (GET)
// ====================================================================
if (isset($_GET['editar'])) {
    $id_editar = (int)$_GET['editar'];
    try {
        $stmtEdit = $pdo->prepare("SELECT * FROM habilidades WHERE id = :id");
        $stmtEdit->execute([':id' => $id_editar]);
        $habEdit = $stmtEdit->fetch();
        if ($habEdit) {
            $modo_edicion = true;
            $edit_nombre = $habEdit['nombre'];
            $edit_icono = $habEdit['icono_class'];
        } else {
            $error_msg = "La habilidad seleccionada para editar no existe.";
        }
    } catch (\PDOException $e) {
        $error_msg = "Error al consultar la habilidad para editar.";
    }
}

// ====================================================================
// PROCESAR ELIMINACIÓN (GET)
// ====================================================================
if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    
    try {
        $stmtDel = $pdo->prepare("DELETE FROM habilidades WHERE id = :id");
        $stmtDel->execute([':id' => $id_eliminar]);
        
        if ($stmtDel->rowCount() > 0) {
            $success_msg = "Habilidad eliminada correctamente.";
        } else {
            $error_msg = "La habilidad especificada no existe o ya fue eliminada.";
        }
    } catch (\PDOException $e) {
        $error_msg = "No se pudo eliminar la habilidad debido a un error en la base de datos.";
    }
}

// ====================================================================
// PROCESAR FORMULARIO (POST: Agregar o Editar)
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $icono_class = trim($_POST['icono_class'] ?? '');
    $id_post = isset($_POST['id_editar']) ? (int)$_POST['id_editar'] : 0;
    
    if (empty($nombre) || empty($icono_class)) {
        $error_msg = "Todos los campos son obligatorios.";
    } else {
        if ($id_post > 0) {
            // EDITAR
            try {
                $stmtUpdate = $pdo->prepare("UPDATE habilidades SET nombre = :nombre, icono_class = :icono WHERE id = :id");
                $stmtUpdate->execute([
                    ':nombre' => $nombre,
                    ':icono'  => $icono_class,
                    ':id'     => $id_post
                ]);
                $success_msg = "Habilidad actualizada correctamente.";
                $modo_edicion = false;
            } catch (\PDOException $e) {
                $error_msg = "Error al actualizar la habilidad.";
            }
        } else {
            // AGREGAR
            try {
                $stmtInsert = $pdo->prepare("INSERT INTO habilidades (nombre, icono_class) VALUES (:nombre, :icono)");
                $stmtInsert->execute([
                    ':nombre' => $nombre,
                    ':icono'  => $icono_class
                ]);
                $success_msg = "Habilidad agregada correctamente.";
            } catch (\PDOException $e) {
                $error_msg = "Error al guardar la habilidad. Posiblemente esté duplicada.";
            }
        }
    }
}

// ====================================================================
// OBTENER TODAS LAS HABILIDADES
// ====================================================================
$habilidades = [];
try {
    $stmt = $pdo->query("SELECT * FROM habilidades ORDER BY id ASC");
    $habilidades = $stmt->fetchAll();
} catch (\PDOException $e) {
    $error_db = "No se pudieron cargar las habilidades. Asegúrate de tener importada la base de datos.";
}
?>

<!-- Encabezado de Sección -->
<div class="admin-title-section">
    <h1>Administrar Habilidades</h1>
    <p>Gestiona los lenguajes, frameworks y herramientas que dominas y que se muestran con iconos en tu portafolio público</p>
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
    <!-- Columna Izquierda: Listado de Habilidades -->
    <div class="col-lg-8">
        <div class="card border border-light-subtle rounded-3 shadow-sm bg-white p-4">
            <h3 class="fs-6 fw-bold text-secondary mb-3 text-uppercase">Habilidades Registradas</h3>
            
            <?php if (count($habilidades) > 0): ?>
                <div class="table-responsive">
                    <table class="table dashboard-table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>Nombre de Habilidad</th>
                                <th>Clase de Ícono (Bootstrap/FA)</th>
                                <th style="width: 100px; text-align: center;">Vista Previa</th>
                                <th style="width: 120px; text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($habilidades as $hab): ?>
                                <tr class="<?php echo ($modo_edicion && $id_editar === $hab['id']) ? 'table-primary-subtle' : ''; ?>">
                                    <td class="text-secondary"><?php echo $hab['id']; ?></td>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($hab['nombre']); ?></td>
                                    <td><code class="text-secondary"><?php echo htmlspecialchars($hab['icono_class']); ?></code></td>
                                    <td class="text-center">
                                        <!-- Renderizado del ícono -->
                                        <div class="d-inline-flex align-items-center justify-content-center border rounded-3 bg-light text-primary fs-4" style="width: 40px; height: 40px;">
                                            <i class="<?php echo htmlspecialchars($hab['icono_class']); ?>"></i>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="action-btns justify-content-center gap-2">
                                            <a href="habilidades.php?editar=<?php echo $hab['id']; ?>" 
                                               class="btn-action-icon edit-btn text-primary text-decoration-none" 
                                               title="Editar Habilidad">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <a href="habilidades.php?eliminar=<?php echo $hab['id']; ?>" 
                                               class="btn-action-icon delete-btn text-danger text-decoration-none" 
                                               title="Eliminar Habilidad"
                                               onclick="return confirm('¿Estás seguro de que deseas eliminar la habilidad &quot;<?php echo htmlspecialchars($hab['nombre']); ?>&quot;?');">
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
                    <i class="fas fa-star-half-alt text-light mb-3" style="font-size: 3rem;"></i>
                    <h5 class="text-secondary mb-1">Sin habilidades registradas</h5>
                    <p class="text-muted small mb-0">Comienza agregando tu primera habilidad en el formulario de la derecha.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Columna Derecha: Formulario de Adición / Edición -->
    <div class="col-lg-4">
        <div class="card border border-light-subtle rounded-3 shadow-sm bg-white p-4">
            <h3 class="fs-6 fw-bold text-secondary mb-3 text-uppercase">
                <?php echo $modo_edicion ? '<i class="fas fa-edit text-primary me-1"></i> Editar Habilidad' : '<i class="fas fa-plus text-primary me-1"></i> Agregar Habilidad'; ?>
            </h3>
            
            <form action="habilidades.php" method="POST">
                <?php if ($modo_edicion): ?>
                    <input type="hidden" name="id_editar" value="<?php echo $id_editar; ?>">
                <?php endif; ?>

                <!-- Nombre de la habilidad -->
                <div class="mb-3">
                    <label for="nombre" class="form-label fw-semibold text-secondary small">Nombre de la Habilidad</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                           value="<?php echo htmlspecialchars($modo_edicion ? $edit_nombre : ''); ?>" 
                           placeholder="Ej. JavaScript" required>
                </div>

                <!-- Clase de Icono -->
                <div class="mb-4">
                    <label for="icono_class" class="form-label fw-semibold text-secondary small">Clase del Ícono</label>
                    <input type="text" class="form-control" id="icono_class" name="icono_class" 
                           value="<?php echo htmlspecialchars($modo_edicion ? $edit_icono : ''); ?>" 
                           placeholder="Ej. fab fa-js o bi bi-bootstrap" required>
                    <div class="form-text text-muted small mt-2">
                        Puedes usar clases de <strong>FontAwesome 6</strong> (ej. <code>fab fa-php</code>, <code>fas fa-database</code>) o de <strong>Bootstrap Icons</strong> (ej. <code>bi bi-bootstrap</code>).
                    </div>
                </div>

                <!-- Botón de Envío -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1 fw-semibold" style="border-radius: 6px;">
                        <i class="fas fa-save me-1"></i> <?php echo $modo_edicion ? 'Guardar Cambios' : 'Agregar Habilidad'; ?>
                    </button>
                    <?php if ($modo_edicion): ?>
                        <a href="habilidades.php" class="btn btn-outline-secondary fw-semibold" style="border-radius: 6px;">
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
