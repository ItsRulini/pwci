// Definición de cargarCategorias (puede estar fuera o dentro del DOMContentLoaded si se ajusta el alcance)
function cargarCategorias() {
    const selectCategoria = document.getElementById("categoria");
    const cathegoryCarousel = document.getElementById("cathegory-carousel"); // Necesario para saber qué hay en el carrusel
    const contenedorMensajeCategorias = document.getElementById("mensajeNoCategorias");

    if (!selectCategoria || !cathegoryCarousel) {
        // console.error("Elementos select#categoria o cathegory-carousel no encontrados. No se pueden cargar categorías.");
        return;
    }

    // 1. Guardar los valores de las categorías actualmente en el carrusel
    const valoresEnCarrusel = new Set(); // Usamos un Set para eficiencia y evitar duplicados
    cathegoryCarousel.querySelectorAll(".categoria-item").forEach(item => {
        valoresEnCarrusel.add(item.dataset.value);
    });

    fetch('../../controllers/getCategorias.php')
        .then(response => response.json())
        .then(data => {
            // Guardar el valor actual del select si algo está seleccionado y no en carrusel (menos común en tu flujo)
            // const valorSeleccionadoActual = selectCategoria.value; 

            selectCategoria.innerHTML = ''; // Limpiar opciones existentes del select

            let hayCategoriasParaMostrarEnSelect = false;

            if (data.success && data.data.length > 0) {
                data.data.forEach(cat => {
                    const valorOpcion = cat.nombre.toLowerCase().replace(/\s+/g, "-");
                    
                    // 3. Solo añadir al select si NO está ya en el carrusel
                    if (!valoresEnCarrusel.has(valorOpcion)) {
                        const option = document.createElement("option");
                        option.value = cat.idCategoria;
                        option.textContent = cat.nombre;
                        option.title = cat.descripcion;
                        selectCategoria.appendChild(option);
                        hayCategoriasParaMostrarEnSelect = true;
                    }
                });
                if (contenedorMensajeCategorias) {
                     // Ocultar mensaje si el select tiene opciones o el carrusel tiene items
                    if (hayCategoriasParaMostrarEnSelect || valoresEnCarrusel.size > 0) {
                        contenedorMensajeCategorias.style.display = 'none';
                    } else {
                        // Este caso es si la BD tiene categorías pero todas están en el carrusel
                        // O si la BD no tiene y el carrusel tampoco.
                        contenedorMensajeCategorias.textContent = "Todas las categorías disponibles ya están seleccionadas o no hay más disponibles.";
                        contenedorMensajeCategorias.style.display = 'block';
                    }
                }

            } else { // No hay categorías en la BD o hubo un error
                if (valoresEnCarrusel.size === 0) { // Si tampoco hay nada en el carrusel
                    const option = document.createElement("option");
                    option.disabled = true;
                    option.selected = true;
                    option.textContent = "No hay categorías";
                    selectCategoria.appendChild(option);
                    if (contenedorMensajeCategorias) {
                        contenedorMensajeCategorias.textContent = data.message || "No hay categorías para mostrar. Añade una nueva.";
                        contenedorMensajeCategorias.style.display = 'block';
                    }
                } else {
                    // Hay ítems en el carrusel, pero la BD no devolvió nada más. El select quedará vacío.
                    if (contenedorMensajeCategorias) contenedorMensajeCategorias.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error cargando categorías periódicamente:', error);
            // No modificar drásticamente el select en caso de error de red para no perder el trabajo del usuario
            // Podrías mostrar un pequeño mensaje de error no intrusivo
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
    const categoriaSelect = document.getElementById("categoria");
    const cathegoryCarousel = document.getElementById("cathegory-carousel");
    const previewContainer = document.getElementById("preview-carousel");
    
    const form = document.getElementById("formDashboard");

    // --- INICIALIZACIÓN ---
    if (form) { // Solo si el formulario principal existe
        cargarCategorias(); // Cargar categorías al inicio

        const intervaloActualizacionCategorias = 30000; // 30 segundos (ajusta según necesidad)
        setInterval(cargarCategorias, intervaloActualizacionCategorias);
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
            e.preventDefault();
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
            
            //--- Preparar datos para el envío ---
            const formData = new FormData(form);

            fetch('../../controllers/registrarProducto.php', {
                method: 'POST',
                body: formData // FormData se encarga del enctype="multipart/form-data"
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || "Producto publicado correctamente.");
                    form.reset(); // Limpiar el formulario
                    
                    // Limpiar carruseles visuales
                    if (cathegoryCarousel) cathegoryCarousel.innerHTML = '';
                    if (previewContainer) previewContainer.innerHTML = ''; // 'previewContainer' es el ID de tu carrusel de imágenes/video
                    
                    // Recargar categorías en el select por si alguna nueva se añadió por otro lado
                    // o para resetearlo a su estado inicial limpio.
                    cargarCategorias(); 
                } else {
                    alert("Error: " + (data.message || "No se pudo registrar el producto."));
                }
            })
            .catch(error => {
                console.error('Error al registrar el producto:', error);
                alert('Ocurrió un error de conexión o en el servidor.');
            });
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
                    // Antes de añadir, verificar si ya existe en el select para evitar duplicados
                    // si la lógica de carga periódica se cruza de mala manera (poco probable con el set).
                    let yaExisteEnSelect = false;
                    for(let i=0; i < categoriaSelect.options.length; i++) {
                        if(categoriaSelect.options[i].value === value) {
                            yaExisteEnSelect = true;
                            break;
                        }
                    }
                    if (!yaExisteEnSelect) {
                        categoriaSelect.appendChild(newOption);
                    }
                });
                cathegoryCarousel.appendChild(item);
                select.removeChild(option);

                // Actualizar mensaje de "No hay categorías" si aplica
                const contenedorMensajeCategorias = document.getElementById("mensajeNoCategorias");
                if (contenedorMensajeCategorias) {
                    if (categoriaSelect.options.length === 0 && cathegoryCarousel.querySelectorAll(".categoria-item").length === 0) {
                        contenedorMensajeCategorias.textContent = "No hay categorías para mostrar. Añade una nueva.";
                        contenedorMensajeCategorias.style.display = 'block';
                    } else if (categoriaSelect.options.length === 0 && cathegoryCarousel.querySelectorAll(".categoria-item").length > 0) {
                        // Todas las disponibles están en el carrusel
                        contenedorMensajeCategorias.style.display = 'none'; 
                    } else {
                        contenedorMensajeCategorias.style.display = 'none';
                    }
                }
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