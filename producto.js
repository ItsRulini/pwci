document.addEventListener("DOMContentLoaded", function() {
    var iconoPopup = document.querySelector(".fa-ellipsis-v");
    var popup = document.getElementById("popup");
    var cerrarPopup = document.querySelector(".cerrar-popup");

    iconoPopup.addEventListener("click", function(event) {
        popup.style.display = "flex";
        event.stopPropagation();
    });

    cerrarPopup.addEventListener("click", function() {
        popup.style.display = "none";
    });

    document.addEventListener("click", function(event) {
        if (!popup.contains(event.target) && event.target !== iconoPopup) {
            popup.style.display = "none";
        }
    });
});
