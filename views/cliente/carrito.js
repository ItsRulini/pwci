document.addEventListener("DOMContentLoaded", function () {
    const contenidoCarrito = document.querySelector(".carrito");
    const carrito = document.querySelector(".contenido-carrito");
    const vaciarBtn = document.querySelector(".clear-btn");
    const vacio = document.querySelector(".carrito-vacio");
    const comprarBtn = document.querySelector(".btn-comprar");

    const resumenSubtotal = document.querySelector(".linea-resumen span:nth-child(2)");
    const resumenEnvio = document.querySelectorAll(".linea-resumen span:nth-child(2)")[1];
    const resumenImpuestos = document.querySelectorAll(".linea-resumen span:nth-child(2)")[2];
    const totalResumen = document.querySelector(".linea-total span:nth-child(2)");

    const ENVIO = 150;
    const IVA = 0.16;

    function formatearMoneda(cantidad) {
        return `$${cantidad.toLocaleString("es-MX", { minimumFractionDigits: 2 })} MXN`;
    }

    function obtenerTotal() {
        return parseFloat(totalResumen.textContent.replace(/[^0-9.]/g, ""));
    }

    function recalcularTotales() {
        let subtotal = 0;
        const productos = carrito.querySelectorAll(".producto");

        productos.forEach(producto => {
            const precioTexto = producto.querySelector(".info p").textContent;
            const cantidad = parseInt(producto.querySelector(".cantidad span").textContent);
            const precioUnitario = parseFloat(precioTexto.replace(/[^0-9.]/g, ""));
            subtotal += precioUnitario * cantidad;
        });

        const impuestos = subtotal * IVA;
        const total = subtotal + impuestos + (subtotal > 0 ? ENVIO : 0);

        resumenSubtotal.textContent = formatearMoneda(subtotal);
        resumenEnvio.textContent = formatearMoneda(subtotal > 0 ? ENVIO : 0);
        resumenImpuestos.textContent = formatearMoneda(impuestos);
        totalResumen.textContent = formatearMoneda(total);
    }

    function verificarCarritoVacio() {
        const productos = carrito.querySelectorAll(".producto");
        if (productos.length === 0) {
            vacio.style.display = "block";
            contenidoCarrito.style.display = "none";
        } else {
            vacio.style.display = "none";
            contenidoCarrito.style.display = "block";
        }
    }

    // Manejo de eventos en los botones dentro del carrito
    carrito.addEventListener("click", function (e) {
        const target = e.target;

        // Aumentar cantidad
        if (target.textContent === "+") {
            const spanCantidad = target.previousElementSibling;
            let cantidad = parseInt(spanCantidad.textContent);
            spanCantidad.textContent = cantidad + 1;
            recalcularTotales();
        }

        // Disminuir cantidad
        if (target.textContent === "-") {
            const spanCantidad = target.nextElementSibling;
            let cantidad = parseInt(spanCantidad.textContent);
            if (cantidad > 1) {
                spanCantidad.textContent = cantidad - 1;
                recalcularTotales();
            }
        }

        // Eliminar producto individual
        if (target.closest(".eliminar-btn")) {
            const producto = target.closest(".producto");
            if (producto) {
                producto.remove();
                verificarCarritoVacio();
                recalcularTotales();
            }
        }
    });

    // Vaciar todo el carrito
    vaciarBtn.addEventListener("click", function () {
        carrito.innerHTML = "";
        verificarCarritoVacio();
        recalcularTotales();
    });

    comprarBtn.addEventListener("click", function () {
        const productos = carrito.querySelectorAll(".producto");
        if (productos.length > 0) {
            pago(); // Genera los botones PayPal
        } else {
            alert("No hay productos en el carrito.");
        }
    });

    // Esta variable evita múltiples renderizados del botón de PayPal
    let paypalRendered = false;

    function pago() {
        if (paypalRendered) return; // Si ya está renderizado, no hacer nada

        paypal.Buttons({
            style: {
                layout: 'vertical',
                color: 'gold',
                shape: 'rect',
                label: 'paypal',
                with: 100,
                height: 40
            },
            createOrder: function (data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: obtenerTotal().toFixed(2) // Total dinámico
                        }
                    }]
                });
            },
            onCancel: function (data) {
                alert('Pago cancelado. Puedes seguir comprando.');
                paypalContainer.innerHTML = ""; // Limpia el botón de PayPal después de cancelar
                paypalRendered = false; // Permitir un nuevo render si vuelve a haber productos
            },
            onApprove: function (data, actions) {
                return actions.order.capture().then(function (details) {
                    alert('Compra realizada con éxito. Gracias, ' + details.payer.name.given_name);
                    carrito.innerHTML = "";
                    verificarCarritoVacio();
                    recalcularTotales();
                    paypalContainer.innerHTML = ""; // Limpia el botón de PayPal después del pago
                    paypalRendered = false; // Permitir un nuevo render si vuelve a haber productos
                });
            }
        }).render('#paypal-button-container');

        paypalRendered = true;
    }

    // Inicial
    recalcularTotales();
    verificarCarritoVacio();
});
