// views/administrador/perfil.js

$(document).ready(function() {
    // --- SELECTORES DE ELEMENTOS DEL FORMULARIO ---
    const emailInput = $('#email');
    const usuarioInput = $('#usuario');
    const passwordInput = $('#password');
    const nacimientoInput = $('#nacimiento');
    const submitButton = $('#submitPerfil');

    // --- SELECTORES DE MENSAJES DE VALIDACIÓN ---
    const emailValidationMessage = $('#emailValidationMessage');
    const usuarioValidationMessage = $('#usuarioValidationMessage');
    const passwordValidationMessage = $('#passwordValidationMessage');
    const nacimientoValidationMessage = $('#nacimientoValidationMessage');

    // --- ESTADO DE VALIDACIÓN GLOBAL ---
    let isFormValid = {
        email: esEmailValido(emailInput.val() || ''),
        usuario: esNombreUsuarioValido(usuarioInput.val() || ''),
        password: true, 
        nacimiento: esFechaNacimientoValida(nacimientoInput.val() || '')
    };
    
    const originalEmail = emailInput.val();
    const originalUsuario = usuarioInput.val();

    function updateSubmitButtonState() {
        const todosValidos = Object.values(isFormValid).every(status => status === true);
        if (todosValidos) {
            submitButton.prop('disabled', false).removeClass('disabled-button-style');
        } else {
            submitButton.prop('disabled', true).addClass('disabled-button-style');
        }
    }
    
    // --- FUNCIONES DE VALIDACIÓN ESPECÍFICAS ---
    function esEmailValido(email) {
        if (email === '') return false;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function esNombreUsuarioValido(nombreUsuario) {
        if (nombreUsuario === '') return false;
        return nombreUsuario.length >= 3;
    }

    function esPasswordValida(password) {
        if (password === '') return true; 
        if (password.length < 8) return false;
        if (!/[A-Z]/.test(password)) return false;
        if (!/[a-z]/.test(password)) return false;
        if (!/[0-9]/.test(password)) return false;
        const specialCharRegex = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/;
        return specialCharRegex.test(password);
    }

    function esFechaNacimientoValida(fechaNacimientoStr) {
        if (!fechaNacimientoStr) return false;
        const fechaNacimiento = new Date(fechaNacimientoStr + "T00:00:00Z"); // Usar Z para UTC y evitar problemas de zona horaria
        const hoy = new Date();
        hoy.setUTCHours(0, 0, 0, 0); 
        return fechaNacimiento < hoy;
    }

    // --- LISTENERS DE VALIDACIÓN ---
    emailInput.on('input blur', function() {
        const email = $(this).val().trim();
        emailValidationMessage.text('').removeClass('error success');
        $(this).removeClass('input-error input-success');

        if (email === '') {
            emailValidationMessage.text('El correo es obligatorio.').addClass('error');
            $(this).addClass('input-error');
            isFormValid.email = false;
        } else if (!esEmailValido(email)) {
            emailValidationMessage.text('Formato de correo no válido.').addClass('error');
            $(this).addClass('input-error');
            isFormValid.email = false;
        } else if (email === originalEmail) {
            emailValidationMessage.text('Correo original (válido).').addClass('success');
            $(this).addClass('input-success');
            isFormValid.email = true;
        } else {
            emailValidationMessage.text('Validando...').removeClass('error success');
            $.ajax({
                url: '../../controllers/validarEmailExistenteAjax.php', // Ruta correcta
                type: 'GET', data: { email: email }, dataType: 'json',
                success: function(response) {
                    if (response.valid) {
                        emailValidationMessage.text(response.message || 'Correo disponible.').addClass('success');
                        emailInput.addClass('input-success');
                        isFormValid.email = true;
                    } else {
                        emailValidationMessage.text(response.message || 'El correo ya está en uso.').addClass('error');
                        emailInput.addClass('input-error');
                        isFormValid.email = false;
                    }
                    updateSubmitButtonState();
                },
                error: function() { 
                    emailValidationMessage.text('Error al validar correo.').addClass('error');
                    emailInput.addClass('input-error');
                    isFormValid.email = false;
                    updateSubmitButtonState();
                }
            });
            return; 
        }
        updateSubmitButtonState();
    });

    usuarioInput.on('input blur', function() {
        const nombreUsuario = $(this).val().trim();
        usuarioValidationMessage.text('').removeClass('error success');
        $(this).removeClass('input-error input-success');

        if (nombreUsuario === '') {
            usuarioValidationMessage.text('El nombre de usuario es obligatorio.').addClass('error');
            $(this).addClass('input-error');
            isFormValid.usuario = false;
        } else if (!esNombreUsuarioValido(nombreUsuario)) {
            usuarioValidationMessage.text('Debe tener al menos 3 caracteres.').addClass('error');
            $(this).addClass('input-error');
            isFormValid.usuario = false;
        } else if (nombreUsuario === originalUsuario) {
            usuarioValidationMessage.text('Usuario original (válido).').addClass('success');
            $(this).addClass('input-success');
            isFormValid.usuario = true;
        } else {
            usuarioValidationMessage.text('Validando...').removeClass('error success');
            $.ajax({
                url: '../../controllers/validarUsuarioExistenteAjax.php', // Ruta correcta
                type: 'GET', data: { usuario: nombreUsuario }, dataType: 'json',
                success: function(response) {
                    if (response.valid) {
                        usuarioValidationMessage.text(response.message || 'Usuario disponible.').addClass('success');
                        usuarioInput.addClass('input-success');
                        isFormValid.usuario = true;
                    } else {
                        usuarioValidationMessage.text(response.message || 'Usuario ya en uso.').addClass('error');
                        usuarioInput.addClass('input-error');
                        isFormValid.usuario = false;
                    }
                    updateSubmitButtonState();
                },
                error: function() { 
                    usuarioValidationMessage.text('Error al validar usuario.').addClass('error');
                    usuarioInput.addClass('input-error');
                    isFormValid.usuario = false;
                    updateSubmitButtonState();
                }
            });
            return; 
        }
        updateSubmitButtonState();
    });

    passwordInput.on('input blur', function() {
        const password = $(this).val();
        passwordValidationMessage.text('').removeClass('error success');
        $(this).removeClass('input-error input-success');

        if (password === '') {
            isFormValid.password = true; 
        } else if (!esPasswordValida(password)) {
            let errorMsg = "La contraseña debe tener:<ul>";
            if (password.length < 8) errorMsg += "<li>Al menos 8 caracteres.</li>";
            if (!/[A-Z]/.test(password)) errorMsg += "<li>Al menos una mayúscula.</li>";
            if (!/[a-z]/.test(password)) errorMsg += "<li>Al menos una minúscula.</li>";
            if (!/[0-9]/.test(password)) errorMsg += "<li>Al menos un número.</li>";
            const specialCharRegex = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/;
            if (!specialCharRegex.test(password)) errorMsg += "<li>Al menos un carácter especial.</li>";
            errorMsg += "</ul>";
            passwordValidationMessage.html(errorMsg).addClass('error');
            $(this).addClass('input-error');
            isFormValid.password = false;
        } else {
            passwordValidationMessage.text('Contraseña válida (si se cambia).').addClass('success');
            $(this).addClass('input-success');
            isFormValid.password = true;
        }
        updateSubmitButtonState();
    });

    nacimientoInput.on('change blur', function() {
        const fechaNacimientoStr = $(this).val();
        nacimientoValidationMessage.text('').removeClass('error success');
        $(this).removeClass('input-error input-success');

        if (!fechaNacimientoStr) { // Si el campo es requerido y está vacío
            nacimientoValidationMessage.text('La fecha de nacimiento es obligatoria.').addClass('error');
            $(this).addClass('input-error');
            isFormValid.nacimiento = false;
        } else if (!esFechaNacimientoValida(fechaNacimientoStr)) {
            nacimientoValidationMessage.text('La fecha no puede ser hoy ni futura.').addClass('error');
            $(this).addClass('input-error');
            isFormValid.nacimiento = false;
        } else {
            nacimientoValidationMessage.text('Fecha válida.').addClass('success');
            $(this).addClass('input-success');
            isFormValid.nacimiento = true;
        }
        updateSubmitButtonState();
    });

    $('#formPerfil').on('submit', function(event) {
        emailInput.trigger('blur'); 
        usuarioInput.trigger('blur');
        passwordInput.trigger('blur');
        nacimientoInput.trigger('blur');

        // Se usa un pequeño timeout para dar tiempo a las validaciones AJAX a completarse
        // antes de verificar isFormValid.
        let formCanSubmit = true;
        for (const field in isFormValid) {
            if (!isFormValid[field]) {
                formCanSubmit = false;
                break;
            }
        }

        if (!formCanSubmit) {
            event.preventDefault(); 
            alert('Por favor, corrige los errores en el formulario antes de guardar.');
        }
        // Si es válido, el formulario se envía.
    });

    // --- LÓGICA PARA CARGA DE IMAGEN ---
    const inputFileDisplay = document.getElementById("input-file");
    if (inputFileDisplay) {
        inputFileDisplay.addEventListener("change", function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const profileImage = document.getElementById("profile-image");
                    if (profileImage) profileImage.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // --- LÓGICA PARA CARGAR PRODUCTOS APROBADOS/RECHAZADOS POR EL ADMIN ---
    // (Este es el código que ya tenías en tu perfil.js de administrador)
    function cargarProductosAprobadosAdmin() {
        fetch('../../controllers/getProductosAprobados.php') // Este SP trae TODOS los aprobados, no solo por este admin
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('aprobadosContainerAdmin');
                if (!container) return;
                container.innerHTML = '';
                if (!data || data.length === 0) {
                    container.innerHTML = `<div class="no-products-message">No hay productos aprobados actualmente.</div>`;
                    return;
                }
                data.forEach(producto => container.appendChild(crearCardProductoAdmin(producto)));
            });
    }

    function cargarProductosRechazadosAdmin() {
        fetch('../../controllers/getProductosRechazados.php') // Este SP trae TODOS los rechazados
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('rechazadosContainerAdmin');
                if (!container) return;
                container.innerHTML = '';
                if (!data || data.length === 0) {
                    container.innerHTML = `<div class="no-products-message">No hay productos rechazados actualmente.</div>`;
                    return;
                }
                data.forEach(producto => container.appendChild(crearCardProductoAdmin(producto)));
            });
    }

    function crearCardProductoAdmin(producto) { // Renombrada para evitar conflicto si hay otro crearCardProducto
        const card = document.createElement('div');
        card.classList.add('card');
        const imagen = producto.imagenPrincipal 
            ? `../../multimedia/productos/${producto.idProducto}/${producto.imagenPrincipal}` 
            : '../../multimedia/default/default.jpg';
        card.innerHTML = `
            <img src="${imagen}" alt="${escapeHtml(producto.nombre)}" class="card-image">
            <h3 class="card-title">${escapeHtml(producto.nombre)}</h3>
            <p class="card-description">${escapeHtml(producto.descripcion) || 'Sin descripción.'}</p>  
            <p class="card-price">$${parseFloat(producto.precio || 0).toFixed(2)} MXN</p>
            <button class="card-button-ver-mas" onclick="verProductoAdmin(${producto.idProducto})">Ver Detalles</button>
        `;
        return card;
    }
    
    window.verProductoAdmin = function(idProducto) { // Renombrada para evitar conflicto
        // El admin podría ir a una vista de producto diferente a la del cliente/vendedor
        // o a la misma pero con diferentes opciones.
        // Por ahora, asumimos que va a una página genérica de producto.
        // Si el admin tiene su propia vista de producto, cambia la ruta.
        window.location.href = `producto.php?idProducto=${idProducto}`; 
    }

    function escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') return '';
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    // Cargas iniciales
    cargarProductosAprobadosAdmin();
    cargarProductosRechazadosAdmin();
    
    // Validar campos al cargar la página
    emailInput.trigger('blur');
    usuarioInput.trigger('blur');
    nacimientoInput.trigger('blur');
    updateSubmitButtonState();
});
