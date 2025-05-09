// Definir cargarCategorias en un ámbito accesible si se llama desde fuera del DOMContentLoaded principal
// o si prefieres mantenerla aquí arriba por claridad.
function cargarCategorias() {
    const selectCategoria = document.getElementById("categoria");
    const contenedorMensajeCategorias = document.getElementById("mensajeNoCategorias");

    // Asegurarse de que los elementos existan antes de usarlos
    if (!selectCategoria) {
        console.error("Elemento select#categoria no encontrado.");
        return;
    }

    fetch('../../controllers/getCategorias.php')
        .then(response => response.json())
        .then(data => {
            selectCategoria.innerHTML = ''; // Limpiar opciones existentes

            if (data.success && data.data.length > 0) {
                data.data.forEach(cat => {
                    const option = document.createElement("option");
                    // Usar el idCategoria como valor es generalmente más robusto
                    // option.value = cat.idCategoria; // Si el backend lo envía y lo necesitas
                    //option.value = cat.nombre.toLowerCase().replace(/\s+/g, "-"); // Valor actual
                    option.valur = cat.idCategoria;
                    option.textContent = cat.nombre;
                    option.title = cat.descripcion;
                    selectCategoria.appendChild(option);
                });
                if (contenedorMensajeCategorias) contenedorMensajeCategorias.style.display = 'none';
            } else {
                const option = document.createElement("option");
                option.disabled = true;
                option.selected = true;
                option.textContent = "No hay categorías";
                selectCategoria.appendChild(option);
                if (contenedorMensajeCategorias) {
                    contenedorMensajeCategorias.textContent = data.message || "No hay categorías para mostrar. Añade una nueva.";
                    contenedorMensajeCategorias.style.display = 'block';
                }
            }
        })
        .catch(error => {
            console.error('Error cargando categorías:', error);
            if (selectCategoria) {
                selectCategoria.innerHTML = '<option disabled selected>Error al cargar</option>';
            }
            if (contenedorMensajeCategorias) {
                contenedorMensajeCategorias.textContent = "Error al cargar categorías.";
                contenedorMensajeCategorias.style.display = 'block';
            }
        });
}

