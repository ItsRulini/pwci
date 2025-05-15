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

                 // Generar HTML dependiendo del tipo
                 let botonAccion = "";

                 if (producto.tipo === "Venta") {
                     botonAccion = `<button onclick="agregarAlCarrito(${producto.idProducto})">Añadir al carrito</button>`;
                 } else if (producto.tipo === "Cotizacion") {
                     botonAccion = `<button disabled style="background-color: #ccc;">Enviar mensaje</button>`;
                 }

                 li.innerHTML = `
                     <img src="${imagen}" alt="${producto.nombreProducto}">
                     <div class="info">
                         <a href="producto.php?idProducto=${producto.idProducto}">${producto.nombreProducto}</a>
                         <p>${producto.tipo === 'Venta' ? `$${producto.precio} MXN` : 'Negociable'}</p>
                         ${botonAccion}
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

// // busqueda.js

// document.addEventListener("DOMContentLoaded", function () {
//     const lista = document.getElementById("ListaResultados");
//     const noResultados = document.getElementById("noResultados");

//     const params = new URLSearchParams(window.location.search);
//     const query = params.get("query") || "";
//     const categoria = params.get("categoria") || "";
//     const precioMin = params.get("precioMin") || "";
//     const precioMax = params.get("precioMax") || "";

//     cargarCategorias();
//     cargarResultados(query, categoria, precioMin, precioMax);

//     document.getElementById("filtrosForm").addEventListener("submit", function (e) {
//         e.preventDefault();

//         const nuevaCategoria = document.getElementById("categoria").value;
//         const nuevaMin = document.getElementById("precioMin").value;
//         const nuevaMax = document.getElementById("precioMax").value;

//         const nuevaURL = new URL(window.location.href);
//         nuevaURL.searchParams.set("query", query);
//         nuevaURL.searchParams.set("categoria", nuevaCategoria);
//         nuevaURL.searchParams.set("precioMin", nuevaMin);
//         nuevaURL.searchParams.set("precioMax", nuevaMax);

//         window.history.pushState({}, '', nuevaURL);

//         cargarResultados(query, nuevaCategoria, nuevaMin, nuevaMax);
//     });
// });

// function cargarCategorias() {
//     fetch("../../controllers/getCategoriasBuscador.php")
//         .then(res => res.json())
//         .then(data => {
//             const select = document.getElementById("categoria");
//             select.innerHTML = '<option value="">Todas</option>';
//             select.innerHTML += '<option value="Cotizacion">Cotización</option>';
//             data.forEach(categoria => {
//                 select.innerHTML += `<option value="${categoria.nombre}">${categoria.nombre}</option>`;
//             });
//         })
//         .catch(err => console.error("Error al cargar categorías:", err));
// }

// function cargarResultados(query, categoria, precioMin, precioMax) {
//     fetch(`../../controllers/buscarProductos.php?query=${encodeURIComponent(query)}&categoria=${encodeURIComponent(categoria)}&precioMin=${precioMin}&precioMax=${precioMax}`)
//         .then(res => res.json())
//         .then(data => renderResultados(data))
//         .catch(err => {
//             console.error("Error en búsqueda:", err);
//             document.getElementById("ListaResultados").innerHTML = "";
//             document.getElementById("noResultados").style.display = "block";
//         });
// }

// function renderResultados(productos) {
//     const lista = document.getElementById("ListaResultados");
//     const noResultados = document.getElementById("noResultados");

//     lista.innerHTML = "";
//     if (productos.length === 0) {
//         noResultados.style.display = "block";
//         return;
//     }
//     noResultados.style.display = "none";

//     productos.forEach(producto => {
//         const li = document.createElement("li");
//         li.classList.add("producto");

//         const imagen = producto.imagenPrincipal 
//             ? `../../multimedia/productos/${producto.idProducto}/${producto.imagenPrincipal}`
//             : `../../multimedia/default/default.jpg`;

//         const contenido = `
//             <img src="${imagen}" alt="${producto.nombreProducto}">
//             <div class="info">
//                 <a href="producto.php?idProducto=${producto.idProducto}">${producto.nombreProducto}</a>
//                 <p>${producto.tipo === 'Venta' ? `$${producto.precio} MXN` : 'Negociable'}</p>
//                 ${producto.tipo === 'Venta'
//                     ? `<button onclick="agregarAlCarrito(${producto.idProducto})">Añadir al carrito</button>`
//                     : `<button disabled>Enviar mensaje</button>`
//                 }
//             </div>
//         `;

//         li.innerHTML = contenido;
//         lista.appendChild(li);
//     });
// }

// function agregarAlCarrito(idProducto) {
//     const formData = new FormData();
//     formData.append('idProducto', idProducto);

//     fetch('../../controllers/agregarCarrito.php', {
//         method: 'POST',
//         body: formData
//     })
//     .then(response => response.json())
//     .then(data => {
//         if (data.success) {
//             alert(data.message || "Artículo agregado al carrito.");
//         } else {
//             alert("Error: " + (data.message || "No se pudo agregar al carrito."));
//         }
//     })
//     .catch(error => {
//         console.error('Error al agregar al carrito:', error);
//         alert('Ocurrió un error al conectar con el servidor.');
//     });
// }
