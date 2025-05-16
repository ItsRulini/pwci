document.addEventListener('DOMContentLoaded', function() {
    cargarProductosPopulares();
    cargarProductosCotizacion();
    cargarProductosRecientes();
    cargarProductosGenerales();
});


function cargarProductosPopulares() {
    fetch('../../controllers/getProductosPopulares.php')
        .then(response => response.json())
        .then(productos => {
            const section = document.getElementById('Populares');
            const lista = document.getElementById('ListaPopulares');
            lista.innerHTML = '';

            if (!productos.length) {
                section.style.display = 'none';
                return;
            } else {
                section.style.display = 'block';
            }

            productos.forEach(producto => {
                lista.innerHTML += crearProductoHTML(producto, true);
            });
        });
}

function cargarProductosCotizacion() {
    fetch('../../controllers/getProductosCotizacion.php')
        .then(response => response.json())
        .then(productos => {
            const section = document.getElementById('ParaCotizar');
            const lista = document.getElementById('ListaCotizacion');
            lista.innerHTML = '';

            if (!productos.length) {
                section.style.display = 'none';
                return;
            } else {
                section.style.display = 'block';
            }

            productos.forEach(producto => {
                lista.innerHTML += crearProductoHTML(producto, false, true);
            });
        });
}

function cargarProductosRecientes() {
    fetch('../../controllers/getProductosRecientes.php')
        .then(response => response.json())
        .then(productos => {
            const section = document.getElementById('Recientes');
            const lista = document.getElementById('ListaRecientes');
            lista.innerHTML = '';

            if (!productos.length) {
                section.style.display = 'none';
                return;
            } else {
                section.style.display = 'block';
            }

            productos.forEach(producto => {
                lista.innerHTML += crearProductoHTML(producto, true);
            });
        });
}

function cargarProductosGenerales() {
    fetch('../../controllers/getProductosGenerales.php')
        .then(response => response.json())
        .then(productos => {
            const section = document.getElementById('General');
            const lista = document.getElementById('ListaProductos');
            lista.innerHTML = '';

            if (!productos.length) {
                section.style.display = 'none';
                return;
            } else {
                section.style.display = 'block';
            }

            productos.forEach(producto => {
                lista.innerHTML += crearProductoHTML(producto, true);
            });
        });
}


// function crearProductoHTML(producto, mostrarPrecio = true, esCotizacion = false) {
//      const imagen = producto.imagenPrincipal 
//          ? `../../multimedia/productos/${producto.idProducto}/${producto.imagenPrincipal}` 
//          : '../../multimedia/default/default.jpg';

//      const precio = mostrarPrecio
//          ? `<p>$${producto.precio} MXN</p><button onclick="agregarAlCarrito(${producto.idProducto})">Añadir al carrito</button>
// `
//          : `<p>Negociable</p><button>Enviar mensaje</button>`;

//      return `
//          <li class="producto">
//              <img src="${imagen}" alt="${producto.nombre}">
//              <div class="info">
//                  <a href="producto.php?idProducto=${producto.idProducto}">${producto.nombre}</a>
//                  ${precio}
//              </div>
//          </li>
//      `;
//  }

function crearProductoHTML(producto, mostrarPrecio = true, esCotizacion = false) {
    const imagen = producto.imagenPrincipal
        ? `../../multimedia/productos/${producto.idProducto}/${producto.imagenPrincipal}`
        : '../../multimedia/default/default.jpg';

    const acciones = mostrarPrecio
        ? `<p>$${producto.precio} MXN</p><button onclick="agregarAlCarrito(${producto.idProducto})">Añadir al carrito</button>`
        : `<p>Negociable</p><button onclick="iniciarChat(${producto.idProducto})">Enviar mensaje</button>`;

    return `
        <li class="producto">
            <img src="${imagen}" alt="${producto.nombre}">
            <div class="info">
                <a href="producto.php?idProducto=${producto.idProducto}">${producto.nombre}</a>
                ${acciones}
            </div>
        </li>
    `;
}



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

function iniciarChat(idProducto) {
    const formData = new FormData();
    formData.append("idProducto", idProducto);

    fetch("../../controllers/iniciarChat.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.idChat) {
            window.location.href = `chat.php?idChat=${data.idChat}`;
        } else {
            alert("No se pudo iniciar el chat.");
        }
    })
    .catch(err => {
        console.error("Error iniciando chat:", err);
        alert("Ocurrió un error al intentar abrir el chat.");
    });
}
