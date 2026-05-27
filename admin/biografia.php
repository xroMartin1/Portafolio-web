<?php
/**
 * Panel de Control de la Sección Hero / Biografía
 * Portafolio Web Profesional - Martín Valdebenito
 * 
 * FUNCIONALIDAD:
 * - Lectura de datos actuales desde la tabla `biografia` (LIMIT 1)
 * - Actualización de campos de texto (nombre, presentación, descripción, CV URL) via UPDATE
 * - Subida segura de imagen avatar/logo con validación de tipo y tamaño
 * - Eliminación del archivo anterior al subir uno nuevo (unlink)
 * - Alertas Bootstrap de éxito/error
 */
$page = 'biografia';
include 'header.php';

// ====================================================================
// CONFIGURACIÓN DE SUBIDA DE ARCHIVOS
// ====================================================================
$uploadDir = '../uploads/';
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
$maxFileSize = 5 * 1024 * 1024; // 5 MB máximo

// ====================================================================
// VARIABLES DE ESTADO
// ====================================================================
$nombre_completo = '';
$presentacion_breve = '';
$descripcion_personal = '';
$foto_avatar = '../uploads/default-avatar.png'; // Fallback
$cv_url = '';
$error_db = '';
$success_msg = '';
$error_msg = '';

// ====================================================================
// PROCESAMIENTO DEL FORMULARIO (POST)
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- A) Procesar datos de texto ---
    $nombre_completo_post     = trim($_POST['nombre_completo'] ?? '');
    $presentacion_breve_post  = trim($_POST['presentacion_breve'] ?? '');
    $descripcion_personal_post = trim($_POST['descripcion_personal'] ?? '');
    $cv_url_post              = trim($_POST['cv_url'] ?? '');

    // Validación básica de campos requeridos
    if (empty($nombre_completo_post) || empty($presentacion_breve_post) || empty($descripcion_personal_post)) {
        $error_msg = 'Los campos Nombre, Presentación y Descripción son obligatorios.';
    } else {
        
        // --- B) Procesar imagen (si se subió una) ---
        $new_avatar_path = null; // null = no se cambió la imagen
        
        if (isset($_FILES['foto_avatar']) && $_FILES['foto_avatar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['foto_avatar'];
            
            // Validar tipo MIME real del archivo
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $realMimeType = $finfo->file($file['tmp_name']);
            
            if (!in_array($realMimeType, $allowedTypes)) {
                $error_msg = 'Tipo de archivo no permitido. Solo se aceptan: JPG, PNG, GIF, WEBP, SVG.';
            } elseif ($file['size'] > $maxFileSize) {
                $error_msg = 'La imagen es demasiado grande. El tamaño máximo es 5 MB.';
            } else {
                // Generar nombre único para evitar conflictos de caché
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $newFileName = 'avatar_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
                $destinationPath = $uploadDir . $newFileName;
                
                // Asegurar que el directorio de uploads exista
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
                    // La ruta que se guarda en BD es relativa a la raíz del proyecto
                    $new_avatar_path = 'uploads/' . $newFileName;
                } else {
                    $error_msg = 'Error al mover el archivo subido. Verifica los permisos del directorio uploads/.';
                }
            }
        }
        // Si se envió el campo file pero no se seleccionó archivo, $_FILES['foto_avatar']['error'] === UPLOAD_ERR_NO_FILE
        // En ese caso simplemente no se actualiza la imagen (correcto).
        
        // --- C) Ejecutar UPDATE en la base de datos (solo si no hay errores) ---
        if (empty($error_msg)) {
            try {
                if ($new_avatar_path !== null) {
                    // UPDATE con nueva imagen: también eliminar la imagen anterior
                    // Primero obtener la ruta de la imagen actual para borrarla
                    $stmtOld = $pdo->query("SELECT foto_avatar FROM biografia LIMIT 1");
                    $oldData = $stmtOld->fetch();
                    
                    if ($oldData && !empty($oldData['foto_avatar'])) {
                        $oldFilePath = '../' . $oldData['foto_avatar'];
                        // Solo borrar si existe, no es la imagen por defecto, y no es el placeholder
                        if (
                            file_exists($oldFilePath) 
                            && $oldData['foto_avatar'] !== 'uploads/default-avatar.png'
                            && $oldData['foto_avatar'] !== 'uploads/placeholder-logo.png'
                        ) {
                            unlink($oldFilePath);
                        }
                    }
                    
                    // UPDATE con imagen
                    $stmt = $pdo->prepare("
                        UPDATE biografia SET 
                            nombre_completo = :nombre,
                            presentacion_breve = :presentacion,
                            descripcion_personal = :descripcion,
                            foto_avatar = :avatar,
                            cv_url = :cv
                        WHERE id = (SELECT min_id FROM (SELECT MIN(id) AS min_id FROM biografia) AS t)
                    ");
                    $stmt->execute([
                        ':nombre'       => $nombre_completo_post,
                        ':presentacion' => $presentacion_breve_post,
                        ':descripcion'  => $descripcion_personal_post,
                        ':avatar'       => $new_avatar_path,
                        ':cv'           => $cv_url_post ?: null,
                    ]);
                } else {
                    // UPDATE sin cambiar imagen
                    $stmt = $pdo->prepare("
                        UPDATE biografia SET 
                            nombre_completo = :nombre,
                            presentacion_breve = :presentacion,
                            descripcion_personal = :descripcion,
                            cv_url = :cv
                        WHERE id = (SELECT min_id FROM (SELECT MIN(id) AS min_id FROM biografia) AS t)
                    ");
                    $stmt->execute([
                        ':nombre'       => $nombre_completo_post,
                        ':presentacion' => $presentacion_breve_post,
                        ':descripcion'  => $descripcion_personal_post,
                        ':cv'           => $cv_url_post ?: null,
                    ]);
                }
                
                $success_msg = '¡Biografía actualizada correctamente! Los cambios ya se reflejan en tu sitio público.';
                
            } catch (\PDOException $e) {
                $error_msg = 'Error de base de datos al guardar: ' . $e->getMessage();
                // Si se subió una imagen nueva pero falló el UPDATE, borrar el archivo subido
                if ($new_avatar_path !== null && file_exists('../' . $new_avatar_path)) {
                    unlink('../' . $new_avatar_path);
                }
            }
        }
    }
}

// ====================================================================
// OBTENER DATOS ACTUALES DE LA BASE DE DATOS (para el formulario)
// Se ejecuta SIEMPRE (tanto en GET como después del POST) para reflejar datos actualizados
// ====================================================================
try {
    $stmt = $pdo->query("SELECT * FROM biografia LIMIT 1");
    $bio = $stmt->fetch();
    
    if ($bio) {
        $nombre_completo = $bio['nombre_completo'];
        $presentacion_breve = $bio['presentacion_breve'];
        $descripcion_personal = $bio['descripcion_personal'];
        if (!empty($bio['foto_avatar'])) {
            // Asegurar que la ruta apunte correctamente al directorio uploads relativo a admin
            $foto_avatar = '../' . $bio['foto_avatar'];
        }
        $cv_url = $bio['cv_url'];
    }
} catch (\PDOException $e) {
    $error_db = "No se pudieron obtener los datos de la biografía. Verifica que la base de datos esté importada.";
}
?>

<!-- Encabezado de Sección -->
<div class="admin-title-section">
    <h1>Administrar Biografía</h1>
    <p>Edita la información principal y el logo temporal que se visualizan en el Hero de tu portafolio público</p>
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

<!-- Formulario de Biografía (UNIFICADO: texto + imagen en un solo form) -->
<form action="" method="POST" enctype="multipart/form-data">
<div class="row g-4">
    <!-- Columna Izquierda: Formulario de Texto -->
    <div class="col-lg-8">
        <div class="card border border-light-subtle rounded-3 shadow-sm p-4 bg-white">
                
                <!-- Nombre Completo -->
                <div class="mb-3">
                    <label for="nombre_completo" class="form-label fw-semibold text-secondary small">Nombre Completo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fas fa-user-circle"></i></span>
                        <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" 
                               value="<?php echo htmlspecialchars($nombre_completo); ?>" 
                               placeholder="Ej. Martín Valdebenito" required>
                    </div>
                </div>

                <!-- Presentación Breve -->
                <div class="mb-3">
                    <label for="presentacion_breve" class="form-label fw-semibold text-secondary small">Presentación Breve</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fas fa-briefcase"></i></span>
                        <input type="text" class="form-control" id="presentacion_breve" name="presentacion_breve" 
                               value="<?php echo htmlspecialchars($presentacion_breve); ?>" 
                               placeholder="Ej. Estudiante de Técnico en Informática" required>
                    </div>
                    <div class="form-text text-muted small">Una línea corta que describe tu rol principal.</div>
                </div>

                <!-- Descripción Personal / Acerca de ti -->
                <div class="mb-3">
                    <label for="descripcion_personal" class="form-label fw-semibold text-secondary small">Descripción Personal</label>
                    <textarea class="form-control" id="descripcion_personal" name="descripcion_personal" rows="6" 
                              placeholder="Describe tu experiencia, pasiones y metas profesionales..." required><?php echo htmlspecialchars($descripcion_personal); ?></textarea>
                    <div class="form-text text-muted small">Este párrafo se mostrará en detalle en la biografía de tu sitio público.</div>
                </div>

                <!-- Enlace al CV -->
                <div class="mb-4">
                    <label for="cv_url" class="form-label fw-semibold text-secondary small">Enlace URL del Currículum (CV)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fas fa-link"></i></span>
                        <input type="url" class="form-control" id="cv_url" name="cv_url" 
                               value="<?php echo htmlspecialchars($cv_url); ?>" 
                               placeholder="Ej. https://tuservidor.com/cv.pdf">
                    </div>
                    <div class="form-text text-muted small">Puedes subir tu CV a Google Drive, Dropbox o al servidor y colocar el link directo.</div>
                </div>

                <!-- Botón de Guardar -->
                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                    <span class="text-muted small"><i class="fas fa-lock me-1"></i> Panel Seguro</span>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold" style="border-radius: 6px;">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
        </div>
    </div>

    <!-- Columna Derecha: Foto de Perfil / Logo Temporal -->
    <div class="col-lg-4">
        <!-- Tarjeta de previsualización -->
        <div class="card border border-light-subtle rounded-3 shadow-sm bg-white p-4 text-center">
            <h4 class="fs-6 fw-bold text-secondary mb-3 text-uppercase">Foto de Perfil / Logo</h4>
            
            <!-- Marco de Avatar / Logo -->
            <div class="mb-3 d-flex justify-content-center">
                <div class="rounded-circle border overflow-hidden bg-light d-flex align-items-center justify-content-center" 
                     style="width: 160px; height: 160px; border-width: 3px !important; border-color: #0d6efd !important;">
                    <img src="<?php echo htmlspecialchars($foto_avatar); ?>" 
                         id="avatarPreview" class="img-fluid" style="max-height: 100%; max-width: 100%; object-fit: contain;" 
                         alt="Avatar actual">
                </div>
            </div>
            
            <p class="text-muted small mb-3">Esta es la imagen que se mostrará en la sección Hero del sitio público.</p>
            
            <!-- Input de archivo (parte del formulario unificado) -->
            <div class="mb-3">
                <input class="form-control form-control-sm" type="file" id="foto_avatar" name="foto_avatar" accept="image/*">
            </div>
            <p class="text-muted small mb-0"><i class="fas fa-info-circle me-1"></i> Formatos: JPG, PNG, GIF, WEBP, SVG. Máx: 5 MB.</p>
        </div>

        <!-- Tarjeta de información sobre la previsualización pública -->
        <div class="card border border-light-subtle rounded-3 shadow-sm bg-white p-4 mt-3">
            <h4 class="fs-6 fw-bold text-secondary mb-3 text-uppercase"><i class="fas fa-eye me-1"></i> Vista Previa</h4>
            <div class="text-start">
                <p class="mb-1"><strong class="small text-dark">Nombre:</strong></p>
                <p class="text-muted small mb-2" id="previewNombre"><?php echo htmlspecialchars($nombre_completo ?: '(sin definir)'); ?></p>
                <p class="mb-1"><strong class="small text-dark">Presentación:</strong></p>
                <p class="text-muted small mb-2" id="previewPresentacion"><?php echo htmlspecialchars($presentacion_breve ?: '(sin definir)'); ?></p>
                <p class="mb-1"><strong class="small text-dark">CV:</strong></p>
                <p class="text-muted small mb-0" id="previewCV"><?php echo !empty($cv_url) ? '<a href="' . htmlspecialchars($cv_url) . '" target="_blank" class="text-primary">' . htmlspecialchars($cv_url) . '</a>' : '<span class="text-warning">No configurado</span>'; ?></p>
            </div>
        </div>
    </div>
</div>
</form>

<!-- JS para previsualizar imagen seleccionada localmente + vista previa en tiempo real -->
<script>
// Preview de imagen al seleccionar un archivo
document.getElementById('foto_avatar').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        // Validación de tamaño en el cliente (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('La imagen es demasiado grande. El tamaño máximo es 5 MB.');
            this.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Vista previa en tiempo real de los campos de texto
document.getElementById('nombre_completo').addEventListener('input', function() {
    document.getElementById('previewNombre').textContent = this.value || '(sin definir)';
});
document.getElementById('presentacion_breve').addEventListener('input', function() {
    document.getElementById('previewPresentacion').textContent = this.value || '(sin definir)';
});
document.getElementById('cv_url').addEventListener('input', function() {
    const preview = document.getElementById('previewCV');
    if (this.value) {
        preview.innerHTML = '<a href="' + this.value + '" target="_blank" class="text-primary">' + this.value + '</a>';
    } else {
        preview.innerHTML = '<span class="text-warning">No configurado</span>';
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
