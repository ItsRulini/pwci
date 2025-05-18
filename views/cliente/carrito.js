document.addEventListener("DOMContentLoaded", function () {
    const contenidoCarritoEl = document.querySelector(".carrito");
    const listaProductosEl = document.querySelector(".contenido-carrito");
    const vaciarBtn = document.querySelector(".clear-btn");
    const carritoVacioEl = document.querySelector(".carrito-vacio");
    const comprarBtnPayPal = document.getElementById("btnProcederPagoPayPal"); // ID actualizado
    const btnPagarEfectivo = document.getElementById("btnPagarEfectivo"); // Nuevo botón

    const resumenSubtotalEl = document.getElementById("resumenSubtotal"); // Usar IDs para más especificidad
    const resumenEnvioEl = document.getElementById("resumenEnvio");
    const resumenImpuestosEl = document.getElementById("resumenImpuestos");
    const totalResumenEl = document.getElementById("resumenTotal");

    const ENVIO = 150;
    const IVA_PORCENTAJE = 0.16;

    const paypalContainer = document.getElementById('paypal-button-container');
    let paypalRendered = false;

    // --- Popup Pago en Efectivo ---
    const cashPaymentOverlay = document.getElementById("cashPaymentOverlay");
    const cashPaymentPopup = document.getElementById("cashPaymentPopup");
    const closeCashPopupBtn = document.getElementById("closeCashPopupBtn");
    const barcodeDisplay = document.getElementById("barcodeDisplay");
    const cashTotalAmount = document.getElementById("cashTotalAmount");
    const btnConfirmarPagoEfectivo = document.getElementById("btnConfirmarPagoEfectivo");
    const cashPaymentMessage = document.getElementById("cashPaymentMessage");

    function formatearMoneda(cantidad) {
        return `$${Number(cantidad).toLocaleString("es-MX", { minimumFractionDigits: 2, maximumFractionDigits: 2 })} MXN`;
    }

    function obtenerTotalNumerico() {
        const textoTotal = totalResumenEl.textContent;
        return parseFloat(textoTotal.replace(/[^0-9.]/g, "")) || 0;
    }

    function recalcularTotales() {
        let subtotal = 0;
        const productosLi = listaProductosEl.querySelectorAll(".producto");
        productosLi.forEach(productoLi => {
            const precioTexto = productoLi.querySelector(".info p").textContent;
            const cantidadTexto = productoLi.querySelector(".cantidad span").textContent;
            const precioUnitario = parseFloat(precioTexto.replace(/[^0-9.]/g, ""));
            const cantidad = parseInt(cantidadTexto);
            if (!isNaN(precioUnitario) && !isNaN(cantidad)) {
                subtotal += precioUnitario * cantidad;
            }
        });
        const impuestos = subtotal * IVA_PORCENTAJE;
        const total = subtotal + impuestos + (subtotal > 0 ? ENVIO : 0);

        if (resumenSubtotalEl) resumenSubtotalEl.textContent = formatearMoneda(subtotal);
        if (resumenEnvioEl) resumenEnvioEl.textContent = formatearMoneda(subtotal > 0 ? ENVIO : 0);
        if (resumenImpuestosEl) resumenImpuestosEl.textContent = formatearMoneda(impuestos);
        if (totalResumenEl) totalResumenEl.textContent = formatearMoneda(total);
    }

    function verificarCarritoVacio() {
        const productosLi = listaProductosEl.querySelectorAll(".producto");
        const hayProductos = productosLi.length > 0;

        if (carritoVacioEl) carritoVacioEl.style.display = hayProductos ? "none" : "block"; // O 'block'
        if (contenidoCarritoEl) contenidoCarritoEl.style.display = hayProductos ? "block" : "none";
        
        if (paypalContainer) paypalContainer.innerHTML = '';
        paypalRendered = false;
        
        // Mostrar u ocultar botones de pago
        if(comprarBtnPayPal) comprarBtnPayPal.style.display = hayProductos ? 'block' : 'none';
        if(btnPagarEfectivo) btnPagarEfectivo.style.display = hayProductos ? 'block' : 'none';
    }

    function cargarCarrito() {
        fetch('../../controllers/getCarrito.php')
            .then(response => response.json())
            .then(productos => {
                listaProductosEl.innerHTML = '';
                if (!Array.isArray(productos)) {
                    console.error("getCarrito.php no devolvió un array:", productos);
                    verificarCarritoVacio();
                    recalcularTotales();
                    return;
                }
                if (productos.length === 0) {
                    verificarCarritoVacio();
                    recalcularTotales();
                    return;
                }
                productos.forEach(producto => {
                    let cantidadHTML = '';
                    if (producto.tipoProducto === 'Venta') {
                        cantidadHTML = `<div class="cantidad"><button type="button" class="btn-restar" data-idproducto="${producto.idProducto}">-</button><span>${producto.cantidad}</span><button type="button" class="btn-sumar" data-idproducto="${producto.idProducto}">+</button></div>`;
                    } else {
                        cantidadHTML = `<div class="cantidad"><span>${producto.cantidad} (Cotización)</span></div>`;
                    }
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
                                <button class="eliminar-btn" data-idproducto="${producto.idProducto}"><i class="fas fa-trash"></i></button>
                            </div>
                        </li>`;
                });
                verificarCarritoVacio();
                recalcularTotales();
                // No configurar PayPal aquí directamente, se hará al hacer clic en el botón de PayPal
            })
            .catch(error => {
                console.error('Error cargando carrito:', error);
                listaProductosEl.innerHTML = '<li style="color:red; text-align:center;">Error al cargar el carrito.</li>';
                verificarCarritoVacio();
                recalcularTotales();
            });
    }

    listaProductosEl.addEventListener("click", function (e) {
        const target = e.target;
        const productoLi = target.closest(".producto");
        if (!productoLi) return;
        const idProducto = productoLi.dataset.idproducto;

        if (target.classList.contains("btn-sumar") || target.closest(".btn-sumar")) {
            fetch('../../controllers/sumarCantidadCarrito.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `idProducto=${idProducto}`})
            .then(response => response.json()).then(data => { if (data.success) cargarCarrito(); else alert(data.message || 'Error'); });
        } else if (target.classList.contains("btn-restar") || target.closest(".btn-restar")) {
            fetch('../../controllers/restarCantidadCarrito.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `idProducto=${idProducto}`})
            .then(response => response.json()).then(data => { if (data.success) cargarCarrito(); else alert(data.message || 'Error'); });
        } else if (target.classList.contains("eliminar-btn") || target.closest(".eliminar-btn")) {
            if (confirm("¿Eliminar este producto del carrito?")) {
                fetch('../../controllers/eliminarProductoCarrito.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `idProducto=${idProducto}`})
                .then(response => response.json()).then(data => { if (data.success) cargarCarrito(); else alert(data.message || 'Error'); });
            }
        }
    });

    if (vaciarBtn) {
        vaciarBtn.addEventListener("click", function () {
            if (confirm("¿Vaciar todo el carrito?")) {
                fetch('../../controllers/vaciarCarrito.php', { method: 'POST' })
                .then(response => response.json()).then(data => { if (data.success) cargarCarrito(); else alert(data.message || 'Error'); });
            }
        });
    }

    // --- Lógica para Pago en Efectivo ---
    function generarCodigoReferencia(longitud = 12) {
        let codigo = '';
        const caracteres = '0123456789';
        for (let i = 0; i < longitud; i++) {
            codigo += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
        }
        // Formatear para que parezca un código de barras (opcional)
        return codigo.match(/.{1,4}/g).join('-'); // Ej: 1234-5678-9012
    }

    if (btnPagarEfectivo) {
        btnPagarEfectivo.addEventListener("click", function() {
            const totalPagar = obtenerTotalNumerico();
            if (totalPagar <= 0) {
                alert("Tu carrito está vacío. Añade productos para pagar en efectivo.");
                return;
            }
            if (barcodeDisplay) barcodeDisplay.textContent = generarCodigoReferencia();
            if (cashTotalAmount) cashTotalAmount.textContent = formatearMoneda(totalPagar);
            if (cashPaymentMessage) cashPaymentMessage.textContent = ''; // Limpiar mensaje previo
            if (cashPaymentOverlay) cashPaymentOverlay.style.display = "block";
            if (cashPaymentPopup) cashPaymentPopup.style.display = "flex";
        });
    }

    if (closeCashPopupBtn) {
        closeCashPopupBtn.addEventListener("click", function() {
            if (cashPaymentOverlay) cashPaymentOverlay.style.display = "none";
            if (cashPaymentPopup) cashPaymentPopup.style.display = "none";
            if (cashPaymentMessage) {
                cashPaymentMessage.textContent = 'Pago cancelado.';
                cashPaymentMessage.className = 'payment-message cancelled';
            }
        });
    }
    if (cashPaymentOverlay) { // Cerrar si se hace clic en el overlay
        cashPaymentOverlay.addEventListener("click", function() {
            if (cashPaymentOverlay) cashPaymentOverlay.style.display = "none";
            if (cashPaymentPopup) cashPaymentPopup.style.display = "none";
             if (cashPaymentMessage) {
                cashPaymentMessage.textContent = 'Pago cancelado.';
                cashPaymentMessage.className = 'payment-message cancelled';
            }
        });
    }


    if (btnConfirmarPagoEfectivo) {
        btnConfirmarPagoEfectivo.addEventListener("click", function() {
            // Simular procesamiento de pago y llamar al backend para registrar la compra
            if (cashPaymentMessage) {
                cashPaymentMessage.textContent = 'Procesando pago...';
                cashPaymentMessage.className = 'payment-message';
            }

            fetch('../../controllers/procesarCompra.php', { 
                method: 'POST' 
                // No se envía `paypalOrderID` ni `paypalPayerID` para pago en efectivo
            })
            .then(response => response.json())
            .then(backendResponse => {
                if (backendResponse.success) {
                    if (cashPaymentMessage) {
                        cashPaymentMessage.textContent = '¡Pago realizado con éxito! ' + (backendResponse.message || '');
                        cashPaymentMessage.className = 'payment-message success';
                    }
                    alert('¡Compra completada y registrada! ' + (backendResponse.message || ''));
                    cargarCarrito(); // Vaciará el carrito y mostrará el mensaje
                    // Cerrar popup después de un momento para que el usuario vea el mensaje
                    setTimeout(() => {
                        if (cashPaymentOverlay) cashPaymentOverlay.style.display = "none";
                        if (cashPaymentPopup) cashPaymentPopup.style.display = "none";
                    }, 3000);
                } else {
                     if (cashPaymentMessage) {
                        cashPaymentMessage.textContent = 'Error al registrar la compra: ' + (backendResponse.message || 'Error desconocido.');
                        cashPaymentMessage.className = 'payment-message error';
                    }
                    alert('Error al registrar la compra: ' + (backendResponse.message || 'Error desconocido.'));
                }
            })
            .catch(error => {
                console.error('Error al procesar compra en efectivo:', error);
                if (cashPaymentMessage) {
                    cashPaymentMessage.textContent = 'Error de conexión al finalizar la compra.';
                    cashPaymentMessage.className = 'payment-message error';
                }
                alert('Error de conexión al finalizar la compra. Contacta a soporte.');
            });
        });
    }


    // --- Lógica de PayPal ---
    function configurarPaypal() {
        if (!paypalContainer || typeof paypal === 'undefined') {
            console.warn("Contenedor de PayPal o SDK no encontrado.");
            if (comprarBtnPayPal) comprarBtnPayPal.style.display = 'none'; // Ocultar si PayPal no carga
            return;
        }
        const totalPagar = obtenerTotalNumerico();
        if (totalPagar <= 0) {
            paypalContainer.innerHTML = ""; 
            paypalRendered = false;
            if (comprarBtnPayPal) comprarBtnPayPal.style.display = 'block'; // Mostrar botón genérico si no hay total
            return;
        }
        
        if (paypalRendered) return; // Evitar re-renderizar si ya está
        
        paypalContainer.innerHTML = ""; // Limpiar antes de renderizar
        if(comprarBtnPayPal) comprarBtnPayPal.style.display = 'none'; // Ocultar el botón genérico si PayPal se va a renderizar

        paypal.Buttons({
            style: { layout: 'vertical', color:  'gold', shape:  'rect', label:  'pay' },
            createOrder: function(data, actions) {
                const totalActual = obtenerTotalNumerico();
                if (totalActual <= 0) return actions.reject();
                return actions.order.create({
                    purchase_units: [{ amount: { value: totalActual.toFixed(2) }}]
                });
            },
            onCancel: function (data) {
                console.log("Pago de PayPal cancelado:", data);
                alert("Pago cancelado.");
                if(comprarBtnPayPal) comprarBtnPayPal.style.display = 'block'; // Mostrar botón genérico
                paypalContainer.innerHTML = ""; // Limpiar botones de PayPal
                paypalRendered = false;
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    console.log('PayPal capture details:', details);
                    fetch('../../controllers/procesarCompra.php', { method: 'POST' /* Podrías enviar details si tu backend los usa */ })
                    .then(response => response.json())
                    .then(backendResponse => {
                        if (backendResponse.success) {
                            alert('¡Compra completada y registrada! ' + (backendResponse.message || ''));
                            cargarCarrito(); 
                        } else {
                            alert('Error al registrar la compra: ' + (backendResponse.message || 'Error desconocido.'));
                        }
                    })
                    .catch(error => {
                        console.error('Error al procesar compra post-PayPal:', error);
                        alert('Error de conexión al finalizar la compra.');
                    })
                    .finally(() => {
                        paypalContainer.innerHTML = ""; 
                        paypalRendered = false; 
                        if(comprarBtnPayPal) comprarBtnPayPal.style.display = 'block'; // Reactivar botón genérico
                    });
                });
            },
            onError: function (err) {
                console.error("Error de PayPal SDK:", err);
                alert("Ocurrió un error con PayPal. Intenta de nuevo o usa otro método de pago.");
                if(comprarBtnPayPal) comprarBtnPayPal.style.display = 'block';
                paypalContainer.innerHTML = "<p style='color:red;'>Error al cargar PayPal.</p>";
                paypalRendered = false;
            }
        }).render('#paypal-button-container').then(() => {
            paypalRendered = true;
        }).catch(err => {
            console.error("Error al renderizar botones de PayPal:", err);
            if (paypalContainer) paypalContainer.innerHTML = "<p style='color:red;'>Error al cargar opciones de pago de PayPal.</p>";
            if(comprarBtnPayPal) comprarBtnPayPal.style.display = 'block';
        });
    }
    
    // Event listener para el botón "Proceder al pago" que ahora es específico para PayPal
    if(comprarBtnPayPal) {
        comprarBtnPayPal.addEventListener("click", function () {
            const productosLi = listaProductosEl.querySelectorAll(".producto");
            if (productosLi.length > 0) {
                if (!paypalRendered) { 
                    configurarPaypal();
                }
                // Si ya están visibles, PayPal maneja el flujo.
                // Si el contenedor de PayPal está vacío (porque falló la carga del SDK),
                // el usuario no verá los botones de PayPal.
            } else {
                alert("Tu carrito está vacío.");
            }
        });
    }
    
    cargarCarrito(); 
});
