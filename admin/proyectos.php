<?php
/**
 * Panel de Gestión de Proyectos Realizados
 * Portafolio Web Profesional - Martín Valdebenito
 */
$page = 'proyectos';
include 'header.php';

$success_msg = '';
$error_msg = '';
$error_db = '';

$uploadDir = '../uploads/';
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
$maxFileSize = 5 * 1024 * 1024; // 5 MB

// Variables para control de edición
$modo_edicion = false;
$id_editar = 0;
$edit_titulo = '';
$edit_descripcion = '';
$edit_demo_url = '';
$edit_github_url = '';
$edit_imagen_url = 'uploads/default-proyecto.png';

// ====================================================================
// CARGAR DATOS PARA EDICIÓN (GET)
// ====================================================================
if (isset($_GET['editar'])) {
    $id_editar = (int)$_GET['editar'];
    try {
        $stmtEdit = $pdo->prepare("SELECT * FROM proyectos WHERE id = :id");
        $stmtEdit->execute([':id' => $id_editar]);
        $proyEdit = $stmtEdit->fetch();
        
        if ($proyEdit) {
            $modo_edicion = true;
            $edit_titulo = $proyEdit['titulo'];
            $edit_descripcion = $proyEdit['descripcion'];
            $edit_demo_url = $proyEdit['demo_url'] ?? '';
            $edit_github_url = $proyEdit['github_url'] ?? '';
            $edit_imagen_url = $proyEdit['imagen_url'];
        } else {
            $error_msg = "El proyecto seleccionado para editar no existe.";
        }
    } catch (\PDOException $e) {
        $error_msg = "Error al obtener los datos del proyecto para edición.";
    }
}

// ====================================================================
// PROCESAR ELIMINACIÓN (GET)
// ====================================================================
if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    
    try {
        // Obtener la ruta de la imagen antes de eliminar de la base de datos
        $stmtGet = $pdo->prepare("SELECT imagen_url FROM proyectos WHERE id = :id");
        $stmtGet->execute([':id' => $id_eliminar]);
        $project = $stmtGet->fetch();
        
        if ($project) {
            $imagePath = $project['imagen_url'];
            
            // Borrar registro de la base de datos
            $stmtDel = $pdo->prepare("DELETE FROM proyectos WHERE id = :id");
            $stmtDel->execute([':id' => $id_eliminar]);
            
            if ($stmtDel->rowCount() > 0) {
                // Eliminar archivo físico de la imagen
                if (!empty($imagePath) && $imagePath !== 'uploads/default-proyecto.png') {
                    $physicalPath = '../' . $imagePath;
                    if (file_exists($physicalPath)) {
                        unlink($physicalPath);
                    }
                }
                $success_msg = "Proyecto eliminado correctamente.";
            } else {
                $error_msg = "No se pudo eliminar el proyecto de la base de datos.";
            }
        } else {
            $error_msg = "El proyecto especificado no existe.";
        }
    } catch (\PDOException $e) {
        $error_msg = "Error en la base de datos al intentar eliminar.";
    }
}

