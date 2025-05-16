// Este archivo se usará en chat.php para ambos roles: comprador y vendedor
// Requiere que el archivo PHP incluya las variables JS:
// ID_USUARIO_ACTUAL, ROL_USUARIO_ACTUAL, ID_CHAT_ACTIVO_URL

document.addEventListener("DOMContentLoaded", function () {
    // Elementos de la UI
    const chatListUl = document.getElementById("chatListUl");
    const chatMessagesContainer = document.getElementById("chatMessagesContainer");
    const mensajeInput = document.getElementById("mensajeInput");
    const sendBtn = document.getElementById("sendBtn");
    const inputMensajeDiv = document.getElementById("inputMensajeDiv");
    
    const chatHeaderAvatar = document.getElementById("chatHeaderAvatar");
    const chatHeaderNombreUsuario = document.getElementById("chatHeaderNombreUsuario");
    const chatHeaderProducto = document.getElementById("chatHeaderProducto");
    const buscarConversacionesInput = document.getElementById("buscarConversacionesInput");

    // Elementos específicos del Vendedor para ofertas
    const offerBtn = document.getElementById("offerBtn"); // Puede ser null si es Comprador
    const ofertaContainerDiv = document.getElementById("ofertaContainerDiv"); // Puede ser null
    const popupOverlay = document.getElementById("popupOverlay"); // Puede ser null
    const closeOfferPopupBtn = document.getElementById("closeOfferPopupBtn"); // Puede ser null
    const ofertaForm = document.getElementById("oferta-form"); // Puede ser null

    let idChatSeleccionado = ID_CHAT_ACTIVO_URL !== 0 ? ID_CHAT_ACTIVO_URL : null;
    let todasLasConversaciones = []; // Para el filtro

    // --- FUNCIONES PRINCIPALES ---

    /**
     * Carga la lista de conversaciones del usuario actual.
     */
    function cargarListaConversaciones() {
        const placeholder = chatListUl.querySelector('.chat-item-placeholder');
        if(placeholder) placeholder.style.display = 'block';

        fetch(`../../controllers/getConversaciones.php`)
            .then(res => res.json())
            .then(data => {
                if(placeholder) placeholder.style.display = 'none';
                chatListUl.innerHTML = ''; // Limpiar lista actual (excepto el placeholder que ya se ocultó)

                if (data.success && data.conversaciones.length > 0) {
                    todasLasConversaciones = data.conversaciones;
                    renderizarListaConversaciones(todasLasConversaciones);
                    
                    // Si venimos de iniciar un chat (ID_CHAT_ACTIVO_URL tiene valor), lo seleccionamos
                    if (idChatSeleccionado) {
                        const chatItemActivo = chatListUl.querySelector(`.chat-item[data-idchat="${idChatSeleccionado}"]`);
                        if (chatItemActivo) {
                            seleccionarChat(chatItemActivo, idChatSeleccionado, chatItemActivo.dataset.nombreotro, chatItemActivo.dataset.avatarotro, chatItemActivo.dataset.nombreproducto);
                        } else {
                             // Si el idChat de la URL no está en la lista, reseteamos.
                            idChatSeleccionado = null; 
                            mostrarPlaceholderMensajes();
                        }
                    } else {
                         mostrarPlaceholderMensajes();
                    }

                } else {
                    chatListUl.innerHTML = '<li style="text-align: center; padding: 20px; color: #ccc;">No tienes conversaciones.</li>';
                    mostrarPlaceholderMensajes();
                }
            })
            .catch(err => {
                if(placeholder) placeholder.style.display = 'none';
                console.error("Error al cargar conversaciones:", err);
                chatListUl.innerHTML = '<li style="text-align: center; padding: 20px; color: #f00;">Error al cargar.</li>';
                mostrarPlaceholderMensajes();
            });
    }

    /**
     * Renderiza los items de la lista de conversaciones.
     * @param {Array} conversaciones - Array de objetos de conversación.
     */
    function renderizarListaConversaciones(conversaciones) {
        chatListUl.innerHTML = ''; // Limpiar antes de renderizar
        if (conversaciones.length === 0) {
            chatListUl.innerHTML = '<li style="text-align: center; padding: 20px; color: #ccc;">No se encontraron conversaciones.</li>';
            return;
        }
        conversaciones.forEach(conv => {
            const li = document.createElement("li");
            li.classList.add("chat-item");
            li.dataset.idchat = conv.idChat;
            li.dataset.nombreotro = conv.nombreOtroUsuario; // Guardar para el header
            li.dataset.avatarotro = conv.fotoOtroUsuarioRuta; // Guardar para el header
            li.dataset.nombreproducto = conv.nombreProducto; // Guardar para el header


            li.innerHTML = `
                <div class="chat-avatar">
                    <img src="${conv.fotoOtroUsuarioRuta}" alt="${conv.nombreOtroUsuario}">
                    </div>
                <div class="chat-info">
                    <h3>${conv.nombreOtroUsuario}</h3>
                    <p class="producto-chat">Producto: ${conv.nombreProducto}</p>
                    <p class="ultimo-mensaje">${conv.ultimoMensaje || 'Conversación iniciada'}</p>
                </div>
                <div class="chat-meta">
                    <span class="time">${conv.tiempoUltimoMensaje || ''}</span>
                </div>
            `;
            li.addEventListener("click", function() {
                seleccionarChat(this, conv.idChat, conv.nombreOtroUsuario, conv.fotoOtroUsuarioRuta, conv.nombreProducto);
            });
            chatListUl.appendChild(li);
        });
    }
    
    /**
     * Maneja la selección de un chat de la lista.
     * @param {HTMLElement} chatItemElement - El elemento <li> del chat seleccionado.
     * @param {number} idChat - El ID del chat seleccionado.
     * @param {string} nombreOtroUsuario - Nombre del otro usuario.
     * @param {string} avatarOtroUsuario - Ruta del avatar del otro usuario.
     * @param {string} nombreProducto - Nombre del producto asociado al chat.
     */
    function seleccionarChat(chatItemElement, idChat, nombreOtroUsuario, avatarOtroUsuario, nombreProducto) {
        idChatSeleccionado = idChat;

        // Marcar como activo en la lista
        document.querySelectorAll("#chatListUl .chat-item.active").forEach(active => active.classList.remove("active"));
        chatItemElement.classList.add("active");

        // Actualizar header del chat
        chatHeaderAvatar.src = avatarOtroUsuario || '../../multimedia/default/default.jpg';
        chatHeaderNombreUsuario.textContent = nombreOtroUsuario || 'Usuario';
        chatHeaderProducto.textContent = `Sobre: ${nombreProducto || 'Producto desconocido'}`;


        // Mostrar input de mensaje
        inputMensajeDiv.style.display = "flex";
        
        // Cargar mensajes
        cargarMensajes(idChatSeleccionado);
    }

    /**
     * Muestra el placeholder cuando no hay chat seleccionado.
     */
    function mostrarPlaceholderMensajes() {
        chatMessagesContainer.innerHTML = `
            <div class="message-placeholder" style="text-align: center; padding: 50px; color: #aaa;">
                Selecciona una conversación para ver los mensajes.
            </div>`;
        chatHeaderAvatar.src = '../../multimedia/default/default.jpg';
        chatHeaderNombreUsuario.textContent = 'Selecciona un chat';
        chatHeaderProducto.textContent = '';
        inputMensajeDiv.style.display = "none";
    }


    /**
     * Carga los mensajes para el chat actualmente seleccionado.
     * @param {number} idChatParam - El ID del chat cuyos mensajes se cargarán.
     */
    function cargarMensajes(idChatParam) {
        if (!idChatParam) {
            mostrarPlaceholderMensajes();
            return;
        }
        chatMessagesContainer.innerHTML = '<div class="message-placeholder" style="text-align: center; padding: 20px; color: #ccc;">Cargando mensajes...</div>';

        fetch(`../../controllers/getMensajesChat.php?idChat=${idChatParam}`)
            .then(res => res.json())
            .then(data => {
                chatMessagesContainer.innerHTML = ''; // Limpiar

                if (data.success && data.mensajes.length > 0) {
                    const idUsuarioActual = data.idUsuarioActual; // El backend ahora nos da esto
                    data.mensajes.forEach(msg => {
                        const div = document.createElement("div");
                        div.classList.add("message");
                        // msg.esMio ya viene del backend
                        div.classList.add(msg.esMio ? "message-sent" : "message-received");

                        if (msg.tipo === "texto") {
                            div.innerHTML = `
                                <div class="message-content">
                                    <p>${escapeHtml(msg.mensaje)}</p>
                                </div>
                                <span class="message-time">${msg.hora}</span>
                            `;
                        } else if (msg.tipo === "oferta") {
                            // Botones de oferta (simplificado, la lógica de aceptar/rechazar se implementará después)
                            let botonesOfertaHTML = '';
                            if (msg.esMio) { // El que envió la oferta (Vendedor)
                                botonesOfertaHTML = `<button class="button-cancel-offer" data-idoferta="${msg.idMensaje}" title="Cancelar Oferta"><i class="fas fa-times"></i></button>`;
                            } else { // El que recibe la oferta (Comprador)
                                botonesOfertaHTML = `
                                    <button class="button-approve-offer" data-idoferta="${msg.idMensaje}" title="Aceptar Oferta"><i class="fas fa-check"></i></button>
                                    <button class="button-disapprove-offer" data-idoferta="${msg.idMensaje}" title="Rechazar Oferta"><i class="fas fa-times"></i></button>
                                `;
                            }
                            // Solo mostrar botones si la oferta está pendiente
                            if (msg.ofertaEstatus !== 'pendiente') {
                                botonesOfertaHTML = `<span class="oferta-estatus-${msg.ofertaEstatus}">Oferta ${msg.ofertaEstatus}</span>`;
                            }


                            div.innerHTML = `
                                <div class="offer-content">
                                    <span>Oferta: $${parseFloat(msg.ofertaPrecio).toFixed(2)} MXN</span>
                                    <p>${escapeHtml(msg.mensaje)}</p>
                                    <div class="offer-actions">
                                       ${botonesOfertaHTML}
                                    </div>
                                </div>
                                <span class="message-time">${msg.hora}</span>
                            `;
                        }
                        chatMessagesContainer.appendChild(div);
                    });
                } else if (data.success && data.mensajes.length === 0) {
                    chatMessagesContainer.innerHTML = '<div class="message-date"><span>Aún no hay mensajes en este chat.</span></div>';
                } else {
                    chatMessagesContainer.innerHTML = `<div class="message-date"><span>Error: ${data.message || 'No se pudieron cargar los mensajes.'}</span></div>`;
                }
                chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
            })
            .catch(err => {
                console.error("Error al cargar mensajes:", err);
                chatMessagesContainer.innerHTML = '<div class="message-date"><span>Error al conectar con el servidor.</span></div>';
            });
    }
    
    /**
     * Envía un mensaje de texto.
     */
    function enviarMensajeTexto() {
        if (!idChatSeleccionado) {
            alert("Por favor, selecciona una conversación primero.");
            return;
        }
        const texto = mensajeInput.value.trim();
        if (!texto) return;

        const formData = new FormData();
        formData.append("idChat", idChatSeleccionado);
        formData.append("mensaje", texto);
        // El idRemitente se toma de la sesión en el backend

        fetch("../../controllers/enviarMensajeTexto.php", { // Necesitarás crear este controlador
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mensajeInput.value = "";
                cargarMensajes(idChatSeleccionado); // Recargar mensajes para ver el nuevo
            } else {
                alert("Error al enviar mensaje: " + (data.message || "Error desconocido."));
            }
        })
        .catch(err => {
            console.error("Error al enviar mensaje:", err);
            alert("Ocurrió un error de red al enviar el mensaje.");
        });
    }

    // --- EVENT LISTENERS ---
    if (sendBtn) {
        sendBtn.addEventListener("click", enviarMensajeTexto);
    }
    if (mensajeInput) {
        mensajeInput.addEventListener("keypress", function (e) {
            if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault(); // Evitar nueva línea
                enviarMensajeTexto();
            }
        });
    }
    
    if (buscarConversacionesInput) {
        buscarConversacionesInput.addEventListener('input', function() {
            const terminoBusqueda = this.value.toLowerCase();
            const conversacionesFiltradas = todasLasConversaciones.filter(conv => {
                return conv.nombreOtroUsuario.toLowerCase().includes(terminoBusqueda) ||
                       conv.nombreProducto.toLowerCase().includes(terminoBusqueda) ||
                       (conv.ultimoMensaje && conv.ultimoMensaje.toLowerCase().includes(terminoBusqueda));
            });
            renderizarListaConversaciones(conversacionesFiltradas);
        });
    }

    // Lógica para el popup de ofertas (solo si los elementos existen - Vendedor)
    if (ROL_USUARIO_ACTUAL === "Vendedor" && offerBtn && ofertaContainerDiv && popupOverlay && closeOfferPopupBtn && ofertaForm) {
        offerBtn.addEventListener("click", () => {
            if (!idChatSeleccionado) {
                alert("Selecciona una conversación para hacer una oferta.");
                return;
            }
            ofertaContainerDiv.style.display = "block";
            popupOverlay.style.display = "block";
        });

        closeOfferPopupBtn.addEventListener("click", () => {
            ofertaContainerDiv.style.display = "none";
            popupOverlay.style.display = "none";
            ofertaForm.reset();
        });
        
        popupOverlay.addEventListener("click", () => { // Cerrar si se hace clic en el overlay
            ofertaContainerDiv.style.display = "none";
            popupOverlay.style.display = "none";
            ofertaForm.reset();
        });

        ofertaForm.addEventListener("submit", function (e) {
            e.preventDefault();
            if (!idChatSeleccionado) {
                alert("Error: No hay un chat seleccionado para enviar la oferta.");
                return;
            }

            const precio = parseFloat(document.getElementById("ofertaPrecioInput").value);
            const descripcion = document.getElementById("ofertaDescripcionInput").value.trim();

            if (isNaN(precio) || precio <= 0 || !descripcion) {
                alert("Debes completar todos los campos de la oferta correctamente.");
                return;
            }

            const formData = new FormData();
            formData.append("idChat", idChatSeleccionado);
            formData.append("precio", precio);
            formData.append("descripcion", descripcion);
            // idRemitente (Vendedor) se toma de la sesión en el backend

            fetch("../../controllers/enviarOferta.php", { // Necesitarás crear este controlador
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    ofertaForm.reset();
                    ofertaContainerDiv.style.display = "none";
                    popupOverlay.style.display = "none";
                    cargarMensajes(idChatSeleccionado); // Recargar para ver la nueva oferta
                } else {
                    alert("Error al enviar oferta: " + (data.message || "Error desconocido."));
                }
            })
            .catch(err => {
                console.error("Error al enviar oferta:", err);
                alert("Ocurrió un error de red al enviar la oferta.");
            });
        });
    }
    
    // Helper para escapar HTML y prevenir XSS simple
    function escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') return '';
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    // --- INICIALIZACIÓN ---
    cargarListaConversaciones(); 
    // El `idChatSeleccionado` ya se maneja dentro de `cargarListaConversaciones` si viene de la URL.

    // Refresco periódico de mensajes y lista de chats (opcional)
    setInterval(cargarListaConversaciones, 15000); // Recarga lista de chats cada 15s
    setInterval(() => {
        if (idChatSeleccionado) {
            cargarMensajes(idChatSeleccionado);
        }
    }, 5000); // Recarga mensajes del chat activo cada 5s

});
