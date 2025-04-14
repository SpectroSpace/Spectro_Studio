<?php
// filepath: C:\xampp\htdocs\Spectro_Studio\backend\spec_admin__login.php
// Inițializarea sesiunii
session_start();

// Definim constanta pentru a permite accesul la fișierul de credențiale
define('IS_AUTHORIZED_ACCESS', true);

// Verificare dacă utilizatorul este deja autentificat
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: spec_admin__index.php');  // Actualizat cu numele corect al dashboard-ului
    exit;
}

// Variabile pentru erori și mesaje
$error = '';
$success = '';

// Verificăm dacă a fost trimis formularul de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obținem datele din formular
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Importăm credențialele
    require_once 'spec_admin__db__credentials.php';
    
    // Verificăm credențialele
    if ($username === $admin_username && password_verify($password, $admin_password_hash)) {
        // Asigurăm-ne că sesiunea este curată și proaspătă
        session_regenerate_id(true);
        
        // Autentificare reușită
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['admin_email'] = $admin_email;
        $_SESSION['last_activity'] = time();
        
        // Salvăm sesiunea înainte de redirecționare
        session_write_close();
        
        // Redirect with JavaScript and a small delay to prevent "message port closed" error
        echo '<script>
            setTimeout(function() {
                window.location.href = "spec_admin__index.php";
            }, 100);
        </script>';
        exit;
    } else {
        // Autentificare eșuată
        $error = 'Numele de utilizator sau parola sunt incorecte.';
        sleep(1); // Delay pentru prevenirea atacurilor
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Spectro Studio Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;700;800&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/sp_backend__styles.css">
</head>
<body class="login-page">
    <div class="form-gradient-container login-container">
        <div class="admin-form">
            <h1 class="form-title">Spectro Studio</h1>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Utilizator:</label>
                    <input type="text" id="username" name="username" required autofocus autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label for="password">Parolă:</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Autentificare</button>
                </div>
            </form>
            
            <div class="back-link">
                <a href="../index.php">← Înapoi la site</a>
            </div>
        </div>
    </div>
</body>
</html>