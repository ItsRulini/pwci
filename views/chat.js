// chat.js

// Este archivo se usará en chat.php para ambos roles: comprador y vendedor
// Requiere que el archivo PHP incluya las variables JS: idChat y rolUsuario

document.addEventListener("DOMContentLoaded", function () {
    const chatContainer = document.getElementById("chat");
    const inputMensaje = document.getElementById("mensaje");
    const sendBtn = document.querySelector(".send-btn");
    const offerBtn = document.querySelector(".offer-btn");
    const popup = document.querySelector(".oferta-container");
    const popupOverlay = document.getElementById("popupOverlay");
    const closeBtn = document.querySelector(".close-btn");
    const ofertaForm = document.getElementById("oferta-form");

    // Ocultar opción de oferta si no es vendedor
    if (typeof rolUsuario !== "undefined" && rolUsuario !== "Vendedor") {
        if (offerBtn) offerBtn.style.display = "none";
    }

    function cargarMensajes() {
        fetch(`../../controllers/getMensajesChat.php?idChat=${idChat}`)
            .then(res => res.json())
            .then(data => {
                chatContainer.innerHTML = ''; // Limpiar

                if (data.length === 0) {
                    chatContainer.innerHTML = '<div class="message-date"><span>Aún no hay mensajes</span></div>';
                    return;
                }

                data.forEach(msg => {
                    const div = document.createElement("div");
                    div.classList.add("message");
                    div.classList.add(msg.esMio ? "message-sent" : "message-received");

                    if (msg.tipo === "texto") {
                        div.innerHTML = `
                            <div class="message-content">
                                <p>${msg.mensaje}</p>
                            </div>
                            <span class="message-time">${msg.hora}</span>
                        `;
                    } else if (msg.tipo === "oferta") {
                        const botones = msg.esMio
                            ? '<button class="button-cancel" data-id="' + msg.idOferta + '"><i class="fas fa-times"></i></button>'
                            : `<button class="button-approve" data-id="${msg.idOferta}"><i class="fas fa-check"></i></button>
                               <button class="button-disapprove" data-id="${msg.idOferta}"><i class="fas fa-times"></i></button>`;

                        div.innerHTML = `
                            <div class="offer-content">
                                <span>Precio: $${msg.precio} MXN</span>
                                <p>${msg.mensaje}</p>
                                ${botones}
                            </div>
                            <span class="message-time">${msg.hora}</span>
                        `;
                    }

                    chatContainer.appendChild(div);
                });

                chatContainer.scrollTop = chatContainer.scrollHeight;
            })
            .catch(err => console.error("Error al cargar mensajes:", err));
    }

    function enviarMensaje() {
        const texto = inputMensaje.value.trim();
        if (!texto) return;

        const formData = new FormData();
        formData.append("idChat", idChat);
        formData.append("mensaje", texto);

        fetch("../../controllers/enviarMensajeTexto.php", {
            method: "POST",
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    inputMensaje.value = "";
                    cargarMensajes();
                } else {
                    alert(data.message);
                }
            })
            .catch(err => console.error("Error al enviar mensaje:", err));
    }

    sendBtn.addEventListener("click", enviarMensaje);

    inputMensaje.addEventListener("keypress", function (e) {
        if (e.key === "Enter") enviarMensaje();
    });

    // Oferta
    if (offerBtn) {
        offerBtn.addEventListener("click", () => {
            popup.style.display = "block";
            popupOverlay.style.display = "block";
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            popup.style.display = "none";
            popupOverlay.style.display = "none";
        });
    }

    if (ofertaForm) {
        ofertaForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const precio = parseFloat(document.getElementById("precio").value);
            const descripcion = document.getElementById("descripcion").value.trim();

            if (!precio || !descripcion) {
                alert("Debes completar todos los campos");
                return;
            }

            const formData = new FormData();
            formData.append("idChat", idChat);
            formData.append("precio", precio);
            formData.append("descripcion", descripcion);

            fetch("../../controllers/enviarOferta.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        ofertaForm.reset();
                        popup.style.display = "none";
                        popupOverlay.style.display = "none";
                        cargarMensajes();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(err => console.error("Error al enviar oferta:", err));
        });
    }

    // Inicial
    cargarMensajes();

    // Puedes usar setInterval si quieres recargar cada X tiempo:
    setInterval(cargarMensajes, 3000); // Cada 3s
});
