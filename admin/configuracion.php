<?php
/**
 * Panel de Configuración General y Ajustes de Cuenta
 * Portafolio Web Profesional - Martín Valdebenito
 * 
 * FUNCIONALIDAD:
 * - Cambio de contraseña del usuario en sesión (verificando la actual con password_verify)
 * - Guardado de redes sociales en la tabla `biografia` (github_url, linkedin_url, correo_contacto)
 */
$page = 'configuracion';
include 'header.php';

$success_pass   = '';
$error_pass     = '';
$success_redes  = '';
$error_redes    = '';

// ====================================================================
// PROCESAR CAMBIO DE CONTRASEÑA (POST con campo oculto 'form_type')
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'password') {

    $pass_actual    = $_POST['password_actual']    ?? '';
    $pass_nueva     = $_POST['nueva_password']     ?? '';
    $pass_confirmar = $_POST['confirmar_password'] ?? '';

    if (empty($pass_actual) || empty($pass_nueva) || empty($pass_confirmar)) {
        $error_pass = 'Todos los campos de contraseña son obligatorios.';
    } elseif (strlen($pass_nueva) < 6) {
        $error_pass = 'La nueva contraseña debe tener al menos 6 caracteres.';
    } elseif ($pass_nueva !== $pass_confirmar) {
        $error_pass = 'La nueva contraseña y su confirmación no coinciden.';
    } else {
        try {
            // Obtener el hash actual del usuario en sesión
            $stmtUser = $pdo->prepare("SELECT id, password_hash FROM usuarios WHERE usuario = :usuario LIMIT 1");
            $stmtUser->execute([':usuario' => $_SESSION['usuario']]);
            $userData = $stmtUser->fetch();

            if (!$userData || !password_verify($pass_actual, $userData['password_hash'])) {
                $error_pass = 'La contraseña actual es incorrecta.';
            } else {
                $nuevoHash = password_hash($pass_nueva, PASSWORD_BCRYPT);
                $stmtUpd = $pdo->prepare("UPDATE usuarios SET password_hash = :password WHERE id = :id");
                $stmtUpd->execute([':password' => $nuevoHash, ':id' => $userData['id']]);
                $success_pass = '¡Contraseña actualizada correctamente!';
            }
        } catch (\PDOException $e) {
            $error_pass = 'Error de base de datos: ' . $e->getMessage();
        }
    }
}

// ====================================================================
// PROCESAR GUARDADO DE REDES SOCIALES (POST)
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'redes') {

    $github_url       = trim($_POST['github_url']       ?? '');
    $linkedin_url     = trim($_POST['linkedin_url']     ?? '');

    try {
        // Verificar si existe fila en biografia
        $checkStmt = $pdo->query("SELECT COUNT(*) FROM biografia");
        $existe = (int)$checkStmt->fetchColumn();

        if ($existe > 0) {
            $stmtRedes = $pdo->prepare(
                "UPDATE biografia SET github_url = :github, linkedin_url = :linkedin LIMIT 1"
            );
        } else {
            $stmtRedes = $pdo->prepare(
                "INSERT INTO biografia (github_url, linkedin_url) VALUES (:github, :linkedin)"
            );
        }

        $stmtRedes->execute([
            ':github'   => $github_url,
            ':linkedin' => $linkedin_url,
        ]);

        $success_redes = '¡Redes sociales guardadas correctamente!';
    } catch (\PDOException $e) {
        $error_redes = 'Error al guardar: ' . $e->getMessage();
    }
}

// ====================================================================
// LEER VALORES ACTUALES DE REDES DESDE LA BD
// ====================================================================
$github_url     = '';
$linkedin_url   = '';

try {
    $stmtBio = $pdo->query("SELECT github_url, linkedin_url FROM biografia LIMIT 1");
    $bio = $stmtBio->fetch();
    if ($bio) {
        $github_url     = $bio['github_url']   ?? '';
        $linkedin_url   = $bio['linkedin_url'] ?? '';
    }
} catch (\PDOException $e) {
    // Si la columna no existe o hay error, dejamos vacío
}
?>

<!-- Encabezado de Sección -->
<div class="admin-title-section">
    <h1>Configuración General</h1>
    <p>Administra los enlaces de tus redes sociales y la seguridad de tu cuenta de acceso</p>
</div>