// ====================================================================
// PROCESAR FORMULARIO (POST: Agregar o Editar)
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo      = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $demo_url    = trim($_POST['demo_url'] ?? '');
    $github_url  = trim($_POST['github_url'] ?? '');
    $id_post     = isset($_POST['id_editar']) ? (int)$_POST['id_editar'] : 0;
    
    if (empty($titulo) || empty($descripcion)) {
        $error_msg = "El título y la descripción son campos obligatorios.";
    } else {
        // Si estamos editando, usar la imagen actual como base
        if ($id_post > 0) {
            try {
                $stmtImg = $pdo->prepare("SELECT imagen_url FROM proyectos WHERE id = :id");
                $stmtImg->execute([':id' => $id_post]);
                $currentProject = $stmtImg->fetch();
                $final_image_path = $currentProject ? $currentProject['imagen_url'] : 'uploads/default-proyecto.png';
            } catch (\PDOException $e) {
                $final_image_path = 'uploads/default-proyecto.png';
            }
        } else {
            $final_image_path = 'uploads/default-proyecto.png';
        }
        
        $upload_ok = true;
        
        // Procesar subida de archivo si se envió uno nuevo
        if (isset($_FILES['imagen_proyecto']) && $_FILES['imagen_proyecto']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['imagen_proyecto'];
            
            // Validar tipo MIME
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $realMime = $finfo->file($file['tmp_name']);
            
            if (!in_array($realMime, $allowedTypes)) {
                $error_msg = "Tipo de archivo no permitido. Solo se aceptan: JPG, PNG, GIF, WEBP, SVG.";
                $upload_ok = false;
            } elseif ($file['size'] > $maxFileSize) {
                $error_msg = "La imagen supera el tamaño máximo permitido de 5 MB.";
                $upload_ok = false;
            } else {
                // Generar nombre de archivo único
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $newFileName = 'project_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destPath = $uploadDir . $newFileName;
                
                // Crear directorio si no existe
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    // Si se está editando y tiene una imagen anterior diferente a la default, borrarla
                    if ($id_post > 0 && !empty($final_image_path) && $final_image_path !== 'uploads/default-proyecto.png') {
                        $oldPhysical = '../' . $final_image_path;
                        if (file_exists($oldPhysical)) {
                            unlink($oldPhysical);
                        }
                    }
                    $final_image_path = 'uploads/' . $newFileName;
                } else {
                    $error_msg = "Error al mover el archivo subido al servidor.";
                    $upload_ok = false;
                }
            }
        }
        
        if ($upload_ok) {
            if ($id_post > 0) {
                // EDITAR
                try {
                    $stmtUpdate = $pdo->prepare("
                        UPDATE proyectos SET 
                            titulo = :titulo, 
                            descripcion = :descripcion, 
                            imagen_url = :imagen, 
                            demo_url = :demo, 
                            github_url = :github 
                        WHERE id = :id
                    ");
                    $stmtUpdate->execute([
                        ':titulo'      => $titulo,
                        ':descripcion' => $descripcion,
                        ':imagen'      => $final_image_path,
                        ':demo'        => $demo_url ?: null,
                        ':github'      => $github_url ?: null,
                        ':id'          => $id_post
                    ]);
                    $success_msg = "Proyecto actualizado correctamente.";
                    $modo_edicion = false;
                } catch (\PDOException $e) {
                    $error_msg = "Error en la base de datos al actualizar el proyecto: " . $e->getMessage();
                }
            } else {
                // AGREGAR
                try {
                    $stmtInsert = $pdo->prepare("
                        INSERT INTO proyectos (titulo, descripcion, imagen_url, demo_url, github_url) 
                        VALUES (:titulo, :descripcion, :imagen, :demo, :github)
                    ");
                    $stmtInsert->execute([
                        ':titulo'      => $titulo,
                        ':descripcion' => $descripcion,
                        ':imagen'      => $final_image_path,
                        ':demo'        => $demo_url ?: null,
                        ':github'      => $github_url ?: null
                    ]);
                    
                    $success_msg = "Proyecto agregado correctamente.";
                } catch (\PDOException $e) {
                    $error_msg = "Error en la base de datos al guardar el proyecto: " . $e->getMessage();
                    // Eliminar archivo subido si falla el insert
                    if ($final_image_path !== 'uploads/default-proyecto.png' && file_exists('../' . $final_image_path)) {
                        unlink('../' . $final_image_path);
                    }
                }
            }
        }
    }
}

// ====================================================================
// OBTENER TODOS LOS PROYECTOS
// ====================================================================
$proyectos = [];
try {
    $stmt = $pdo->query("SELECT * FROM proyectos ORDER BY id ASC");
    $proyectos = $stmt->fetchAll();
} catch (\PDOException $e) {
    $error_db = "No se pudieron cargar los proyectos. Asegúrate de tener importada la base de datos.";
}
?>

