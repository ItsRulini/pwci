<?php
require_once '../../models/Usuario.php';

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php"); // Redirigir al login si no hay sesión
    exit();
}

$usuario = $_SESSION['usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chats</title>

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="chat.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    
    <!-- Navbar -->
    <nav class="navbar">
        <a href="main.php" class="logo-link">
            <h1 class="logo">Papu Tienda</h1>
        </a>

        <ul class="nav-links">
            <li><a href="perfil.php">Perfil</a></li>
            <li><a href="chat.php">Chat</a></li>
            <li><a href="ventas.php">Ventas</a></li>
            <li><a href="../logout.php">Cerrar sesión</a></li>
        </ul>
    </nav>

    <div class="chat-layout">
        <!-- Lista de chats -->
        <aside id="chats_container">
            <div class="chat-list-header">
                <h2>Conversaciones</h2>
            </div>
            
            <div class="chat-search">
                <input type="text" placeholder="Buscar conversaciones...">
                <i class="fas fa-search"></i>
            </div>
            
            <ul class="chat-list">
                <li class="chat-item active">
                    <div class="chat-avatar">
                        <img src="../../multimedia/default/default.jpg" alt="Usuario 1">
                        <span class="status online"></span>
                    </div>
                    <div class="chat-info">
                        <h3>Usuario 1</h3>
                        <p>Siii porfavor, entregas en el metro de Simón Bolivar?</p>
                    </div>
                    <div class="chat-meta">
                        <span class="time">12:30</span>
                    </div>
                </li>
                
                <li class="chat-item">
                    <div class="chat-avatar">
                        <img src="../../multimedia/default/default.jpg" alt="Usuario 2">
                        <span class="status offline"></span>
                    </div>
                    <div class="chat-info">
                        <h3>Usuario 2</h3>
                        <p>Gracias por la información!</p>
                    </div>
                    <div class="chat-meta">
                        <span class="time">Ayer</span>
                    </div>
                </li>
                
                <li class="chat-item">
                    <div class="chat-avatar">
                        <img src="../../multimedia/default/default.jpg" alt="Usuario 3">
                        <span class="status online"></span>
                    </div>
                    <div class="chat-info">
                        <h3>Usuario 3</h3>
                        <p>¿Cuánto cuesta el envío?</p>
                    </div>
                    <div class="chat-meta">
                        <span class="time">Lun</span>
                    </div>
                </li>
            </ul>
        </aside>

        <!-- Conversación actual -->
        <section id="chat_container">
            <div class="chat-header">
                <div class="chat-user-info">
                    <img src="../../multimedia/default/default.jpg" alt="Usuario 1">
                    <div>
                        <a href="perfil.php" target="_blank"><h3>Usuario 1</h3></a>
                    </div>
                </div>
                
            </div>
            
            <div id="chat" class="chat-messages">
                <div class="message-date">
                    <span>Hoy</span>
                </div>
                
                <div class="message message-sent">
                    <div class="message-content">
                        <p>
                            ¡Hola! me gustaría saber si aún tienen en venta el Zelda OOT
                        </p>
                    </div>
                    <span class="message-time">10:17</span>
                </div>
                
                <div class="message message-received">
                    <div class="message-content">
                        <p>
                            Que onda, smn si lo tenemos. Te interesa?
                        </p>
                    </div>
                    <span class="message-time">10:20</span>
                </div>
                
                <div class="message message-sent">
                    <div class="message-content">
                        <p>
                            Siii porfavor, entregas en el metro de Simón Bolivar?
                        </p>
                    </div>
                    <span class="message-time">10:22</span>
                </div>

                <div class="message message-sent">
                    <div class="offer-content">
                        
                        <span>Precio: $100 MXN</span>
                        <p>
                            Descripción: El juego está en perfecto estado, sin rayones ni fallas. Incluye caja y manual.
                        </p>
                        <button class="button-cancel"><i class="fas fa-times"></i></button>
                    </div>
                    <span class="message-time">10:25</span>
                </div>

            </div>

            <div id="inputMensaje" class="message-input">
                <input type="text" id="mensaje" placeholder="Escribe un mensaje...">

                <button title="Hacer una oferta" class="offer-btn"><i class="fa-solid fa-envelope"></i></button>
                <button title="Enviar mensaje" class="send-btn"><i class="fas fa-paper-plane"></i></button>
            </div>
        </section>
    </div>

    <!-- Overlay y el Popup -->
    <div class="popup-overlay" id="popupOverlay"></div>
    <div class="oferta-container" style="display: none;">
        <div class="oferta-header">
            <h2>Hacer una oferta</h2>
            <button class="close-btn"><i class="fas fa-times"></i></button>
        </div>
        
        <form id="oferta-form" action="#" method="POST">
            <label for="precio">Precio:</label>
            <input type="number" id="precio" name="precio" required>

            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" rows="4" required></textarea>

            <button title="Enviar mensaje" class="send-btn">Enviar oferta</i></button>
        </form>

    </div>

    <script src="chat.js"></script>
</body>
</html>