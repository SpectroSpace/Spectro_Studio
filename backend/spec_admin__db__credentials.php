<?php
// filepath: f:\SITE SPECTRO STUDIO\backend\spec_admin__db__credentials.php
// Împiedică accesul direct la fișier
if (!defined('IS_AUTHORIZED_ACCESS')) {
    http_response_code(403);
    die('Acces interzis');
}

// Credențiale bază de date
$servername = getenv('DB_HOST') ?: "localhost"; 
$username = getenv('DB_USER') ?: "spectros_florin";
$password = getenv('DB_PASS') ?: "SpectroStudio-2025!!";
$dbname = getenv('DB_NAME') ?: "spectros_studio";

// Credențiale administrator
$admin_username = getenv('ADMIN_USER') ?: "admin-florin";
// Hash pre-generat al parolei
$admin_password_hash = '$2y$10$vWDUj0z8RJPsZqtd4gZTTeiTUQKPYisR4JtuCOrwupHGe89jM8n6u'; // Înlocuiește cu hash-ul generat

// Alte credențiale sensibile
$admin_email = getenv('ADMIN_EMAIL') ?: "admin@spectrostudio.com";
?>