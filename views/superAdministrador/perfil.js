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
