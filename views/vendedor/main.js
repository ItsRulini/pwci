document.getElementById("input-file").addEventListener("change", function(event) {
    const file = event.target.files[0]; // Obtiene el archivo seleccionado
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById("product-image").src = e.target.result; // Asigna la imagen al src
        };
        reader.readAsDataURL(file);
    }
});

// document.addEventListener("DOMContentLoaded", () => {
//     const tipoVenta = document.querySelectorAll('input[name="tipo"]');
//     const precioInput = document.getElementById("precio");
//     const cantidadInput = document.getElementById("cantidad");
//     const sinStock = document.getElementById("sinStock");
//     const cantidadField = document.getElementById("cantidad");

//     tipoVenta.forEach(radio => {
//         radio.addEventListener("change", () => {
//             if (radio.value === "venta" && radio.checked) {
//                 precioInput.style.display = "block";
//                 cantidadInput.style.display = "block";
//             } else {
//                 precioInput.style.display = "none";
//                 cantidadInput.style.display = "none";
//                 sinStock.style.display = "none";
//             }
//         });
//     });

//     cantidadField.addEventListener("input", () => {
//         if (parseInt(cantidadField.value) <= 0) {
//             sinStock.style.display = "block";
//         } else {
//             sinStock.style.display = "none";
//         }
//     });
// });

document.addEventListener("DOMContentLoaded", function () {
    const tipoPublicacionRadios = document.querySelectorAll('input[name="tipo"]');
    const precioInput = document.getElementById("precio");
    const cantidadInput = document.getElementById("cantidad");
    const disponibilidadMsg = document.getElementById("sinStock");
    const form = document.getElementById("formDashboard");
    const imagenesInput = document.getElementById("input-file");
    const videoInput = document.getElementById("input-video");

    // Mostrar u ocultar campos según la opción elegida (venta o cotización)
    tipoPublicacionRadios.forEach(radio => {
        radio.addEventListener("change", () => {
            if (radio.value === "venta") {
                precioInput.style.display = "block";
                cantidadInput.style.display = "block";
                disponibilidadMsg.style.display = "none";
            } else {
                precioInput.style.display = "none";
                cantidadInput.style.display = "none";
                disponibilidadMsg.style.display = "none";
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

    // Validación en el envío del formulario
    form.addEventListener("submit", function (e) {
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
                e.preventDefault();
                return;
            }

            if (!cantidadInput.value || parseInt(cantidadInput.value) <= 0) {
                alert("Debes ingresar una cantidad válida para venta.");
                e.preventDefault();
                return;
            }
        }
    });
});
