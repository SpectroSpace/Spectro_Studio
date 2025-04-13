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
require_once '../spec_admin__db__credentials.php';

// Inițializează conexiunea PDO la baza de date
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die('Eroare de conexiune la baza de date: ' . $e->getMessage());
}

// CSS pentru interfață
echo '<style>
    body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; background-color: #111; color: #f5f5f5; }
    h1, h2, h3 { color: #e0a80d; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 20px; background-color: #1e1e1e; }
    th, td { padding: 10px; text-align: left; border: 1px solid #333; }
    th { background-color: #333; color: #e0a80d; }
    .success { color: #4CAF50; }
    .warning { color: #ff9800; }
    .error { color: #f44336; }
    .container { max-width: 1200px; margin: 0 auto; }
    .card { border: 1px solid #333; border-radius: 5px; padding: 15px; margin-bottom: 20px; background-color: #1e1e1e; }
    .btn { display: inline-block; padding: 8px 15px; background: #e0a80d; color: #111; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
    .btn:hover { opacity: 0.9; }
    code, pre { background-color: #0d0d0d; padding: 10px; border-radius: 5px; display: block; white-space: pre-wrap; color: #ddd; }
    .highlight { color: #e0a80d; }
    .spacer { margin-top: 30px; }
</style>';

echo '<div class="container">';
echo '<h1>Verificare Integritate Bază de Date</h1>';

// Funcție pentru afișarea rezultatelor unei interogări
function displayQueryResults($conn, $query, $title, $description = '') {
    echo '<div class="card">';
    echo "<h2>$title</h2>";
    if (!empty($description)) {
        echo "<p>$description</p>";
    }
    
    try {
        $stmt = $conn->query($query);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($results) > 0) {
            echo '<table>';
            // Header pentru tabel
            echo '<tr>';
            foreach (array_keys($results[0]) as $column) {
                echo "<th>$column</th>";
            }
            echo '</tr>';
            
            // Rânduri de date
            foreach ($results as $row) {
                echo '<tr>';
                foreach ($row as $value) {
                    echo "<td>" . (is_null($value) ? "<span class='warning'>NULL</span>" : htmlspecialchars($value)) . "</td>";
                }
                echo '</tr>';
            }
            echo '</table>';
            
            echo '<p>Total rezultate: ' . count($results) . '</p>';
        } else {
            echo '<p class="warning">Nu există rezultate pentru această interogare.</p>';
        }
    } catch (PDOException $e) {
        echo '<p class="error">Eroare la executarea interogării: ' . $e->getMessage() . '</p>';
    }
    
    // Afișează query-ul pentru debugging
    echo '<details>';
    echo '<summary>Vezi interogarea SQL</summary>';
    echo '<code>' . htmlspecialchars($query) . '</code>';
    echo '</details>';
    
    echo '</div>';
}

// 1. Verificare tabel photographers - toate înregistrările
displayQueryResults(
    $conn,
    "SELECT id, name, slug, is_active, created_at, updated_at FROM photographers ORDER BY id",
    "Toți Fotografii",
    "Lista completă a fotografilor din baza de date."
);

// 2. Verificare câmpul is_active pentru fotografi
displayQueryResults(
    $conn,
    "SELECT id, name, is_active FROM photographers WHERE is_active != 1",
    "Fotografi Inactivi",
    "Fotografii care sunt marcați ca inactivi (is_active = 0)"
);

// 3. Verificare câmpul created_at
displayQueryResults(
    $conn,
    "SELECT id, name, created_at FROM photographers WHERE created_at IS NULL",
    "Fotografi fără dată de creare",
    "Fotografii care nu au setat câmpul created_at"
);

// 4. Verificare relații în tabelul photographer_categories
displayQueryResults(
    $conn,
    "SELECT pc.photographer_id, p.name as photographer_name, COUNT(pc.category_id) as category_count 
     FROM photographers p
     LEFT JOIN photographer_categories pc ON p.id = pc.photographer_id 
     GROUP BY pc.photographer_id, p.name",
    "Numărul de categorii per fotograf",
    "Această interogare arată câte categorii are fiecare fotograf."
);

// 5. Verificare posibile probleme de relații
displayQueryResults(
    $conn,
    "SELECT pc.photographer_id, pc.category_id, p.name as photographer_name, gc.name as category_name
     FROM photographer_categories pc
     LEFT JOIN photographers p ON pc.photographer_id = p.id
     LEFT JOIN gallery_categories gc ON pc.category_id = gc.id
     WHERE p.id IS NULL OR gc.id IS NULL",
    "Relații problematice între fotografi și categorii",
    "Această interogare arată dacă există relații în tabelul photographer_categories care referă fotografi sau categorii inexistente."
);

// 6. Verificare duplicitate în tabelul photographer_categories
displayQueryResults(
    $conn,
    "SELECT photographer_id, category_id, COUNT(*) as duplicate_count
     FROM photographer_categories
     GROUP BY photographer_id, category_id
     HAVING COUNT(*) > 1",
    "Duplicate în relațiile fotograf-categorie",
    "Această interogare verifică dacă există duplicate în tabelul photographer_categories."
);

// 7. Verificare referințe la tabelul dashboard
echo '<div class="card">';
echo "<h2>Verificare Dashboard</h2>";
echo "<p>Verificăm cum sunt obținuți fotografii în dashboard-ul principal.</p>";

try {
    // Executăm interogarea din dashboard
    $stmt = $conn->query("SELECT COUNT(*) as count FROM photographers");
    $photographers_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    echo "<p>Număr total de fotografi în baza de date: <strong class='highlight'>$photographers_count</strong></p>";
    
    // Verificăm fotografii activi
    $stmt = $conn->query("SELECT COUNT(*) as count FROM photographers WHERE is_active = 1");
    $active_photographers = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    echo "<p>Număr de fotografi activi: <strong class='highlight'>$active_photographers</strong></p>";
    
    if ($photographers_count != $active_photographers) {
        echo "<p class='warning'>ATENȚIE: Numărul total de fotografi diferă de numărul de fotografi activi. Dacă dashboard-ul afișează doar fotografii activi, aceasta ar putea explica diferența.</p>";
    } else {
        echo "<p class='success'>Toți fotografii sunt activi.</p>";
    }
    
} catch (PDOException $e) {
    echo '<p class="error">Eroare la verificarea dashboard-ului: ' . $e->getMessage() . '</p>';
}
echo '</div>';

// 8. Verificare coerență structură tabel
echo '<div class="card">';
echo "<h2>Structura tabelului photographers</h2>";

try {
    $stmt = $conn->query("DESCRIBE photographers");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<table>';
    echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>';
    
    foreach ($columns as $column) {
        echo '<tr>';
        foreach ($column as $value) {
            echo "<td>" . (is_null($value) ? "<span class='warning'>NULL</span>" : htmlspecialchars($value)) . "</td>";
        }
        echo '</tr>';
    }
    echo '</table>';
    
} catch (PDOException $e) {
    echo '<p class="error">Eroare la obținerea structurii tabelului: ' . $e->getMessage() . '</p>';
}
echo '</div>';

// 9. Soluție SQL pentru probleme potențiale
echo '<div class="card">';
echo "<h2>Soluții SQL</h2>";

echo '<div class="spacer">';
echo "<h3>Actualizare fotografi inactivi</h3>";
echo "<p>Dacă există fotografi marcați incorect ca inactivi, puteți rula următoarea interogare:</p>";
echo '<code>UPDATE photographers SET is_active = 1 WHERE is_active != 1;</code>';
echo '</div>';

echo '<div class="spacer">';
echo "<h3>Curățare relații problematice</h3>";
echo "<p>Dacă există relații în tabelul photographer_categories care referă entități inexistente:</p>";
echo '<code>DELETE FROM photographer_categories WHERE photographer_id NOT IN (SELECT id FROM photographers) OR category_id NOT IN (SELECT id FROM gallery_categories);</code>';
echo '</div>';

echo '<div class="spacer">';
echo "<h3>Eliminare duplicate din relații</h3>";
echo "<p>Pentru a elimina duplicate din tabelul de relații:</p>";
echo '<code>
CREATE TEMPORARY TABLE temp_photographer_categories 
SELECT DISTINCT photographer_id, category_id FROM photographer_categories;

TRUNCATE TABLE photographer_categories;

INSERT INTO photographer_categories (photographer_id, category_id)
SELECT photographer_id, category_id FROM temp_photographer_categories;
</code>';
echo '</div>';

echo '<div class="spacer">';
echo "<h3>Reparare câmpuri NULL</h3>";
echo "<p>Dacă există câmpuri created_at care sunt NULL:</p>";
echo '<code>UPDATE photographers SET created_at = NOW() WHERE created_at IS NULL;</code>';
echo '</div>';

echo '</div>'; // .card

// Adăugăm un link pentru a reveni la dashboard
echo '<div class="spacer">';
echo '<a href="../spec_admin__index.php" class="btn">Înapoi la Dashboard</a>';
echo '</div>';

echo '</div>'; // .container
?>