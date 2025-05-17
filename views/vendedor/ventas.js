document.addEventListener("DOMContentLoaded", function () {
    const filtrosForm = document.getElementById("filtrosForm");
    const selectCategoriaFiltro = document.getElementById("categoria"); // El select de categorías en los filtros

    // IDs corregidos para los tbody
    const tbodyVentasDetalladas = document.querySelector(".ventas-detallada-section .ventas-table tbody");
    const tbodyVentasAgrupadas = document.querySelector(".venta-agrupada-section .ventas-table tbody");

    /**
     * Carga las categorías en el dropdown de filtros.
     */
    function cargarCategoriasFiltro() {
        if (!selectCategoriaFiltro) return;
        selectCategoriaFiltro.innerHTML = '<option value="0">Cargando categorías...</option>';

        fetch(`../../controllers/getCategorias.php`) // Reutilizamos el controlador existente
            .then(response => response.json())
            .then(data => {
                selectCategoriaFiltro.innerHTML = '<option value="0">Todas</option>'; // Opción por defecto
                if (data.success && data.data.length > 0) {
                    data.data.forEach(categoria => {
                        const option = document.createElement("option");
                        option.value = categoria.idCategoria;
                        option.textContent = categoria.nombre;
                        selectCategoriaFiltro.appendChild(option);
                    });
                } else {
                    console.warn("No se encontraron categorías para el filtro:", data.message);
                }
            })
            .catch(error => {
                console.error('Error al cargar categorías para el filtro:', error);
                if (selectCategoriaFiltro) selectCategoriaFiltro.innerHTML = '<option value="0">Error al cargar</option>';
            });
    }

    /**
     * Carga y muestra las ventas detalladas del vendedor.
     * @param {object} filtros - Objeto con idCategoria, fechaDesde, fechaHasta.
     */
    function cargarVentasDetalladas(filtros = {}) {
        const params = new URLSearchParams();
        if (filtros.idCategoria && filtros.idCategoria !== "0") {
            params.append('idCategoria', filtros.idCategoria);
        }
        if (filtros.fechaDesde) {
            params.append('fechaDesde', filtros.fechaDesde);
        }
        if (filtros.fechaHasta) {
            params.append('fechaHasta', filtros.fechaHasta);
        }
        const queryParams = params.toString();

        // Colspan actualizado a 7
        if (tbodyVentasDetalladas) tbodyVentasDetalladas.innerHTML = '<tr><td colspan="7" style="text-align:center;">Cargando ventas detalladas...</td></tr>';

        fetch(`../../controllers/getVentasDetalladasVendedor.php?${queryParams}`)
            .then(response => response.json())
            .then(data => {
                if (tbodyVentasDetalladas) tbodyVentasDetalladas.innerHTML = ''; 

                if (data.success && data.ventas.length > 0) {
                    data.ventas.forEach(item => {
                        const tr = document.createElement("tr");
                        const fechaVenta = new Date(item.fechaTransaccion).toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' });
                        const calificacionPromedio = item.calificacionPromedioProducto ? parseFloat(item.calificacionPromedioProducto).toFixed(1) : 'N/A';
                        
                        tr.innerHTML = `
                            <td>${fechaVenta}</td>
                            <td>${item.categoriasProducto || 'N/A'}</td>
                            <td>${item.nombreProducto}</td>
                            <td>${item.cantidadVendida}</td>
                            <td>${calificacionPromedio}</td>
                            <td>$${parseFloat(item.precioVenta).toFixed(2)} MXN</td>
                            <td>${item.existenciaActual}</td>
                        `;
                        tbodyVentasDetalladas.appendChild(tr);
                    });
                } else if (data.success && data.ventas.length === 0) {
                    // Colspan actualizado a 7
                    if (tbodyVentasDetalladas) tbodyVentasDetalladas.innerHTML = '<tr><td colspan="7" style="text-align:center;">No hay ventas detalladas con estos filtros.</td></tr>';
                } else {
                    // Colspan actualizado a 7
                    if (tbodyVentasDetalladas) tbodyVentasDetalladas.innerHTML = `<tr><td colspan="7" style="text-align:center;">Error: ${data.message || 'No se pudo cargar el detalle.'}</td></tr>`;
                }
            })
            .catch(error => {
                console.error('Error al cargar ventas detalladas:', error);
                // Colspan actualizado a 7
                if (tbodyVentasDetalladas) tbodyVentasDetalladas.innerHTML = '<tr><td colspan="7" style="text-align:center;">Error de conexión.</td></tr>';
            });
    }

    /**
     * Carga y muestra las ventas agrupadas del vendedor.
     * @param {object} filtros - Objeto con idCategoria, fechaDesde, fechaHasta.
     */
    function cargarVentasAgrupadas(filtros = {}) {
        let queryParams = new URLSearchParams(filtros).toString();
        if (tbodyVentasAgrupadas) tbodyVentasAgrupadas.innerHTML = '<tr><td colspan="3" style="text-align:center;">Cargando ventas agrupadas...</td></tr>';

        fetch(`../../controllers/getVentasAgrupadasVendedor.php?${queryParams}`)
            .then(response => response.json())
            .then(data => {
                if (tbodyVentasAgrupadas) tbodyVentasAgrupadas.innerHTML = '';

                if (data.success && data.ventas.length > 0) {
                    // Agrupar por mesAnioVenta para la visualización
                    const ventasPorMes = data.ventas.reduce((acc, venta) => {
                        const mesAnio = venta.mesAnioVenta;
                        if (!acc[mesAnio]) {
                            acc[mesAnio] = [];
                        }
                        acc[mesAnio].push(venta);
                        return acc;
                    }, {});

                    for (const mesAnio in ventasPorMes) {
                        const tr = document.createElement("tr");
                        const categoriasHTML = ventasPorMes[mesAnio].map(v => v.nombreCategoria).join('<br>');
                        const unidadesHTML = ventasPorMes[mesAnio].map(v => v.totalUnidadesVendidas).join('<br>');
                        
                        // Formatear mesAnio para mostrar nombre del mes
                        const [year, month] = mesAnio.split('-');
                        const fechaFormateada = new Date(year, month - 1).toLocaleString('es-MX', { month: 'long', year: 'numeric' });

                        tr.innerHTML = `
                            <td>${fechaFormateada.charAt(0).toUpperCase() + fechaFormateada.slice(1)}</td>
                            <td>${categoriasHTML}</td>
                            <td>${unidadesHTML}</td>
                        `;
                        tbodyVentasAgrupadas.appendChild(tr);
                    }

                } else if (data.success && data.ventas.length === 0) {
                    if (tbodyVentasAgrupadas) tbodyVentasAgrupadas.innerHTML = '<tr><td colspan="3" style="text-align:center;">No hay ventas agrupadas con estos filtros.</td></tr>';
                } else {
                    if (tbodyVentasAgrupadas) tbodyVentasAgrupadas.innerHTML = `<tr><td colspan="3" style="text-align:center;">Error: ${data.message || 'No se pudo cargar el resumen.'}</td></tr>`;
                }
            })
            .catch(error => {
                console.error('Error al cargar ventas agrupadas:', error);
                if (tbodyVentasAgrupadas) tbodyVentasAgrupadas.innerHTML = '<tr><td colspan="3" style="text-align:center;">Error de conexión.</td></tr>';
            });
    }


    // Manejar envío del formulario de filtros
    if (filtrosForm) {
        filtrosForm.addEventListener("submit", function(event) {
            event.preventDefault();
            const formData = new FormData(filtrosForm);
            const filtros = {
                idCategoria: formData.get("idCategoria"), // Antes: "categoria"
                fechaDesde: formData.get("fechaDesde"),   // Antes: "desde"
                fechaHasta: formData.get("fechaHasta")     // Antes: "hasta"
            };
            cargarVentasDetalladas(filtros);
            cargarVentasAgrupadas(filtros);
        });
    }

    // Cargas iniciales
    cargarCategoriasFiltro();
    cargarVentasDetalladas(); // Sin filtros inicialmente
    cargarVentasAgrupadas();  // Sin filtros inicialmente
});
