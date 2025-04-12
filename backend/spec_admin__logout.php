<?php
session_start();

// Distruge toate datele sesiunii
$_SESSION = array();

// Distruge cookie-ul sesiunii dacă există
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Distruge sesiunea
session_destroy();

// Redirecționează la pagina de login sau home
header("Location: ../index.php");
exit;
?>