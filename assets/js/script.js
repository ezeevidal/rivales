// scripts.js

document.addEventListener('DOMContentLoaded', () => {
    const tipoUsuario = document.getElementById('tipo_usuario');
    const jugadorFields = document.getElementById('jugadorFields');
    const proveedorFields = document.getElementById('proveedorFields');

    tipoUsuario.addEventListener('change', function() {
        if (this.value === 'jugador') {
            jugadorFields.classList.remove('hidden');
            proveedorFields.classList.add('hidden');
        } else if (this.value === 'proveedor') {
            proveedorFields.classList.remove('hidden');
            jugadorFields.classList.add('hidden');
        } else {
            jugadorFields.classList.add('hidden');
            proveedorFields.classList.add('hidden');
        }
    });
});
