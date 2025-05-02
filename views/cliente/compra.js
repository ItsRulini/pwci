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
                grupo.dataset.calificacion = calificacionActual;
            });
        });

        function actualizarEstrellas(n) {
            estrellas.forEach((estrella, i) => {
                estrella.classList.toggle("active", i < n);
            });
        }
    });

    const resultados = []; // Aquí vamos a guardar todas las calificaciones
    // Botón de guardar calificaciones
    const botonGuardar = document.getElementById("calificar-btn");
    botonGuardar.addEventListener("click", () => {
        resultados.length = 0; // Limpiar el array antes de guardar nuevo

        const filas = document.querySelectorAll("table tr");

        filas.forEach(fila => {
            const categoria = fila.children[0]?.textContent.trim();
            const producto = fila.children[1]?.textContent.trim();
            const estrellasDiv = fila.querySelector(".estrellas");
            const comentarioInput = fila.querySelector(".comentario");

            if (estrellasDiv && comentarioInput) {
                const calificacion = estrellasDiv.dataset.calificacion || 0;
                const comentario = comentarioInput.value.trim();

                resultados.push({
                    categoria,
                    producto,
                    calificacion: Number(calificacion),
                    comentario
                });
            }
        });

        console.log("Resultados guardados:", resultados);
        alert("¡Calificaciones guardadas! Revisa la consola (F12).");
    });

});