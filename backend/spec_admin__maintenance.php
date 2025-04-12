<?php
// filepath: f:\SITE SPECTRO STUDIO\backend\spec_admin__maintenance.php
// Acest fișier verifică dacă site-ul este în mentenanță și afișează pagina corespunzătoare

// Inițializare sesiune
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Procesare formular login dacă suntem în modul mentenanță și formularul a fost trimis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    // Definim constanta pentru a permite accesul la fișierul de credențiale
    define('IS_AUTHORIZED_ACCESS', true);
    
    // Importăm credențialele
    require_once __DIR__ . '/spec_admin__db__credentials.php';
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Verificăm credențialele
    if ($username === $admin_username && password_verify($password, $admin_password_hash)) {
        // Autentificare reușită
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['admin_email'] = $admin_email;
        $_SESSION['last_activity'] = time();
        
        // Redirecționare către dashboard
        header('Location: ' . (str_contains($_SERVER['REQUEST_URI'], 'backend') ? '' : 'backend/') . 'spec_admin__index.php');
        exit;
    }
}

// Creează directorul config dacă nu există
if (!file_exists(__DIR__ . '/../config')) {
    mkdir(__DIR__ . '/../config', 0755, true);
}

// Creează fișierul maintenance.json doar dacă nu există
if (!file_exists(__DIR__ . '/../config/maintenance.json')) {
    $maintenance_config = [
        'enabled' => false,
        'message' => 'Site-ul este în curs de actualizare. Revenim în curând cu o experiență îmbunătățită!',
        'end_time' => date('Y-m-d H:i:s', strtotime('+24 hours'))
    ];
    
    file_put_contents(__DIR__ . '/../config/maintenance.json', json_encode($maintenance_config, JSON_PRETTY_PRINT));
}

// Încărcăm statusul modului de mentenanță din fișier
if (file_exists(__DIR__ . '/../config/maintenance.json')) {
    $maintenance_config = json_decode(file_get_contents(__DIR__ . '/../config/maintenance.json'), true);
    $maintenance_mode = isset($maintenance_config['enabled']) ? $maintenance_config['enabled'] : false;
    $maintenance_message = isset($maintenance_config['message']) ? $maintenance_config['message'] : '';
    $maintenance_end_time = isset($maintenance_config['end_time']) ? $maintenance_config['end_time'] : '';
} else {
    $maintenance_mode = false;
    $maintenance_message = '';
    $maintenance_end_time = '';
}

// Lista de IP-uri care pot accesa site-ul în timpul mentenanței
$allowed_ips = [
    '127.0.0.1',
    '::1', // localhost IPv6
    // Adăugați IP-ul dvs. aici pentru a putea accesa site-ul în timpul mentenanței
];

// Verifică dacă utilizatorul este logat în panoul de administrare
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$isAdmin = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Verifică dacă site-ul este în mentenanță și dacă utilizatorul nu are acces
if ($maintenance_mode && !in_array($_SERVER['REMOTE_ADDR'], $allowed_ips) && !$isAdmin) {
    // Setează header pentru statusul 503 (Service Unavailable)
    header('HTTP/1.1 503 Service Temporarily Unavailable');
    header('Status: 503 Service Temporarily Unavailable');
    header('Retry-After: 3600'); // Sugerează clientului să reîncerce după 1 oră
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentenanță - Spectro Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;700;800&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #111111;
            --color-secondary: #FFFFFF;
            --color-accent: #E0A80D;
            --color-tertiary: #FF0055;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
            background-color: var(--color-primary);
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('../assets/img/maintenance-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: var(--color-secondary);
        }
        
        .maintenance-container {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
            max-width: 500px;
            width: 90%;
            border: 1px solid var(--color-accent);
        }
        
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 20px;
            letter-spacing: 1px;
            color: var(--color-accent);
        }
        
        .icon {
            font-size: 48px;
            margin: 20px 0;
        }
        
        h1 {
            margin-top: 0;
            color: var(--color-secondary);
            font-size: 28px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .message {
            margin: 25px 0;
            line-height: 1.6;
        }
        
        form {
            margin-top: 30px;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 5px;
            border: 1px solid rgba(255,255,255,0.2);
            color: var(--color-secondary);
            text-align: center;
        }
        
        form h3 {
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px;
            color: var(--color-accent);
            font-weight: 600;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            text-align: left;
        }
        
        input {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 15px;
            background-color: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 4px;
            color: var(--color-secondary);
            font-family: 'Montserrat', sans-serif;
            box-sizing: border-box;
        }
        
        input:focus {
            outline: none;
            border-color: var(--color-accent);
        }
        
        button {
            background-color: var(--color-accent);
            color: var(--color-primary);
            border: none;
            padding: 12px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #c99b0c;
        }
        
        .end-time {
            margin-top: 20px;
            font-weight: 500;
            color: var(--color-accent);
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="logo">Spectro Studio</div>
        <div class="icon">🛠️</div>
        <h1>Site în Mentenanță</h1>
        <div class="message">
            <?php if (!empty($maintenance_message)): ?>
                <p><?php echo htmlspecialchars($maintenance_message); ?></p>
            <?php else: ?>
                <p>Ne pare rău pentru inconveniență, dar site-ul nostru este momentan în mentenanță.</p>
                <p>Facem îmbunătățiri pentru a vă oferi o experiență mai bună. Vă rugăm să reveniți în curând!</p>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($maintenance_end_time)): ?>
            <p class="end-time"><strong>Data estimativă de reluare:</strong> <?php echo date('d.m.Y H:i', strtotime($maintenance_end_time)); ?></p>
        <?php else: ?>
            <p class="end-time"><strong>Data estimativă de reluare:</strong> <?php echo date('d.m.Y H:i', strtotime('+24 hours')); ?></p>
        <?php endif; ?>

        <?php if (!$isAdmin): ?>
        <form method="POST">
            <h3>Administrator Login</h3>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Login</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
    exit; // Oprește execuția oricărui alt cod
}
// Dacă site-ul nu este în mentenanță sau utilizatorul are acces, execuția continuă normal
?>