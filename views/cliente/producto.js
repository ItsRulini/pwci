document.addEventListener("DOMContentLoaded", function () {
    // Elementos del DOM a actualizar, usando selectores que coinciden con tu CSS/HTML
    const tituloPagina = document.querySelector('title');
    const productoSection = document.getElementById("seccionProductoDetalle"); // Contenedor principal del producto

    // Si productoSection no existe, no continuar (ej. si ID_PRODUCTO_ACTUAL no es válido y se borró el main)
    if (!productoSection) {
        if (typeof ID_PRODUCTO_ACTUAL === 'undefined' || ID_PRODUCTO_ACTUAL <= 0) {
            document.body.innerHTML = "<h1>Error: Producto no especificado o inválido.</h1>";
        }
        return;
    }
    
    const categoriasUl = document.getElementById("productoCategoriasUl"); // ul.categorias
    const multimediaDiv = document.getElementById("productoMultimediaDiv");   // div.multimedia
    
    // Elementos dentro de div.info
    const infoDiv = document.getElementById("productoInfoDiv"); // div.info
    const nombreProductoH2 = infoDiv ? infoDiv.querySelector("h2") : null;
    const precioProductoP = infoDiv ? infoDiv.querySelector("p:nth-of-type(1)") : null; // Primer <p> para precio/negociable
    const descripcionProductoP = infoDiv ? infoDiv.querySelector("p:nth-of-type(2)") : null; // Segundo <p> para descripción
    const btnAccionProducto = document.getElementById("btnAccionProducto"); // Botón de acción

    // Sección de calificación
    const calificacionSection = document.getElementById("productoCalificacionSection"); // section.calificacion
    const estrellasPromedioDisplay = document.getElementById("productoEstrellasPromedio"); // div.estrellas dentro de section.calificacion

    // Sección de comentarios
    const listaComentariosOl = document.getElementById("productoListaComentariosOl"); // ol dentro de section.comentarios

    // Popup de Wishlist
    const popupWishlist = document.getElementById("popup"); // ID original del popup
    const btnCerrarPopupWishlist = document.getElementById("btnCerrarPopup"); // ID original
    const btnAbrirPopupWishlist = document.getElementById("btnAbrirPopupWishlist"); // ID del ícono de tres puntos
    const popupWishlistListasUl = document.getElementById("popupWishlistListasUl");
    const popupBtnAgregarWishlist = document.getElementById("popupBtnAgregarWishlist");

    // Lógica del popup de wishlist
    if (btnAbrirPopupWishlist && popupWishlist && btnCerrarPopupWishlist) {
        btnAbrirPopupWishlist.addEventListener("click", function (event) {
            event.stopPropagation(); // Evita que el clic se propague al window listener inmediatamente
            popupWishlist.classList.add("mostrar");
            // TODO: Aquí deberías llamar a una función para cargar las wishlists del usuario en popupWishlistListasUl
            // Ejemplo: cargarWishlistsUsuario(); 
        });
    }
    if (popupWishlist && btnCerrarPopupWishlist) {
         btnCerrarPopupWishlist.addEventListener("click", () => {
            popupWishlist.classList.remove("mostrar");
         });
    }
    // Cerrar popup si se hace clic fuera de su contenido (en el overlay)
    if (popupWishlist) {
        popupWishlist.addEventListener("click", function (event) {
            // Si el clic fue directamente en el overlay (popupWishlist) y no en sus hijos (popup-content)
            if (event.target === popupWishlist) {
                popupWishlist.classList.remove("mostrar");
            }
        });
    }
    if (popupWishlist && btnCerrarPopupWishlist) {
         btnCerrarPopupWishlist.addEventListener("click", () => popupWishlist.classList.remove("mostrar"));
         window.addEventListener("click", function (e) {
             if (e.target === popupWishlist) popupWishlist.classList.remove("mostrar");
         });
    }
    // La funcionalidad de "Agregar" del popup de wishlist se manejaría con un listener en popupBtnAgregarWishlist


    if (typeof ID_PRODUCTO_ACTUAL === 'undefined' || ID_PRODUCTO_ACTUAL <= 0) {
        // El mensaje de error ya se maneja al inicio si productoSection no existe.
        // Si productoSection existe pero el ID es inválido, podrías poner un mensaje dentro de productoSection.
        if(productoSection) productoSection.innerHTML = "<h1>Producto no válido.</h1>";
        return;
    }

    // --- FUNCIONES GLOBALES PARA ACCIONES DE BOTONES ---
    window.agregarAlCarrito = function (idProducto) {
        const formData = new FormData();
        formData.append('idProducto', idProducto);
        fetch('../../controllers/agregarCarrito.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || (data.success ? "Artículo agregado al carrito." : "No se pudo agregar al carrito."));
        })
        .catch(error => {
            console.error('Error al agregar al carrito:', error);
            alert('Ocurrió un error al conectar con el servidor.');
        });
    };

    window.iniciarChat = function (idProducto) {
        const formData = new FormData();
        formData.append("idProducto", idProducto);
        fetch("../../controllers/iniciarChat.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.idChat) {
                window.location.href = `chat.php?idChat=${data.idChat}`;
            } else {
                alert("No se pudo iniciar el chat: " + (data.message || "Error desconocido."));
            }
        })
        .catch(err => {
            console.error("Error iniciando chat:", err);
            alert("Ocurrió un error al intentar abrir el chat.");
        });
    };
    
    // --- CARGA DE DATOS DEL PRODUCTO ---
    function cargarDetallesProducto() {
        fetch(`../../controllers/getDetallesProductoCliente.php?idProducto=${ID_PRODUCTO_ACTUAL}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.producto) {
                    const producto = data.producto;
                    
                    tituloPagina.textContent = producto.nombre || "Detalle de Producto";
                    if (nombreProductoH2) nombreProductoH2.textContent = producto.nombre;
                    if (descripcionProductoP) descripcionProductoP.textContent = producto.descripcion || "No hay descripción disponible.";

                    if (precioProductoP && btnAccionProducto) {
                        if (producto.tipo === 'Cotizacion') {
                            precioProductoP.textContent = "Negociable"; // No "Precio: Negociable" para que coincida con tu main.php
                            btnAccionProducto.textContent = "Enviar mensaje";
                            btnAccionProducto.onclick = function() { iniciarChat(producto.idProducto); };
                        } else { 
                            precioProductoP.textContent = `$${parseFloat(producto.precio).toFixed(2)} MXN`;
                            btnAccionProducto.textContent = "Añadir al carrito";
                            btnAccionProducto.onclick = function() { agregarAlCarrito(producto.idProducto); };
                        }
                        btnAccionProducto.classList.remove("btn-placeholder-accion"); // Quitar clase placeholder
                    }

                    if (categoriasUl && producto.nombreCategorias) {
                        categoriasUl.innerHTML = ''; 
                        const arrCategorias = producto.nombreCategorias.split(', ');
                        arrCategorias.forEach(catNombre => {
                            const li = document.createElement("li");
                            li.classList.add("categoria"); // Usa la clase .categoria de tu CSS
                            li.textContent = catNombre;
                            categoriasUl.appendChild(li);
                        });
                    } else if (categoriasUl) {
                        categoriasUl.innerHTML = '<li class="categoria">Sin categorías</li>';
                    }

                    if (multimediaDiv && producto.urlsMultimedia) {
                        multimediaDiv.innerHTML = ''; 
                        const arrMultimedia = producto.urlsMultimedia.split(';');
                        let hasMedia = false;
                        arrMultimedia.forEach(url => {
                            if (url && url.trim() !== "") { 
                                hasMedia = true;
                                const nombreArchivo = url.substring(url.lastIndexOf('/') + 1);
                                const rutaCompleta = `../../multimedia/productos/${producto.idProducto}/${nombreArchivo}`;
                                
                                if (nombreArchivo.toLowerCase().endsWith('.mp4') || nombreArchivo.toLowerCase().endsWith('.webm') || nombreArchivo.toLowerCase().endsWith('.ogg')) {
                                    const video = document.createElement("video");
                                    video.controls = true;
                                    video.innerHTML = `<source src="${rutaCompleta}" type="video/mp4">Tu navegador no soporta el video.`;
                                    multimediaDiv.appendChild(video);
                                } else {
                                    const img = document.createElement("img");
                                    img.src = rutaCompleta;
                                    img.alt = producto.nombre || "Imagen del producto";
                                    multimediaDiv.appendChild(img);
                                }
                            }
                        });
                        if (!hasMedia && multimediaDiv) { // Si después de iterar no hubo media válida
                           multimediaDiv.innerHTML = '<img src="../../multimedia/default/default.jpg" alt="Imagen no disponible">';
                        }
                    } else if (multimediaDiv) {
                        multimediaDiv.innerHTML = '<img src="../../multimedia/default/default.jpg" alt="Imagen no disponible">';
                    }

                    // Dentro de cargarDetallesProducto, en la sección de Calificación Promedio:
                    if (estrellasPromedioDisplay) { // estrellasPromedioDisplay es tu div con id="productoEstrellasPromedio"
                        if (producto.calificacionPromedio) {
                            const promedio = parseFloat(producto.calificacionPromedio);
                            estrellasPromedioDisplay.innerHTML = ''; // Limpiar
                            for (let i = 1; i <= 5; i++) {
                                const estrellaIcon = document.createElement("i");
                                estrellaIcon.classList.add("fas", "fa-star"); // Clase base de FontAwesome para estrella llena
                                if (i <= promedio) {
                                    estrellaIcon.classList.add("active"); // Estrella llena
                                } else if (i - 0.5 <= promedio) {
                                    // Para media estrella, FontAwesome usa clases diferentes
                                    // estrellaIcon.classList.remove("fa-star"); // Quitar la llena
                                    // estrellaIcon.classList.add("fa-star-half-alt"); // Poner media estrella (sólida)
                                    // estrellaIcon.classList.add("active"); // También necesita active para el color
                                    // Simplificado: si es >= .5 pero < 1, la marcamos como llena por simplicidad visual
                                    // o puedes usar fa-star-half-alt si tienes el ícono correcto y ajustas CSS.
                                    // Por ahora, la lógica que tenías redondea hacia abajo para estrellas completas.
                                    // Si quieres media estrella visual:
                                    estrellaIcon.classList.remove("fa-star"); // Quitar la llena por si acaso
                                    estrellaIcon.classList.add("fas", "fa-star-half-alt"); // O `far fa-star-half-alt` dependiendo de tu set de FA
                                    estrellaIcon.classList.add("active"); // Para el color
                                } else {
                                    // No necesita clase .active, se quedará con el color base de #productoEstrellasPromedio
                                    // Opcionalmente, para asegurar que sea la estrella vacía si usas FA Pro:
                                    // estrellaIcon.classList.remove("fas");
                                    // estrellaIcon.classList.add("far", "fa-star"); // 'far' para estrella regular (vacía)
                                }
                                estrellasPromedioDisplay.appendChild(estrellaIcon);
                            }
                            estrellasPromedioDisplay.innerHTML += ` (${promedio.toFixed(1)})`;
                        } else {
                            estrellasPromedioDisplay.textContent = "Sin calificaciones aún.";
                        }
                    }

                } else {
                    if(productoSection) productoSection.innerHTML = `<h1>Error: ${data.message || 'Producto no disponible.'}</h1>`;
                }
            })
            .catch(error => {
                console.error('Error al cargar detalles del producto:', error);
                 if(productoSection) productoSection.innerHTML = "<h1>Error de conexión al cargar el producto.</h1>";
            });
    }

    function cargarComentarios() {
        if (!listaComentariosOl) return;
        listaComentariosOl.innerHTML = '<li class="comentario">Cargando comentarios...</li>'; // Usa la clase .comentario

        fetch(`../../controllers/getComentariosProducto.php?idProducto=${ID_PRODUCTO_ACTUAL}`)
            .then(response => response.json())
            .then(data => {
                listaComentariosOl.innerHTML = ''; 
                if (data.success && data.comentarios.length > 0) {
                    data.comentarios.forEach(com => {
                        const li = document.createElement("li");
                        li.classList.add("comentario"); // Usa la clase .comentario de tu CSS
                        const fotoAvatar = com.fotoUsuarioComenta 
                            ? `../../multimedia/imagenPerfil/${com.fotoUsuarioComenta}` 
                            : '../../multimedia/default/default.jpg';
                        // Asumiendo que fechaComentario es un string de fecha válido
                        const fechaCom = com.fechaComentario ? new Date(com.fechaComentario).toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' }) : 'Fecha no disponible';

                        li.innerHTML = `
                            <img src="${fotoAvatar}" alt="${com.nombreUsuarioComenta || 'Usuario'}">
                            <div class="info">
                                <h3>${com.nombreUsuarioComenta || 'Anónimo'}</h3>
                                <p>${escapeHtml(com.comentario)}</p>
                            </div>
                        `;
                        listaComentariosOl.appendChild(li);
                    });
                } else if (data.success && data.comentarios.length === 0) {
                    listaComentariosOl.innerHTML = '<li class="comentario">No hay comentarios para este producto aún.</li>';
                } else {
                    listaComentariosOl.innerHTML = `<li class="comentario">Error: ${data.message || 'No se pudieron cargar los comentarios.'}</li>`;
                }
            })
            .catch(error => {
                console.error('Error al cargar comentarios:', error);
                listaComentariosOl.innerHTML = '<li class="comentario">Error de conexión al cargar comentarios.</li>';
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

    // --- EJECUCIÓN INICIAL ---
    if (ID_PRODUCTO_ACTUAL > 0) {
        cargarDetallesProducto();
        cargarComentarios();
    }
    
    // Lógica para la sección de calificación interactiva (si el usuario puede calificar desde esta página)
    // Esta sección de tu JS original para las estrellas interactivas en "producto.php"
    // se refería a #estrellas, que ahora es #productoEstrellasPromedio y es solo para display.
    // Si quieres que el usuario CALIFIQUE desde aquí, necesitarías un NUEVO set de estrellas interactivas.
    // Por ahora, la sección .calificacion solo muestra el promedio.
    /*
    const estrellasCalificar = document.querySelectorAll("#seccionProductoDetalle .calificacion .estrellas i"); // Ejemplo si tuvieras estrellas para input
    let calificacionUsuarioActual = 0; // Para la calificación que el usuario está por dar

    if (estrellasCalificar.length > 0) {
        estrellasCalificar.forEach((estrella, index) => {
            estrella.addEventListener("mouseover", () => {
                // Lógica para iluminar estrellas al pasar el mouse
            });
            estrella.addEventListener("mouseout", () => {
                // Lógica para restaurar estrellas
            });
            estrella.addEventListener("click", () => {
                // Guardar calificacionUsuarioActual
                // Potencialmente enviar al backend
            });
        });
    }
    */
});
