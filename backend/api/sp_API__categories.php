<?php
// API de gestionare a categoriilor și subcategoriilor

// Inițializare sesiune
session_start();

// Verifică autentificarea administratorului
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acces neautorizat']);
    exit;
}

// Setează header pentru JSON
header('Content-Type: application/json');

// Stabilim conexiunea la bază de date
if (!isset($conn)) {
    define('IS_AUTHORIZED_ACCESS', true);
    require_once __DIR__ . '/../spec_admin__db__credentials.php';
}

// Funcție pentru generarea unui slug din nume
function generateSlug($name) {
    $slug = strtolower($name);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug); // Elimină caracterele speciale
    $slug = preg_replace('/\s+/', '-', $slug); // Înlocuiește spațiile cu -
    return $slug;
}

// Funcție pentru verificarea existenței unui slug
function slugExists($slug, $table, $exclude_id = null) {
    global $conn;
    
    $sql = "SELECT COUNT(*) as count FROM $table WHERE slug = :slug";
    $params = [':slug' => $slug];
    
    if ($exclude_id !== null) {
        $sql .= " AND id != :exclude_id";
        $params[':exclude_id'] = $exclude_id;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['count'] > 0;
}

// Determină acțiunea
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'list_categories':
        // Listează toate categoriile
        try {
            $stmt = $conn->query("SELECT * FROM gallery_categories ORDER BY order_index, name");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $categories
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Eroare la obținerea categoriilor: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'list_subcategories':
        // Listează subcategoriile, opțional filtrând după categorie
        try {
            $sql = "SELECT s.*, c.name as category_name 
                    FROM gallery_subcategories s 
                    LEFT JOIN gallery_categories c ON s.category_id = c.id";
            
            // Filtrează după categorie dacă e specificată
            $params = [];
            if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
                $sql .= " WHERE s.category_id = :category_id";
                $params[':category_id'] = $_GET['category_id'];
            }
            
            $sql .= " ORDER BY s.category_id, s.order_index, s.name";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $subcategories
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Eroare la obținerea subcategoriilor: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'add_category':
        // Adaugă o categorie nouă
        try {
            // Validare date
            if (empty($_POST['name'])) {
                throw new Exception('Numele categoriei este obligatoriu');
            }
            
            // Generează slug dacă nu este furnizat
            $slug = !empty($_POST['slug']) ? $_POST['slug'] : generateSlug($_POST['name']);
            
            // Verifică unicitatea slug-ului
            if (slugExists($slug, 'gallery_categories')) {
                $slug .= '-' . time();
            }
            
            // Pregătire și execuție interogare
            $stmt = $conn->prepare("
                INSERT INTO gallery_categories (name, slug, description, order_index) 
                VALUES (:name, :slug, :description, :order_index)
            ");
            
            $stmt->execute([
                ':name' => $_POST['name'],
                ':slug' => $slug,
                ':description' => isset($_POST['description']) ? $_POST['description'] : '',
                ':order_index' => isset($_POST['order_index']) ? intval($_POST['order_index']) : 0
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoria a fost adăugată cu succes',
                'id' => $conn->lastInsertId()
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Eroare la adăugarea categoriei: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'edit_category':
        // Editează o categorie existentă
        try {
            // Validare date
            if (empty($_POST['id']) || empty($_POST['name'])) {
                throw new Exception('ID-ul și numele categoriei sunt obligatorii');
            }
            
            $category_id = intval($_POST['id']);
            
            // Verifică existența categoriei
            $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM gallery_categories WHERE id = :id");
            $check_stmt->execute([':id' => $category_id]);
            $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] == 0) {
                throw new Exception('Categoria nu există');
            }
            
            // Generează slug dacă nu este furnizat
            $slug = !empty($_POST['slug']) ? $_POST['slug'] : generateSlug($_POST['name']);
            
            // Verifică unicitatea slug-ului (exceptând acest ID)
            if (slugExists($slug, 'gallery_categories', $category_id)) {
                $slug .= '-' . time();
            }
            
            // Pregătire și execuție interogare
            $stmt = $conn->prepare("
                UPDATE gallery_categories 
                SET name = :name, 
                    slug = :slug, 
                    description = :description, 
                    order_index = :order_index 
                WHERE id = :id
            ");
            
            $stmt->execute([
                ':id' => $category_id,
                ':name' => $_POST['name'],
                ':slug' => $slug,
                ':description' => isset($_POST['description']) ? $_POST['description'] : '',
                ':order_index' => isset($_POST['order_index']) ? intval($_POST['order_index']) : 0
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoria a fost actualizată cu succes'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Eroare la actualizarea categoriei: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'delete_category':
        // Șterge o categorie
        try {
            // Validare date
            if (empty($_POST['id'])) {
                throw new Exception('ID-ul categoriei este obligatoriu');
            }
            
            $category_id = intval($_POST['id']);
            
            // Începe tranzacția pentru a asigura integritatea datelor
            $conn->beginTransaction();
            
            // Mai întâi, obține numele categoriei pentru mesajul de confirmare
            $name_stmt = $conn->prepare("SELECT name FROM gallery_categories WHERE id = :id");
            $name_stmt->execute([':id' => $category_id]);
            $category = $name_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$category) {
                throw new Exception('Categoria nu există');
            }
            
            // Șterge toate subcategoriile asociate
            $subcats_stmt = $conn->prepare("DELETE FROM gallery_subcategories WHERE category_id = :category_id");
            $subcats_stmt->execute([':category_id' => $category_id]);
            
            // Optional: Actualizează toate galeriile care folosesc această categorie 
            // pentru a le atribui o altă categorie sau a le marca ca "necategorizate"
            // Acest pas depinde de decizia de business și structura bazei de date
            
            // Șterge categoria
            $stmt = $conn->prepare("DELETE FROM gallery_categories WHERE id = :id");
            $stmt->execute([':id' => $category_id]);
            
            // Finalizează tranzacția
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoria "' . htmlspecialchars($category['name']) . '" și toate subcategoriile asociate au fost șterse'
            ]);
        } catch (Exception $e) {
            // Anulează tranzacția în caz de eroare
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            
            echo json_encode([
                'success' => false,
                'message' => 'Eroare la ștergerea categoriei: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'add_subcategory':
        // Adaugă o subcategorie nouă
        try {
            // Validare date
            if (empty($_POST['name']) || empty($_POST['category_id'])) {
                throw new Exception('Numele subcategoriei și categoria părinte sunt obligatorii');
            }
            
            $category_id = intval($_POST['category_id']);
            
            // Verifică existența categoriei părinte
            $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM gallery_categories WHERE id = :id");
            $check_stmt->execute([':id' => $category_id]);
            $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] == 0) {
                throw new Exception('Categoria părinte nu există');
            }
            
            // Generează slug dacă nu este furnizat
            $slug = !empty($_POST['slug']) ? $_POST['slug'] : generateSlug($_POST['name']);
            
            // Verifică unicitatea slug-ului
            if (slugExists($slug, 'gallery_subcategories')) {
                $slug .= '-' . time();
            }
            
            // Pregătire și execuție interogare
            $stmt = $conn->prepare("
                INSERT INTO gallery_subcategories (name, slug, description, category_id, order_index) 
                VALUES (:name, :slug, :description, :category_id, :order_index)
            ");
            
            $stmt->execute([
                ':name' => $_POST['name'],
                ':slug' => $slug,
                ':description' => isset($_POST['description']) ? $_POST['description'] : '',
                ':category_id' => $category_id,
                ':order_index' => isset($_POST['order_index']) ? intval($_POST['order_index']) : 0
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Subcategoria a fost adăugată cu succes',
                'id' => $conn->lastInsertId()
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Eroare la adăugarea subcategoriei: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'edit_subcategory':
        // Editează o subcategorie existentă
        try {
            // Validare date
            if (empty($_POST['id']) || empty($_POST['name']) || empty($_POST['category_id'])) {
                throw new Exception('ID-ul, numele subcategoriei și categoria părinte sunt obligatorii');
            }
            
            $subcategory_id = intval($_POST['id']);
            $category_id = intval($_POST['category_id']);
            
            // Verifică existența subcategoriei
            $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM gallery_subcategories WHERE id = :id");
            $check_stmt->execute([':id' => $subcategory_id]);
            $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] == 0) {
                throw new Exception('Subcategoria nu există');
            }
            
            // Verifică existența categoriei părinte
            $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM gallery_categories WHERE id = :id");
            $check_stmt->execute([':id' => $category_id]);
            $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] == 0) {
                throw new Exception('Categoria părinte nu există');
            }
            
            // Generează slug dacă nu este furnizat
            $slug = !empty($_POST['slug']) ? $_POST['slug'] : generateSlug($_POST['name']);
            
            // Verifică unicitatea slug-ului (exceptând acest ID)
            if (slugExists($slug, 'gallery_subcategories', $subcategory_id)) {
                $slug .= '-' . time();
            }
            
            // Pregătire și execuție interogare
            $stmt = $conn->prepare("
                UPDATE gallery_subcategories 
                SET name = :name, 
                    slug = :slug, 
                    description = :description, 
                    category_id = :category_id,
                    order_index = :order_index 
                WHERE id = :id
            ");
            
            $stmt->execute([
                ':id' => $subcategory_id,
                ':name' => $_POST['name'],
                ':slug' => $slug,
                ':description' => isset($_POST['description']) ? $_POST['description'] : '',
                ':category_id' => $category_id,
                ':order_index' => isset($_POST['order_index']) ? intval($_POST['order_index']) : 0
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Subcategoria a fost actualizată cu succes'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Eroare la actualizarea subcategoriei: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'delete_subcategory':
        // Șterge o subcategorie
        try {
            // Validare date
            if (empty($_POST['id'])) {
                throw new Exception('ID-ul subcategoriei este obligatoriu');
            }
            
            $subcategory_id = intval($_POST['id']);
            
            // Începe tranzacția
            $conn->beginTransaction();
            
            // Mai întâi, obține numele subcategoriei pentru mesajul de confirmare
            $name_stmt = $conn->prepare("SELECT name FROM gallery_subcategories WHERE id = :id");
            $name_stmt->execute([':id' => $subcategory_id]);
            $subcategory = $name_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$subcategory) {
                throw new Exception('Subcategoria nu există');
            }
            
            // Optional: Actualizează toate galeriile care folosesc această subcategorie
            // Similar cu procesul de ștergere a categoriei
            
            // Șterge subcategoria
            $stmt = $conn->prepare("DELETE FROM gallery_subcategories WHERE id = :id");
            $stmt->execute([':id' => $subcategory_id]);
            
            // Finalizează tranzacția
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Subcategoria "' . htmlspecialchars($subcategory['name']) . '" a fost ștearsă'
            ]);
        } catch (Exception $e) {
            // Anulează tranzacția în caz de eroare
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            
            echo json_encode([
                'success' => false,
                'message' => 'Eroare la ștergerea subcategoriei: ' . $e->getMessage()
            ]);
        }
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Acțiune nerecunoscută'
        ]);
}
?>