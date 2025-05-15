document.addEventListener("DOMContentLoaded", function () {
    const lista = document.getElementById("ListaResultados");
    const noResultados = document.getElementById("noResultados");
    const formFiltros = document.getElementById("filtrosForm");

    const params = new URLSearchParams(window.location.search);
    const query = params.get("query") || "";

    // Aplicar filtros al enviar el formulario
    formFiltros.addEventListener("submit", function (e) {
        e.preventDefault();
        cargarResultados();
    });

    // Carga inicial
    cargarResultados();

    function cargarResultados() {
        const categoria = document.getElementById("categoria").value;
        const precioMin = document.getElementById("precioMin").value;
        const precioMax = document.getElementById("precioMax").value;

        const url = new URL("pwci/controllers/buscarProductos.php", window.location.origin);
        url.searchParams.set("query", query);
        url.searchParams.set("categoria", categoria);
        if (precioMin !== "") url.searchParams.set("precioMin", precioMin);
        if (precioMax !== "") url.searchParams.set("precioMax", precioMax);

        fetch(url)
            .then(res => res.json())
            .then(data => {
                lista.innerHTML = "";

                if (data.length === 0) {
                    noResultados.style.display = "block";
                    return;
                }

                noResultados.style.display = "none";

                data.forEach(producto => {
                    const li = document.createElement("li");
                    li.classList.add("producto");

                    const imagen = producto.imagenPrincipal
                        ? `../../multimedia/productos/${producto.idProducto}/${producto.imagenPrincipal}`
                        : `../../multimedia/default/default.jpg`;

                    const boton = producto.tipo === 'Cotizacion'
                        ? `<button onclick="enviarMensaje(${producto.idProducto})">Enviar mensaje</button>`
                        : `<button onclick="agregarAlCarrito(${producto.idProducto})">Añadir al carrito</button>`;

                    li.innerHTML = `
                        <img src="${imagen}" alt="${producto.nombreProducto}">
                        <div class="info">
                            <a href="producto.php?idProducto=${producto.idProducto}">${producto.nombreProducto}</a>
                            <p>${producto.tipo === 'Venta' ? `$${producto.precio} MXN` : 'Negociable'}</p>
                            ${boton}
                        </div>
                    `;

                    lista.appendChild(li);
                });
            })
            .catch(err => {
                noResultados.style.display = "block";
                lista.innerHTML = "";
                console.error("Error en búsqueda:", err);
            });
    }

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

    window.enviarMensaje = function (idProducto) {
        // Futuro: redirigir a chat o abrir modal
        alert(`Funcionalidad de chat en desarrollo para el producto ${idProducto}.`);
    };
});

function cargarCategorias() {
    fetch('../../controllers/getCategoriasBuscador.php')
        .then(res => res.json())
        .then(categorias => {
            const select = document.getElementById("categoria");
            select.innerHTML = `
                <option value="">Todas</option>
                <option value="Cotizacion">Cotización</option>
            `;

            categorias.forEach(cat => {
                select.innerHTML += `<option value="${cat.nombre}">${cat.nombre}</option>`;
            });
        })
        .catch(err => {
            console.error("Error cargando categorías:", err);
        });
}

// Cargar al inicio
document.addEventListener("DOMContentLoaded", cargarCategorias);
