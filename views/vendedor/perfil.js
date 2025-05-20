// views/vendedor/perfil.js

$(document).ready(function() {
    // --- SELECTORES DE ELEMENTOS DEL FORMULARIO ---
    const formPerfil = $('#formPerfil'); // Ya no es necesario, se usa el selector directo
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
    // Inicializar basado en si los campos ya tienen valores válidos (o asumirlos válidos hasta el primer cambio)
    let isFormValid = {
        email: esEmailValido(emailInput.val()),
        usuario: esNombreUsuarioValido(usuarioInput.val()),
        password: true, // Contraseña es opcional, válida si está vacía o cumple criterios
        nacimiento: esFechaNacimientoValida(nacimientoInput.val())
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
    
    // --- FUNCIONES DE VALIDACIÓN ESPECÍFICAS (para reutilizar) ---
    function esEmailValido(email) {
        if (email === '') return false; // No puede estar vacío si es required
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function esNombreUsuarioValido(nombreUsuario) {
        if (nombreUsuario === '') return false;
        return nombreUsuario.length >= 3;
    }

    function esPasswordValida(password) {
        if (password === '') return true; // Válido si está vacío (opcional)
        if (password.length < 8) return false;
        if (!/[A-Z]/.test(password)) return false;
        if (!/[a-z]/.test(password)) return false;
        if (!/[0-9]/.test(password)) return false;
        const specialCharRegex = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/; // Excluye ñ
        return specialCharRegex.test(password);
    }

    function esFechaNacimientoValida(fechaNacimientoStr) {
        if (!fechaNacimientoStr) return false;
        const fechaNacimiento = new Date(fechaNacimientoStr + "T00:00:00");
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
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
            emailValidationMessage.text('Correo original (válido).').addClass('success'); // Opcional: mensaje para el original
            $(this).addClass('input-success');
            isFormValid.email = true;
        } else {
            $.ajax({
                url: '../../controllers/validarEmailExistenteAjax.php',
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
                error: function() { /* ... (manejo de error AJAX) ... */ 
                    emailValidationMessage.text('Error al validar.').addClass('error');
                    isFormValid.email = false;
                    updateSubmitButtonState();
                }
            });
            // No actualizar isFormValid.email aquí, esperar respuesta AJAX
            // updateSubmitButtonState(); // Se llama dentro del success/error de AJAX
            return; // Evitar que se actualice el botón antes de la respuesta AJAX
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
            $.ajax({
                url: '../../controllers/validarUsuarioExistenteAjax.php',
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
                error: function() { /* ... (manejo de error AJAX) ... */ 
                    usuarioValidationMessage.text('Error al validar.').addClass('error');
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
            isFormValid.password = true; // Válido si está vacío
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

        if (!esFechaNacimientoValida(fechaNacimientoStr)) {
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
        // Re-validar todos los campos antes del submit final
        emailInput.trigger('blur'); // Trigger blur para forzar la validación AJAX si es necesario
        usuarioInput.trigger('blur');
        passwordInput.trigger('blur');
        nacimientoInput.trigger('blur');

        // Pequeño delay para permitir que las validaciones AJAX terminen si se dispararon por el blur
        setTimeout(() => {
            if (!isFormValid.email || !isFormValid.usuario || !isFormValid.password || !isFormValid.nacimiento) {
                event.preventDefault(); 
                alert('Por favor, corrige los errores en el formulario.');
            }
            // Si todo es válido, el formulario se enviará.
        }, 200); // Ajusta el delay si es necesario
    });

    // --- LÓGICA PARA CARGA DE IMAGEN (código existente) ---
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

    // --- LÓGICA PARA CARGAR PRODUCTOS DEL VENDEDOR (código existente) ---
    function cargarProductosPendientes() {
        fetch('../../controllers/getProductosPendientesVendedor.php')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('pendientesContainer'); // Usar ID específico
                if (!container) return;
                container.innerHTML = '';
                if (!data || data.length === 0) {
                    container.innerHTML = `<div class="no-products-message">No tienes solicitudes pendientes.</div>`;
                    return;
                }
                data.forEach(producto => container.appendChild(crearCardProducto(producto)));
            });
    }

    function cargarProductosAprobados() {
        fetch('../../controllers/getProductosAprobadosVendedor.php')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('aprobadosContainer'); // Usar ID específico
                if (!container) return;
                container.innerHTML = '';
                if (!data || data.length === 0) {
                    container.innerHTML = `<div class="no-products-message">No tienes productos aprobados.</div>`;
                    return;
                }
                data.forEach(producto => container.appendChild(crearCardProducto(producto)));
            });
    }

    function cargarProductosRechazados() {
        fetch('../../controllers/getProductosRechazadosVendedor.php')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('rechazadosContainer'); // Usar ID específico
                if (!container) return;
                container.innerHTML = '';
                if (!data || data.length === 0) {
                    container.innerHTML = `<div class="no-products-message">No tienes productos rechazados.</div>`;
                    return;
                }
                data.forEach(producto => container.appendChild(crearCardProducto(producto)));
            });
    }

    function crearCardProducto(producto) {
        const card = document.createElement('div');
        card.classList.add('card');
        const imagen = producto.imagenPrincipal 
            ? `../../multimedia/productos/${producto.idProducto}/${producto.imagenPrincipal}` 
            : '../../multimedia/default/default.jpg';
        card.innerHTML = `
            <img src="${imagen}" alt="Imagen del producto" class="card-image">
            <h3 class="card-title">${escapeHtml(producto.nombre)}</h3>
            <p class="card-description">${escapeHtml(producto.descripcion) || 'Sin descripción.'}</p>  
            <p class="card-price">$${parseFloat(producto.precio || 0).toFixed(2)} MXN</p>
            <button class="card-button-ver-mas" onclick="verProducto(${producto.idProducto})">Ver más</button>
        `;
        return card;
    }
    
    window.verProducto = function(idProducto) { // Hacerla global si es llamada por onclick
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

    // Cargas iniciales para el vendedor
    cargarProductosPendientes();
    cargarProductosAprobados();
    cargarProductosRechazados();
    
    // Validar campos al cargar la página si ya tienen contenido
    emailInput.trigger('blur');
    usuarioInput.trigger('blur');
    nacimientoInput.trigger('blur');
    // No disparamos passwordInput al inicio
    updateSubmitButtonState(); // Estado inicial del botón
});