// ÚNICO DOMContentLoaded listener principal
document.addEventListener("DOMContentLoaded", function () {
    // Selectores de elementos del DOM (declarados una vez)
    const tipoPublicacionRadios = document.querySelectorAll('input[name="tipo"]');
    const precioInput = document.getElementById("precio");
    const cantidadInput = document.getElementById("cantidad");
    const disponibilidadMsg = document.getElementById("sinStock");
    const precioSelectMsg = document.getElementById("sinPrecio");
    const imagenesInput = document.getElementById("input-file");
    const videoInput = document.getElementById("input-video");
    const form = document.getElementById("formDashboard");
    const categoriaSelect = document.getElementById("categoria");
    const cathegoryCarousel = document.getElementById("cathegory-carousel");
    const previewContainer = document.getElementById("preview-carousel");

    // --- INICIALIZACIÓN ---
    if (form) { // Solo si el formulario principal existe
        cargarCategorias(); // Cargar categorías al inicio
    } else {
        // console.warn("El formulario principal 'formDashboard' no se encontró. Algunas funcionalidades pueden no estar activas.");
        // No hacer nada si el formulario no está presente, evita errores en otras páginas si este JS se carga globalmente.
        return; 
    }


    // --- EVENT LISTENERS PARA CAMPOS DEL FORMULARIO (fuera del submit) ---
    if (tipoPublicacionRadios.length > 0 && precioInput && cantidadInput) {
        tipoPublicacionRadios.forEach(radio => {
            radio.addEventListener("change", () => {
                if (radio.value === "venta") {
                    precioInput.style.display = "block";
                    cantidadInput.style.display = "block";
                    if(disponibilidadMsg) disponibilidadMsg.style.display = "none";
                    if(precioSelectMsg) precioSelectMsg.style.display = "none";
                } else {
                    precioInput.style.display = "none";
                    cantidadInput.style.display = "none";
                    if(disponibilidadMsg) disponibilidadMsg.style.display = "none";
                    if(precioSelectMsg) precioSelectMsg.style.display = "none";
                    precioInput.value = "";
                    cantidadInput.value = "";
                }
            });
        });
    }

    if (cantidadInput && disponibilidadMsg) {
        cantidadInput.addEventListener("input", () => {
            const cantidad = parseInt(cantidadInput.value);
            disponibilidadMsg.style.display = (!isNaN(cantidad) && cantidad <= 0) ? "block" : "none";
        });
    }

    if (precioInput && precioSelectMsg) {
        precioInput.addEventListener("input", () => {
            const precio = parseFloat(precioInput.value);
            precioSelectMsg.style.display = (!isNaN(precio) && precio <= 0) ? "block" : "none";
        });
    }

    // --- VALIDACIÓN Y ENVÍO DEL FORMULARIO PRINCIPAL ---
    if (form) {
        form.addEventListener("submit", function (e) {
            // Validación de Categorías
            if (cathegoryCarousel && cathegoryCarousel.querySelectorAll(".categoria-item").length === 0) {
                alert("Debes seleccionar al menos una categoría para el producto.");
                e.preventDefault(); return;
            }

            // Validación Tipo de Publicación
            const tipoSeleccionado = document.querySelector('input[name="tipo"]:checked');
            if (!tipoSeleccionado) {
                alert("Debes seleccionar un tipo de publicación.");
                e.preventDefault(); return;
            }

            // Validar imágenes
            if (imagenesInput && imagenesInput.files.length < 3) {
                alert("Debes subir al menos 3 imágenes.");
                e.preventDefault(); return;
            }

            // Validar video
            if (videoInput && videoInput.files.length < 1) {
                alert("Debes subir al menos 1 video.");
                e.preventDefault(); return;
            }

            // Validar precio y cantidad si es venta
            if (tipoSeleccionado.value === "venta") {
                if (!precioInput || !precioInput.value || parseFloat(precioInput.value) <= 0) {
                    alert("Debes ingresar un precio válido para venta.");
                    if(precioInput) precioInput.focus();
                    e.preventDefault(); return;
                }
                if (!cantidadInput || !cantidadInput.value || parseInt(cantidadInput.value) <= 0) {
                    alert("Debes ingresar una cantidad válida para venta.");
                    if(cantidadInput) cantidadInput.focus();
                    e.preventDefault(); return;
                }
            }

            // Re-popular el select de categorías antes del envío
            if (cathegoryCarousel && categoriaSelect) {
                // Limpiar select por si acaso (aunque la lógica del carrusel ya debería hacerlo)
                // categoriaSelect.innerHTML = ''; 
                cathegoryCarousel.querySelectorAll(".categoria-item").forEach(item => {
                    const option = document.createElement("option");
                    option.value = item.dataset.value;
                    option.textContent = item.textContent;
                    option.selected = true;
                    categoriaSelect.appendChild(option);
                });
            }
            
            // Si todo está bien, el formulario se enviará
            // Aquí podrías hacer el fetch para enviar el producto si no usas el action del form
            // Por ahora, asumo que el action del form HTML se encarga de enviar los datos.
        });
    }

    // --- FUNCIONALIDAD PREVISUALIZACIÓN IMÁGENES/VIDEO ---
    if (imagenesInput && videoInput && previewContainer) {
        function clearPreview() {
            previewContainer.innerHTML = "";
        }
        function createPreview(file, type) {
            const item = document.createElement("div");
            item.classList.add("carousel-item");
            if (type === "image") {
                const img = document.createElement("img");
                img.src = URL.createObjectURL(file);
                item.appendChild(img);
            } else if (type === "video") {
                const video = document.createElement("video");
                video.src = URL.createObjectURL(file);
                video.controls = true;
                item.appendChild(video);
            }
            previewContainer.appendChild(item);
        }
        imagenesInput.addEventListener("change", function () {
            clearPreview();
            Array.from(this.files).forEach(file => {
                if (file.type.startsWith("image/")) createPreview(file, "image");
            });
            if (videoInput.files.length > 0) createPreview(videoInput.files[0], "video");
        });
        videoInput.addEventListener("change", function () {
            clearPreview();
            Array.from(imagenesInput.files).forEach(file => { // Corregido para usar imagenesInput
                if (file.type.startsWith("image/")) createPreview(file, "image");
            });
            if (this.files.length > 0 && this.files[0].type.startsWith("video/")) {
                createPreview(this.files[0], "video");
            }
        });
    }

    // --- FUNCIONALIDAD CARRUSEL DE CATEGORÍAS (Select -> Carrusel y viceversa) ---
    if (categoriaSelect && cathegoryCarousel) {
        categoriaSelect.addEventListener("change", function () {
            Array.from(this.selectedOptions).forEach(option => {
                const value = option.value;
                const text = option.textContent;
                const title = option.title;

                // Evitar duplicados en el carrusel si por alguna razón la opción no se eliminó
                if (cathegoryCarousel.querySelector(`.categoria-item[data-value="${value}"]`)) {
                    select.removeChild(option); // Solo remover del select
                    return;
                }

                const item = document.createElement("div");
                item.classList.add("carousel-item", "categoria-item");
                item.textContent = text;
                item.dataset.value = value;
                item.title = title;

                item.addEventListener("click", () => {
                    cathegoryCarousel.removeChild(item);
                    const newOption = document.createElement("option");
                    newOption.value = value;
                    newOption.textContent = text;
                    newOption.title = title;
                    categoriaSelect.appendChild(newOption);
                });
                cathegoryCarousel.appendChild(item);
                select.removeChild(option);
            });
        });
    }

    // --- MODAL PARA NUEVAS CATEGORÍAS (Lógica de Fetch) ---
    const modal = document.getElementById("categoryModal");
    const openBtn = document.getElementById("openCategoryModal");
    const closeBtn = document.getElementById("closeCategoryModal");
    const newCategoryForm = document.getElementById("newCategoryForm");

    if (openBtn && modal) {
        openBtn.addEventListener("click", () => { modal.style.display = "block"; });
    }
    if (closeBtn && modal) {
        closeBtn.addEventListener("click", () => { modal.style.display = "none"; });
    }
    if (modal) {
        window.addEventListener("click", (e) => {
            if (e.target === modal) modal.style.display = "none";
        });
    }
    if (newCategoryForm && modal) {
        newCategoryForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const nameInput = document.getElementById("newCategoryName");
            const descriptionInput = document.getElementById("newCategoryDescription");
            const name = nameInput.value.trim();
            const description = descriptionInput.value.trim();

            if (!name || !description) {
                alert("El nombre y la descripción de la categoría son obligatorios."); return;
            }

            const formData = new FormData();
            formData.append('nombre', name);
            formData.append('descripcion', description);

            fetch('../../controllers/registrarCategoria.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        newCategoryForm.reset();
                        modal.style.display = "none";
                        cargarCategorias(); // RECARGAR CATEGORÍAS
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error al registrar categoría:', error);
                    alert('Ocurrió un error al intentar registrar la categoría.');
                });
        });
    }
});

