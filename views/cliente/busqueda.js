document.addEventListener("DOMContentLoaded", function () {
    const lista = document.getElementById("ListaResultados");
    const noResultados = document.getElementById("noResultados");

    const params = new URLSearchParams(window.location.search);
    const query = params.get("query");

    if (!query) {
        noResultados.style.display = "block";
        lista.innerHTML = "";
        return;
    }

    fetch("../../controllers/buscarProductos.php?query=" + encodeURIComponent(query))
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

                li.innerHTML = `
                    <img src="${imagen}" alt="${producto.nombreProducto}">
                    <div class="info">
                        <a href="producto.php?idProducto=${producto.idProducto}">${producto.nombreProducto}</a>
                        <p>${producto.tipo === 'Venta' ? `$${producto.precio} MXN` : 'Negociable'}</p>
                        <button onclick="agregarAlCarrito(${producto.idProducto})">Añadir al carrito</button>
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
});

 function agregarAlCarrito(idProducto) {
    const formData = new FormData();
    formData.append('idProducto', idProducto);

    fetch('../../controllers/agregarCarrito.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message || "Artículo agregado al carrito.");
        } else {
            alert("Error: " + (data.message || "No se pudo agregar al carrito."));
        }
    })
    .catch(error => {
        console.error('Error al agregar al carrito:', error);
        alert('Ocurrió un error al conectar con el servidor.');
    });
}

