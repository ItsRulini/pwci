document.addEventListener("DOMContentLoaded", function () {
    // ... (selectores y código existente de la Iteración 2.2) ...
    const ulListas = document.querySelector(".wishlists .listas");
    const popupEditarLista = document.getElementById("popupEditarLista");
    const btnCerrarEditarLista = document.getElementById("btnCerrarEditarLista");
    const formEditarWishlist = document.getElementById("formEditarWishlist");
    // Contenedor para los productos dentro del popup de edición
    const ulProductosEditar = document.getElementById("listaProductosEditar"); 

    // --- Funciones de Carga y Renderizado (cargarYRenderizarWishlists, cargarProductosDeWishlist) ---
    // Estas funciones ya las tienes de la iteración anterior. Asegúrate que estén aquí.
    // ... (pegar aquí las funciones cargarYRenderizarWishlists y cargarProductosDeWishlist de la respuesta anterior)

    /**
     * Carga y renderiza las wishlists del usuario.
     */
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
        if (popupEditarLista && formEditarWishlist && ulProductosEditar) {
            // Poblar detalles de la lista
            formEditarWishlist.querySelector('#editarNombreLista').value = nombre;
            formEditarWishlist.querySelector('#editarDescripcionLista').value = descripcion;
            const radiosPrivacidad = formEditarWishlist.querySelectorAll('input[name="editarListaPrivacidad"]');
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
    const formWishlist = document.getElementById("formWishlist");
    // const ulListas = document.querySelector(".wishlists .listas"); // Ya definido arriba

    // const popupEditarLista = document.getElementById("popupEditarLista"); // Ya definido arriba
    // const btnCerrarEditarLista = document.getElementById("btnCerrarEditarLista"); // Ya definido arriba
    // const formEditarWishlist = document.getElementById("formEditarWishlist"); // Ya definido arriba


    // Abrir/Cerrar Popup de Crear Wishlist
    if (btnAbrirPopupCrear && popupCrearWishlist) {
        btnAbrirPopupCrear.addEventListener("click", function() {
            popupCrearWishlist.style.display = "flex";
            if(formWishlist) {
                formWishlist.reset(); 
                const radiosPrivacidad = formWishlist.querySelectorAll('input[name="listaPrivacidad"]');
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
    if (formWishlist) {
        formWishlist.addEventListener("submit", function(event) {
            event.preventDefault();
            const privacidadSeleccionada = formWishlist.querySelector('input[name="listaPrivacidad"]:checked');
            if (!privacidadSeleccionada) {
                alert("Por favor, selecciona un tipo de privacidad para la wishlist (Pública o Privada).");
                return; 
            }
            const formData = new FormData(formWishlist);
            
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
    if (formEditarWishlist) {
        formEditarWishlist.addEventListener('submit', function(event) {
            event.preventDefault();
            const idListaActual = popupEditarLista.dataset.idlistaactual;
            if (!idListaActual) {
                alert("Error: No se pudo identificar la wishlist a editar.");
                return;
            }

            const privacidadSeleccionada = formEditarWishlist.querySelector('input[name="editarListaPrivacidad"]:checked');
            if (!privacidadSeleccionada) {
                alert("Por favor, selecciona un tipo de privacidad para la wishlist.");
                return;
            }

            const formData = new FormData(formEditarWishlist);
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
});