// document.addEventListener("DOMContentLoaded", function () {
//     const tipoPublicacionRadios = document.querySelectorAll('input[name="tipo"]');
//     const precioInput = document.getElementById("precio");
//     const cantidadInput = document.getElementById("cantidad");
//     const disponibilidadMsg = document.getElementById("sinStock");
//     const precioSelectMsg = document.getElementById("sinPrecio");
//     const imagenesInput = document.getElementById("input-file");
//     const videoInput = document.getElementById("input-video");
//     const form = document.getElementById("formDashboard");
//     const categoriaSelect = document.getElementById("categoria"); // El select de categorías
//     const cathegoryCarousel = document.getElementById("cathegory-carousel"); // El carrusel de categorías

//     cargarCategorias(); // Cargar categorías al inicio

//     // Validación en el envío del formulario
//     form.addEventListener("submit", function (e) {
//         // Mostrar u ocultar campos según la opción elegida (venta o cotización)
//         tipoPublicacionRadios.forEach(radio => {
//             radio.addEventListener("change", () => {
//                 if (radio.value === "venta") {
//                     precioInput.style.display = "block";
//                     cantidadInput.style.display = "block";
//                     disponibilidadMsg.style.display = "none";
//                     precioSelectMsg.style.display = "none";
//                 } else {
//                     precioInput.style.display = "none";
//                     cantidadInput.style.display = "none";
//                     disponibilidadMsg.style.display = "none";
//                     precioSelectMsg.style.display = "none";
//                     precioInput.value = "";
//                     cantidadInput.value = "";
//                 }
//             });
//         });

//         // Mostrar mensaje si la cantidad es 0
//         cantidadInput.addEventListener("input", () => {
//             const cantidad = parseInt(cantidadInput.value);

//             if (!isNaN(cantidad) && cantidad <= 0) {
//                 disponibilidadMsg.style.display = "block";
//             } else {
//                 disponibilidadMsg.style.display = "none";
//             }
//         });

//         precioInput.addEventListener("input", () => {
//             const precio = parseFloat(precioInput.value);

