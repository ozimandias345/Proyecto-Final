document.getElementById('showRegister').addEventListener('click', () => {
    document.getElementById('loginForm').classList.remove('active');
    document.getElementById('registerForm').classList.add('active');
    document.getElementById('showRegister').classList.add('hidden');
    document.getElementById('showLogin').classList.remove('hidden');
});

document.getElementById('showLogin').addEventListener('click', () => {
    document.getElementById('registerForm').classList.remove('active');
    document.getElementById('loginForm').classList.add('active');
    document.getElementById('showLogin').classList.add('hidden');
    document.getElementById('showRegister').classList.remove('hidden');
});

document.getElementById('registerForm').addEventListener('submit', function(event) {
    const nombre = document.getElementById('nombre').value.trim();
    const apellido = document.getElementById('apellido').value.trim();
    
    // Expresión regular para validar nombres y apellidos
    const nombreValido = /^[A-Za-zÁÉÍÓÚáéíóúÑñ ]{2,}$/.test(nombre);
    const apellidoValido = /^[A-Za-zÁÉÍÓÚáéíóúÑñ ]{2,}$/.test(apellido);
    
    if (!nombreValido) {
        alert('El nombre debe contener solo letras y al menos dos caracteres.');
        event.preventDefault(); // Evita que el formulario se envíe
    }
    
    if (!apellidoValido) {
        alert('El apellido debe contener solo letras y al menos dos caracteres.');
        event.preventDefault();
    }
});
