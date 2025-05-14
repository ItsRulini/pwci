<?php
// auth.php

require_once '../../models/Usuario.php'; // Ajusta si lo pones en otra carpeta

session_start();

// Verifica que haya sesión activa
if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario)) {
    header("Location: ../views/index.php"); // Redirige al login si no está logueado
    exit();
}

$usuario = $_SESSION['usuario'];

/**
 * Validar que el usuario tenga alguno de los roles permitidos.
 * Llama esta función después de incluir auth.php, por ejemplo:
 *   require_once '../../auth.php';
 *   requireRole(['Admin', 'SuperAdmin']);
 */
function requireRole(array $roles) {
    global $usuario;
    if (!in_array($usuario->getRol(), $roles)) {
        header("Location: ../views/error.html"); // O una página de acceso denegado
        exit();
    }
}
