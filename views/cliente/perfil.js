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

//document.querySelectorAll(".headerLista .fas").forEach(function(icono) {



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
