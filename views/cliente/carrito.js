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

    const paypalContainer = document.getElementById('paypal-button-container');
    let paypalRendered = false;

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

    function cargarCarrito() {
        fetch('../../controllers/getCarrito.php')
            .then(response => response.json())
            .then(productos => {
                carrito.innerHTML = '';

                if (productos.length === 0) {
                    vacio.style.display = "block";
                    contenidoCarrito.style.display = "none";
                    return;
                }

                productos.forEach(producto => {
                    carrito.innerHTML += `
                    <li class="producto" data-id="${producto.idProducto}">
                        <img src="../../multimedia/productos/${producto.idProducto}/${producto.imagenPrincipal || 'default.jpg'}" alt="${producto.nombre}">
                        <div class="info">
                            <span>${producto.nombre}</span>
                            <p>$${producto.precio} MXN</p>
                            <div class="cantidad">
                                <button type="button" class="btn-restar">-</button>
                                <span>${producto.cantidad}</span>
                                <button type="button" class="btn-sumar">+</button>
                            </div>
                        </div>
                        <div class="acciones">
                            <button class="eliminar-btn">
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
            });
    }

    carrito.addEventListener("click", function (e) {
        const target = e.target;
        const producto = target.closest(".producto");

        if (!producto) return;

        const idProducto = producto.dataset.id;

        // Sumar cantidad
        if (target.classList.contains("btn-sumar")) {
            fetch('../../controllers/sumarCantidadCarrito.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `idProducto=${idProducto}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cargarCarrito();
                } else {
                    alert(data.message || 'No se pudo aumentar la cantidad');
                }
            });
        }

        // Restar cantidad
        if (target.classList.contains("btn-restar")) {
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
                    alert(data.message || 'No se pudo disminuir la cantidad');
                }
            });
        }

        // Eliminar producto
        if (target.closest(".eliminar-btn")) {
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
                    alert(data.message || 'No se pudo eliminar el producto');
                }
            });
        }
    });

    // Vaciar todo el carrito
    vaciarBtn.addEventListener("click", function () {
        fetch('../../controllers/vaciarCarrito.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cargarCarrito();
            } else {
                alert(data.message || 'No se pudo vaciar el carrito');
            }
        });
    });

    // Paypal
    function pago() {
        if (paypalRendered) return;

        paypal.Buttons({
            style: {
                layout: 'vertical',
                color: 'gold',
                shape: 'rect',
                label: 'pay',
            },
            createOrder: function (data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: obtenerTotal().toFixed(2)
                        }
                    }]
                });
            },
            onCancel: function () {
                alert('Pago cancelado.');
                paypalContainer.innerHTML = "";
                paypalRendered = false;
            },
            onApprove: function (data, actions) {
                return actions.order.capture().then(function (details) {
                    alert('Compra realizada. Gracias ' + details.payer.name.given_name);
                    carrito.innerHTML = "";
                    verificarCarritoVacio();
                    recalcularTotales();
                    paypalContainer.innerHTML = "";
                    paypalRendered = false;
                });
            }
        }).render('#paypal-button-container');

        paypalRendered = true;
    }

    comprarBtn.addEventListener("click", function () {
        const productos = carrito.querySelectorAll(".producto");
        if (productos.length > 0) {
            pago();
        } else {
            alert("No hay productos en el carrito.");
        }
    });

    // Inicial
    cargarCarrito();
});