document.getElementById("input-file").addEventListener("change", function(event) {
    const file = event.target.files[0]; // Obtiene el archivo seleccionado
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById("profile-image").src = e.target.result; // Asigna la imagen al src
        };
        reader.readAsDataURL(file);
    }
});

document.addEventListener('DOMContentLoaded', function() {
    cargarProductosAprobados();
    cargarProductosRechazados();

    document.getElementById("input-file").addEventListener("change", function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById("profile-image").src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
});

function cargarProductosAprobados() {
    fetch('../../controllers/getProductosAprobados.php')
        .then(response => response.json())
        .then(data => {
            const container = document.querySelectorAll('.card-container')[0];
            container.innerHTML = '';

            if (!data.length) {
                container.innerHTML = `
                    <div class="no-products-message">
                        No has aprobado ningún producto aún.
                    </div>`;
                return;
            }

            data.forEach(producto => {
                const card = document.createElement('div');
                card.classList.add('card');

                const imagen = producto.imagenPrincipal 
                    ? `../../multimedia/productos/${producto.idProducto}/${producto.imagenPrincipal}` 
                    : '../../multimedia/default/default.jpg';

                card.innerHTML = `
                    <img src="${imagen}" alt="Imagen del producto" class="card-image">
                    <h3 class="card-title">${producto.nombre}</h3>
                    <p class="card-description">${producto.descripcion || 'Sin descripción.'}</p>  
                    <p class="card-price">$${producto.precio ?? '0.00'} MXN</p>
                    <button class="card-button-ver-mas" onclick="verProducto(${producto.idProducto})">Ver más</button>
                `;

                container.appendChild(card);
            });
        })
        .catch(error => {
            console.error('Error cargando productos aprobados:', error);
        });
}

function cargarProductosRechazados() {
    fetch('../../controllers/getProductosRechazados.php')
        .then(response => response.json())
        .then(data => {
            const container = document.querySelectorAll('.card-container')[1];
            container.innerHTML = '';

            if (!data.length) {
                container.innerHTML = `
                    <div class="no-products-message">
                        No has rechazado ningún producto aún.
                    </div>`;
                return;
            }

            data.forEach(producto => {
                const card = document.createElement('div');
                card.classList.add('card');

                const imagen = producto.imagenPrincipal 
                    ? `../../multimedia/productos/${producto.idProducto}/${producto.imagenPrincipal}` 
                    : '../../multimedia/default/default.jpg';

                card.innerHTML = `
                    <img src="${imagen}" alt="Imagen del producto" class="card-image">
                    <h3 class="card-title">${producto.nombre}</h3>
                    <p class="card-description">${producto.descripcion || 'Sin descripción.'}</p>  
                    <p class="card-price">$${producto.precio ?? '0.00'} MXN</p>
                    <button class="card-button-ver-mas" onclick="verProducto(${producto.idProducto})">Ver más</button>
                `;

                container.appendChild(card);
            });
        })
        .catch(error => {
            console.error('Error cargando productos rechazados:', error);
        });
}

function verProducto(idProducto) {
    window.location.href = `producto.php?idProducto=${idProducto}`;
}
