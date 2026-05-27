<?php
/**
 * Portafolio Web Profesional - Sitio Público
 * Martín Valdebenito
 */
require_once 'config/db.php';

// Inicializar variables por defecto (fallbacks) por si no hay datos en la BD
$nombre_completo = "Martín Valdebenito";
$presentacion_breve = "Desarrollador Web";
$descripcion_personal = "Soy desarrollador web con experiencia en la creación de sitios y aplicaciones modernas, funcionales y responsivas. Me apasiona resolver problemas y crear soluciones digitales que generen impacto.";
$foto_avatar = "uploads/placeholder-logo.png";
$cv_url = "";
$github_url     = "";
$linkedin_url   = "";

$habilidades = [];
$tecnologias = [];
$proyectos = [];

$contact_success = '';
$contact_error = '';

// --- Procesar formulario de contacto (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $nombre  = trim($_POST['nombre'] ?? '');
    $correo  = trim($_POST['correo'] ?? '');
    $asunto  = trim($_POST['asunto'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');
    
    if (empty($nombre) || empty($correo) || empty($asunto) || empty($mensaje)) {
        $contact_error = 'Todos los campos del formulario de contacto son obligatorios.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $contact_error = 'El correo electrónico ingresado no es válido.';
    } else {
        try {
            $stmtMsg = $pdo->prepare("
                INSERT INTO mensajes (nombre, correo, asunto, mensaje) 
                VALUES (:nombre, :correo, :asunto, :mensaje)
            ");
            $stmtMsg->execute([
                ':nombre'  => $nombre,
                ':correo'  => $correo,
                ':asunto'  => $asunto,
                ':mensaje' => $mensaje
            ]);
            $contact_success = '¡Tu mensaje ha sido enviado correctamente! Me pondré en contacto contigo pronto.';
        } catch (\PDOException $e) {
            $contact_error = 'Hubo un error al enviar el mensaje. Inténtalo de nuevo más tarde.';
        }
    }
}

try {
    // Obtener los datos de la biografía
    $stmtBio = $pdo->query("SELECT * FROM biografia LIMIT 1");
    $bio = $stmtBio->fetch();
    if ($bio) {
        $nombre_completo = $bio['nombre_completo'];
        $presentacion_breve = $bio['presentacion_breve'];
        $descripcion_personal = $bio['descripcion_personal'];
        if (!empty($bio['foto_avatar'])) {
            $foto_avatar = $bio['foto_avatar'];
        }
        $cv_url = $bio['cv_url'];
        $github_url      = $bio['github_url']   ?? '';
        $linkedin_url    = $bio['linkedin_url'] ?? '';
    }

    // Obtener habilidades
    $stmtHab = $pdo->query("SELECT * FROM habilidades ORDER BY id ASC");
    $habilidades = $stmtHab->fetchAll();

    // Obtener tecnologías
    $stmtTec = $pdo->query("SELECT * FROM tecnologias ORDER BY id ASC");
    $tecnologias = $stmtTec->fetchAll();

    // Obtener proyectos
    $stmtProy = $pdo->query("SELECT * FROM proyectos ORDER BY id ASC");
    $proyectos = $stmtProy->fetchAll();
} catch (\PDOException $e) {
    // Silencioso en producción o fallbacks activos
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portafolio Web Profesional - <?php echo htmlspecialchars($nombre_completo); ?></title>
    <!-- Bootstrap 5.3.8 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Hoja de estilos personalizada -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($nombre_completo); ?>
                <span class="d-block small"><?php echo htmlspecialchars($presentacion_breve); ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#biografia"><i class="fas fa-user me-1"></i> Biografía</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#habilidades"><i class="fas fa-star me-1"></i> Habilidades</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tecnologias"><i class="fas fa-code me-1"></i> Tecnologías</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#proyectos"><i class="fas fa-briefcase me-1"></i> Proyectos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contacto"><i class="fas fa-envelope me-1"></i> Contacto</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="admin/index.php" class="btn btn-outline-light"><i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section / Biografía -->
    <section id="biografia" class="py-5 vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 text-center">
                    <img src="<?php echo htmlspecialchars($foto_avatar); ?>" class="img-fluid mb-3 hero-logo" alt="<?php echo htmlspecialchars($nombre_completo); ?>">
                </div>
                <div class="col-md-8">
                    <span class="text-primary fw-bold">HOLA, SOY</span>
                    <h1 class="display-4 fw-bold"><?php echo htmlspecialchars($nombre_completo); ?></h1>
                    <h2 class="lead text-muted"><?php echo htmlspecialchars($presentacion_breve); ?></h2>
                    <p class="mt-3"><?php echo nl2br(htmlspecialchars($descripcion_personal)); ?></p>
                    
                    <?php if (!empty($cv_url)): ?>
                        <a href="<?php echo htmlspecialchars($cv_url); ?>" target="_blank" class="btn btn-primary btn-lg mt-3 me-2">
                            <i class="fas fa-download me-2"></i> Descargar CV
                        </a>
                    <?php endif; ?>
                    
                    <div class="social-icons mt-4">
                        <?php if (!empty($github_url)): ?>
                            <a href="<?php echo htmlspecialchars($github_url); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-dark me-2" title="GitHub">
                                <i class="fab fa-github fa-2x"></i>
                            </a>
                        <?php else: ?>
                            <a href="#" class="btn btn-outline-dark me-2 disabled" title="GitHub (no configurado)">
                                <i class="fab fa-github fa-2x"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($linkedin_url)): ?>
                            <a href="<?php echo htmlspecialchars($linkedin_url); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-dark me-2" title="LinkedIn">
                                <i class="fab fa-linkedin fa-2x"></i>
                            </a>
                        <?php else: ?>
                            <a href="#" class="btn btn-outline-dark me-2 disabled" title="LinkedIn (no configurado)">
                                <i class="fab fa-linkedin fa-2x"></i>
                            </a>
                        <?php endif; ?>

                        <a href="#contacto" class="btn btn-outline-dark" title="Ir al formulario de contacto">
                            <i class="fas fa-envelope fa-2x"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Habilidades y Herramientas -->
    <section id="habilidades" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5"><i class="fas fa-star me-2"></i> Habilidades y Herramientas</h2>
            <div class="row text-center justify-content-center">
                <?php if (count($habilidades) > 0): ?>
                    <?php foreach ($habilidades as $hab): ?>
                        <div class="col-6 col-md-3 mb-4">
                            <div class="card h-100 shadow-sm border-0 py-3">
                                <div class="card-body">
                                    <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3 fs-1" style="width: 70px; height: 70px;">
                                        <i class="<?php echo htmlspecialchars($hab['icono_class']); ?>"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-0"><?php echo htmlspecialchars($hab['nombre']); ?></h5>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-4">
                        <p class="text-muted">No hay habilidades registradas de momento.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Tecnologías Dominadas -->
    <section id="tecnologias" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5"><i class="fas fa-code me-2"></i> Tecnologías Dominadas</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <?php if (count($tecnologias) > 0): ?>
                        <?php foreach ($tecnologias as $tec): ?>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-semibold text-dark"><?php echo htmlspecialchars($tec['nombre']); ?></span>
                                    <span class="text-primary fw-bold"><?php echo (int)$tec['porcentaje']; ?>%</span>
                                </div>
                                <div class="progress" style="height: 14px; border-radius: 20px; background-color: #f0f2f5;">
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         style="width: <?php echo (int)$tec['porcentaje']; ?>%; border-radius: 20px;" 
                                         aria-valuenow="<?php echo (int)$tec['porcentaje']; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <p class="text-muted">No hay tecnologías dominadas registradas de momento.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Proyectos Realizados -->
    <section id="proyectos" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5"><i class="fas fa-briefcase me-2"></i> Proyectos Realizados</h2>
            <div class="row">
                <?php if (count($proyectos) > 0): ?>
                    <?php foreach ($proyectos as $proy): ?>
                        <?php
                            $img_src = $proy['imagen_url'];
                            if (empty($proy['imagen_url'])) {
                                $img_src = 'uploads/default-proyecto.png';
                            }
                        ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="overflow-hidden" style="height: 220px; background-color: #f8f9fa;">
                                    <img src="<?php echo htmlspecialchars($img_src); ?>" class="card-img-top w-100 h-100" style="object-fit: cover;" alt="<?php echo htmlspecialchars($proy['titulo']); ?>">
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title fw-bold text-dark mb-2"><?php echo htmlspecialchars($proy['titulo']); ?></h5>
                                    <p class="card-text text-secondary small flex-grow-1" style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($proy['descripcion'])); ?></p>
                                    <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                                        <?php if (!empty($proy['demo_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($proy['demo_url']); ?>" target="_blank" class="btn btn-primary btn-sm fw-semibold"><i class="fas fa-external-link-alt me-1"></i> Ver Demo</a>
                                        <?php else: ?>
                                            <button class="btn btn-primary btn-sm fw-semibold" disabled><i class="fas fa-external-link-alt me-1"></i> No Demo</button>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($proy['github_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($proy['github_url']); ?>" target="_blank" class="btn btn-outline-dark btn-sm fw-semibold"><i class="fab fa-github me-1"></i> GitHub</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-briefcase text-light mb-3" style="font-size: 3rem;"></i>
                        <h5 class="text-secondary">Sin proyectos registrados</h5>
                        <p class="text-muted small">Regresa más tarde para ver mis nuevos desarrollos.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Formulario de Contacto -->
    <section id="contacto" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5"><i class="fas fa-envelope me-2"></i> Formulario de Contacto</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Alerta de Éxito -->
                    <?php if (!empty($contact_success)): ?>
                        <div class="alert alert-success border-0 rounded-3 py-3 shadow-sm mb-4 alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($contact_success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Alerta de Error -->
                    <?php if (!empty($contact_error)): ?>
                        <div class="alert alert-danger border-0 rounded-3 py-3 shadow-sm mb-4 alert-dismissible fade show" role="alert">
                            <i class="fas fa-times-circle me-2"></i> <?php echo htmlspecialchars($contact_error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    <?php endif; ?>

                    <form action="index.php#contacto" method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <input type="text" class="form-control" name="nombre" placeholder="Nombre Completo" aria-label="Nombre Completo" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" class="form-control" name="correo" placeholder="Correo Electrónico" aria-label="Correo Electrónico" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" name="asunto" placeholder="Asunto" aria-label="Asunto" required>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" name="mensaje" rows="5" placeholder="Mensaje" required></textarea>
                        </div>
                        <div class="text-center">
                            <button type="submit" name="send_message" class="btn btn-primary btn-lg"><i class="fas fa-paper-plane me-2"></i> Enviar Mensaje</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="social-icons">
                        <a href="<?php echo htmlspecialchars($github_url); ?>" target="_blank" class="text-white me-3"><i class="fab fa-github fa-2x"></i></a>
                        <a href="<?php echo htmlspecialchars($linkedin_url); ?>" target="_blank" class="text-white me-3"><i class="fab fa-linkedin fa-2x"></i></a>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Diseñado y desarrollado por Martin Valdebenito. Desarrollador Junior enfocado en crear código limpio, eficiente y soluciones web escalables. </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3.8 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>