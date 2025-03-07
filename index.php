<?php
    if (isset($_GET["error"]) && $_GET["error"] == 1) {
         echo "<script>alert('Credenciales incorrectas, intenta de nuevo.');
         window.location.assign('index.php')</script>";
    }
    
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loign</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <section>
        <h3>Iniciar sesión</h3>
        <form id="loginForm" action="backend/login.php" method="post">
            <input type="text" placeholder="Usuario" name="usuario" required>
            <input type="password" placeholder="Contraseña" name="pass" required>

            <input type="submit" id="login" value="Iniciar">
            <a href="registro.html" id="registro">Registro</a>
        </form>
        
    </section>

    
    <!--<script src="index.js"></script>-->
</body>
</html>