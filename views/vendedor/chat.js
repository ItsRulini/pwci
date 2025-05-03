document.addEventListener("DOMContentLoaded", function () {
    const offerBtn = document.querySelector('.offer-btn');
    const closeBtn = document.querySelector('.close-btn');
    const popupOverlay = document.getElementById('popupOverlay');
    const ofertaContainer = document.querySelector('.oferta-container');

    // Función para abrir el pop-up
    function abrirOferta() {
        popupOverlay.style.display = 'block';
        ofertaContainer.style.display = 'block';
    }

    // Función para cerrar el pop-up
    function cerrarOferta() {
        popupOverlay.style.display = 'none';
        ofertaContainer.style.display = 'none';
    }

    // Abrir al dar click en el botón de oferta
    offerBtn.addEventListener('click', abrirOferta);

    // Cerrar al dar click en el botón de cerrar
    closeBtn.addEventListener('click', cerrarOferta);

    // Cerrar al dar click fuera del popup (en el overlay)
    popupOverlay.addEventListener('click', cerrarOferta);
});
