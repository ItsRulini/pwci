// views/cliente/perfil.js (o el perfil.js del rol correspondiente)

$(document).ready(function() {
    // --- SELECTORES DE ELEMENTOS DEL FORMULARIO ---
    const formPerfil = $('#formPerfil');
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
        email: emailInput.val() ? true : false, // Asumir válido si ya tiene valor al cargar (se revalidará al cambiar)
        usuario: usuarioInput.val() ? true : false,
        password: true, // Contraseña es opcional, válida si está vacía o cumple criterios
        nacimiento: nacimientoInput.val() ? true : false
    };
    
    // Guardar valores originales para email y usuario (para no validar contra sí mismo innecesariamente)
    const originalEmail = emailInput.val();
    const originalUsuario = usuarioInput.val();

    function updateSubmitButtonState() {
        // Habilitar botón solo si todos los campos requeridos son válidos
        if (isFormValid.email && isFormValid.usuario && isFormValid.password && isFormValid.nacimiento) {
            submitButton.prop('disabled', false).removeClass('disabled-button-style'); // Asume que tienes un estilo para deshabilitado
        } else {
            submitButton.prop('disabled', true).addClass('disabled-button-style');
        }
    }

    // --- FUNCIONES DE VALIDACIÓN INDIVIDUALES ---

    // 1. Validar Email (Existencia)
    emailInput.on('input blur', function() {
        const email = $(this).val().trim();
        emailValidationMessage.text('').removeClass('error success');
        $(this).removeClass('input-error input-success');

        if (email === '') {
            emailValidationMessage.text('El correo es obligatorio.').addClass('error');
            isFormValid.email = false;
            updateSubmitButtonState();
            return;
        }
        // Simple validación de formato de email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            emailValidationMessage.text('Formato de correo no válido.').addClass('error');
            $(this).addClass('input-error');
            isFormValid.email = false;
            updateSubmitButtonState();
            return;
        }

        if (email === originalEmail) { // Si es el mismo email original, no hacer AJAX
            isFormValid.email = true;
            updateSubmitButtonState();
            return;
        }

        // AJAX para validar existencia (solo si es diferente al original)
        $.ajax({
            url: '../../controllers/validarEmailExistenteAjax.php', // Ajusta ruta si es necesario
            type: 'GET',
            data: { email: email },
            dataType: 'json',
            success: function(response) {
                if (response.valid) {
                    emailValidationMessage.text('Correo disponible.').addClass('success');
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
                emailValidationMessage.text('Error al validar correo. Intenta de nuevo.').addClass('error');
                emailInput.addClass('input-error');
                isFormValid.email = false;
                updateSubmitButtonState();
            }
        });
    });

    // 2. Validar Nombre de Usuario (Existencia y Longitud)
    usuarioInput.on('input blur', function() {
        const nombreUsuario = $(this).val().trim();
        usuarioValidationMessage.text('').removeClass('error success');
        $(this).removeClass('input-error input-success');

        if (nombreUsuario === '') {
            usuarioValidationMessage.text('El nombre de usuario es obligatorio.').addClass('error');
            isFormValid.usuario = false;
            updateSubmitButtonState();
            return;
        }

        if (nombreUsuario.length < 3) {
            usuarioValidationMessage.text('El nombre de usuario debe tener al menos 3 caracteres.').addClass('error');
            $(this).addClass('input-error');
            isFormValid.usuario = false;
            updateSubmitButtonState();
            return;
        }
        
        if (nombreUsuario === originalUsuario) { // Si es el mismo usuario original, no hacer AJAX
             isFormValid.usuario = true;
             updateSubmitButtonState();
             return;
        }

        // AJAX para validar existencia (solo si es diferente al original)
        $.ajax({
            url: '../../controllers/validarUsuarioExistenteAjax.php', // Ajusta ruta si es necesario
            type: 'GET',
            data: { usuario: nombreUsuario }, // El controlador espera 'usuario'
            dataType: 'json',
            success: function(response) {
                if (response.valid) {
                    usuarioValidationMessage.text('Nombre de usuario disponible.').addClass('success');
                    usuarioInput.addClass('input-success');
                    isFormValid.usuario = true;
                } else {
                    usuarioValidationMessage.text(response.message || 'El nombre de usuario ya está en uso.').addClass('error');
                    usuarioInput.addClass('input-error');
                    isFormValid.usuario = false;
                }
                updateSubmitButtonState();
            },
            error: function() {
                usuarioValidationMessage.text('Error al validar usuario. Intenta de nuevo.').addClass('error');
                usuarioInput.addClass('input-error');
                isFormValid.usuario = false;
                updateSubmitButtonState();
            }
        });
    });

    // 3. Validar Contraseña (Complejidad)
    passwordInput.on('input blur', function() {
        const password = $(this).val(); // No trim, espacios pueden ser parte de la contraseña
        passwordValidationMessage.text('').removeClass('error success');
        $(this).removeClass('input-error input-success');

        if (password === '') { // Contraseña es opcional para actualización
            isFormValid.password = true;
            updateSubmitButtonState();
            return;
        }

        let errors = [];
        if (password.length < 8) {
            errors.push("Debe tener al menos 8 caracteres.");
        }
        if (!/[A-Z]/.test(password)) {
            errors.push("Debe contener al menos una mayúscula.");
        }
        if (!/[a-z]/.test(password)) {
            errors.push("Debe contener al menos una minúscula.");
        }
        if (!/[0-9]/.test(password)) {
            errors.push("Debe contener al menos un número.");
        }
        if (!/[^A-Za-z0-9]/.test(password)) { // Excluye la ñ si es necesario con un regex más específico
            errors.push("Debe contener al menos un carácter especial.");
        }
        // Regex que excluye la ñ como carácter especial:
        const specialCharRegex = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/;
        if (!specialCharRegex.test(password)) {
            errors.push("Debe contener al menos un carácter especial (ej. !@#$%).");
        }


        if (errors.length > 0) {
            passwordValidationMessage.html(errors.join('<br>')).addClass('error');
            $(this).addClass('input-error');
            isFormValid.password = false;
        } else {
            passwordValidationMessage.text('Contraseña válida.').addClass('success');
            $(this).addClass('input-success');
            isFormValid.password = true;
        }
        updateSubmitButtonState();
    });

    // 4. Validar Fecha de Nacimiento
    nacimientoInput.on('change blur', function() {
        const fechaNacimientoStr = $(this).val();
        nacimientoValidationMessage.text('').removeClass('error success');
        $(this).removeClass('input-error input-success');

        if (!fechaNacimientoStr) {
            nacimientoValidationMessage.text('La fecha de nacimiento es obligatoria.').addClass('error');
            isFormValid.nacimiento = false;
            updateSubmitButtonState();
            return;
        }

        const fechaNacimiento = new Date(fechaNacimientoStr + "T00:00:00"); // Asegurar que se interprete como local
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0); // Poner la hora a cero para comparar solo fechas

        if (fechaNacimiento >= hoy) {
            nacimientoValidationMessage.text('La fecha de nacimiento no puede ser hoy ni una fecha futura.').addClass('error');
            $(this).addClass('input-error');
            isFormValid.nacimiento = false;
        } else {
            // Aquí podrías añadir validación de edad mínima si es necesario en el futuro.
            nacimientoValidationMessage.text('Fecha válida.').addClass('success');
            $(this).addClass('input-success');
            isFormValid.nacimiento = true;
        }
        updateSubmitButtonState();
    });

    // --- MANEJO DEL SUBMIT DEL FORMULARIO ---
    formPerfil.on('submit', function(event) {
        // Re-validar todos los campos por si acaso antes del submit
        emailInput.trigger('blur');
        usuarioInput.trigger('blur');
        passwordInput.trigger('blur'); // Validar contraseña si se ha escrito algo
        nacimientoInput.trigger('blur');

        if (!isFormValid.email || !isFormValid.usuario || !isFormValid.password || !isFormValid.nacimiento) {
            event.preventDefault(); // Detener envío si hay errores
            alert('Por favor, corrige los errores en el formulario antes de guardar.');
        }
        // Si todo es válido, el formulario se enviará normalmente.
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
    
    // --- LÓGICA PARA WISHLISTS (si este es el perfil del cliente) ---
    // ... (Tu código existente para manejar popups de wishlists, cargar wishlists, etc.)
    // ... (Este código debe permanecer aquí si es el perfil.js del cliente)
    // const btnAbrirPopupCrear = document.getElementById("btnAbrirPopup"); 
    // const popupCrearWishlist = document.getElementById("popup"); 
    // const btnCerrarPopupCrear = document.getElementById("btnCerrarPopup"); 
    const formWishlistEl = document.getElementById("formWishlist"); // Renombrado para evitar conflicto con la variable formWishlist de jQuery
    const ulListas = document.querySelector(".wishlists .listas"); 

    const popupEditarLista = document.getElementById("popupEditarLista");
    const btnCerrarEditarLista = document.getElementById("btnCerrarEditarLista");
    const formEditarWishlistEl = document.getElementById("formEditarWishlist");
    const ulProductosEditar = document.getElementById("listaProductosEditar");

    // (Aquí irían tus funciones cargarYRenderizarWishlists, cargarProductosDeWishlist, etc.)
    // (Y los listeners para los popups de wishlist)
    function cargarYRenderizarWishlists() {
        if (!ulListas) return;
        ulListas.innerHTML = '<li>Cargando tus wishlists...</li>';

        fetch('../../controllers/getMisWishlists.php')
            .then(response => response.json())
            .then(data => {
                ulListas.innerHTML = ''; 
                if (data.success && data.wishlists.length > 0) {
                    data.wishlists.forEach(wishlist => {
                        const li = document.createElement("li");
                        li.classList.add("lista");
                        li.dataset.idlista = wishlist.idLista;
                        li.dataset.nombre = wishlist.nombre;
                        li.dataset.descripcion = wishlist.descripcion || '';
                        li.dataset.privacidad = wishlist.privacidad || 'Privada';

                        li.innerHTML = `
                            <div class="headerLista">
                                <span>${escapeHtml(wishlist.nombre)}</span>
                                <i class="fas fa-ellipsis-v btn-opciones-lista" title="Opciones"></i>
                            </div>
                            <p>${escapeHtml(wishlist.descripcion) || 'Sin descripción.'}</p>
                            <ol class="contenidoLista">
                                {/* Productos se cargarán aquí */}
                            </ol>
                            <div class="pop-up-options" style="display: none;">
                                <div class="pop-up-content">
                                    <span class="close btnCerrarPopupOptions">&times;</span>
                                    <h3>Opciones de lista</h3>
                                    <button class="btn-editar-lista" 
                                            data-idlista="${wishlist.idLista}" 
                                            data-nombre="${escapeHtml(wishlist.nombre)}" 
                                            data-descripcion="${escapeHtml(wishlist.descripcion || '')}" 
                                            data-privacidad="${escapeHtml(wishlist.privacidad || 'Privada')}">Editar lista</button>
                                    <button class="btn-eliminar-lista" data-idlista="${wishlist.idLista}">Eliminar lista</button>
                                </div>
                            </div>
                        `;
                        ulListas.appendChild(li);
                        cargarProductosDeWishlist(wishlist.idLista, li.querySelector('.contenidoLista'));
                    });
                } else if (data.success && data.wishlists.length === 0) {
                    ulListas.innerHTML = '<li>No tienes wishlists creadas. ¡Crea una!</li>';
                } else {
                    ulListas.innerHTML = `<li>Error al cargar wishlists: ${data.message || 'Error desconocido.'}</li>`;
                }
            })
            .catch(error => {
                console.error('Error fetching wishlists:', error);
                if (ulListas) ulListas.innerHTML = '<li>Error de conexión al cargar wishlists.</li>';
            });
    }

    function cargarProductosDeWishlist(idLista, contenedorProductosOl) {
        if (!contenedorProductosOl) return;
        contenedorProductosOl.innerHTML = '<li class="producto-placeholder">Cargando productos...</li>';

        fetch(`../../controllers/getProductosDeLista.php?idLista=${idLista}`)
            .then(response => response.json())
            .then(data => {
                contenedorProductosOl.innerHTML = ''; 
                if (data.success && data.productos.length > 0) {
                    data.productos.forEach(producto => {
                        const prodLi = document.createElement("li");
                        prodLi.classList.add("producto"); 
                        prodLi.dataset.idproducto = producto.idProducto;

                        const imagenSrc = producto.imagenPrincipal 
                            ? `../../multimedia/productos/${producto.idProducto}/${producto.imagenPrincipal}`
                            : '../../multimedia/default/default.jpg';
                        
                        let precioHTML = '';
                        if (producto.tipoProducto === 'Venta') {
                            precioHTML = `<p>$${parseFloat(producto.precio).toFixed(2)} MXN</p>`;
                        } else { 
                            precioHTML = `<p>Negociable</p>`;
                        }
                        prodLi.innerHTML = `
                            <a href="producto.php?idProducto=${producto.idProducto}" class="enlace-producto-wishlist">
                                <img src="${imagenSrc}" alt="${escapeHtml(producto.nombre)}">
                                <div class="info">
                                    <span>${escapeHtml(producto.nombre)}</span>
                                    ${precioHTML}
                                </div>
                            </a>
                        `;
                        contenedorProductosOl.appendChild(prodLi);
                    });
                } else if (data.success && data.productos.length === 0) {
                    contenedorProductosOl.innerHTML = '<li class="producto-placeholder">Esta wishlist está vacía.</li>';
                } else {
                    contenedorProductosOl.innerHTML = `<li class="producto-placeholder">Error: ${data.message || 'No se pudieron cargar los productos.'}</li>`;
                }
            })
            .catch(error => {
                console.error(`Error fetching productos para wishlist ${idLista}:`, error);
                if (contenedorProductosOl) contenedorProductosOl.innerHTML = '<li class="producto-placeholder">Error de conexión.</li>';
            });
    }

    /**
     * Abre el popup para editar una wishlist, poblando sus campos y productos.
     */
    function abrirPopupEditarWishlist(idLista, nombre, descripcion, privacidad) {
        if (popupEditarLista && formEditarWishlistEl && ulProductosEditar) {
            // Poblar detalles de la lista
            formEditarWishlistEl.querySelector('#editarNombreLista').value = nombre;
            formEditarWishlistEl.querySelector('#editarDescripcionLista').value = descripcion;
            const radiosPrivacidad = formEditarWishlistEl.querySelectorAll('input[name="editarListaPrivacidad"]');
            radiosPrivacidad.forEach(radio => {
                radio.checked = (radio.value === privacidad);
            });
            
            popupEditarLista.dataset.idlistaactual = idLista; // Guardar ID para el submit del form

            // Cargar y mostrar productos de esta wishlist dentro del popup
            ulProductosEditar.innerHTML = '<li class="producto-editar-placeholder">Cargando productos...</li>';
            fetch(`../../controllers/getProductosDeLista.php?idLista=${idLista}`)
                .then(response => response.json())
                .then(data => {
                    ulProductosEditar.innerHTML = ''; // Limpiar
                    if (data.success && data.productos.length > 0) {
                        data.productos.forEach(producto => {
                            const itemLi = document.createElement("li");
                            itemLi.classList.add("producto-editar"); // Clase para estilizar si es necesario
                            itemLi.dataset.idproducto = producto.idProducto;
                            itemLi.innerHTML = `
                                <span>${escapeHtml(producto.nombre)}</span>
                                <button class="btn-eliminar-producto-de-wishlist" title="Eliminar de esta wishlist">
                                    <i class="fas fa-trash"></i>
                                </button>
                            `;
                            ulProductosEditar.appendChild(itemLi);
                        });
                    } else if (data.success && data.productos.length === 0) {
                        ulProductosEditar.innerHTML = '<li class="producto-editar-placeholder">No hay productos en esta wishlist.</li>';
                    } else {
                        ulProductosEditar.innerHTML = `<li class="producto-editar-placeholder">Error: ${data.message || 'No se pudieron cargar los productos.'}</li>`;
                    }
                })
                .catch(error => {
                    console.error(`Error cargando productos para editar wishlist ${idLista}:`, error);
                    ulProductosEditar.innerHTML = '<li class="producto-editar-placeholder">Error de conexión.</li>';
                });

            popupEditarLista.style.display = "flex"; // O 'flex'
        } else {
             console.error("Elementos del popup de edición no encontrados.");
        }
    }
    
    /**
     * Elimina un producto específico de una wishlist (desde el popup de edición).
     */
    function eliminarProductoDeWishlist(idLista, idProducto, botonEliminar) {
        if (!idLista || !idProducto) return;

        if (confirm(`¿Seguro que quieres eliminar este producto de la wishlist?`)) {
            const formData = new FormData();
            formData.append('idLista', idLista);
            formData.append('idProducto', idProducto);

            fetch('../../controllers/eliminarProductoDeWishlist.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || "Producto eliminado de la wishlist.");
                    // Eliminar el elemento del DOM o recargar la lista de productos en el popup
                    const itemLi = botonEliminar.closest(".producto-editar");
                    if (itemLi) itemLi.remove();
                    // Opcionalmente, verificar si la lista de productos en el popup está vacía
                    if (ulProductosEditar && ulProductosEditar.children.length === 0) {
                        ulProductosEditar.innerHTML = '<li class="producto-editar-placeholder">No hay productos en esta wishlist.</li>';
                    }
                    // También recargar la vista principal de wishlists para reflejar el cambio si es necesario
                    cargarYRenderizarWishlists(); 
                } else {
                    alert("Error: " + (data.message || "No se pudo eliminar el producto."));
                }
            })
            .catch(error => {
                console.error('Error al eliminar producto de wishlist:', error);
                alert('Ocurrió un error de conexión.');
            });
        }
    }

    // Event listener para el popup de edición de productos (delegación)
    if (ulProductosEditar) {
        ulProductosEditar.addEventListener('click', function(event) {
            const target = event.target;
            const botonEliminar = target.closest('.btn-eliminar-producto-de-wishlist');
            if (botonEliminar) {
                const idLista = popupEditarLista.dataset.idlistaactual; // Obtener de la data del popup
                const productoLi = botonEliminar.closest(".producto-editar");
                const idProducto = productoLi ? productoLi.dataset.idproducto : null;
                if (idLista && idProducto) {
                    eliminarProductoDeWishlist(idLista, idProducto, botonEliminar);
                }
            }
        });
    }

    // ... (resto del código: listeners para crear wishlist, opciones de lista, eliminar lista completa, submit de editar lista, etc.)
    // Asegúrate que el código de la Iteración 2.2 (eliminar wishlist completa) y la Iteración 1 (crear wishlist) estén aquí.
    // Solo he mostrado las funciones modificadas/nuevas y el listener para eliminar producto del popup.

    // --- MANEJO DE IMAGEN DE PERFIL (código existente) ---
    // ... (ya lo tienes)

    // --- MANEJO DE WISHLISTS ---
    const btnAbrirPopupCrear = document.getElementById("btnAbrirPopup");
    const popupCrearWishlist = document.getElementById("popup");
    const btnCerrarPopupCrear = document.getElementById("btnCerrarPopup");
    //const formWishlist = document.getElementById("formWishlist");
    // const ulListas = document.querySelector(".wishlists .listas"); // Ya definido arriba

    // const popupEditarLista = document.getElementById("popupEditarLista"); // Ya definido arriba
    // const btnCerrarEditarLista = document.getElementById("btnCerrarEditarLista"); // Ya definido arriba
    // const formEditarWishlist = document.getElementById("formEditarWishlist"); // Ya definido arriba


    // Abrir/Cerrar Popup de Crear Wishlist
    if (btnAbrirPopupCrear && popupCrearWishlist) {
        btnAbrirPopupCrear.addEventListener("click", function() {
            popupCrearWishlist.style.display = "flex";
            if(formWishlistEl) {
                formWishlistEl.reset(); 
                const radiosPrivacidad = formWishlistEl.querySelectorAll('input[name="listaPrivacidad"]');
                radiosPrivacidad.forEach(radio => radio.checked = false);
            }
        });
    }
    if (btnCerrarPopupCrear && popupCrearWishlist) {
        btnCerrarPopupCrear.addEventListener("click", function() {
            popupCrearWishlist.style.display = "none";
        });
    }
    if (popupCrearWishlist) {
        popupCrearWishlist.addEventListener("click", function(event) {
            if (event.target === popupCrearWishlist) {
                popupCrearWishlist.style.display = "none";
            }
        });
    }

    // Manejar envío del Formulario para Crear Wishlist
    if (formWishlistEl) {
        formWishlistEl.addEventListener("submit", function(event) {
            event.preventDefault();
            const privacidadSeleccionada = formWishlistEl.querySelector('input[name="listaPrivacidad"]:checked');
            if (!privacidadSeleccionada) {
                alert("Por favor, selecciona un tipo de privacidad para la wishlist (Pública o Privada).");
                return; 
            }
            const formData = new FormData(formWishlistEl);
            
            fetch('../../controllers/crearWishlist.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || "Wishlist creada correctamente.");
                    if (popupCrearWishlist) popupCrearWishlist.style.display = "none";
                    cargarYRenderizarWishlists(); 
                } else {
                    alert("Error: " + (data.message || "No se pudo crear la wishlist."));
                }
            })
            .catch(error => {
                console.error('Error al crear wishlist:', error);
                alert('Ocurrió un error de conexión al crear la wishlist.');
            });
        });
    }

    // --- MANEJO DE OPCIONES DE WISHLIST (Eliminar/Editar - Iteración 2) ---
    if (ulListas) {
        ulListas.addEventListener("click", function(event) {
            const target = event.target;
            const closestButtonOpciones = target.closest(".btn-opciones-lista");
            const closestButtonCerrarOpciones = target.closest(".btnCerrarPopupOptions");
            const closestButtonEliminar = target.closest(".btn-eliminar-lista");
            const closestButtonEditar = target.closest(".btn-editar-lista");

            if (closestButtonOpciones) {
                event.stopPropagation();
                document.querySelectorAll(".pop-up-options").forEach(popup => popup.style.display = "none");
                const popupOptions = closestButtonOpciones.closest(".lista").querySelector(".pop-up-options");
                if (popupOptions) {
                    const rect = closestButtonOpciones.getBoundingClientRect();
                    popupOptions.style.left = `${(rect.left + window.scrollX - popupOptions.offsetWidth + rect.width / 2) -115}px`;
                    popupOptions.style.top = `${rect.bottom + window.scrollY + 5}px`;
                    popupOptions.style.display = "block";
                }
            }
            else if (closestButtonCerrarOpciones) {
                event.stopPropagation();
                const popupOptions = closestButtonCerrarOpciones.closest(".pop-up-options");
                if (popupOptions) popupOptions.style.display = "none";
            }
            else if (closestButtonEliminar) {
                event.stopPropagation();
                const idLista = closestButtonEliminar.dataset.idlista;
                if (idLista && confirm("¿Estás seguro de que quieres eliminar esta wishlist? Esta acción no se puede deshacer.")) {
                    eliminarWishlist(idLista); 
                }
                 const popupOptions = closestButtonEliminar.closest(".pop-up-options"); 
                 if (popupOptions) popupOptions.style.display = "none";
            }
            else if (closestButtonEditar) {
                 event.stopPropagation();
                const idLista = closestButtonEditar.dataset.idlista;
                const nombreActual = closestButtonEditar.dataset.nombre;
                const descripcionActual = closestButtonEditar.dataset.descripcion;
                const privacidadActual = closestButtonEditar.dataset.privacidad;
                
                if (idLista) {
                    abrirPopupEditarWishlist(idLista, nombreActual, descripcionActual, privacidadActual);
                }
                 const popupOptions = closestButtonEditar.closest(".pop-up-options"); 
                 if (popupOptions) popupOptions.style.display = "none";
            }
        });
    }
    document.addEventListener("click", function(event) {
        if (!event.target.closest(".pop-up-options") && !event.target.classList.contains("btn-opciones-lista")) {
            document.querySelectorAll(".pop-up-options").forEach(popup => {
                popup.style.display = "none";
            });
        }
    });

    // Función para eliminar wishlist completa
    function eliminarWishlist(idLista) {
        const formData = new FormData();
        formData.append('idLista', idLista);

        fetch('../../controllers/eliminarWishlist.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || "Wishlist eliminada correctamente.");
                cargarYRenderizarWishlists(); 
            } else {
                alert("Error: " + (data.message || "No se pudo eliminar la wishlist."));
            }
        })
        .catch(error => {
            console.error('Error al eliminar wishlist:', error);
            alert('Ocurrió un error de conexión al intentar eliminar la wishlist.');
        });
    }
    
    // Cerrar popup de edición
    if(btnCerrarEditarLista && popupEditarLista){
        btnCerrarEditarLista.addEventListener("click", () => {
            popupEditarLista.style.display = "none";
        });
    }
    
    // Listener para el submit del form de edición de wishlist
    if (formEditarWishlistEl) {
        formEditarWishlistEl.addEventListener('submit', function(event) {
            event.preventDefault();
            const idListaActual = popupEditarLista.dataset.idlistaactual;
            if (!idListaActual) {
                alert("Error: No se pudo identificar la wishlist a editar.");
                return;
            }

            const privacidadSeleccionada = formEditarWishlistEl.querySelector('input[name="editarListaPrivacidad"]:checked');
            if (!privacidadSeleccionada) {
                alert("Por favor, selecciona un tipo de privacidad para la wishlist.");
                return;
            }

            const formData = new FormData(formEditarWishlistEl);
            formData.append('idLista', idListaActual); 

            fetch('../../controllers/actualizarWishlist.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || "Wishlist actualizada.");
                    if (popupEditarLista) popupEditarLista.style.display = "none";
                    cargarYRenderizarWishlists(); 
                } else {
                    alert("Error al actualizar: " + (data.message || "Error desconocido."));
                }
            })
            .catch(error => {
                console.error('Error al actualizar wishlist:', error);
                alert('Error de conexión al actualizar la wishlist.');
            });
        });
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

    cargarYRenderizarWishlists();

    // Llamada inicial para actualizar estado del botón de submit
    // y validar campos que ya tienen valor al cargar la página
    emailInput.trigger('blur');
    usuarioInput.trigger('blur');
    // No disparamos passwordInput.trigger('blur') al inicio porque es opcional si está vacío
    nacimientoInput.trigger('blur');
    updateSubmitButtonState(); // Estado inicial del botón
});



// document.addEventListener("DOMContentLoaded", function () {
//     // ... (selectores y código existente de la Iteración 2.2) ...
//     const ulListas = document.querySelector(".wishlists .listas");
//     const popupEditarLista = document.getElementById("popupEditarLista");
//     const btnCerrarEditarLista = document.getElementById("btnCerrarEditarLista");
//     const formEditarWishlist = document.getElementById("formEditarWishlist");
//     // Contenedor para los productos dentro del popup de edición
//     const ulProductosEditar = document.getElementById("listaProductosEditar"); 

//     // --- Funciones de Carga y Renderizado (cargarYRenderizarWishlists, cargarProductosDeWishlist) ---
//     // Estas funciones ya las tienes de la iteración anterior. Asegúrate que estén aquí.
//     // ... (pegar aquí las funciones cargarYRenderizarWishlists y cargarProductosDeWishlist de la respuesta anterior)

//     /**
//      * Carga y renderiza las wishlists del usuario.
//      */
    
// });