<!-- Encabezado de Sección -->
<div class="admin-title-section">
    <h1>Administrar Proyectos</h1>
    <p>Gestiona los proyectos destacados en tu portafolio, sus enlaces a demostraciones en vivo, repositorios de código e imágenes</p>
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
    <!-- Columna Izquierda: Listado de Proyectos -->
    <div class="col-lg-8">
        <div class="card border border-light-subtle rounded-3 shadow-sm bg-white p-4">
            <h3 class="fs-6 fw-bold text-secondary mb-3 text-uppercase">Proyectos Registrados</h3>
            
            <?php if (count($proyectos) > 0): ?>
                <div class="table-responsive">
                    <table class="table dashboard-table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th style="width: 100px;">Miniatura</th>
                                <th>Título del Proyecto</th>
                                <th>Demo URL</th>
                                <th>GitHub URL</th>
                                <th style="width: 120px; text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proyectos as $proy): ?>
                                <?php
                                    $img_src = '../' . $proy['imagen_url'];
                                    if (empty($proy['imagen_url'])) {
                                        $img_src = '../uploads/default-proyecto.png';
                                    }
                                ?>
                                <tr class="<?php echo ($modo_edicion && $id_editar === $proy['id']) ? 'table-primary-subtle' : ''; ?>">
                                    <td class="text-secondary"><?php echo $proy['id']; ?></td>
                                    <td>
                                        <!-- Miniatura de imagen -->
                                        <div class="border rounded-2 overflow-hidden bg-light d-flex align-items-center justify-content-center" style="width: 70px; height: 45px;">
                                            <img src="<?php echo htmlspecialchars($img_src); ?>" class="img-fluid" style="max-height: 100%; max-width: 100%; object-fit: cover;" alt="Miniatura">
                                        </div>
                                    </td>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($proy['titulo']); ?></td>
                                    <td>
                                        <?php if (!empty($proy['demo_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($proy['demo_url']); ?>" target="_blank" class="text-primary text-decoration-none">
                                                <i class="fas fa-external-link-alt me-1 small"></i> Demo
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">No definido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($proy['github_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($proy['github_url']); ?>" target="_blank" class="text-dark text-decoration-none fw-semibold">
                                                <i class="fab fa-github me-1"></i> GitHub
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">No definido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="action-btns justify-content-center gap-2">
                                            <a href="proyectos.php?editar=<?php echo $proy['id']; ?>" 
                                               class="btn-action-icon edit-btn text-primary text-decoration-none" 
                                               title="Editar Proyecto">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <a href="proyectos.php?eliminar=<?php echo $proy['id']; ?>" 
                                               class="btn-action-icon delete-btn text-danger text-decoration-none" 
                                               title="Eliminar Proyecto"
                                               onclick="return confirm('¿Estás seguro de que deseas eliminar el proyecto &quot;<?php echo htmlspecialchars($proy['titulo']); ?>&quot;? Se borrará del servidor.');">
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
                    <i class="fas fa-briefcase text-light mb-3" style="font-size: 3rem;"></i>
                    <h5 class="text-secondary mb-1">Sin proyectos registrados</h5>
                    <p class="text-muted small mb-0">Comienza agregando tu primer proyecto en el formulario de la derecha.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Columna Derecha: Formulario de Adición / Edición -->
    <div class="col-lg-4">
        <div class="card border border-light-subtle rounded-3 shadow-sm bg-white p-4">
            <h3 class="fs-6 fw-bold text-secondary mb-3 text-uppercase">
                <?php echo $modo_edicion ? '<i class="fas fa-edit text-primary me-1"></i> Editar Proyecto' : '<i class="fas fa-plus text-primary me-1"></i> Agregar Proyecto'; ?>
            </h3>
            
            <form action="proyectos.php" method="POST" enctype="multipart/form-data">
                <?php if ($modo_edicion): ?>
                    <input type="hidden" name="id_editar" value="<?php echo $id_editar; ?>">
                <?php endif; ?>

                <!-- Título -->
                <div class="mb-3">
                    <label for="titulo" class="form-label fw-semibold text-secondary small">Título del Proyecto</label>
                    <input type="text" class="form-control" id="titulo" name="titulo" 
                           value="<?php echo htmlspecialchars($modo_edicion ? $edit_titulo : ''); ?>" 
                           placeholder="Ej. Tienda Online" required>
                </div>

                <!-- Descripción -->
                <div class="mb-3">
                    <label for="descripcion" class="form-label fw-semibold text-secondary small">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="4" 
                              placeholder="Breve resumen del proyecto..." required><?php echo htmlspecialchars($modo_edicion ? $edit_descripcion : ''); ?></textarea>
                </div>

                <!-- Demo URL -->
                <div class="mb-3">
                    <label for="demo_url" class="form-label fw-semibold text-secondary small">Demo URL (Enlace en Vivo)</label>
                    <input type="url" class="form-control" id="demo_url" name="demo_url" 
                           value="<?php echo htmlspecialchars($modo_edicion ? $edit_demo_url : ''); ?>" 
                           placeholder="https://ejemplo.com">
                </div>

                <!-- GitHub URL -->
                <div class="mb-3">
                    <label for="github_url" class="form-label fw-semibold text-secondary small">GitHub URL (Repositorio)</label>
                    <input type="url" class="form-control" id="github_url" name="github_url" 
                           value="<?php echo htmlspecialchars($modo_edicion ? $edit_github_url : ''); ?>" 
                           placeholder="https://github.com/usuario/repo">
                </div>

                <!-- Carga de Imagen del Proyecto -->
                <div class="mb-4">
                    <label for="imagen_proyecto" class="form-label fw-semibold text-secondary small">
                        <?php echo $modo_edicion ? 'Cambiar Imagen (Opcional)' : 'Imagen del Proyecto'; ?>
                    </label>
                    <input class="form-control form-control-sm" type="file" id="imagen_proyecto" name="imagen_proyecto" accept="image/*">
                    
                    <!-- Previsualización contextual -->
                    <div class="mt-2 text-center" id="imgPreviewWrapper" <?php echo $modo_edicion ? '' : 'style="display: none;"'; ?>>
                        <span class="text-secondary small d-block mb-1">Previsualización:</span>
                        <div class="border rounded-2 overflow-hidden bg-light d-inline-flex align-items-center justify-content-center" style="width: 140px; height: 90px;">
                            <img src="<?php echo htmlspecialchars($modo_edicion ? '../' . $edit_imagen_url : ''); ?>" 
                                 id="imgPreview" class="img-fluid" style="max-height: 100%; max-width: 100%; object-fit: cover;">
                        </div>
                    </div>
                </div>

                <!-- Botones de Envío -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1 fw-semibold" style="border-radius: 6px;">
                        <i class="fas fa-save me-1"></i> <?php echo $modo_edicion ? 'Guardar Cambios' : 'Agregar Proyecto'; ?>
                    </button>
                    <?php if ($modo_edicion): ?>
                        <a href="proyectos.php" class="btn btn-outline-secondary fw-semibold" style="border-radius: 6px;">
                            Cancelar
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JS para previsualizar imagen seleccionada localmente -->
<script>
document.getElementById('imagen_proyecto').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            alert('La imagen es demasiado grande. El tamaño máximo es 5 MB.');
            this.value = '';
            <?php if (!$modo_edicion): ?>
                document.getElementById('imgPreviewWrapper').style.display = 'none';
            <?php endif; ?>
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imgPreview').src = e.target.result;
            document.getElementById('imgPreviewWrapper').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

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