<div class="row g-4">
    <!-- Columna Izquierda: Redes Sociales -->
    <div class="col-lg-7">
        <div class="card border border-light-subtle rounded-3 shadow-sm bg-white p-4 mb-4">
            <h3 class="fs-6 fw-bold text-secondary mb-3 text-uppercase">
                <i class="fas fa-share-nodes text-primary me-2"></i> Redes Sociales
            </h3>

            <!-- Alertas de redes -->
            <?php if (!empty($success_redes)): ?>
                <div class="alert alert-success border-0 rounded-3 py-2 shadow-sm mb-3 alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($success_redes); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($error_redes)): ?>
                <div class="alert alert-danger border-0 rounded-3 py-2 shadow-sm mb-3 alert-dismissible fade show" role="alert">
                    <i class="fas fa-times-circle me-2"></i> <?php echo htmlspecialchars($error_redes); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="configuracion.php" method="POST">
                <input type="hidden" name="form_type" value="redes">

                <!-- GitHub -->
                <div class="mb-3">
                    <label for="github_url" class="form-label fw-semibold text-secondary small">Perfil de GitHub</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fab fa-github"></i></span>
                        <input type="url" class="form-control" id="github_url" name="github_url"
                               value="<?php echo htmlspecialchars($github_url); ?>"
                               placeholder="https://github.com/tuusuario">
                    </div>
                </div>

                <!-- LinkedIn -->
                <div class="mb-4">
                    <label for="linkedin_url" class="form-label fw-semibold text-secondary small">Perfil de LinkedIn</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fab fa-linkedin"></i></span>
                        <input type="url" class="form-control" id="linkedin_url" name="linkedin_url"
                               value="<?php echo htmlspecialchars($linkedin_url); ?>"
                               placeholder="https://linkedin.com/in/tuusuario">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-sm fw-semibold px-4" style="border-radius: 6px;">
                    <i class="fas fa-save me-1"></i> Guardar Enlaces
                </button>
            </form>
        </div>
    </div>

    <!-- Columna Derecha: Cambio de Contraseña -->
    <div class="col-lg-5">
        <div class="card border border-light-subtle rounded-3 shadow-sm bg-white p-4">
            <h3 class="fs-6 fw-bold text-secondary mb-3 text-uppercase">
                <i class="fas fa-key text-primary me-2"></i> Cambiar Contraseña
            </h3>

            <!-- Alertas de contraseña -->
            <?php if (!empty($success_pass)): ?>
                <div class="alert alert-success border-0 rounded-3 py-2 shadow-sm mb-3 alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($success_pass); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($error_pass)): ?>
                <div class="alert alert-danger border-0 rounded-3 py-2 shadow-sm mb-3 alert-dismissible fade show" role="alert">
                    <i class="fas fa-times-circle me-2"></i> <?php echo htmlspecialchars($error_pass); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="configuracion.php" method="POST">
                <input type="hidden" name="form_type" value="password">

                <!-- Contraseña Actual -->
                <div class="mb-3">
                    <label for="password_actual" class="form-label fw-semibold text-secondary small">Contraseña Actual</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password_actual" name="password_actual"
                               placeholder="Tu contraseña actual" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePass('password_actual', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Nueva Contraseña -->
                <div class="mb-3">
                    <label for="nueva_password" class="form-label fw-semibold text-secondary small">Nueva Contraseña</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="nueva_password" name="nueva_password"
                               placeholder="Mínimo 6 caracteres" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePass('nueva_password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Confirmar Nueva Contraseña -->
                <div class="mb-4">
                    <label for="confirmar_password" class="form-label fw-semibold text-secondary small">Confirmar Nueva Contraseña</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="confirmar_password" name="confirmar_password"
                               placeholder="Repite tu nueva contraseña" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePass('confirmar_password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Info de sesión actual -->
                <div class="alert alert-light border rounded-3 py-2 mb-3 small text-secondary">
                    <i class="fas fa-user-circle me-1 text-primary"></i>
                    Sesión activa como: <strong><?php echo htmlspecialchars($_SESSION['usuario'] ?? 'Administrador'); ?></strong>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-semibold" style="border-radius: 6px;">
                    <i class="fas fa-shield-alt me-1"></i> Actualizar Contraseña
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-ocultar alertas después de 5 segundos
document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
    setTimeout(function() {
        var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        if (bsAlert) bsAlert.close();
    }, 5000);
});

// Toggle mostrar/ocultar contraseña
function togglePass(fieldId, btn) {
    var input = document.getElementById(fieldId);
    var icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>

<?php
include 'footer.php';
?>
