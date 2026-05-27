/**
 * Lógica de Interacciones de la Pantalla de Login
 * Portafolio Web Profesional - Martín Valdebenito
 */
document.addEventListener('DOMContentLoaded', function () {
    const btnTogglePassword = document.getElementById('btnTogglePassword');
    const passwordInput = document.getElementById('password');

    if (btnTogglePassword && passwordInput) {
        btnTogglePassword.addEventListener('click', function () {
            // Alternar visibilidad de la contraseña
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Alternar clase de ícono (ojo abierto / ojo cerrado)
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }
});
