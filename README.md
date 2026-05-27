# Portafolio Web Profesional Autoadministrable

Un sistema web dinámico y autoadministrable diseñado para展示 el perfil profesional, habilidades, tecnologías y proyectos de un desarrollador. Cuenta con una interfaz pública moderna y responsiva, además de un panel de administración (dashboard) privado y seguro para gestionar todo el contenido de forma dinámica sin tocar el código.

---

## Características Principales

### Vista Pública (Portfolio)

- **Diseño Adaptativo (Responsive):** Totalmente optimizado para dispositivos móviles, tablets y computadoras de escritorio gracias a **Bootstrap 5**.
- **Biografía Dinámica:** Foto de perfil/avatar, presentación breve, descripción profesional y enlace de descarga de currículum vitae (CV) cargados desde la base de datos.
- **Habilidades y Tecnologías:** Visualización limpia de herramientas con iconos modernos de Font Awesome y barras de progreso porcentuales interactivas.
- **Galería de Proyectos:** Tarjetas con la información de los proyectos desarrollados, incluyendo imagen representativa, descripción y enlaces directos a demos y repositorios de GitHub.
- **Formulario de Contacto Directo:** Permite a los visitantes enviar mensajes directamente al panel de administración del propietario.

### Panel de Administración (Dashboard)

- **Acceso Protegido:** Autenticación de sesiones de usuario con protección de rutas no autorizadas.
- **Gestión de Biografía y Redes:** Formulario para editar la presentación, actualizar el avatar y guardar enlaces directos de GitHub y LinkedIn que se reflejan de inmediato en la vista pública.
- **CRUD Completo:**
  - **Proyectos:** Añadir, editar (con actualización de imágenes) y eliminar proyectos.
  - **Tecnologías:** Controlar y actualizar el listado de tecnologías y sus porcentajes de dominio.
  - **Habilidades:** Configurar habilidades técnicas y sus respectivos iconos.
- **Buzón de Mensajes:** Visualización en tiempo real de los mensajes enviados desde el formulario de contacto con la opción de eliminarlos individualmente.
- **Seguridad y Configuración:** Opción para cambiar la contraseña del usuario actual de manera segura.

---

## Tecnologías Utilizadas

- **Frontend:** HTML5, CSS3 personalizado, JavaScript, [Bootstrap v5.3.3](https://getbootstrap.com/), [Font Awesome v6.5.2](https://fontawesome.com/) y Bootstrap Icons.
- **Backend:** PHP (Programación orientada a conexión PDO).
- **Base de Datos:** MySQL.
- **Entorno Local:** XAMPP (Apache + MySQL).

---

## Estructura del Proyecto

```text
Portafolio-web/
├── admin/                 # Panel de administración privado
│   ├── configuracion.php  # Gestión de contraseña y enlaces de redes
│   ├── biografia.php      # Gestión de información personal y avatar
│   ├── proyectos.php      # CRUD de proyectos
│   ├── tecnologías.php    # CRUD de tecnologías
│   ├── habilidades.php    # CRUD de habilidades
│   ├── mensajes.php       # Visualización y eliminación de mensajes de contacto
│   ├── index.php          # Inicio del dashboard (métricas y bandeja rápida)
│   ├── login.php          # Pantalla de inicio de sesión
│   ├── logout.php         # Destrucción segura de sesiones
│   ├── header.php         # Layout superior del panel
│   └── footer.php         # Layout inferior del panel
├── config/
│   └── db.php             # Conexión PDO a la base de datos MySQL
├── css/
│   └── styles.css         # Estilos globales personalizados
├── js/                    # Scripts de interactividad y validaciones JS
├── uploads/               # Carpeta para almacenamiento de avatares e imágenes cargadas
├── bd.sql                 # Script de creación de tablas y datos semilla
├── .gitignore             # Configuración de exclusión para Git
└── README.md              # Documentación del proyecto
```

---

## Instalación y Configuración Local

Sigue estos pasos para ejecutar el proyecto en tu máquina local usando XAMPP:

### 1. Clonar el repositorio

Clona este proyecto en la carpeta `htdocs` de tu instalación de XAMPP (usualmente `C:\xampp\htdocs\` en Windows):

```bash
git clone <URL_DEL_REPOSITORIO>
```

### 2. Importar la Base de Datos

1. Inicia **Apache** y **MySQL** desde el Panel de Control de XAMPP.
2. Abre tu navegador e ingresa a `http://localhost/phpmyadmin/`.
3. Crea una nueva base de datos llamada `portafolio_db`.
4. Selecciona la base de datos creada, ve a la pestaña **Importar**, selecciona el archivo `bd.sql` ubicado en la raíz del proyecto y haz clic en **Importar**.

### 3. Configurar Conexión

Si utilizas credenciales de MySQL distintas a las por defecto (`host: localhost`, `user: root`, `password: ""`), puedes configurarlas editando el archivo:

- [db.php](file:///c:/xampp/htdocs/Portafolio-web/config/db.php)

### 4. Ejecutar el Proyecto

Accede a las siguientes URLs en tu navegador:

- **Vista Pública:** `http://localhost/Portafolio-web/`
- **Panel de Administración:** `http://localhost/Portafolio-web/login.php` (o `http://localhost/Portafolio-web/admin/`)

---

## Credenciales de Acceso por Defecto

Para acceder al panel de administración utiliza las siguientes credenciales iniciales (puedes cambiar la contraseña una vez ingreses en la sección de configuración):

- **Usuario:** `admin`
- **Contraseña:** `admin123`

---

## Seguridad Implementada

- **Inyección SQL:** Consultas preparadas utilizando PDO para todos los procesos de lectura, inserción, actualización y eliminación de datos.
- **Ataques XSS:** Sanitización activa de las salidas en pantalla mediante la función `htmlspecialchars()` de PHP.
- **Protección de Contraseñas:** Encriptación de claves mediante algoritmos seguros con las funciones nativas `password_hash()` y `password_verify()`.
- **Control de Acceso:** Validación de sesiones activas (`session_start()`) en todas las páginas administrativas. Si un usuario no autenticado intenta acceder directamente, es redirigido automáticamente a la pantalla de login.
