document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const idProducto = params.get('idProducto');

    if (!idProducto) {
        alert('Producto no encontrado.');
        window.location.href = 'main.php';
        return;
    }

    fetch(`../../controllers/getProductoPorId.php?idProducto=${idProducto}`)
        .then(response => response.json())
        .then(data => {
            if (data.success === false) {
                alert('Producto no encontrado.');
                window.location.href = 'main.php';
                return;
            }

            // Cargar info
            document.querySelector('.info h2').textContent = data.nombre;
            document.querySelector('.info p:nth-of-type(1)').textContent = `$${data.precio} MXN`;
            document.querySelector('.info p:nth-of-type(2)').textContent = data.descripcion;
            document.querySelector('.info-seller p').textContent = `Publicado por: ${data.nombreVendedor}`;
        });

    // Cargar multimedia
    fetch(`../../controllers/getMultimediaProducto.php?idProducto=${idProducto}`)
        .then(response => response.json())
        .then(files => {
            const multimediaContainer = document.querySelector('.multimedia');
            multimediaContainer.innerHTML = '';

            files.forEach(file => {
                if (file.endsWith('.mp4')) {
                    multimediaContainer.innerHTML += `
                        <video controls>
                            <source src="../../multimedia/productos/${idProducto}/${file}" type="video/mp4">
                        </video>
                    `;
                } else {
                    multimediaContainer.innerHTML += `
                        <img src="../../multimedia/productos/${idProducto}/${file}" alt="Imagen producto">
                    `;
                }
            });
        });

    // Cargar categorías
    fetch(`../../controllers/getCategoriasProducto.php?idProducto=${idProducto}`)
        .then(response => response.json())
        .then(categorias => {
            const categoriasList = document.querySelector('.categorias');
            categoriasList.innerHTML = '';
            categorias.forEach(categoria => {
                categoriasList.innerHTML += `<li class="categoria">${categoria}</li>`;
            });
        });

});

// Aprobar producto
function autorizarProducto(idProducto) {
    fetch('../../controllers/aprobarProducto.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `idProducto=${idProducto}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'main.php';
        } else {
            alert('Error al aprobar.');
        }
    });
}

// Rechazar producto
function rechazarProducto(idProducto) {
    fetch('../../controllers/rechazarProducto.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `idProducto=${idProducto}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'main.php';
        } else {
            alert('Error al rechazar.');
        }
    });
}

