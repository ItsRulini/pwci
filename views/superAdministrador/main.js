window.onload = function () {
    cargarUsuarios(); // Llama a cargarUsuarios apenas abra main.php
};

function cargarUsuarios() {
    fetch('../../controllers/getUsuariosRegistrados.php?action=listar')
        .then(response => response.json())
        .then(data => {
            const usuariosList = document.getElementById('usuarios-list');
            usuariosList.innerHTML = '';

            data.forEach(usuario => {
                const row = `
                <tr>
                    <td>${usuario.idUsuario}</td>
                    <td>${usuario.nombreUsuario}</td>
                    <td>${usuario.email}</td>
                    <td>${usuario.rol}</td>
                    <td>${usuario.fechaRegistro}</td>
                    <td>${usuario.estatus ? 'Activo' : 'Inactivo'}</td>
                </tr>
                `;
                usuariosList.innerHTML += row;
            });
        })
        .catch(error => {
            console.error('Error cargando usuarios:', error);
        });
}