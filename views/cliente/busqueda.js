$(document).ready(function () {
    $("#buscador").on("input", function () {
        let query = $(this).val();
        let categoria = $("#categoria").val();
        let orden = $("#orden").val();
        obtenerProductos(query, categoria, orden);
    });

    $("#categoria, #orden").on("change", function () {
        let query = $("#buscador").val();
        let categoria = $("#categoria").val();
        let orden = $("#orden").val();
        obtenerProductos(query, categoria, orden);
    });
});

function obtenerProductos(query, categoria, orden) {
    $.ajax({
        url: "controllers/buscarProductos.php",
        method: "GET",
        data: { query: query, categoria: categoria, orden: orden },
        success: function (data) {
            $("#resultados").html(data);
        }
    });
}
