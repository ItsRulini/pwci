document.addEventListener("DOMContentLoaded", function () {
    const contenidoCarritoEl = document.querySelector(".carrito"); // Renombrado para evitar confusión con la variable 'carrito' del array
    const listaProductosEl = document.querySelector(".contenido-carrito"); // El UL donde van los <li>
    const vaciarBtn = document.querySelector(".clear-btn");
    const carritoVacioEl = document.querySelector(".carrito-vacio");
    const comprarBtn = document.querySelector(".btn-comprar"); // Ya lo tienes

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
            carritoVacioEl.style.display = "block"; 
            contenidoCarritoEl.style.display = "none";
            if (paypalContainer) paypalContainer.innerHTML = ''; 
            paypalRendered = false;
            if(comprarBtn) comprarBtn.style.display = 'none'; // Ocultar botón de PayPal si no hay items
        } else {
            carritoVacioEl.style.display = "none";
            contenidoCarritoEl.style.display = "block";
             if(comprarBtn) comprarBtn.style.display = 'block'; // Mostrar botón de PayPal
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

                // if(comprarBtn) {
                //     comprarBtn.addEventListener("click", function () {
                //         if (productos.length > 0) {
                //             configurarPaypal();
                //         } else {
                //             if (paypalContainer) paypalContainer.innerHTML = '';
                //             paypalRendered = false;
                //         }
                //     });
                // }
                // Configurar PayPal solo si hay productos y el contenedor existe
                if (productos.length > 0 && paypalContainer) {
                    configurarPaypal();
                } else if (paypalContainer) {
                    paypalContainer.innerHTML = ''; // Limpiar si no hay productos
                    paypalRendered = false;
                }
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

    function configurarPaypal() {
        if (!paypalContainer || typeof paypal === 'undefined') {
            console.warn("Contenedor de PayPal o SDK no encontrado.");
            return;
        }
        // Solo renderizar si no está ya renderizado y hay un total > 0
        const totalPagar = obtenerTotalNumerico();
        if (paypalRendered && totalPagar > 0) return; 
        if (totalPagar <= 0) {
            paypalContainer.innerHTML = ""; 
            paypalRendered = false;
            return;
        }
        
        paypalContainer.innerHTML = ""; // Limpiar antes de re-renderizar

        paypal.Buttons({
            style: {
                layout: 'vertical',
                color:  'gold',
                shape:  'rect',
                label:  'pay',
            },
            createOrder: function(data, actions) {
                const totalActual = obtenerTotalNumerico(); // Obtener el total más reciente
                if (totalActual <= 0) {
                    alert("El total del carrito es cero. No se puede proceder con el pago.");
                    return actions.reject();
                }
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: totalActual.toFixed(2) 
                        }
                    }]
                });
            },
            onCancel: function (data) {
                console.log("Pago de PayPal cancelado:", data);
                alert("Pago cancelado.");
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    // El pago fue aprobado por PayPal.
                    // Ahora, procesar la compra en el backend.
                    console.log('PayPal capture details:', details);
                    
                    const formData = new FormData();
                    // Puedes enviar detalles de PayPal si los necesitas en el backend
                    // formData.append('paypalOrderID', data.orderID);
                    // formData.append('paypalPayerID', details.payer.payer_id);

                    fetch('../../controllers/procesarCompra.php', {
                        method: 'POST',
                        body: formData // Enviar formData si es necesario, o vacío si el backend usa la sesión
                    })
                    .then(response => response.json())
                    .then(backendResponse => {
                        if (backendResponse.success) {
                            alert('¡Compra completada y registrada! ' + (backendResponse.message || ''));
                            // La sesión de idLista se actualiza en el backend.
                            // El carrito actual se marcó como comprado.
                            // Se creó un nuevo carrito vacío para el usuario.
                            cargarCarrito(); // Esto cargará el nuevo carrito vacío.
                        } else {
                            alert('Error al registrar la compra: ' + (backendResponse.message || 'Error desconocido.'));
                            // El pago en PayPal se hizo, pero hubo un error en tu backend.
                            // Deberías tener un sistema para manejar estas discrepancias.
                        }
                    })
                    .catch(error => {
                        console.error('Error al procesar la compra en el backend:', error);
                        alert('Error de conexión al finalizar la compra. Contacta a soporte.');
                    })
                    .finally(() => {
                        paypalContainer.innerHTML = ""; 
                        paypalRendered = false; 
                    });
                });
            },
            onError: function (err) {
                console.error("Error de PayPal SDK:", err);
                alert("Ocurrió un error con el proceso de pago de PayPal. Por favor, intenta de nuevo.");
                paypalContainer.innerHTML = "<p style='color:red;'>Error al cargar opciones de pago.</p>";
                paypalRendered = false;
            }
        }).render('#paypal-button-container').then(() => {
            paypalRendered = true;
            console.log("Botones de PayPal renderizados.");
        }).catch(err => {
            console.error("Error al renderizar botones de PayPal:", err);
            if (paypalContainer) {
                paypalContainer.innerHTML = "<p style='color:red;'>Error al cargar opciones de pago de PayPal.</p>";
            }
        });
    }
    
    if(comprarBtn && paypalContainer) {
        comprarBtn.addEventListener("click", function () {
            const productosLi = listaProductosEl.querySelectorAll(".producto");
            if (productosLi.length > 0) {
                // No llamamos a configurarPaypal() directamente aquí.
                // Se llamará desde cargarCarrito() si es necesario,
                // o si quieres que el botón "Proceder al pago" active la renderización:
                if (!paypalRendered) { // Solo si no están ya visibles
                    configurarPaypal();
                }
                // Si ya están visibles, PayPal maneja el flujo.
            } else {
                alert("Tu carrito está vacío.");
                if (paypalContainer) paypalContainer.innerHTML = '';
                paypalRendered = false;
            }
        });
    } else if (comprarBtn && !paypalContainer) {
        // Si el botón de comprar existe pero el contenedor de PayPal no,
        // podrías implementar un flujo de compra sin PayPal aquí.
        comprarBtn.addEventListener("click", function() {
            alert("El contenedor de PayPal no está disponible. Procediendo con flujo alternativo (no implementado).");
            // Aquí iría la lógica para procesar la compra sin PayPal,
            // similar al fetch que se hace en onApprove pero sin los detalles de PayPal.
             const productosLi = listaProductosEl.querySelectorAll(".producto");
             if (productosLi.length === 0) {
                 alert("Tu carrito está vacío.");
                 return;
             }

            if (confirm("¿Confirmar compra (sin PayPal)?")) {
                 fetch('../../controllers/procesarCompra.php', { method: 'POST' })
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
                     console.error('Error al procesar la compra en el backend:', error);
                     alert('Error de conexión al finalizar la compra. Contacta a soporte.');
                 });
            }
        });
    }


    // Inicializar
    cargarCarrito(); 
    // La configuración de PayPal se llamará desde cargarCarrito si es necesario.

});
