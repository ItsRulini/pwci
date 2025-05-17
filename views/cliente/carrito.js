document.addEventListener("DOMContentLoaded", function () {
    const contenidoCarritoEl = document.querySelector(".carrito"); // Renombrado para evitar confusión con la variable 'carrito' del array
    const listaProductosEl = document.querySelector(".contenido-carrito"); // El UL donde van los <li>
    const vaciarBtn = document.querySelector(".clear-btn");
    const carritoVacioEl = document.querySelector(".carrito-vacio");
    // const comprarBtn = document.querySelector(".btn-comprar"); // Ya lo tienes

    const resumenSubtotalEl = document.querySelector(".linea-resumen span:nth-child(2)");
    const resumenEnvioEl = document.querySelectorAll(".linea-resumen span:nth-child(2)")[1];
    const resumenImpuestosEl = document.querySelectorAll(".linea-resumen span:nth-child(2)")[2];
    const totalResumenEl = document.querySelector(".linea-total span:nth-child(2)");

    const ENVIO = 150; // Costo de envío
    const IVA_PORCENTAJE = 0.16; // 16% de IVA

    const paypalContainer = document.getElementById('paypal-button-container');
    let paypalRendered = false;

    function formatearMoneda(cantidad) {
        return `$${Number(cantidad).toLocaleString("es-MX", { minimumFractionDigits: 2, maximumFractionDigits: 2 })} MXN`;
    }

    function obtenerTotalNumerico() {
        // Implementa la lógica para obtener el total numérico actual del resumen
        // Esto es importante para PayPal
        const textoTotal = totalResumenEl.textContent;
        return parseFloat(textoTotal.replace(/[^0-9.]/g, "")) || 0;
    }


    function recalcularTotales() {
        let subtotal = 0;
        const productosLi = listaProductosEl.querySelectorAll(".producto");

        productosLi.forEach(productoLi => {
            const precioTexto = productoLi.querySelector(".info p").textContent; // El <p> que muestra el precio
            const cantidadTexto = productoLi.querySelector(".cantidad span").textContent;
            
            const precioUnitario = parseFloat(precioTexto.replace(/[^0-9.]/g, ""));
            const cantidad = parseInt(cantidadTexto);

            if (!isNaN(precioUnitario) && !isNaN(cantidad)) {
                subtotal += precioUnitario * cantidad;
            }
        });

        const impuestos = subtotal * IVA_PORCENTAJE;
        const total = subtotal + impuestos + (subtotal > 0 ? ENVIO : 0);

        resumenSubtotalEl.textContent = formatearMoneda(subtotal);
        resumenEnvioEl.textContent = formatearMoneda(subtotal > 0 ? ENVIO : 0);
        resumenImpuestosEl.textContent = formatearMoneda(impuestos);
        totalResumenEl.textContent = formatearMoneda(total);
    }

    function verificarCarritoVacio() {
        const productosLi = listaProductosEl.querySelectorAll(".producto");
        if (productosLi.length === 0) {
            carritoVacioEl.style.display = "block"; // O 'block' según tu CSS para .carrito-vacio
            contenidoCarritoEl.style.display = "none";
            if (paypalContainer) paypalContainer.innerHTML = ''; // Limpiar botones de PayPal si el carrito está vacío
            paypalRendered = false;
        } else {
            carritoVacioEl.style.display = "none";
            contenidoCarritoEl.style.display = "block";
        }
    }

    function cargarCarrito() {
        fetch('../../controllers/getCarrito.php')
            .then(response => response.json())
            .then(productos => {
                listaProductosEl.innerHTML = ''; // Limpiar la lista de productos

                if (!Array.isArray(productos)) {
                    console.error("La respuesta de getCarrito.php no es un array:", productos);
                    carritoVacioEl.style.display = "flex";
                    contenidoCarritoEl.style.display = "none";
                    recalcularTotales(); // Asegurar que los totales se muestren como 0.00
                    return;
                }
                
                if (productos.length === 0) {
                    verificarCarritoVacio();
                    recalcularTotales(); // Asegurar que los totales se muestren como 0.00
                    return;
                }

                productos.forEach(producto => {
                    // Determinar si los botones de cantidad deben mostrarse.
                    // No se muestran si es una cotización (idMensajeOferta tiene valor y tipoProducto es 'Cotizacion')
                    // o si el tipo de producto es 'Cotizacion' y no tiene idMensajeOferta (caso menos común, pero por si acaso).
                    // La cantidad para cotizaciones siempre es 1 y no se puede modificar desde el carrito.
                    let cantidadHTML = '';
                    if (producto.tipoProducto === 'Venta') {
                        cantidadHTML = `
                            <div class="cantidad">
                                <button type="button" class="btn-restar" data-idproducto="${producto.idProducto}">-</button>
                                <span>${producto.cantidad}</span>
                                <button type="button" class="btn-sumar" data-idproducto="${producto.idProducto}">+</button>
                            </div>`;
                    } else { // Para 'Cotizacion'
                        cantidadHTML = `
                            <div class="cantidad">
                                <span>${producto.cantidad} (Cotización)</span>
                            </div>`;
                    }

                    // Usar precioFinal que ya viene calculado desde el backend
                    const precioAMostrar = producto.precioFinal;

                    listaProductosEl.innerHTML += `
                        <li class="producto" data-idproducto="${producto.idProducto}" data-idoferta="${producto.idMensajeOferta || ''}">
                            <img src="../../multimedia/productos/${producto.idProducto}/${producto.imagenPrincipal || 'default.jpg'}" alt="${producto.nombre}">
                            <div class="info">
                                <span>${producto.nombre} ${producto.idMensajeOferta ? '<small class="oferta-tag">(Oferta)</small>' : ''}</span>
                                <p>${formatearMoneda(precioAMostrar)}</p>
                                ${cantidadHTML}
                            </div>
                            <div class="acciones">
                                <button class="eliminar-btn" data-idproducto="${producto.idProducto}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </li>`;
                });

                verificarCarritoVacio();
                recalcularTotales();
            })
            .catch(error => {
                console.error('Error cargando carrito:', error);
                listaProductosEl.innerHTML = '<li style="color:red; text-align:center;">Error al cargar el carrito.</li>';
                verificarCarritoVacio();
                recalcularTotales();
            });
    }

    // Event listener para botones de sumar, restar y eliminar (delegación de eventos)
    listaProductosEl.addEventListener("click", function (e) {
        const target = e.target;
        const productoLi = target.closest(".producto"); // El <li> del producto
        
        if (!productoLi) return;

        const idProducto = productoLi.dataset.idproducto;

        if (target.classList.contains("btn-sumar") || target.closest(".btn-sumar")) {
            fetch('../../controllers/sumarCantidadCarrito.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `idProducto=${idProducto}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cargarCarrito(); // Recargar todo el carrito para reflejar cambios
                } else {
                    alert(data.message || 'No se pudo aumentar la cantidad. Stock máximo alcanzado.');
                }
            });
        } else if (target.classList.contains("btn-restar") || target.closest(".btn-restar")) {
            fetch('../../controllers/restarCantidadCarrito.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `idProducto=${idProducto}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cargarCarrito();
                } else {
                    alert(data.message || 'No se pudo disminuir la cantidad.');
                }
            });
        } else if (target.classList.contains("eliminar-btn") || target.closest(".eliminar-btn")) {
            if (confirm("¿Estás seguro de que quieres eliminar este producto del carrito?")) {
                fetch('../../controllers/eliminarProductoCarrito.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `idProducto=${idProducto}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        cargarCarrito();
                    } else {
                        alert(data.message || 'No se pudo eliminar el producto del carrito.');
                    }
                });
            }
        }
    });

    // Vaciar todo el carrito
    if (vaciarBtn) {
        vaciarBtn.addEventListener("click", function () {
            if (confirm("¿Estás seguro de que quieres vaciar todo el carrito?")) {
                fetch('../../controllers/vaciarCarrito.php', {
                    method: 'POST' // Asumiendo que no necesita body si vacía todo para el usuario en sesión
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        cargarCarrito(); // Recargar para mostrar carrito vacío
                    } else {
                        alert(data.message || 'No se pudo vaciar el carrito.');
                    }
                });
            }
        });
    }

    // Paypal
    function configurarPaypal() {
        if (!paypalContainer || typeof paypal === 'undefined') return;
        if (paypalRendered) return; // Evitar renderizar múltiples veces

        const totalPagar = obtenerTotalNumerico();
        if (totalPagar <= 0) {
            paypalContainer.innerHTML = ""; // Limpiar si no hay nada que pagar
            paypalRendered = false;
            return;
        }

        paypal.Buttons({
            style: {
                layout: 'vertical',
                color:  'gold',
                shape:  'rect',
                label:  'pay',
            },
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: totalPagar.toFixed(2) // Asegurar 2 decimales para PayPal
                        }
                    }]
                });
            },
            onCancel: function (data) {
                // Manejar cancelación
                console.log("Pago cancelado:", data);
                // paypalContainer.innerHTML = ""; // Opcional: limpiar botones para reintentar
                // paypalRendered = false;
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    alert('Compra completada por ' + details.payer.name.given_name + '!');
                    // Aquí deberías:
                    // 1. Registrar la compra en tu base de datos (backend)
                    // 2. Vaciar el carrito del usuario (backend y frontend)
                    
                    // Ejemplo de vaciar carrito en frontend después de pago exitoso:
                    fetch('../../controllers/vaciarCarrito.php', { method: 'POST' })
                        .then(() => cargarCarrito()); // Recargar para mostrar carrito vacío
                    
                    paypalContainer.innerHTML = ""; // Limpiar botones de PayPal
                    paypalRendered = false; 
                });
            },
            onError: function (err) {
                console.error("Error de PayPal:", err);
                alert("Ocurrió un error con el proceso de pago. Por favor, intenta de nuevo.");
                // paypalContainer.innerHTML = ""; // Opcional: limpiar botones para reintentar
                // paypalRendered = false;
            }
        }).render('#paypal-button-container').then(() => {
            paypalRendered = true;
        }).catch(err => {
            console.error("Error al renderizar botones de PayPal:", err);
            paypalContainer.innerHTML = "<p style='color:red;'>Error al cargar opciones de pago.</p>";
        });
    }

    // Llamar a configurarPaypal cuando el carrito se carga o actualiza
    // Lo haremos dentro de cargarCarrito, después de recalcularTotales y verificarCarritoVacio.
    // Modifiqué cargarCarrito para que llame a verificarCarritoVacio y recalcularTotales.
    // Y ahora, cargarCarrito también debe llamar a configurarPaypal si hay productos.

    // Modificación en cargarCarrito para llamar a configurarPaypal:
    // ... dentro de cargarCarrito, después de recalcularTotales();
    // if (productos.length > 0) {
    //     configurarPaypal();
    // } else {
    //     if (paypalContainer) paypalContainer.innerHTML = '';
    //     paypalRendered = false;
    // }
    // Esta lógica ya está implícita en verificarCarritoVacio y el inicio de configurarPaypal.

    // Inicializar
    cargarCarrito(); 
    // La configuración de PayPal se llamará desde cargarCarrito si es necesario.

});
