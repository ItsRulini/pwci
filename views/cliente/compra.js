document.addEventListener("DOMContentLoaded", function () {
    const gruposEstrellas = document.querySelectorAll(".estrellas");

    gruposEstrellas.forEach(grupo => {
        const estrellas = grupo.querySelectorAll("i");
        let calificacionActual = 0;

        estrellas.forEach((estrella, index) => {
            // Hover
            estrella.addEventListener("mouseover", () => {
                actualizarEstrellas(index + 1);
            });
            // Salir del hover
            estrella.addEventListener("mouseout", () => {
                actualizarEstrellas(calificacionActual);
            });
            // Click
            estrella.addEventListener("click", () => {
                calificacionActual = index + 1;
                actualizarEstrellas(calificacionActual);
                console.log("Calificación seleccionada para este producto:", calificacionActual);
            });
        });

        function actualizarEstrellas(n) {
            estrellas.forEach((estrella, i) => {
                estrella.classList.toggle("active", i < n);
            });
        }
    });
});
