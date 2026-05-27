<?php
/**
 * Pantalla de Inicio de Sesión - Diseño Minimalista y Modular
 * Portafolio Web Profesional - Martín Valdebenito
 */
session_start();

// Si ya está autenticado, redirigir al panel administrativo
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header("Location: admin/index.php");
    exit;
}

$error_message = '';

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($usuario) || empty($password)) {
        $error_message = 'Por favor, ingresa tu usuario y contraseña.';
    } else {
        require_once 'config/db.php';
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :usuario LIMIT 1");
            $stmt->execute(['usuario' => $usuario]);
            $user_db = $stmt->fetch();

            if ($user_db && password_verify($password, $user_db['password_hash'])) {
                $_SESSION['login'] = true;
                $_SESSION['usuario'] = $user_db['usuario'];
                $_SESSION['user_id'] = $user_db['id'];
                
                session_regenerate_id(true);

                header("Location: admin/index.php");
                exit;
            } else {
                $error_message = 'Credenciales incorrectas. Inténtalo de nuevo.';
            }
        } catch (\PDOException $e) {
            $error_message = 'Error en el sistema de autenticación.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Portafolio de Martín</title>
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Hoja de estilos personalizada compartida (con estilos de login agregados) -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-lock"></i>
            <h1 class="login-title">Acceso Administrativo</h1>
            <p class="login-subtitle">Introduce tus datos para ingresar al panel</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-minimalist d-flex align-items-center gap-2" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <div><?php echo htmlspecialchars($error_message); ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- Usuario -->
            <div class="mb-3">
                <label for="usuario" class="form-label fw-semibold text-secondary small">Usuario</label>
                <input type="text" class="form-control form-control-custom" id="usuario" name="usuario" placeholder="Nombre de usuario" required autocomplete="username">
            </div>

            <!-- Contraseña -->
            <div class="mb-4">
                <label for="password" class="form-label fw-semibold text-secondary small">Contraseña</label>
                <div class="input-group-password">
                    <input type="password" class="form-control form-control-custom" id="password" name="password" placeholder="Tu contraseña" required autocomplete="current-password">
                    <i class="fas fa-eye toggle-password" id="btnTogglePassword"></i>
                </div>
            </div>

            <!-- Botón de Ingreso -->
            <button type="submit" class="btn btn-primary btn-primary-custom w-100">
                Ingresar <i class="fas fa-chevron-right ms-1 small"></i>
            </button>
        </form>

        <div class="back-link">
            <a href="index.php"><i class="fas fa-arrow-left me-1"></i> Volver al Portafolio</a>
        </div>
    </div>

    <!-- Bootstrap 5.3.3 JS Bundle (Requerido para componentes Bootstrap interactivos) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <!-- Script personalizado separado para interacciones del login -->
    <script src="js/login.js"></script>
</body>
</html>