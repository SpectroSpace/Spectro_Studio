<?php
// Acest endpoint salvează setările de mentenanță

// Verificare sesiune
session_start();

// Header pentru JSON
header('Content-Type: application/json');

// Verifică dacă utilizatorul este logat ca administrator
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acces neautorizat']);
    exit;
}

// Verifică dacă au fost trimise datele necesare
if (!isset($_POST['message']) || !isset($_POST['end_time'])) {
    echo json_encode(['success' => false, 'message' => 'Parametrii necesari lipsesc']);
    exit;
}

// Directorul config
$config_dir = __DIR__ . '/../../config';

// Creare director config dacă nu există
if (!file_exists($config_dir)) {
    mkdir($config_dir, 0755, true);
}

// Calea către fișierul de configurare
$config_file = $config_dir . '/maintenance.json';

try {
    // Încărcăm configurația existentă sau creăm una nouă
    if (file_exists($config_file)) {
        $maintenance_config = json_decode(file_get_contents($config_file), true);
    } else {
        $maintenance_config = [
            'enabled' => true,
            'message' => 'Site-ul este în mentenanță. Reveniți în curând!',
            'end_time' => date('Y-m-d H:i:s', strtotime('+24 hours'))
        ];
    }
    
    // Actualizăm datele
    $maintenance_config['message'] = $_POST['message'];
    $maintenance_config['end_time'] = $_POST['end_time'];
    
    // Salvăm configurația
    file_put_contents($config_file, json_encode($maintenance_config, JSON_PRETTY_PRINT));
    
    // Returnăm rezultatul
    echo json_encode([
        'success' => true, 
        'message' => 'Setările pentru mentenanță au fost salvate cu succes',
        'status' => $maintenance_config['enabled']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Eroare: ' . $e->getMessage()]);
}
?>