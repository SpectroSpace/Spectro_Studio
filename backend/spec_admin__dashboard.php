<?php
// Inițializarea sesiunii și verificarea autentificării
session_start();

// Definește constanta pentru a permite accesul la fișierul de credențiale
define('IS_AUTHORIZED_ACCESS', true);
define('IS_ADMIN_DASHBOARD', true);

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: spec_admin__login.php');
    exit;
}

// Include fișierul de credențiale pentru a avea acces la conexiunea cu baza de date
require_once 'spec_admin__db__credentials.php';

// Inițializează conexiunea PDO la baza de date
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Eroare de conexiune la baza de date: " . $e->getMessage());
}

// Verifică dacă site-ul este în modul mentenanță
$config_file = __DIR__ . '/../config/maintenance.json';
if (file_exists($config_file)) {
    $maintenance_config = json_decode(file_get_contents($config_file), true);
    $maintenance_mode = isset($maintenance_config['enabled']) ? $maintenance_config['enabled'] : false;
} else {
    $maintenance_mode = false;
}

// Actualizează timestamp-ul ultimei activități pentru a monitoriza sesiunea
$_SESSION['last_activity'] = time();

// Obținem statisticile pentru dashboard
try {
    // Numărul de galerii
    $stmt = $conn->query("SELECT COUNT(*) as count FROM galleries");
    $galleries_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Numărul de fotografii
    $stmt = $conn->query("SELECT COUNT(*) as count FROM images");
    $images_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Numărul de fotografi
    $stmt = $conn->query("SELECT COUNT(*) as count FROM photographers");
    $photographers_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
} catch(PDOException $e) {
    // În caz de eroare, setăm contoarele la 0
    $galleries_count = $images_count = $photographers_count = 0;
}

// Verificăm dacă avem acces direct sau este inclusă în alt fișier
$is_included = (basename($_SERVER['SCRIPT_FILENAME']) !== basename(__FILE__));

// Dacă este accesat direct, redirecționăm către pagina principală de administrare
// în loc să includem de mai multe ori modulele
if (!$is_included) {
    header('Location: spec_admin__index.php');
    exit;
}

// Altfel, doar returnăm variabilele pentru a fi folosite în alte fișiere
?>