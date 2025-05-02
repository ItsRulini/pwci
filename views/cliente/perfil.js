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


document.getElementById("btnAbrirPopup").addEventListener("click", function() {
    document.getElementById("popup").style.display = "flex";
});

document.getElementById("btnCerrarPopup").addEventListener("click", function() {
    document.getElementById("popup").style.display = "none";
});


// // Mostrar el pop-up al hacer clic en el ícono
// document.querySelectorAll(".headerLista .fas").forEach(function(icono) {
//     icono.addEventListener("click", function() {
//         alert("Hola, soy un pop-up de opciones.");
//         const popup = this.closest(".lista").querySelector(".pop-up-options");
//         if (popup) {
//             popup.style.display = "block";
//         }
//     });
// });

// // Cerrar el pop-up al hacer clic en la "X"
// document.querySelectorAll(".close").forEach(function(btn) {
//     btn.addEventListener("click", function() {
//         const popup = this.closest(".pop-up-options");
//         popup.style.display = "none";
//     });
// });

// Mostrar el pop-up al hacer clic en el ícono
document.querySelectorAll(".headerLista .fas").forEach(function(icono) {
    icono.addEventListener("click", function(event) {
        event.stopPropagation(); // Para que no se cierre inmediatamente al hacer click fuera

        // Cerrar otros pop-ups abiertos
        document.querySelectorAll(".pop-up-options").forEach(function(popup) {
            popup.style.display = "none";
        });

        const popup = this.closest(".lista").querySelector(".pop-up-options");
        if (popup) {
            // Obtener posición del ícono
            const rect = this.getBoundingClientRect();
            popup.style.left = (rect.left + window.scrollX - popup.offsetWidth - 150) + "px"; // 10px a la izquierda del ícono
            popup.style.top = (rect.top + window.scrollY) + "px"; // a la misma altura del ícono
            popup.style.display = "block";
        }
    });
});

// Cerrar el pop-up al hacer clic en la "X"
document.querySelectorAll(".close").forEach(function(btn) {
    btn.addEventListener("click", function(event) {
        event.stopPropagation();
        const popup = this.closest(".pop-up-options");
        popup.style.display = "none";
    });
});

// También cerrar si el usuario hace click fuera del pop-up
document.addEventListener("click", function() {
    document.querySelectorAll(".pop-up-options").forEach(function(popup) {
        popup.style.display = "none";
    });
});


// Detectar clic en el botón "Eliminar lista"
document.querySelectorAll("#btnEliminarLista").forEach(function(botonEditar) {
    botonEditar.addEventListener("click", function() {
        const lista = this.closest(".lista"); // Encuentra la lista donde se hizo clic
        eliminarLista(lista);
    });
});

function eliminarLista(lista) {
    const confirmacion = confirm("¿Estás seguro de que quieres eliminar esta lista?");
    if (confirmacion) {
        lista.remove(); // Elimina solo si el usuario acepta
        alert("Lista eliminada.");
    } else {
        // Opcional: mensaje si no se elimina
        console.log("Eliminación cancelada.");
    }
}



// Detectar clic en el botón "Editar lista"
document.querySelectorAll("#btnEditarLista").forEach(function(botonEditar) {
    botonEditar.addEventListener("click", function() {
        const lista = this.closest(".lista"); // Encuentra la lista donde se hizo clic
        abrirPopupEditarLista(lista);
    });
});


// Función para abrir el popup de edición
function abrirPopupEditarLista(lista) {
    // Mostrar el pop-up
    const popup = document.getElementById("popupEditarLista");
    popup.style.display = "block";

    // Cargar productos actuales
    const productos = lista.querySelectorAll(".contenidoLista .producto");
    const listaProductosEditar = document.getElementById("listaProductosEditar");
    listaProductosEditar.innerHTML = ""; // Limpiar anterior

    productos.forEach(function(producto) {
        const item = document.createElement("li");
        item.classList.add("producto-editar");

        const nombreProducto = producto.querySelector(".info span").textContent;
        const precioProducto = producto.querySelector(".info p").textContent; // Obtener el precio (si es necesario)    

        item.innerHTML = `
            <span>${nombreProducto}</span>
            <p style="display: none;">${precioProducto}</p>
            <i class="fas fa-trash eliminarProducto" style="cursor: pointer; color: red; margin-left: 10px;"></i>
        `;

        listaProductosEditar.appendChild(item);
    });

    // Permitir eliminar productos
    document.querySelectorAll(".eliminarProducto").forEach(function(icono) {
        icono.addEventListener("click", function() {
            this.parentElement.remove(); // Quita el <li> del producto
        });
    });

    // Guardar cambios al hacer clic
    document.getElementById("btnGuardarCambios").onclick = function() {
        guardarCambios(lista);
    };
}

// Cerrar el pop-up
document.getElementById("btnCerrarEditarLista").addEventListener("click", function() {
    document.getElementById("popupEditarLista").style.display = "none";
});

// Guardar los cambios aplicados
function guardarCambios(lista) {
    let privacidad = document.getElementById("privadaLista").checked ? "Privada" : "Pública";
    console.log("Nueva privacidad:", privacidad);

    // Actualizar productos visibles
    const productosEditados = document.querySelectorAll("#listaProductosEditar .producto-editar");
    const contenedorProductos = lista.querySelector(".contenidoLista");
    contenedorProductos.innerHTML = ""; // Limpiar productos actuales

    productosEditados.forEach(function(productoEditado) {

        const nombre = productoEditado.querySelector("span").textContent.trim();
        const precio = productoEditado.querySelector("p").textContent; // Obtener el precio (si es necesario) 
        

        const nuevoProducto = document.createElement("li");
        nuevoProducto.classList.add("producto");
        nuevoProducto.innerHTML = `
            <img src="../../multimedia/default/default.jpg" alt="${nombre}">
            <div class="info">
                <span>${nombre}</span>
                <p>${precio}</p>
            </div>
        `;
        contenedorProductos.appendChild(nuevoProducto);
    });

    // Cerrar el popup
    document.getElementById("popupEditarLista").style.display = "none";

    alert("Cambios guardados.");
}

