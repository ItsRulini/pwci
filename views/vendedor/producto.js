document.addEventListener('DOMContentLoaded', function() {
    // ID_PRODUCTO_VENDEDOR se define en el PHP
    if (typeof ID_PRODUCTO_VENDEDOR === 'undefined' || ID_PRODUCTO_VENDEDOR <= 0) {
        alert('Error: ID de producto no especificado.');
        window.location.href = 'main.php'; // Redirigir si no hay ID
        return;
    }

    const productoNombreH2 = document.getElementById('productoNombre');
    const productoPrecioP = document.getElementById('productoPrecio');
    const productoDescripcionP = document.getElementById('productoDescripcion');
    const productoTipoP = document.getElementById('productoTipo'); // Para mostrar el tipo
    const productoCategoriasUl = document.getElementById('productoCategorias');
    const productoMultimediaDiv = document.getElementById('productoMultimedia');
    const productoEstadoInfoP = document.querySelector('#productoEstadoInfo p'); // Asumiendo que hay un <p> dentro

    // Elementos para la gestión de stock
    const stockManagementSection = document.getElementById('stockManagementSection');
    const productoStockActualSpan = document.getElementById('productoStockActual');
    const cantidadAAgregarStockInput = document.getElementById('cantidadAAgregarStock');
    const btnActualizarStock = document.getElementById('btnActualizarStock');
    const stockUpdateMessageP = document.getElementById('stockUpdateMessage');

    let currentProductData = null; // Para guardar los datos del producto actual

    function cargarDetallesDelProducto() {
        fetch(`../../controllers/getProductoPorId.php?idProducto=${ID_PRODUCTO_VENDEDOR}`)
            .then(response => response.json())
            .then(data => {
                if (data.success === false || !data.idProducto) { // El SP ahora devuelve el objeto producto directamente o un mensaje de error
                    alert(data.message || 'Producto no encontrado o no accesible.');
                    window.location.href = 'main.php';
                    return;
                }
                
                currentProductData = data; // Guardar los datos completos del producto

                if (productoNombreH2) productoNombreH2.textContent = currentProductData.nombre;
                document.title = currentProductData.nombre || "Detalle de Mi Producto";

                if (productoPrecioP) {
                    productoPrecioP.textContent = currentProductData.tipo === 'Venta' 
                        ? `$${parseFloat(currentProductData.precio).toFixed(2)} MXN` 
                        : 'Cotización (Precio a negociar)';
                }
                if (productoDescripcionP) productoDescripcionP.textContent = currentProductData.descripcion || 'Sin descripción disponible.';
                if (productoTipoP) productoTipoP.textContent = `Tipo: ${currentProductData.tipo}`;


                // Mostrar estado
                if (productoEstadoInfoP) {
                    let estadoTexto = `Estado: ${currentProductData.estatus.charAt(0).toUpperCase() + currentProductData.estatus.slice(1)}`;
                    if (currentProductData.estatus === 'aceptado' && currentProductData.nombreAdministrador) {
                        estadoTexto += ` (Aprobado por: ${currentProductData.nombreAdministrador})`;
                    } else if (currentProductData.estatus === 'rechazado' && currentProductData.nombreAdministrador) {
                        estadoTexto += ` (Rechazado por: ${currentProductData.nombreAdministrador})`;
                    }
                    productoEstadoInfoP.textContent = estadoTexto;
                }

                // Lógica para mostrar y manejar el stock
                if (currentProductData.tipo === 'Venta' && currentProductData.estatus === 'aceptado') {
                    if (stockManagementSection) stockManagementSection.style.display = 'block';
                    if (productoStockActualSpan) productoStockActualSpan.textContent = currentProductData.stock;
                } else {
                    if (stockManagementSection) stockManagementSection.style.display = 'none';
                }

                // Cargar multimedia y categorías después de obtener los detalles básicos
                cargarMultimedia(ID_PRODUCTO_VENDEDOR);
                cargarCategorias(ID_PRODUCTO_VENDEDOR);
            })
            .catch(error => {
                console.error("Error cargando detalles del producto:", error);
                alert("Error de conexión al cargar el producto.");
                window.location.href = 'main.php';
            });
    }

    function cargarMultimedia(idProducto) {
        if (!productoMultimediaDiv) return;
        fetch(`../../controllers/getMultimediaProducto.php?idProducto=${idProducto}`)
            .then(response => response.json())
            .then(files => {
                productoMultimediaDiv.innerHTML = '';
                if (files && files.length > 0) {
                    files.forEach(file => {
                        const nombreArchivo = file.substring(file.lastIndexOf('/') + 1); // Asumiendo que 'file' es la URL completa o solo el nombre
                        const rutaCompleta = `../../multimedia/productos/${idProducto}/${nombreArchivo}`;
                        if (nombreArchivo.toLowerCase().endsWith('.mp4')) {
                            productoMultimediaDiv.innerHTML += `<video controls><source src="${rutaCompleta}" type="video/mp4"></video>`;
                        } else {
                            productoMultimediaDiv.innerHTML += `<img src="${rutaCompleta}" alt="Imagen producto">`;
                        }
                    });
                } else {
                    productoMultimediaDiv.innerHTML = `<img src="../../multimedia/default/default.jpg" alt="Imagen no disponible">`;
                }
            });
    }

    function cargarCategorias(idProducto) {
        if (!productoCategoriasUl) return;
        fetch(`../../controllers/getCategoriasProducto.php?idProducto=${idProducto}`)
            .then(response => response.json())
            .then(categorias => {
                productoCategoriasUl.innerHTML = '';
                if (categorias && categorias.length > 0) {
                    categorias.forEach(categoria => {
                        productoCategoriasUl.innerHTML += `<li class="categoria">${escapeHtml(categoria)}</li>`;
                    });
                } else {
                    productoCategoriasUl.innerHTML = '<li class="categoria">Sin categorías asignadas</li>';
                }
            });
    }

    // Event listener para actualizar stock
    if (btnActualizarStock && cantidadAAgregarStockInput && stockUpdateMessageP) {
        btnActualizarStock.addEventListener('click', function() {
            const cantidadAAgregar = parseInt(cantidadAAgregarStockInput.value);

            if (isNaN(cantidadAAgregar) || cantidadAAgregar <= 0) {
                stockUpdateMessageP.textContent = 'Por favor, ingresa una cantidad válida mayor a cero.';
                stockUpdateMessageP.style.color = 'red';
                return;
            }

            const formData = new FormData();
            formData.append('idProducto', ID_PRODUCTO_VENDEDOR);
            formData.append('cantidadAAgregar', cantidadAAgregar);

            stockUpdateMessageP.textContent = 'Actualizando...';
            stockUpdateMessageP.style.color = 'inherit';

            fetch('../../controllers/aumentarStockProducto.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    stockUpdateMessageP.textContent = data.message + ` Nuevo stock: ${data.nuevoStock}`;
                    stockUpdateMessageP.style.color = 'green';
                    if (productoStockActualSpan) productoStockActualSpan.textContent = data.nuevoStock;
                    cantidadAAgregarStockInput.value = '1'; // Resetear input
                    // Actualizar currentProductData si es necesario para otras lógicas
                    if(currentProductData) currentProductData.stock = data.nuevoStock;
                } else {
                    stockUpdateMessageP.textContent = `Error: ${data.message || 'No se pudo actualizar el stock.'}`;
                    stockUpdateMessageP.style.color = 'red';
                }
            })
            .catch(error => {
                console.error('Error al actualizar stock:', error);
                stockUpdateMessageP.textContent = 'Error de conexión al actualizar el stock.';
                stockUpdateMessageP.style.color = 'red';
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

    // Carga inicial
    cargarDetallesDelProducto();
});
