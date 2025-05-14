document.addEventListener("DOMContentLoaded", function () {
    const input = document.querySelector(".search-bar");
    const icon = document.querySelector(".search-icon");

    function redirigirBusqueda() {
        const texto = input.value.trim();
        if (texto) {
            const encoded = encodeURIComponent(texto);
            window.location.href = `busqueda.php?query=${encoded}`;
        }
    }

    icon.addEventListener("click", redirigirBusqueda);

    input.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            redirigirBusqueda();
        }
    });
});
