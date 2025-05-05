<?php
session_start();
session_destroy();
header("Location: index.php"); // Ajusta el path si es necesario
exit();
?>
