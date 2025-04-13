<?php
/**
 * API pentru gestionarea fotografilor - Spectro Studio
 * 
 * Acest endpoint gestionează operațiuni CRUD pentru fotografi:
 * - Obținerea listei de fotografi (GET)
 * - Obținerea detaliilor unui fotograf specific (GET cu ID)
 * - Adăugarea unui nou fotograf (POST)
 * - Actualizarea unui fotograf existent (POST cu ID)
 * - Ștergerea unui fotograf (POST cu ID și action=delete)
 */

// Inițializarea sesiunii
session_start();

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Acces interzis. Trebuie să fiți autentificat.'
    ]);
    exit;
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
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Eroare de conexiune la baza de date: ' . $e->getMessage()
    ]);
    exit;
}

// Determină tipul de cerere și acțiunea
$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

// Procesează cererea în funcție de acțiune
try {
    switch ($action) {
        case 'list':
            // Obține lista de fotografi
            $query = "SELECT DISTINCT * FROM photographers ORDER BY name";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $photographers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Deduplicare folosind un array asociativ indexat după ID
            $uniquePhotographers = [];
            foreach ($photographers as $photographer) {
                $uniquePhotographers[$photographer['id']] = $photographer;
            }
            $photographers = array_values($uniquePhotographers);
            
            // Log pentru debugging
            error_log("API: Număr total de fotografi după deduplicare: " . count($photographers));
            foreach ($photographers as $photographer) {
                error_log("API: Fotograf ID: " . $photographer['id'] . ", Nume: " . $photographer['name']);
            }
            
            // Obține specializările pentru fiecare fotograf
            foreach ($photographers as &$photographer) {
                $stmtCats = $conn->prepare("
                    SELECT category_id FROM photographer_categories 
                    WHERE photographer_id = ?
                ");
                $stmtCats->execute([$photographer['id']]);
                $photographer['specializations'] = array_column($stmtCats->fetchAll(PDO::FETCH_ASSOC), 'category_id');
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $photographers
            ]);
            break;
            
        case 'get':
            // Verifică dacă ID-ul este furnizat
            if (!isset($_GET['id']) || empty($_GET['id'])) {
                throw new Exception('ID-ul fotografului nu a fost furnizat.');
            }
            
            $photographer_id = (int) $_GET['id'];
            
            // Obține detaliile fotografului
            $stmt = $conn->prepare("SELECT * FROM photographers WHERE id = ?");
            $stmt->execute([$photographer_id]);
            $photographer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$photographer) {
                throw new Exception('Fotograful nu a fost găsit.');
            }
            
            // Obține specializările fotografului
            $stmtCats = $conn->prepare("
                SELECT category_id FROM photographer_categories 
                WHERE photographer_id = ?
            ");
            $stmtCats->execute([$photographer_id]);
            $photographer['specializations'] = array_column($stmtCats->fetchAll(PDO::FETCH_ASSOC), 'category_id');
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $photographer
            ]);
            break;
            
        case 'add':
            // Verifică dacă numele este furnizat
            if (!isset($_POST['name']) || empty($_POST['name'])) {
                throw new Exception('Numele fotografului este obligatoriu.');
            }
            
            $name = trim($_POST['name']);
            $slug = trim($_POST['slug'] ?? '');
            
            // Generare slug dacă nu e completat
            if (empty($slug)) {
                $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            }
            
            // Verifică dacă slug-ul există
            $checkSlug = $conn->prepare("SELECT COUNT(*) FROM photographers WHERE slug = ?");
            $checkSlug->execute([$slug]);
            
            if ($checkSlug->fetchColumn() > 0) {
                $slug = $slug . '-' . time(); // Adaugă timestamp pentru a face slug-ul unic
            }
            
            // Pregătește interogarea
            $stmt = $conn->prepare("
                INSERT INTO photographers (
                    name, slug, bio, country, city, profile_image, email, 
                    phone, website, facebook, instagram, experience_years, is_featured, is_active
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
            ");
            
            // Execută interogarea
            $stmt->execute([
                $name,
                $slug,
                $_POST['bio'] ?? null,
                $_POST['country'] ?? null,
                $_POST['city'] ?? null,
                $_POST['profile_image'] ?? null,
                $_POST['email'] ?? null,
                $_POST['phone'] ?? null,
                $_POST['website'] ?? null,
                $_POST['facebook'] ?? null,
                $_POST['instagram'] ?? null,
                !empty($_POST['experience_years']) ? (int)$_POST['experience_years'] : null,
                isset($_POST['is_featured']) && $_POST['is_featured'] === '1' ? 1 : 0,
                isset($_POST['is_active']) && $_POST['is_active'] === '1' ? 1 : 0
            ]);
            
            $photographer_id = $conn->lastInsertId();
            
            // Adaugă specializările dacă există
            if (isset($_POST['specializations']) && is_array($_POST['specializations'])) {
                $stmtCats = $conn->prepare("
                    INSERT INTO photographer_categories (photographer_id, category_id) 
                    VALUES (?, ?)
                ");
                
                foreach ($_POST['specializations'] as $category_id) {
                    $stmtCats->execute([$photographer_id, (int)$category_id]);
                }
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Fotograful a fost adăugat cu succes.',
                'id' => $photographer_id
            ]);
            break;
            
        case 'update':
            // Verifică dacă ID-ul este furnizat
            if (!isset($_POST['id']) || empty($_POST['id'])) {
                throw new Exception('ID-ul fotografului nu a fost furnizat.');
            }
            
            $photographer_id = (int) $_POST['id'];
            
            // Verifică dacă fotograful există
            $stmt = $conn->prepare("SELECT COUNT(*) FROM photographers WHERE id = ?");
            $stmt->execute([$photographer_id]);
            
            if ($stmt->fetchColumn() === 0) {
                throw new Exception('Fotograful nu a fost găsit.');
            }
            
            // Verifică dacă numele este furnizat
            if (!isset($_POST['name']) || empty($_POST['name'])) {
                throw new Exception('Numele fotografului este obligatoriu.');
            }
            
            $name = trim($_POST['name']);
            $slug = trim($_POST['slug'] ?? '');
            
            // Generare slug dacă nu e completat
            if (empty($slug)) {
                $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            }
            
            // Verifică dacă slug-ul există (dar nu e al acestui fotograf)
            $checkSlug = $conn->prepare("SELECT COUNT(*) FROM photographers WHERE slug = ? AND id != ?");
            $checkSlug->execute([$slug, $photographer_id]);
            
            if ($checkSlug->fetchColumn() > 0) {
                $slug = $slug . '-' . time(); // Adaugă timestamp pentru a face slug-ul unic
            }
            
            // Pregătește interogarea de actualizare
            $stmt = $conn->prepare("
                UPDATE photographers SET
                    name = ?,
                    slug = ?,
                    bio = ?,
                    country = ?,
                    city = ?,
                    profile_image = ?,
                    email = ?,
                    phone = ?,
                    website = ?,
                    facebook = ?,
                    instagram = ?,
                    experience_years = ?,
                    is_featured = ?,
                    is_active = ?
                WHERE id = ?
            ");
            
            // Execută interogarea
            $stmt->execute([
                $name,
                $slug,
                $_POST['bio'] ?? null,
                $_POST['country'] ?? null,
                $_POST['city'] ?? null,
                $_POST['profile_image'] ?? null,
                $_POST['email'] ?? null,
                $_POST['phone'] ?? null,
                $_POST['website'] ?? null,
                $_POST['facebook'] ?? null,
                $_POST['instagram'] ?? null,
                !empty($_POST['experience_years']) ? (int)$_POST['experience_years'] : null,
                isset($_POST['is_featured']) && $_POST['is_featured'] === '1' ? 1 : 0,
                isset($_POST['is_active']) && $_POST['is_active'] === '1' ? 1 : 0,
                $photographer_id
            ]);
            
            // Actualizează specializările
            // Mai întâi șterge specializările existente
            $stmtDeleteCats = $conn->prepare("DELETE FROM photographer_categories WHERE photographer_id = ?");
            $stmtDeleteCats->execute([$photographer_id]);
            
            // Adaugă noile specializări
            if (isset($_POST['specializations']) && is_array($_POST['specializations'])) {
                $stmtCats = $conn->prepare("
                    INSERT INTO photographer_categories (photographer_id, category_id) 
                    VALUES (?, ?)
                ");
                
                foreach ($_POST['specializations'] as $category_id) {
                    $stmtCats->execute([$photographer_id, (int)$category_id]);
                }
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Fotograful a fost actualizat cu succes.'
            ]);
            break;
            
        case 'delete':
            // Verifică dacă ID-ul este furnizat
            if (!isset($_POST['id']) || empty($_POST['id'])) {
                throw new Exception('ID-ul fotografului nu a fost furnizat.');
            }
            
            $photographer_id = (int) $_POST['id'];
            
            // Șterge fotograful din baza de date
            $stmt = $conn->prepare("DELETE FROM photographers WHERE id = ?");
            $stmt->execute([$photographer_id]);
            
            // Verifică dacă fotograful a fost șters
            if ($stmt->rowCount() === 0) {
                throw new Exception('Fotograful nu a fost găsit sau nu a putut fi șters.');
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Fotograful a fost șters cu succes.'
            ]);
            break;
            
        default:
            throw new Exception('Acțiune necunoscută.');
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>