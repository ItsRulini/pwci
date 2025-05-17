// views/cliente/compra.js

document.addEventListener("DOMContentLoaded", function () {
    const tbodyCompras = document.getElementById("compras-list");
    const filtrosForm = document.getElementById("filtrosForm");
    const selectCategoriaFiltro = document.getElementById("categoria"); // El select de categorías en los filtros
    
    // Elementos para la sección de calificar (se usarán más adelante)
    const selectCompraParaCalificar = document.getElementById("compra"); // El select de "Número de compra"
    const tbodyProductosParaCalificar = document.querySelector(".calificar .compras-table tbody"); // tbody de la tabla "Califica los productos"
    const botonGuardarCalificaciones = document.getElementById("calificar-btn");

    /**
     * Carga y muestra el historial de compras del usuario.
     * @param {object} filtros - Objeto con idCategoria, fechaDesde, fechaHasta.
     */
    function cargarHistorialCompras(filtros = {}) {
        let queryParams = new URLSearchParams(filtros).toString();
        if (tbodyCompras) tbodyCompras.innerHTML = '<tr><td colspan="5" style="text-align:center;">Cargando historial...</td></tr>';

        fetch(`../../controllers/getHistorialCompras.php?${queryParams}`)
            .then(response => response.json())
            .then(data => {
                if (tbodyCompras) tbodyCompras.innerHTML = ''; // Limpiar antes de agregar nuevas filas

                if (data.success && data.historial.length > 0) {
                    data.historial.forEach(item => {
                        const tr = document.createElement("tr");
                        const calificacionDisplay = item.calificacionPromedioProducto ? parseFloat(item.calificacionPromedioProducto).toFixed(1) : 'N/A';
                        tr.innerHTML = `
                            <td>${item.idTransaccion}</td>
                            <td>${item.categoriasProducto || 'N/A'}</td>
                            <td>${item.nombreProducto}</td>
                            <td>$${parseFloat(item.precioPagado).toFixed(2)} MXN</td>
                            <td>${calificacionDisplay}</td>
                        `;
                        if (tbodyCompras) tbodyCompras.appendChild(tr);
                    });
                } else if (data.success && data.historial.length === 0) {
                    if (tbodyCompras) tbodyCompras.innerHTML = '<tr><td colspan="5" style="text-align:center;">No tienes compras registradas con estos filtros.</td></tr>';
                } else {
                    if (tbodyCompras) tbodyCompras.innerHTML = `<tr><td colspan="5" style="text-align:center;">Error: ${data.message || 'No se pudo cargar el historial.'}</td></tr>`;
                }
            })
            .catch(error => {
                console.error('Error al cargar historial de compras:', error);
                if (tbodyCompras) tbodyCompras.innerHTML = '<tr><td colspan="5" style="text-align:center;">Error de conexión al cargar el historial.</td></tr>';
            });
    }

    // Manejar envío del formulario de filtros
    if (filtrosForm) {
        filtrosForm.addEventListener("submit", function(event) {
            event.preventDefault();
            const formData = new FormData(filtrosForm);
            const filtros = {
                idCategoria: formData.get("categoria"), // El SP espera 0 o NULL para "todas"
                fechaDesde: formData.get("desde"),
                fechaHasta: formData.get("hasta")
            };
            cargarHistorialCompras(filtros);
            // También deberíamos recargar el dropdown de transacciones para calificar
            cargarTransaccionesParaCalificarDropdown(filtros); 
        });
    }

    function cargarCategoriasFiltro() {
        if (!selectCategoriaFiltro) return;
        selectCategoriaFiltro.innerHTML = '<option value="0">Cargando categorías...</option>';

        // Usaremos el controlador getCategorias.php que ya existe y devuelve un formato adecuado.
        fetch(`../../controllers/getCategorias.php`) 
            .then(response => response.json())
            .then(data => {
                selectCategoriaFiltro.innerHTML = '<option value="0">Todas</option>'; // Opción por defecto
                if (data.success && data.data.length > 0) {
                    data.data.forEach(categoria => {
                        const option = document.createElement("option");
                        option.value = categoria.idCategoria; // El SP espera el ID
                        option.textContent = categoria.nombre;
                        selectCategoriaFiltro.appendChild(option);
                    });
                } else {
                    // No hacer nada o mostrar un mensaje si no hay categorías
                    console.warn("No se encontraron categorías para el filtro o hubo un error:", data.message);
                }
            })
            .catch(error => {
                console.error('Error al cargar categorías para el filtro:', error);
                if (selectCategoriaFiltro) selectCategoriaFiltro.innerHTML = '<option value="0">Error al cargar</option>';
            });
    }
    // --- LÓGICA PARA LA SECCIÓN DE CALIFICAR (se desarrollará en el siguiente paso) ---
    
    /**
     * Carga las transacciones en el dropdown para seleccionar cuál calificar.
     * Los filtros pueden afectar qué transacciones se muestran.
     */
    function cargarTransaccionesParaCalificarDropdown(filtros = {}) {
        let queryParams = new URLSearchParams();
        // Solo añadir filtros si tienen valor, para no enviar "idCategoria=&fechaDesde=&fechaHasta="
        if (filtros.idCategoria && filtros.idCategoria !== "0") queryParams.append("idCategoria", filtros.idCategoria);
        if (filtros.fechaDesde) queryParams.append("fechaDesde", filtros.fechaDesde);
        if (filtros.fechaHasta) queryParams.append("fechaHasta", filtros.fechaHasta);


        if (!selectCompraParaCalificar) return;
        
        selectCompraParaCalificar.innerHTML = '<option value="">Cargando compras...</option>';

        fetch(`../../controllers/getTransaccionesParaCalificar.php?${queryParams.toString()}`)
            .then(response => response.json())
            .then(data => {
                selectCompraParaCalificar.innerHTML = '<option value="">Selecciona una compra</option>'; 
                if (data.success && data.transacciones.length > 0) {
                    data.transacciones.forEach(transaccion => {
                        const option = document.createElement("option");
                        option.value = transaccion.idTransaccion;
                        option.textContent = `Compra #${transaccion.idTransaccion} (${new Date(transaccion.fechaTransaccion).toLocaleDateString()})`;
                        selectCompraParaCalificar.appendChild(option);
                    });
                } else if (data.success && data.transacciones.length === 0) {
                     selectCompraParaCalificar.innerHTML = '<option value="">No hay compras para calificar</option>';
                } else {
                    selectCompraParaCalificar.innerHTML = `<option value="">Error: ${data.message || 'No se pudo cargar'}</option>`;
                }
            })
            .catch(error => {
                console.error('Error al cargar transacciones para calificar:', error);
                selectCompraParaCalificar.innerHTML = '<option value="">Error de conexión</option>';
            });
    }

    /**
     * Carga los productos de una compra seleccionada en la tabla para calificar.
     */
    function cargarProductosParaCalificar(idTransaccion) {
        if (!idTransaccion) {
            if (tbodyProductosParaCalificar) tbodyProductosParaCalificar.innerHTML = '';
            return;
        }
        if (tbodyProductosParaCalificar) tbodyProductosParaCalificar.innerHTML = '<tr><td colspan="5" style="text-align:center;">Cargando productos de la compra...</td></tr>';

        fetch(`../../controllers/getProductosDeCompra.php?idTransaccion=${idTransaccion}`)
            .then(response => response.json())
            .then(data => {
                if (tbodyProductosParaCalificar) tbodyProductosParaCalificar.innerHTML = ''; 
                if (data.success && data.productos.length > 0) {
                    data.productos.forEach(producto => {
                        const tr = document.createElement("tr");
                        tr.dataset.idproducto = producto.idProducto; 
                        tr.innerHTML = `
                            <td>${producto.categoriasProducto || 'N/A'}</td>
                            <td>${producto.nombreProducto}</td>
                            <td>$${parseFloat(producto.precioPagado).toFixed(2)} MXN</td>
                            <td>
                                <div class="estrellas" data-calificacion="${producto.calificacionActual || 0}">
                                    ${[1,2,3,4,5].map(i => `<i class="fas fa-star" data-index="${i}"></i>`).join('')}
                                </div>
                            </td>
                            <td><input type="text" class="comentario-input" name="comentario_${producto.idProducto}" placeholder="Escribe tu comentario" value="${producto.comentarioActual || ''}"></td>
                        `;
                        tbodyProductosParaCalificar.appendChild(tr);
                        inicializarEstrellasEnFila(tr);
                    });
                } else if (data.success && data.productos.length === 0) {
                     if (tbodyProductosParaCalificar) tbodyProductosParaCalificar.innerHTML = '<tr><td colspan="5" style="text-align:center;">No hay productos en esta compra.</td></tr>';
                }
                else {
                    if (tbodyProductosParaCalificar) tbodyProductosParaCalificar.innerHTML = `<tr><td colspan="5" style="text-align:center;">Error: ${data.message || 'No se pudieron cargar los productos.'}</td></tr>`;
                }
            })
            .catch(error => {
                console.error('Error al cargar productos para calificar:', error);
                 if (tbodyProductosParaCalificar) tbodyProductosParaCalificar.innerHTML = '<tr><td colspan="5" style="text-align:center;">Error de conexión.</td></tr>';
            });
    }
    
    /**
     * Inicializa la funcionalidad de las estrellas para una fila específica.
     */
    function inicializarEstrellasEnFila(filaElement) {
        const estrellasContainer = filaElement.querySelector(".estrellas");
        if (!estrellasContainer) return;

        const estrellas = estrellasContainer.querySelectorAll("i");
        let calificacionActual = parseInt(estrellasContainer.dataset.calificacion) || 0;
        
        // Pintar estrellas iniciales
        actualizarEstrellasVisual(estrellas, calificacionActual);

        estrellas.forEach((estrella, index) => {
            estrella.addEventListener("mouseover", () => {
                actualizarEstrellasVisual(estrellas, index + 1);
            });
            estrella.addEventListener("mouseout", () => {
                actualizarEstrellasVisual(estrellas, calificacionActual);
            });
            estrella.addEventListener("click", () => {
                calificacionActual = index + 1;
                estrellasContainer.dataset.calificacion = calificacionActual; // Guardar en el div
                actualizarEstrellasVisual(estrellas, calificacionActual);
            });
        });
    }

    /**
     * Actualiza la apariencia visual de un conjunto de estrellas.
     */
    function actualizarEstrellasVisual(estrellasNodeList, n) {
        estrellasNodeList.forEach((estrella, i) => {
            estrella.classList.toggle("active", i < n);
        });
    }


    /**
     * Guarda las calificaciones y comentarios.
     */
    function guardarCalificaciones() {
        if (!selectCompraParaCalificar || !selectCompraParaCalificar.value) {
            alert("Por favor, selecciona una compra para calificar.");
            return;
        }
        const idTransaccionSeleccionada = selectCompraParaCalificar.value;
        const filasProductos = tbodyProductosParaCalificar.querySelectorAll("tr[data-idproducto]");
        
        if (filasProductos.length === 0) {
            alert("No hay productos para calificar en esta compra.");
            return;
        }

        const calificacionesAGuardar = [];
        filasProductos.forEach(fila => {
            const idProducto = fila.dataset.idproducto;
            const estrellasDiv = fila.querySelector(".estrellas");
            const calificacion = estrellasDiv ? parseInt(estrellasDiv.dataset.calificacion) : 0;
            const comentarioInput = fila.querySelector(".comentario-input");
            const comentario = comentarioInput ? comentarioInput.value.trim() : "";

            // Solo enviar si hay una calificación (o si quieres permitir solo comentario)
            if (calificacion > 0 || (comentario !== "" && calificacion === 0) ) { // Permitir comentario sin calificación si se desea
                calificacionesAGuardar.push({
                    idProducto: parseInt(idProducto),
                    calificacion: calificacion, // Enviar 0 si no se calificó pero hay comentario
                    comentario: comentario,
                    idTransaccion: parseInt(idTransaccionSeleccionada) // Opcional, para referencia
                });
            }
        });

        if (calificacionesAGuardar.length === 0) {
            alert("No has realizado ninguna calificación o comentario.");
            return;
        }

        fetch(`../../controllers/guardarCalificaciones.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({calificaciones: calificacionesAGuardar})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || "Calificaciones guardadas correctamente.");
                // Opcional: Recargar historial o productos de la compra para ver calificación actualizada
                cargarHistorialCompras(); 
                cargarProductosParaCalificar(idTransaccionSeleccionada); // Recargar para ver si se actualiza la visualización
            } else {
                alert("Error al guardar calificaciones: " + (data.message || "Error desconocido."));
            }
        })
        .catch(error => {
            console.error('Error al guardar calificaciones:', error);
            alert("Error de conexión al guardar calificaciones.");
        });
    }

    // Event listener para el cambio en el select de compra
    if (selectCompraParaCalificar) {
        selectCompraParaCalificar.addEventListener("change", function() {
            cargarProductosParaCalificar(this.value);
        });
    }
    
    // Event listener para el botón de guardar calificaciones
    if (botonGuardarCalificaciones) {
        botonGuardarCalificaciones.addEventListener("click", guardarCalificaciones);
    }

    // Cargas iniciales
    cargarCategoriasFiltro(); // Cargar categorías para el filtro
    cargarHistorialCompras(); // Cargar historial sin filtros
    cargarTransaccionesParaCalificarDropdown();
});
