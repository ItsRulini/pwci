document.getElementById("input-file").addEventListener("change", function(event) {
    const file = event.target.files[0]; // Obtiene el archivo seleccionado
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById("profile-image").src = e.target.result; // Asigna la imagen al src
        };
        reader.readAsDataURL(file);
    }
});


const emailInput = document.getElementById('email');
const emailErrorDiv = document.getElementById('email-error');

// Cada vez que escribe algo, limpia el error
emailInput.addEventListener('input', function() {
    emailErrorDiv.textContent = '';
});

document.getElementById('email').addEventListener('blur', function() {
    let email = this.value;
    if (email !== '') {
        fetch('../../controllers/validarCorreo.php', {
            method: 'POST',
            body: new URLSearchParams({ email })
        })
        .then(response => response.json()) // <<<<< AQUÍ: response.json() no .text()
        .then(data => {
            if (!data.success) { // Cuando success es false
                emailErrorDiv.textContent = data.message;
            }
        });
    }
});

const usuarioInput = document.getElementById('usuario');
const usuarioErrorDiv = document.getElementById('usuario-error');

// Cada vez que escribe algo, limpia el error
usuarioInput.addEventListener('input', function() {
    usuarioErrorDiv.textContent = '';
});

document.getElementById('usuario').addEventListener('blur', function() {
    let usuario = this.value;
    if (usuario !== '') {
        fetch('../../controllers/validarUsuario.php', {
            method: 'POST',
            body: new URLSearchParams({ usuario })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) { // Cuando success es false) {
                usuarioErrorDiv.textContent = data.message;
            }
        });
    }
});

const passInput = document.getElementById('password');
const passErrorDiv = document.getElementById('pass-error');

// Cada vez que escribe algo, limpia el error
passInput.addEventListener('input', function() {
    passErrorDiv.textContent = '';
});

// Función para validar la contraseña
function validarContraseña(pass) {
    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
    return regex.test(pass);
}

// Función para validar usuario
function validarUsuario(usuario) {
    return usuario.length >= 3;
}


document.getElementById('formRegistro').addEventListener('submit', function(e) {
    e.preventDefault(); // Evita el envío normal del formulario

    const pass = passInput.value;
    const usuario = usuarioInput.value;
    const nacimientoInput = document.getElementById('nacimiento');
    const nacimientoErrorDiv = document.getElementById('nacimiento-error');
    const rolErrorDiv = document.getElementById('rol-error');
    const sexoErrorDiv = document.getElementById('sexo-error');

    let valid = true;

    // Limpia todos los errores anteriores
    nacimientoErrorDiv.textContent = '';
    rolErrorDiv.textContent = '';
    sexoErrorDiv.textContent = '';

    // Validar contraseña
    if (!validarContraseña(pass)) {
        passErrorDiv.textContent = 'La contraseña debe tener mínimo 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.';
        valid = false;
    }

    // Validar usuario
    if (!validarUsuario(usuario)) {
        usuarioErrorDiv.textContent = 'El usuario debe tener al menos 3 caracteres.';
        valid = false;
    }

    // Validar fecha de nacimiento (no mayor a hoy)
    const fechaNacimiento = new Date(nacimientoInput.value);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0); // Limpiar hora para comparación exacta

    if (fechaNacimiento > hoy) {
        nacimientoErrorDiv.textContent = 'La fecha de nacimiento no puede ser mayor al día de hoy.';
        valid = false;
    }

    // Validar que se seleccione un rol
    const rolSeleccionado = document.querySelector('input[name="rol"]:checked');
    if (!rolSeleccionado) {
        rolErrorDiv.textContent = 'Debe seleccionar un rol.';
        valid = false;
    }

    // Validar que se seleccione un sexo
    const sexoSeleccionado = document.querySelector('input[name="sexo"]:checked');
    if (!sexoSeleccionado) {
        sexoErrorDiv.textContent = 'Debe seleccionar un género.';
        valid = false;
    }

    if (!valid) {
        return; // No continuar si hay errores
    }

    let formData = new FormData(this); //  Captura todo el formulario, incluyendo archivos
    fetch(this.action, { // Manda al controlador como se planeó
        method: 'POST',
        body: formData
    })
    .then(response => response.json()) // Esperamos JSON del servidor
    .then(data => {
        if (data.success) {
            // Si todo salió bien
            alert(data.message); // Puedes hacer un diseño más bonito aquí
            this.reset(); // Resetea el formulario
            window.location.href = "main.php"; // Redirecciona como en tu PHP
        } else {
            // Si hubo error
            alert(data.message); // Puedes hacer un diseño más bonito aquí
        }
    })
    .catch(error => {
        console.error('Error en el registro:', error);
        alert('Ocurrió un error inesperado.');
    });
});