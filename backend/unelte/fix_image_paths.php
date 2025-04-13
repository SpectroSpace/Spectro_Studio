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

echo '<h1>Diagnosticare Imagini Fotografi</h1>';

// Obține lista de fotografi și căile lor de imagine
$query = "SELECT id, name, profile_image FROM photographers WHERE profile_image IS NOT NULL";
$stmt = $conn->prepare($query);
$stmt->execute();
$photographers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo '<table border="1" cellpadding="5">';
echo '<tr><th>ID</th><th>Nume</th><th>Cale Imagine în BD</th><th>Cale Fizică</th><th>Există?</th></tr>';

foreach ($photographers as $photographer) {
    $image_path = $photographer['profile_image'];
    $physical_path = __DIR__ . '/..' . $image_path;
    
    // Verifică dacă fișierul există fizic
    $exists = file_exists($physical_path) ? 'DA' : 'NU';
    $color = $exists === 'DA' ? 'green' : 'red';
    
    echo '<tr>';
    echo '<td>' . $photographer['id'] . '</td>';
    echo '<td>' . $photographer['name'] . '</td>';
    echo '<td>' . $image_path . '</td>';
    echo '<td>' . $physical_path . '</td>';
    echo '<td style="color: ' . $color . '; font-weight: bold;">' . $exists . '</td>';
    echo '</tr>';
}

echo '</table>';

// Verifică directorul uploads/photographers
echo '<h2>Verificare Director Fotografi</h2>';
$target_dir = __DIR__ . '/../uploads/photographers/';

if (is_dir($target_dir)) {
    echo '<p>Directorul <strong>' . $target_dir . '</strong> există.</p>';
    
    // Listează fișierele din acest director
    $files = scandir($target_dir);
    $files = array_diff($files, ['.', '..']);
    
    if (count($files) > 0) {
        echo '<p>Fișiere găsite în director:</p>';
        echo '<ul>';
        foreach ($files as $file) {
            echo '<li>' . $file . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p style="color: red;">Directorul este gol! Nu există fișiere de imagine.</p>';
        
        echo '<h3>Pași pentru rezolvare:</h3>';
        echo '<ol>';
        echo '<li>Verificați dacă ați încărcat imaginile în directorul <strong>' . $target_dir . '</strong></li>';
        echo '<li>Asigurați-vă că permisiunile directorului permit citirea/scrierea fișierelor</li>';
        echo '<li>Verificați procesul de upload din formularul de adăugare/editare fotografi</li>';
        echo '</ol>';
    }
} else {
    echo '<p style="color: red;">Directorul <strong>' . $target_dir . '</strong> nu există!</p>';
    
    // Încearcă să creeze directorul
    echo '<p>Se încearcă crearea directorului...</p>';
    if (mkdir($target_dir, 0755, true)) {
        echo '<p style="color: green;">Directorul a fost creat cu succes.</p>';
    } else {
        echo '<p style="color: red;">Nu s-a putut crea directorul. Verificați permisiunile.</p>';
    }
}

// Soluția pentru problema cu căile relative în modulul photographers
echo '<h2>Soluție pentru Problema Căilor Relative</h2>';
echo '<p>În modulul fotografilor, imaginile sunt afișate folosind o cale relativă care poate cauza probleme. Pentru a rezolva acest lucru, urmați acești pași:</p>';

echo '<ol>';
echo '<li>Deschideți fișierul <strong>backend/modules/photographers/index.php</strong></li>';
echo '<li>Localizați codul care afișează imaginile fotografilor</li>';
echo '<li>Înlocuiți codul de afișare a imaginii cu următorul cod corect:</li>';
echo '</ol>';

echo '<pre style="background-color: #f5f5f5; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
&lt;?php if (!empty($photographer[\'profile_image\'])): ?&gt;
    &lt;?php
    // Fix image path construction - ensure we always use the correct relative path
    $imagePath = $photographer[\'profile_image\'];
    $correctPath = (strpos($imagePath, \'/\') === 0) ? \'../..\' . $imagePath : \'../../\' . $imagePath;
    ?&gt;
    &lt;img src="&lt;?php echo htmlspecialchars($correctPath); ?&gt;" 
         alt="&lt;?php echo htmlspecialchars($photographer[\'name\']); ?&gt;" 
         style="max-width: 60px; max-height: 60px; object-fit: cover;"&gt;
&lt;?php else: ?&gt;
    &lt;div class="no-image"&gt;Fără imagine&lt;/div&gt;
&lt;?php endif; ?&gt;
</pre>';

echo '<p><a href="spec_admin__index.php" style="display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;">Înapoi la Dashboard</a></p>';
?>