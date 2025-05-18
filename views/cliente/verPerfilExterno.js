// verPerfilExterno.js - Puede ser común para cliente y vendedor

document.addEventListener("DOMContentLoaded", function () {
    const perfilContainer = document.getElementById("perfilExternoContainer");
    const paginaTitulo = document.querySelector("title");

    // ID_PERFIL_CONSULTADO se define en el PHP
    if (typeof ID_PERFIL_CONSULTADO === 'undefined' || ID_PERFIL_CONSULTADO <= 0) {
        if (perfilContainer) perfilContainer.innerHTML = '<p class="error-message">Error: ID de perfil no válido.</p>';
        return;
    }

    function cargarPerfilExterno() {
        if (!perfilContainer) return;
        perfilContainer.innerHTML = '<p class="loading-message">Cargando perfil...</p>';

        fetch(`../../controllers/getDetallesPerfilExterno.php?idUsuario=${ID_PERFIL_CONSULTADO}`)
            .then(response => response.json())
            .then(data => {
                perfilContainer.innerHTML = ''; // Limpiar
                if (data.success && data.perfil) {
                    const perfil = data.perfil;
                    const contenidoAdicional = data.contenidoAdicional;

                    paginaTitulo.textContent = `Perfil de ${escapeHtml(perfil.nombreUsuario)}`;

                    // Renderizar cabecera del perfil
                    const headerDiv = document.createElement("div");
                    headerDiv.classList.add("perfil-header");
                    const fotoAvatar = perfil.fotoAvatar 
                        ? `../../multimedia/imagenPerfil/${perfil.fotoAvatar}` 
                        : '../../multimedia/default/default.jpg';
                    
                    headerDiv.innerHTML = `
                        <img src="${fotoAvatar}" alt="Avatar de ${escapeHtml(perfil.nombreUsuario)}" class="avatar">
                        <div class="info-usuario">
                            <h1>${escapeHtml(perfil.nombreUsuario)}</h1>
                            <p>${escapeHtml(perfil.nombres)} ${escapeHtml(perfil.paterno)} ${escapeHtml(perfil.materno)}</p>
                            <p class="rol">Rol: ${escapeHtml(perfil.rol)}</p>
                        </div>
                    `;
                    perfilContainer.appendChild(headerDiv);

                    // Renderizar contenido adicional
                    const contenidoDiv = document.createElement("div");
                    contenidoDiv.classList.add("seccion-contenido-perfil");

                    if (perfil.rol === 'Comprador') {
                        if (perfil.privacidad === 'Publico') {
                            contenidoDiv.innerHTML += '<h2>Wishlists Públicas</h2>';
                            if (contenidoAdicional.wishlists && contenidoAdicional.wishlists.length > 0) {
                                const ulWishlists = document.createElement("ul");
                                ulWishlists.classList.add("wishlists-publicas-container"); // Para estilizar
                                contenidoAdicional.wishlists.forEach(wishlist => {
                                    const liW = document.createElement("li");
                                    liW.classList.add("lista");
                                    let productosHTML = '<p class="producto-placeholder">Esta wishlist está vacía.</p>';
                                    if(wishlist.productos && wishlist.productos.length > 0){
                                        productosHTML = wishlist.productos.map(p => `
                                            <li class="producto">
                                                <a href="producto.php?idProducto=${p.idProducto}" class="enlace-producto-wishlist">
                                                    <img src="${p.imagenPrincipal ? '../../multimedia/productos/'+p.idProducto+'/'+p.imagenPrincipal : '../../multimedia/default/default.jpg'}" alt="${escapeHtml(p.nombre)}">
                                                    <div class="info">
                                                        <span>${escapeHtml(p.nombre)}</span>
                                                        <p>${p.tipoProducto === 'Venta' ? ('$' + parseFloat(p.precio).toFixed(2) + ' MXN') : 'Negociable'}</p>
                                                    </div>
                                                </a>
                                            </li>
                                        `).join('');
                                    }

                                    liW.innerHTML = `
                                        <div class="headerLista"><span>${escapeHtml(wishlist.nombre)}</span></div>
                                        <p>${escapeHtml(wishlist.descripcion) || 'Sin descripción.'}</p>
                                        <ol class="contenidoLista">${productosHTML}</ol>
                                    `;
                                    ulWishlists.appendChild(liW);
                                });
                                contenidoDiv.appendChild(ulWishlists);
                            } else {
                                contenidoDiv.innerHTML += '<p>Este usuario no tiene wishlists públicas.</p>';
                            }
                        } else { // Comprador Privado
                            contenidoDiv.innerHTML = '<p class="mensaje-privado">Este perfil de comprador es privado.</p>';
                        }
                    } else if (perfil.rol === 'Vendedor') {
                        contenidoDiv.innerHTML += '<h2>Productos del Vendedor</h2>';
                        if (contenidoAdicional.productos && contenidoAdicional.productos.length > 0) {
                            const divProductos = document.createElement("div");
                            divProductos.classList.add("productos-vendedor-container"); // Para estilizar con grid
                            contenidoAdicional.productos.forEach(producto => {
                                const cardProducto = document.createElement("article");
                                // Usar la misma clase que en main.js del cliente para reutilizar estilos si es posible
                                cardProducto.classList.add("producto"); 
                                const imagenSrc = producto.imagenPrincipal 
                                    ? `../../multimedia/productos/${producto.idProducto}/${producto.imagenPrincipal}`
                                    : '../../multimedia/default/default.jpg';
                                
                                let botonAccion = '';
                                if (producto.tipo === 'Venta') {
                                    botonAccion = `<button onclick="window.agregarAlCarrito(${producto.idProducto})">Añadir al carrito</button>`;
                                } else { // Cotizacion
                                    botonAccion = `<button onclick="window.iniciarChat(${producto.idProducto})">Enviar mensaje</button>`;
                                }

                                cardProducto.innerHTML = `
                                    <img src="${imagenSrc}" alt="${escapeHtml(producto.nombre)}">
                                    <div class="info">
                                        <a href="producto.php?idProducto=${producto.idProducto}">${escapeHtml(producto.nombre)}</a>
                                        <p>${producto.tipo === 'Venta' ? ('$' + parseFloat(producto.precio).toFixed(2) + ' MXN') : 'Negociable'}</p>
                                        ${botonAccion}
                                    </div>
                                `;
                                divProductos.appendChild(cardProducto);
                            });
                            contenidoDiv.appendChild(divProductos);
                        } else {
                            contenidoDiv.innerHTML += '<p>Este vendedor aún no tiene productos publicados.</p>';
                        }
                    }
                    perfilContainer.appendChild(contenidoDiv);

                } else {
                    perfilContainer.innerHTML = `<p class="error-message">Error: ${data.message || 'No se pudo cargar el perfil.'}</p>`;
                }
            })
            .catch(error => {
                console.error('Error al cargar perfil externo:', error);
                if (perfilContainer) perfilContainer.innerHTML = '<p class="error-message">Error de conexión al cargar el perfil.</p>';
            });
    }
    
    // Funciones globales para botones de acción (si no están ya en un script global)
    if (typeof window.agregarAlCarrito === 'undefined') {
        window.agregarAlCarrito = function (idProducto) {
            const formData = new FormData();
            formData.append('idProducto', idProducto);
            fetch('../../controllers/agregarCarrito.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => { alert(data.message || (data.success ? "Artículo agregado." : "No se pudo agregar.")); })
            .catch(error => { console.error('Error:', error); alert('Error de conexión.'); });
        };
    }
    if (typeof window.iniciarChat === 'undefined') {
        window.iniciarChat = function (idProducto) {
            const formData = new FormData();
            formData.append("idProducto", idProducto);
            fetch("../../controllers/iniciarChat.php", { method: "POST", body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.idChat) window.location.href = `chat.php?idChat=${data.idChat}`;
                else alert("No se pudo iniciar el chat: " + (data.message || "Error"));
            })
            .catch(err => { console.error("Error:", err); alert("Error de conexión."); });
        };
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

    // Carga inicial del perfil
    cargarPerfilExterno();
});