//             if (!isNaN(precio) && precio <= 0) {
//                 precioSelectMsg.style.display = "block";
//             } else {
//                 precioSelectMsg.style.display = "none";
//             }
//         });
//          // --- INICIO: Validación de Categorías ---
//         const categoriasEnCarrusel = cathegoryCarousel.querySelectorAll(".categoria-item");
        
//         if (categoriasEnCarrusel.length === 0) {
//             alert("Debes seleccionar al menos una categoría para el producto.");
//             e.preventDefault(); // Detener el envío del formulario
//             return; // Salir de la función
//         }
//         // --- FIN: Validación de Categorías ---

//         const tipoSeleccionado = document.querySelector('input[name="tipo"]:checked');
//         if (!tipoSeleccionado) {
//             alert("Debes seleccionar un tipo de publicación.");
//             e.preventDefault();
//             return;
//         }

//         // Validar imágenes
//         if (imagenesInput.files.length < 3) {
//             alert("Debes subir al menos 3 imágenes.");
//             e.preventDefault();
//             return;
//         }

//         // Validar video
//         if (videoInput.files.length < 1) {
//             alert("Debes subir al menos 1 video.");
//             e.preventDefault();
//             return;
//         }

//         // Validar precio solo si es venta
//         const ventaSeleccionada = document.querySelector('input[name="tipo"]:checked').value === "venta";
//         if (ventaSeleccionada) {
//             if (!precioInput.value || parseFloat(precioInput.value) <= 0) {
//                 alert("Debes ingresar un precio válido para venta.");
//                 precioInput.focus();
//                 e.preventDefault();
//                 return;
//             }

//             if (!cantidadInput.value || parseInt(cantidadInput.value) <= 0) {
//                 alert("Debes ingresar una cantidad válida para venta.");
//                 cantidadInput.focus();
//                 e.preventDefault();
//                 return;
//             }
//         }

//         categoriasEnCarrusel.forEach(item => {
//             const option = document.createElement("option");
//             option.value = item.dataset.value; // El valor de la categoría
//             option.textContent = item.textContent; // El texto visible de la categoría
//             option.selected = true; // Marcar como seleccionada para que se envíe
//             categoriaSelect.appendChild(option);
//         });
//     });

//     // const imageInput = document.getElementById("input-file");
//     // const videoInput = document.getElementById("input-video");
//     const previewContainer = document.getElementById("preview-carousel");

//     function clearPreview() {
//         previewContainer.innerHTML = "";
//     }

//     function createPreview(file, type) {
//         const item = document.createElement("div");
//         item.classList.add("carousel-item");

//         if (type === "image") {
//             const img = document.createElement("img");
//             img.src = URL.createObjectURL(file);
//             item.appendChild(img);
//         } else if (type === "video") {
//             const video = document.createElement("video");
//             video.src = URL.createObjectURL(file);
//             video.controls = true;
//             item.appendChild(video);
//         }

//         previewContainer.appendChild(item);
//     }

//     imagenesInput.addEventListener("change", function () {
//         clearPreview();
//         Array.from(this.files).forEach(file => {
//             if (file.type.startsWith("image/")) {
//                 createPreview(file, "image");
//             }
//         });

//         // Mostrar video si ya fue seleccionado
//         if (videoInput.files.length > 0) {
//             createPreview(videoInput.files[0], "video");
//         }
//     });

//     videoInput.addEventListener("change", function () {
//         // Volvemos a mostrar todo para actualizar el carrusel
//         clearPreview();

//         Array.from(imageInput.files).forEach(file => {
//             if (file.type.startsWith("image/")) {
//                 createPreview(file, "image");
//             }
//         });

//         if (this.files.length > 0 && this.files[0].type.startsWith("video/")) {
//             createPreview(this.files[0], "video");
//         }
//     });

//     const select = document.getElementById("categoria");
//     const carousel = document.getElementById("cathegory-carousel");

//     // Cada vez que el select cambia
//     select.addEventListener("change", function () {
//         const selectedOptions = Array.from(this.selectedOptions);

//         selectedOptions.forEach(option => {
//             const value = option.value;
//             const text = option.textContent;
//             const title = option.title; // Obtener el título (descripción)

//             // Crear item en el carrusel
//             const item = document.createElement("div");
//             item.classList.add("carousel-item", "categoria-item");
//             item.textContent = text;
//             item.dataset.value = value;
//             item.title = title; // Agregar título para el tooltip

//             // Al hacer clic en el item, se regresa al select
//             item.addEventListener("click", () => {
//                 // Remover del carrusel
//                 carousel.removeChild(item);

