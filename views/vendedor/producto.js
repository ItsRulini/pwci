document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const idProducto = params.get('idProducto');

    if (!idProducto) {
        alert('Producto no encontrado.');
        window.location.href = 'main.php';
        return;
    }

    // Obtener información principal del producto
    fetch(`../../controllers/getProductoPorId.php?idProducto=${idProducto}`)
        .then(response => response.json())
        .then(data => {
            if (data.success === false) {
                alert('Producto no encontrado.');
                window.location.href = 'main.php';
                return;
            }

            // --- Llenar info general ---
            document.querySelector('.info h2').textContent = data.nombre;
            document.querySelector('.info p:nth-of-type(1)').textContent = `$${data.precio} MXN`;
            document.querySelector('.info p:nth-of-type(2)').textContent = data.descripcion || 'Sin descripción disponible.';

            // --- Leyenda de estatus ---
            const infoSeller = document.querySelector('.info-seller p');
            if (data.estatus === 'pendiente') {
                infoSeller.textContent = 'Estado: En revisión';
            } else if (data.estatus === 'aceptado') {
                infoSeller.textContent = 'Estado: Aprobado';
            } else if (data.estatus === 'rechazado') {
                infoSeller.textContent = 'Estado: Rechazado';
            } else {
                infoSeller.textContent = 'Estado desconocido';
            }
        });

    // Obtener multimedia del producto
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

            // Si no hay multimedia
            if (files.length === 0) {
                multimediaContainer.innerHTML = `
                    <img src="../../multimedia/default/default.jpg" alt="Imagen no disponible">
                `;
            }
        });

    // Obtener categorías del producto
    fetch(`../../controllers/getCategoriasProducto.php?idProducto=${idProducto}`)
        .then(response => response.json())
        .then(categorias => {
            const categoriasList = document.querySelector('.categorias');
            categoriasList.innerHTML = '';

            if (categorias.length === 0) {
                categoriasList.innerHTML = '<li class="categoria">Sin categorías</li>';
            } else {
                categorias.forEach(categoria => {
                    categoriasList.innerHTML += `<li class="categoria">${categoria}</li>`;
                });
            }
        });
});
