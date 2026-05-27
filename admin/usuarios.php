<?php
/**
 * Panel de Gestión de Usuarios Administradores
 * Portafolio Web Profesional - Martín Valdebenito
 */
$page = 'usuarios';
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
        // Verificar primero que no se esté eliminando el usuario logueado actualmente
        $stmtCheck = $pdo->prepare("SELECT usuario FROM usuarios WHERE id = :id");
        $stmtCheck->execute([':id' => $id_eliminar]);
        $userToDelete = $stmtCheck->fetch();
        
        if ($userToDelete) {
            if ($userToDelete['usuario'] === $_SESSION['usuario']) {
                $error_msg = "No puedes eliminar tu propia cuenta de administrador en sesión.";
            } else {
                $stmtDel = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
                $stmtDel->execute([':id' => $id_eliminar]);
                
                if ($stmtDel->rowCount() > 0) {
                    $success_msg = "Usuario administrador eliminado correctamente.";
                } else {
                    $error_msg = "No se pudo eliminar el usuario.";
                }
            }
        } else {
            $error_msg = "El usuario especificado no existe.";
        }
    } catch (\PDOException $e) {
        $error_msg = "Error al intentar eliminar el usuario: " . $e->getMessage();
    }
}

// ====================================================================
// PROCESAR REGISTRO (POST)
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_usuario      = trim($_POST['nuevo_usuario'] ?? '');
    $nueva_password     = $_POST['nueva_password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';
    
    if (empty($nuevo_usuario) || empty($nueva_password) || empty($confirmar_password)) {
        $error_msg = "Todos los campos son obligatorios.";
    } elseif (strlen($nueva_password) < 6) {
        $error_msg = "La contraseña debe tener al menos 6 caracteres.";
    } elseif ($nueva_password !== $confirmar_password) {
        $error_msg = "Las contraseñas ingresadas no coinciden.";
    } else {
        try {
            // Verificar si el usuario ya existe en la BD
            $stmtExist = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = :usuario");
            $stmtExist->execute([':usuario' => $nuevo_usuario]);
            
            if ($stmtExist->fetch()) {
                $error_msg = "El nombre de usuario ya está registrado por otro administrador.";
            } else {
                // Encriptar contraseña de forma sumamente segura con bcrypt
                $password_hash = password_hash($nueva_password, PASSWORD_BCRYPT);
                
                // Insertar en base de datos
                $stmtInsert = $pdo->prepare("INSERT INTO usuarios (usuario, password_hash) VALUES (:usuario, :pass)");
                $stmtInsert->execute([
                    ':usuario' => $nuevo_usuario,
                    ':pass'    => $password_hash
                ]);
                
                $success_msg = "Usuario administrador registrado exitosamente.";
            }
        } catch (\PDOException $e) {
            $error_msg = "Error al registrar el usuario administrador.";
        }
    }
}

// ====================================================================
// OBTENER TODOS LOS USUARIOS ADMINISTRADORES
// ====================================================================
$usuarios = [];
try {
    $stmt = $pdo->query("SELECT id, usuario, fecha_creacion FROM usuarios ORDER BY id ASC");
    $usuarios = $stmt->fetchAll();
} catch (\PDOException $e) {
    $error_db = "No se pudieron cargar los usuarios. Asegúrate de tener importada la base de datos.";
}
?>

<!-- Encabezado de Sección -->
<div class="admin-title-section">
    <h1>Administrar Usuarios</h1>
    <p>Gestiona las cuentas con permisos de acceso al panel de administración de tu portafolio</p>
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
    <!-- Columna Izquierda: Listado de Administradores -->
    <div class="col-lg-8">
        <div class="card border border-light-subtle rounded-3 shadow-sm bg-white p-4">
            <h3 class="fs-6 fw-bold text-secondary mb-3 text-uppercase">Cuentas con Acceso</h3>
            
            <?php if (count($usuarios) > 0): ?>
                <div class="table-responsive">
                    <table class="table dashboard-table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>Nombre de Usuario</th>
                                <th>Fecha de Registro</th>
                                <th style="width: 120px; text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usr): ?>
                                <tr>
                                    <td class="text-secondary"><?php echo $usr['id']; ?></td>
                                    <td class="fw-semibold text-dark">
                                        <i class="fas fa-user-shield text-primary me-2"></i>
                                        <?php echo htmlspecialchars($usr['usuario']); ?>
                                    </td>
                                    <td class="text-secondary">
                                        <?php echo date('d/m/Y H:i', strtotime($usr['fecha_creacion'])); ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="action-btns justify-content-center">
                                            <!-- Si es el usuario logueado actualmente, deshabilitar borrar por seguridad -->
                                            <?php if ($usr['usuario'] === $_SESSION['usuario']): ?>
                                                <span class="badge bg-light text-secondary border rounded-pill py-1 px-2.5">
                                                    Sesión Activa
                                                </span>
                                            <?php else: ?>
                                                <a href="usuarios.php?eliminar=<?php echo $usr['id']; ?>" 
                                                   class="btn-action-icon delete-btn text-danger text-decoration-none" 
                                                   title="Eliminar Usuario"
                                                   onclick="return confirm('¿Estás seguro de que deseas eliminar la cuenta de &quot;<?php echo htmlspecialchars($usr['usuario']); ?>&quot;?');">
                                                    <i class="far fa-trash-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state text-center py-5">
                    <i class="fas fa-user-slash text-light mb-3" style="font-size: 3rem;"></i>
                    <h5 class="text-secondary mb-1">Sin usuarios registrados</h5>
                    <p class="text-muted small">Al parecer no hay cuentas alternativas en el sistema.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Columna Derecha: Formulario de Adición -->
    <div class="col-lg-4">
        <div class="card border border-light-subtle rounded-3 shadow-sm bg-white p-4">
            <h3 class="fs-6 fw-bold text-secondary mb-3 text-uppercase">Registrar Administrador</h3>
            
            <form action="usuarios.php" method="POST">
                <!-- Nombre de usuario -->
                <div class="mb-3">
                    <label for="nuevo_usuario" class="form-label fw-semibold text-secondary small">Nombre de Usuario</label>
                    <input type="text" class="form-control" id="nuevo_usuario" name="nuevo_usuario" placeholder="Ej. martinv" required>
                </div>

                <!-- Contraseña -->
                <div class="mb-3">
                    <label for="nueva_password" class="form-label fw-semibold text-secondary small">Contraseña</label>
                    <input type="password" class="form-control" id="nueva_password" name="nueva_password" placeholder="Mínimo 6 caracteres" required>
                </div>

                <!-- Confirmar Contraseña -->
                <div class="mb-4">
                    <label for="confirmar_password" class="form-label fw-semibold text-secondary small">Confirmar Contraseña</label>
                    <input type="password" class="form-control" id="confirmar_password" name="confirmar_password" placeholder="Repite la contraseña" required>
                </div>

                <!-- Botón de Envío -->
                <button type="submit" class="btn btn-primary w-100 fw-semibold" style="border-radius: 6px;">
                    <i class="fas fa-user-plus me-1"></i> Registrar Cuenta
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
        bsAlert.close();
    }, 5000);
});
</script>

<?php
include 'footer.php';
?>
