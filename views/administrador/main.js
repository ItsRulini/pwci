function cargarProductosPendientes() {
    fetch('../../controllers/getProductosPendientes.php')
        .then(response => response.json())
        .then(data => {
            const container = document.querySelector('.card-container');
            container.innerHTML = ''; // Limpiar antes de recargar

            if (data.length === 0) {
                container.innerHTML = `<p class="no-products-message">No hay productos por autorizar.</p>`;
                return;
            }

            data.forEach(producto => {
                const card = document.createElement('div');
                card.classList.add('card');

                const imagen = producto.imagenPrincipal ? `../../multimedia/productos/${producto.idProducto}/${producto.imagenPrincipal}` : '../../multimedia/default/default.jpg';

                card.innerHTML = `
                    <img src="${imagen}" alt="Imagen del producto" class="card-image">
                    <h3 class="card-title">${producto.nombre}</h3>
                    <p class="card-description">${producto.descripcion || 'Sin descripción.'}</p>
                    <p class="card-price">$${producto.precio ?? '0.00'} MXN</p>
                    <button class="card-button-ver-mas" onclick="verProducto(${producto.idProducto})">Ver más</button>
                    <button class="card-button-approve" onclick="autorizarProducto(${producto.idProducto})"><i class="fas fa-check"></i></button>
                    <button class="card-button-disapprove" onclick="rechazarProducto(${producto.idProducto})"><i class="fas fa-times"></i></button>
                `;
                
                container.appendChild(card);
            });
        })
        .catch(error => {
            console.error('Error cargando productos pendientes:', error);
        });
}

// Refrescar automáticamente cada 5 segundos
setInterval(cargarProductosPendientes, 5000);

// Cargar inmediatamente cuando entra
document.addEventListener('DOMContentLoaded', cargarProductosPendientes);


function autorizarProducto(idProducto) {
    fetch('../../controllers/aprobarProducto.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `idProducto=${idProducto}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cargarProductosPendientes(); // Recargar
        } else {
            alert('Error al aprobar el producto.');
        }
    })
    .catch(error => {
        console.error('Error al aprobar:', error);
    });
}

function rechazarProducto(idProducto) {
    fetch('../../controllers/rechazarProducto.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `idProducto=${idProducto}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cargarProductosPendientes(); // Recargar
        } else {
            alert('Error al rechazar el producto.');
        }
    })
    .catch(error => {
        console.error('Error al rechazar:', error);
    });
}

function verProducto(idProducto) {
    window.location.href = `producto.php?idProducto=${idProducto}`;
}



// document.querySelector(".card-button-ver-mas").addEventListener("click", function() {
//     window.location.href = "producto.php"; // Cambia la URL a la página de perfil
// });
