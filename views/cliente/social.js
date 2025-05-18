// social.js - Este archivo puede ser usado por views/cliente/social.php y views/vendedor/social.php

document.addEventListener("DOMContentLoaded", function () {
    const perfilesContainer = document.getElementById("perfilesContainer");
    const searchInput = document.getElementById("profileSearchInput");
    let todosLosPerfiles = []; // Para almacenar los perfiles y filtrar localmente

    /**
     * Renderiza la lista de perfiles en el contenedor.
     * @param {Array} perfiles - Array de objetos de perfil a mostrar.
     */
    function renderizarPerfiles(perfiles) {
        if (!perfilesContainer) return;
        perfilesContainer.innerHTML = ''; // Limpiar contenedor

        if (perfiles.length === 0) {
            perfilesContainer.innerHTML = '<p class="no-profiles-message">No se encontraron perfiles.</p>';
            return;
        }

        perfiles.forEach(perfil => {
            const article = document.createElement("article");
            article.classList.add("perfil-card"); // Nueva clase para la tarjeta

            const fotoAvatar = perfil.fotoAvatar 
                ? `../../multimedia/imagenPerfil/${perfil.fotoAvatar}` 
                : '../../multimedia/default/default.jpg';

            // La "descripción" ahora será el rol
            article.innerHTML = `
                <img src="${fotoAvatar}" alt="Avatar de ${escapeHtml(perfil.nombreUsuario)}" class="avatar">
                <div class="info">
                    <span class="nombre-usuario">${escapeHtml(perfil.nombreUsuario)}</span>
                    <p class="rol-usuario">${escapeHtml(perfil.rol)}</p>
                    <button class="btn-ver-perfil" data-idusuario="${perfil.idUsuario}" data-rolusuario="${perfil.rol}" data-privacidad="${perfil.privacidad || 'Publico'}">Ir al Perfil</button>
                </div>
            `;
            perfilesContainer.appendChild(article);
        });
    }

    /**
     * Carga los perfiles desde el backend.
     */
    function cargarPerfiles() {
        if (!perfilesContainer) return;
        perfilesContainer.innerHTML = '<p class="loading-profiles">Cargando perfiles...</p>';

        fetch('../../controllers/getPerfilesSocial.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.perfiles) {
                    todosLosPerfiles = data.perfiles;
                    renderizarPerfiles(todosLosPerfiles);
                } else {
                    perfilesContainer.innerHTML = `<p class="no-profiles-message">Error al cargar perfiles: ${data.message || 'Error desconocido.'}</p>`;
                }
            })
            .catch(error => {
                console.error('Error fetching perfiles:', error);
                perfilesContainer.innerHTML = '<p class="no-profiles-message">Error de conexión al cargar perfiles.</p>';
            });
    }

    /**
     * Filtra los perfiles basados en el término de búsqueda.
     * Busca en nombreUsuario y rol.
     */
    function filtrarPerfiles() {
        if (!searchInput) return;
        const terminoBusqueda = searchInput.value.toLowerCase().trim();

        if (!terminoBusqueda) {
            renderizarPerfiles(todosLosPerfiles); // Mostrar todos si no hay búsqueda
            return;
        }

        const perfilesFiltrados = todosLosPerfiles.filter(perfil => {
            const nombreMatch = perfil.nombreUsuario.toLowerCase().includes(terminoBusqueda);
            const rolMatch = perfil.rol.toLowerCase().includes(terminoBusqueda);
            return nombreMatch || rolMatch;
        });
        renderizarPerfiles(perfilesFiltrados);
    }

    // Event listener para la barra de búsqueda de perfiles (búsqueda en tiempo real)
    if (searchInput) {
        searchInput.addEventListener("input", filtrarPerfiles);
    }

    // Event listener para los botones "Ir al Perfil" (delegación de eventos)
    if (perfilesContainer) {
        perfilesContainer.addEventListener("click", function(event) {
            const target = event.target;
            if (target.classList.contains("btn-ver-perfil")) {
                const idUsuario = target.dataset.idusuario;
                // La redirección a verPerfilExterno.php se manejará en la Fase 2.
                // Por ahora, solo un log o alert.
                // alert(`Ir al perfil del usuario ID: ${idUsuario}`); 
                window.location.href = `verPerfilExterno.php?idUsuario=${idUsuario}`;
            }
        });
    }
    
    function escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') return '';
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    // Carga inicial de perfiles
    cargarPerfiles();
});
