document.addEventListener("DOMContentLoaded", function () {
    const tipoPublicacionRadios = document.querySelectorAll('input[name="tipo"]');
    const precioInput = document.getElementById("precio");
    const cantidadInput = document.getElementById("cantidad");
    const disponibilidadMsg = document.getElementById("sinStock");
    const precioSelectMsg = document.getElementById("sinPrecio");
    const form = document.getElementById("formDashboard");
    const imagenesInput = document.getElementById("input-file");
    const videoInput = document.getElementById("input-video");
    const categoriaSelect = document.getElementById("categoria"); // El select de categorías
    const cathegoryCarousel = document.getElementById("cathegory-carousel"); // El carrusel de categorías

    // Validar que se seleccione un tipo de publicación
    // form.addEventListener("submit", function (e) {
    //     const tipoSeleccionado = document.querySelector('input[name="tipo"]:checked');
    //     if (!tipoSeleccionado) {
    //         alert("Debes seleccionar un tipo de publicación.");
    //         e.preventDefault();
    //         return;
    //     }
    // });

    // Mostrar u ocultar campos según la opción elegida (venta o cotización)
    tipoPublicacionRadios.forEach(radio => {
        radio.addEventListener("change", () => {
            if (radio.value === "venta") {
                precioInput.style.display = "block";
                cantidadInput.style.display = "block";
                disponibilidadMsg.style.display = "none";
                precioSelectMsg.style.display = "none";
            } else {
                precioInput.style.display = "none";
                cantidadInput.style.display = "none";
                disponibilidadMsg.style.display = "none";
                precioSelectMsg.style.display = "none";
                precioInput.value = "";
                cantidadInput.value = "";
            }
        });
    });

    // Mostrar mensaje si la cantidad es 0
    cantidadInput.addEventListener("input", () => {
        const cantidad = parseInt(cantidadInput.value);

        if (!isNaN(cantidad) && cantidad <= 0) {
            disponibilidadMsg.style.display = "block";
        } else {
            disponibilidadMsg.style.display = "none";
        }
    });

    precioInput.addEventListener("input", () => {
        const precio = parseFloat(precioInput.value);

        if (!isNaN(precio) && precio <= 0) {
            precioSelectMsg.style.display = "block";
        } else {
            precioSelectMsg.style.display = "none";
        }
    });

    // Validación en el envío del formulario
    form.addEventListener("submit", function (e) {
         // --- INICIO: Validación de Categorías ---
         const categoriasEnCarrusel = cathegoryCarousel.querySelectorAll(".categoria-item");
        
         if (categoriasEnCarrusel.length === 0) {
            alert("Debes seleccionar al menos una categoría para el producto.");
            e.preventDefault(); // Detener el envío del formulario
            return; // Salir de la función
        }
        // --- FIN: Validación de Categorías ---

        const tipoSeleccionado = document.querySelector('input[name="tipo"]:checked');
        if (!tipoSeleccionado) {
            alert("Debes seleccionar un tipo de publicación.");
            e.preventDefault();
            return;
        }

        // Validar imágenes
        if (imagenesInput.files.length < 3) {
            alert("Debes subir al menos 3 imágenes.");
            e.preventDefault();
            return;
        }

        // Validar video
        if (videoInput.files.length < 1) {
            alert("Debes subir al menos 1 video.");
            e.preventDefault();
            return;
        }

        // Validar precio solo si es venta
        const ventaSeleccionada = document.querySelector('input[name="tipo"]:checked').value === "venta";
        if (ventaSeleccionada) {
            if (!precioInput.value || parseFloat(precioInput.value) <= 0) {
                alert("Debes ingresar un precio válido para venta.");
                precioInput.focus();
                e.preventDefault();
                return;
            }

            if (!cantidadInput.value || parseInt(cantidadInput.value) <= 0) {
                alert("Debes ingresar una cantidad válida para venta.");
                cantidadInput.focus();
                e.preventDefault();
                return;
            }
        }

        categoriasEnCarrusel.forEach(item => {
            const option = document.createElement("option");
            option.value = item.dataset.value; // El valor de la categoría
            option.textContent = item.textContent; // El texto visible de la categoría
            option.selected = true; // Marcar como seleccionada para que se envíe
            categoriaSelect.appendChild(option);
        });

    });
});


// Funcionalidad para el carrusel de previsualización
document.addEventListener("DOMContentLoaded", function () {
    const imageInput = document.getElementById("input-file");
    const videoInput = document.getElementById("input-video");
    const previewContainer = document.getElementById("preview-carousel");

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

    imageInput.addEventListener("change", function () {
        clearPreview();
        Array.from(this.files).forEach(file => {
            if (file.type.startsWith("image/")) {
                createPreview(file, "image");
            }
        });

        // Mostrar video si ya fue seleccionado
        if (videoInput.files.length > 0) {
            createPreview(videoInput.files[0], "video");
        }
    });

    videoInput.addEventListener("change", function () {
        // Volvemos a mostrar todo para actualizar el carrusel
        clearPreview();

        Array.from(imageInput.files).forEach(file => {
            if (file.type.startsWith("image/")) {
                createPreview(file, "image");
            }
        });

        if (this.files.length > 0 && this.files[0].type.startsWith("video/")) {
            createPreview(this.files[0], "video");
        }
    });
});


document.addEventListener("DOMContentLoaded", function () {
    const select = document.getElementById("categoria");
    const carousel = document.getElementById("cathegory-carousel");

    // Cada vez que el select cambia
    select.addEventListener("change", function () {
        const selectedOptions = Array.from(this.selectedOptions);

        selectedOptions.forEach(option => {
            const value = option.value;
            const text = option.textContent;
            const title = option.title; // Obtener el título (descripción)

            // Crear item en el carrusel
            const item = document.createElement("div");
            item.classList.add("carousel-item", "categoria-item");
            item.textContent = text;
            item.dataset.value = value;
            item.title = title; // Agregar título para el tooltip

            // Al hacer clic en el item, se regresa al select
            item.addEventListener("click", () => {
                // Remover del carrusel
                carousel.removeChild(item);

                // Volver a agregar al select
                const newOption = document.createElement("option");
                newOption.value = value;
                newOption.textContent = text;
                newOption.title = title; // Agregar descripción como título
                select.appendChild(newOption);
            });

            // Agregar al carrusel
            carousel.appendChild(item);

            // Remover del select original
            select.removeChild(option);
        });
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("categoryModal");
    const openBtn = document.getElementById("openCategoryModal");
    const closeBtn = document.getElementById("closeCategoryModal");
    const form = document.getElementById("newCategoryForm");
    const select = document.getElementById("categoria");

    // Abrir modal
    openBtn.addEventListener("click", () => {
        modal.style.display = "block";
    });

    // Cerrar modal
    closeBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });

    window.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
        }
    });

    // Enviar nueva categoría
    form.addEventListener("submit", (e) => {
        e.preventDefault();
        const name = document.getElementById("newCategoryName").value.trim();
        const description = document.getElementById("newCategoryDescription").value.trim();

        if (!name || !description) return;

        // Crear nueva opción y agregar al select
        const option = document.createElement("option");
        option.value = name.toLowerCase().replace(/\s+/g, "-"); // ej: "Video Juegos" → "video-juegos"
        option.title = description; // Agregar descripción como título
        option.textContent = name;

        select.appendChild(option);

        // Limpiar y cerrar modal
        form.reset();
        modal.style.display = "none";
    });
});
