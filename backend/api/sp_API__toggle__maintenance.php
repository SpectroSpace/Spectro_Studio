<?php
// Verifică dacă cererea este de tip POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => 'Metoda HTTP neacceptată']);
    exit;
}

// Definește constanta pentru a permite accesul la fișierul de credențiale
define('IS_AUTHORIZED_ACCESS', true);

// Verifică dacă utilizatorul este autentificat
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Acces neautorizat']);
    exit;
}

// Calea către fișierul de configurare
$config_file = __DIR__ . '/../../config/maintenance.json';

// Verifică dacă fișierul există
if (!file_exists($config_file)) {
    // Creează un fișier de configurare implicit dacă nu există
    $default_config = [
        'enabled' => false,
        'message' => 'Site-ul este în curs de actualizare. Revenim în curând cu o experiență îmbunătățită!',
        'end_time' => date('Y-m-d H:i:s', strtotime('+1 day'))
    ];
    file_put_contents($config_file, json_encode($default_config, JSON_PRETTY_PRINT));
}

// Citește configurația curentă
$maintenance_config = json_decode(file_get_contents($config_file), true);

// Obține acțiunea din cererea POST
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Actualizează configurația în funcție de acțiune
if ($action == 'enable') {
    $maintenance_config['enabled'] = true;
    $message = 'Modul de mentenanță a fost activat';
} elseif ($action == 'disable') {
    $maintenance_config['enabled'] = false;
    $message = 'Modul de mentenanță a fost dezactivat';
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Acțiune nevalidă']);
    exit;
}

// Salvează configurația actualizată
if (file_put_contents($config_file, json_encode($maintenance_config, JSON_PRETTY_PRINT))) {
    echo json_encode(['success' => true, 'message' => $message, 'status' => $maintenance_config['enabled']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Nu s-a putut actualiza configurația']);
}
?>