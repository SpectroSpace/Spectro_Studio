<?php
// filepath: f:\SITE SPECTRO STUDIO\backend\generate_hash.php

$password = "SpectroStudio-2025!"; // Schimbă cu parola dorită
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<div style='background:#111; color:#fff; padding:20px; font-family:monospace;'>";
echo "<h2>Generator Hash Parolă</h2>";
echo "<p>Parola: " . htmlspecialchars($password) . "</p>";
echo "<p>Hash generat: " . htmlspecialchars($hash) . "</p>";
echo "<p>Copiază acest hash în fișierul spec_admin__db__credentials.php</p>";
echo "</div>";
?>