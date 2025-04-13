<?php
// Inițializarea sesiunii
session_start();

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die('Acces interzis. Trebuie să fiți autentificat.');
}

// Definește constanta pentru a permite accesul la fișierul de credențiale
define('IS_AUTHORIZED_ACCESS', true);

// Include fișierul de credențiale pentru baza de date
require_once 'spec_admin__db__credentials.php';

// Inițializează conexiunea PDO la baza de date
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die('Eroare de conexiune la baza de date: ' . $e->getMessage());
}

// CSS pentru interfață
echo '<style>
    body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
    h1, h2 { color: #333; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
    th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f5f5f5; }
    .btn { display: inline-block; padding: 8px 15px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
    .btn-warning { background: #ff9800; }
    .btn:hover { opacity: 0.9; }
    .container { max-width: 1200px; margin: 0 auto; }
    .card { border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 20px; }
    .badge { display: inline-block; padding: 3px 6px; border-radius: 4px; font-size: 11px; font-weight: bold; margin-right: 5px; }
    .badge-success { background-color: #4CAF50; color: white; }
    .badge-danger { background-color: #f44336; color: white; }
    .checkbox-group { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
    .checkbox-item { margin-right: 15px; }
    .success-msg { color: #4CAF50; font-weight: bold; }
    .error-msg { color: #f44336; font-weight: bold; }
</style>';

echo '<div class="container">';
echo '<h1>Diagnosticare și Reparare Specializări Fotografi</h1>';

// Procesează acțiunile
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'update_specializations') {
        try {
            // Mai întâi șterge asocierile existente pentru fotograful selectat
            $photographer_id = (int)$_POST['photographer_id'];
            $stmt = $conn->prepare("DELETE FROM photographer_categories WHERE photographer_id = ?");
            $stmt->execute([$photographer_id]);
            
            // Adaugă noile asocieri
            if (isset($_POST['specializations']) && is_array($_POST['specializations'])) {
                $stmtInsert = $conn->prepare("INSERT INTO photographer_categories (photographer_id, category_id) VALUES (?, ?)");
                
                foreach ($_POST['specializations'] as $category_id) {
                    $stmtInsert->execute([$photographer_id, (int)$category_id]);
                }
                
                echo '<div class="card">';
                echo '<p class="success-msg">Specializările fotografului au fost actualizate cu succes!</p>';
                echo '</div>';
            } else {
                echo '<div class="card">';
                echo '<p>Toate specializările au fost eliminate pentru acest fotograf.</p>';
                echo '</div>';
            }
        } catch (PDOException $e) {
            echo '<div class="card">';
            echo '<p class="error-msg">Eroare la actualizarea specializărilor: ' . $e->getMessage() . '</p>';
            echo '</div>';
        }
    }
}

// Obține toți fotografii
try {
    $stmt = $conn->query("SELECT * FROM photographers ORDER BY name");
    $photographers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obține toate categoriile
    $stmt = $conn->query("SELECT * FROM gallery_categories ORDER BY order_index, name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Pentru fiecare fotograf, obține specializările
    foreach ($photographers as &$photographer) {
        $stmt = $conn->prepare("
            SELECT pc.category_id, gc.name as category_name
            FROM photographer_categories pc
            JOIN gallery_categories gc ON pc.category_id = gc.id
            WHERE pc.photographer_id = ?
        ");
        $stmt->execute([$photographer['id']]);
        $photographer['specializations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo '<h2>Fotografi și Specializările lor</h2>';
    echo '<div class="card">';
    echo '<p>Mai jos puteți vedea toți fotografii și specializările lor actuale. Puteți edita specializările pentru fiecare fotograf în parte.</p>';
    echo '</div>';
    
    if (count($photographers) > 0) {
        echo '<table>';
        echo '<tr><th>ID</th><th>Nume</th><th>Specializări Curente</th><th>Acțiuni</th></tr>';
        
        foreach ($photographers as $photographer) {
            echo '<tr>';
            echo '<td>' . $photographer['id'] . '</td>';
            echo '<td>' . htmlspecialchars($photographer['name']) . '</td>';
            echo '<td>';
            
            if (!empty($photographer['specializations'])) {
                foreach ($photographer['specializations'] as $spec) {
                    echo '<span class="badge badge-success">' . htmlspecialchars($spec['category_name']) . '</span>';
                }
            } else {
                echo '<span class="badge badge-danger">Fără specializări</span>';
            }
            
            echo '</td>';
            echo '<td><button class="btn" onclick="showEditForm(' . $photographer['id'] . ', \'' . htmlspecialchars(addslashes($photographer['name'])) . '\')">Editează Specializările</button></td>';
            echo '</tr>';
        }
        
        echo '</table>';
    } else {
        echo '<div class="card">';
        echo '<p>Nu există fotografi înregistrați.</p>';
        echo '</div>';
    }
    
    // Formular pentru editarea specializărilor (ascuns inițial)
    echo '<div id="edit-form" style="display: none;" class="card">';
    echo '<h2>Editează Specializările pentru <span id="photographerName"></span></h2>';
    echo '<form method="post">';
    echo '<input type="hidden" name="action" value="update_specializations">';
    echo '<input type="hidden" name="photographer_id" id="photographerId" value="">';
    echo '<div class="checkbox-group">';
    
    foreach ($categories as $category) {
        echo '<div class="checkbox-item">';
        echo '<input type="checkbox" name="specializations[]" value="' . $category['id'] . '" id="cat-' . $category['id'] . '">';
        echo '<label for="cat-' . $category['id'] . '">' . htmlspecialchars($category['name']) . '</label>';
        echo '</div>';
    }
    
    echo '</div>';
    echo '<div style="margin-top: 20px;">';
    echo '<button type="submit" class="btn">Salvează Modificările</button>';
    echo '<button type="button" class="btn btn-warning" style="margin-left: 10px;" onclick="hideEditForm()">Anulează</button>';
    echo '</div>';
    echo '</form>';
    echo '</div>';
    
    // JavaScript pentru gestionarea interacțiunii
    echo '<script>
    // Datele fotografilor și specializărilor lor
    const photographers = ' . json_encode($photographers) . ';
    
    function showEditForm(photographerId, photographerName) {
        document.getElementById("photographerId").value = photographerId;
        document.getElementById("photographerName").textContent = photographerName;
        
        // Resetați toate checkbox-urile
        const checkboxes = document.querySelectorAll("input[name=\'specializations[]\']");
        checkboxes.forEach(cb => cb.checked = false);
        
        // Găsiți fotograful și setați specializările lui
        const photographer = photographers.find(p => p.id == photographerId);
        if (photographer && photographer.specializations) {
            photographer.specializations.forEach(spec => {
                const checkbox = document.getElementById("cat-" + spec.category_id);
                if (checkbox) checkbox.checked = true;
            });
        }
        
        document.getElementById("edit-form").style.display = "block";
        document.getElementById("edit-form").scrollIntoView({ behavior: "smooth" });
    }
    
    function hideEditForm() {
        document.getElementById("edit-form").style.display = "none";
    }
    </script>';
    
} catch (PDOException $e) {
    echo '<div class="card">';
    echo '<p class="error-msg">Eroare la obținerea datelor: ' . $e->getMessage() . '</p>';
    echo '</div>';
}

echo '<div style="margin-top: 20px;">';
echo '<a href="spec_admin__index.php" class="btn">Înapoi la Dashboard</a>';
echo '</div>';

echo '</div>'; // .container
?>