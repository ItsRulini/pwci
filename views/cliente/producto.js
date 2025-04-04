document.addEventListener("DOMContentLoaded", function () {
    const popup = document.getElementById("popup");
    const btnCerrar = document.getElementById("btnCerrarPopup");
    const btnAbrir = document.querySelector(".fas.fa-ellipsis-v");

    // Función para abrir el popup
    btnAbrir.addEventListener("click", function mostrarPopup() {
        popup.classList.add("mostrar");
    });

    // Función para cerrar el popup
    function cerrarPopup() {
        popup.classList.remove("mostrar");
    }

    // Evento para cerrar el popup cuando se presiona la "X"
    btnCerrar.addEventListener("click", cerrarPopup);

    // Evento para cerrar el popup al hacer clic fuera del contenido
    window.addEventListener("click", function (e) {
        if (e.target === popup) {
            cerrarPopup();
        }
    });

    // Simulación de abrir el popup (esto lo puedes cambiar según tu evento)
    setTimeout(mostrarPopup, 1000); // Muestra el popup después de 1 segundo (pruebas)
});