//                 // Volver a agregar al select
//                 const newOption = document.createElement("option");
//                 newOption.value = value;
//                 newOption.textContent = text;
//                 newOption.title = title; // Agregar descripción como título
//                 select.appendChild(newOption);
//             });

//             // Agregar al carrusel
//             carousel.appendChild(item);

//             // Remover del select original
//             select.removeChild(option);
//         });
//     });

//     const modal = document.getElementById("categoryModal");
//     const openBtn = document.getElementById("openCategoryModal");
//     const closeBtn = document.getElementById("closeCategoryModal");
//     const newCategoryForm = document.getElementById("newCategoryForm"); // Renombrada variable
//     //const selectCategoria = document.getElementById("categoria"); // Ya definida arriba

//     if(openBtn) { // Asegurarse que el botón existe antes de añadir listener
//         openBtn.addEventListener("click", () => {
//             if(modal) modal.style.display = "block";
//         });
//     }

//     if(closeBtn) {
//         closeBtn.addEventListener("click", () => {
//             if(modal) modal.style.display = "none";
//         });
//     }

//     window.addEventListener("click", (e) => {
//         if (modal && e.target === modal) {
//             modal.style.display = "none";
//         }
//     });

//     if(newCategoryForm) {
//         newCategoryForm.addEventListener("submit", (e) => {
//             e.preventDefault();
//             const nameInput = document.getElementById("newCategoryName");
//             const descriptionInput = document.getElementById("newCategoryDescription");
            
//             const name = nameInput.value.trim();
//             const description = descriptionInput.value.trim();

//             if (!name || !description) {
//                 alert("El nombre y la descripción de la categoría son obligatorios.");
//                 return;
//             }

//             const formData = new FormData();
//             formData.append('nombre', name);
//             formData.append('descripcion', description);

//             fetch('../../controllers/registrarCategoria.php', { // Ajusta la ruta si es necesario
//                 method: 'POST',
//                 body: formData
//             })
//             .then(response => response.json())
//             .then(data => {
//                 if (data.success) {
//                     alert(data.message);
//                     newCategoryForm.reset();
//                     if(modal) modal.style.display = "none";
//                     cargarCategorias(); // Recargar la lista de categorías para incluir la nueva
//                 } else {
//                     alert("Error: " + data.message);
//                 }
//             })
//             .catch(error => {
//                 console.error('Error al registrar categoría:', error);
//                 alert('Ocurrió un error al intentar registrar la categoría.');
//             });
//         });
//     }

// });


// function cargarCategorias() {
//     const selectCategoria = document.getElementById("categoria");
//     const contenedorMensajeCategorias = document.getElementById("mensajeNoCategorias"); // Necesitarás añadir este div en tu HTML

//     fetch('../../controllers/getCategorias.php') // Ajusta la ruta si es necesario
//         .then(response => response.json())
//         .then(data => {
//             selectCategoria.innerHTML = ''; // Limpiar opciones existentes

//             if (data.success && data.data.length > 0) {
//                 data.data.forEach(cat => {
//                     const option = document.createElement("option");
//                     option.value = cat.nombre.toLowerCase().replace(/\s+/g, "-"); // Crear valor amigable para URL/ID
//                     option.textContent = cat.nombre;
//                     option.title = cat.descripcion; // Para el tooltip
//                     // Si necesitas el ID de la categoría para algo más, guárdalo:
//                     // option.dataset.idCategoria = cat.idCategoria; 
//                     selectCategoria.appendChild(option);
//                 });
//                 if (contenedorMensajeCategorias) contenedorMensajeCategorias.style.display = 'none';
//             } else {
//                 // No hay categorías o hubo un error al obtenerlas
//                 const option = document.createElement("option");
//                 option.disabled = true;
//                 option.selected = true;
//                 option.textContent = "No hay categorías disponibles";
//                 selectCategoria.appendChild(option);
//                 if (contenedorMensajeCategorias) {
//                     contenedorMensajeCategorias.textContent = data.message || "No hay categorías para mostrar. Añade una nueva.";
//                     contenedorMensajeCategorias.style.display = 'block';
//                 }
//             }
//         })
//         .catch(error => {
//             console.error('Error cargando categorías:', error);
//             selectCategoria.innerHTML = '<option disabled selected>Error al cargar categorías</option>';
//             if (contenedorMensajeCategorias) {
//                 contenedorMensajeCategorias.textContent = "Error al cargar categorías.";
//                 contenedorMensajeCategorias.style.display = 'block';
//             }
//         });
// }
