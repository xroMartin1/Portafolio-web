/**
 * Lógica de Interacciones del Dashboard de Administración
 * Portafolio Web Profesional - Martín Valdebenito
 */
document.addEventListener('DOMContentLoaded', function () {
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const adminSidebar = document.querySelector('.admin-sidebar');

    if (sidebarToggleBtn && adminSidebar) {
        sidebarToggleBtn.addEventListener('click', function () {
            // Toggle de la visibilidad del sidebar en dispositivos móviles o colapso en escritorio
            if (window.innerWidth <= 992) {
                if (adminSidebar.style.marginLeft === '0px' || adminSidebar.style.marginLeft === '') {
                    adminSidebar.style.marginLeft = '-250px';
                } else {
                    adminSidebar.style.marginLeft = '0px';
                }
            } else {
                if (adminSidebar.style.width === '0px') {
                    adminSidebar.style.width = '250px';
                    adminSidebar.style.overflow = 'visible';
                } else {
                    adminSidebar.style.width = '0px';
                    adminSidebar.style.overflow = 'hidden';
                }
            }
        });
    }

    // Configurar comportamiento dinámico responsivo inicial
    const handleResize = () => {
        if (window.innerWidth <= 992) {
            if (adminSidebar) {
                adminSidebar.style.marginLeft = '-250px';
                adminSidebar.style.width = '250px';
            }
        } else {
            if (adminSidebar) {
                adminSidebar.style.marginLeft = '0px';
                adminSidebar.style.width = '250px';
            }
        }
    };

    window.addEventListener('resize', handleResize);
    handleResize(); // Ejecutar al cargar la página
});
