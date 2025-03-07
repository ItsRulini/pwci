// document.getElementById("login").addEventListener("click", function (event){
//     login();
// });

document.getElementById("registro").addEventlistener("click", function (event){
     registro();
 });

// document.getElementById("loginForm").addEventListener("submit", function(event) {
//     event.preventDefault(); // Evita la recarga de la página

//     let formData = new FormData(this); // Obtiene los datos del formulario

//     fetch("backend/login.php", {
//         method: "POST",
//         body: formData
//     })
//     .then(response => response.json())
//     .then(data => {
//         if (data.success) {
//             window.location.href = "main.html"; // Redirige si el login es exitoso
//         } else {
//             document.getElementById("mensaje").textContent = "Usuario o contraseña incorrectos.";
//         }
//     })
//     .catch(error => console.error("Error en la solicitud:", error));
// });


function login(){

    window.location.assign("main.html");
    event.preventDefault();
}

function registro(){
    event.preventDefault();
    window.location.assign("registro.html");
}