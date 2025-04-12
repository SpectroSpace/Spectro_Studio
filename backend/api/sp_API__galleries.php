<?php
// filepath: f:\SITE SPECTRO STUDIO\backend\api\sp_API__galleries.php
// API pentru gestionarea galeriilor

// Inițializarea sesiunii
session_start();

// Header pentru JSON
header('Content-Type: application/json');

// Verifică dacă utilizatorul este autentificat ca administrator
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acces neautorizat']);
    exit;
}

// Definește constanta pentru a permite accesul la fișierul de credențiale
define('IS_AUTHORIZED_ACCESS', true);

// Include fișierul de credențiale pentru a avea acces la conexiunea cu baza de date
require_once '../spec_admin__db__credentials.php';

// Inițializează conexiunea PDO la baza de date
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Eroare de conexiune la baza de date: ' . $e->getMessage()]);
    exit;
}

// Funcție pentru generarea slug-urilor
function generateSlug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    return trim($slug, '-');
}

// Rută API pentru a obține subcategorii pentru o categorie dată
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_subcategories') {
    try {
        if (!isset($_GET['category_id'])) {
            echo json_encode(['success' => false, 'message' => 'ID-ul categoriei nu a fost specificat']);
            exit;
        }

        $category_id = (int)$_GET['category_id'];
        
        $query = "SELECT id, name, slug, description FROM gallery_subcategories 
                  WHERE category_id = :category_id
                  ORDER BY order_index, name";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Subcategorii încărcate cu succes', 
            'data' => $subcategories
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Eroare la încărcarea subcategoriilor: ' . $e->getMessage()]);
    }
    exit;
}

// Lista de galerii
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'list') {
    try {
        // Verificăm dacă tabela galleries există
        $stmt = $conn->query("SHOW TABLES LIKE 'galleries'");
        if ($stmt->rowCount() == 0) {
            // Creăm tabela dacă nu există
            $sql = "CREATE TABLE galleries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE,
                description TEXT,
                cover_image VARCHAR(255),
                category_id INT NULL,
                subcategory_id INT NULL,
                is_featured TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES gallery_categories(id) ON DELETE SET NULL,
                FOREIGN KEY (subcategory_id) REFERENCES gallery_subcategories(id) ON DELETE SET NULL
            )";
            $conn->exec($sql);
        }
        
        // Verificăm dacă avem coloanele pentru categorii
        $hasColumns = true;
        try {
            $stmt = $conn->query("SELECT category_id, subcategory_id FROM galleries LIMIT 1");
        } catch(PDOException $e) {
            $hasColumns = false;
        }
        
        // Adaugă coloanele dacă nu există
        if (!$hasColumns) {
            try {
                $conn->exec("ALTER TABLE galleries 
                            ADD COLUMN category_id INT NULL,
                            ADD COLUMN subcategory_id INT NULL,
                            ADD FOREIGN KEY (category_id) REFERENCES gallery_categories(id) ON DELETE SET NULL,
                            ADD FOREIGN KEY (subcategory_id) REFERENCES gallery_subcategories(id) ON DELETE SET NULL");
            } catch(PDOException $e) {
                // Ignoră eroarea dacă nu poate adăuga foreign keys (poate că tabelele de categorii nu există încă)
            }
        }
        
        // Obține lista de galerii cu informații despre categorii
        $sql = "SELECT g.*, 
                c.name as category_name, 
                s.name as subcategory_name,
                (SELECT COUNT(*) FROM images WHERE gallery_id = g.id) as photo_count
                FROM galleries g
                LEFT JOIN gallery_categories c ON g.category_id = c.id
                LEFT JOIN gallery_subcategories s ON g.subcategory_id = s.id
                ORDER BY g.created_at DESC";
                
        $stmt = $conn->query($sql);
        $galleries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'message' => 'Galerii încărcate cu succes', 'data' => $galleries]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Eroare la încărcarea galeriilor: ' . $e->getMessage()]);
    }
    exit;
}

// Adăugare galerie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    try {
        // Verifică dacă avem titlul
        if (!isset($_POST['title']) || trim($_POST['title']) === '') {
            echo json_encode(['success' => false, 'message' => 'Titlul galeriei este obligatoriu']);
            exit;
        }
        
        // Pregătire date
        $title = trim($_POST['title']);
        $slug = isset($_POST['slug']) && trim($_POST['slug']) !== '' ? generateSlug($_POST['slug']) : generateSlug($title);
        $description = isset($_POST['description']) ? trim($_POST['description']) : null;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $category_id = isset($_POST['category_id']) && !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $subcategory_id = isset($_POST['subcategory_id']) && !empty($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : null;
        
        // Verifică dacă slug-ul este unic
        $stmt = $conn->prepare("SELECT id FROM galleries WHERE slug = :slug");
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $slug = $slug . '-' . time(); // Adaugă timestamp pentru a face slug-ul unic
        }
        
        // Procesare imagine copertă, dacă există
        $cover_image = null;
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['cover_image']['tmp_name'];
            $file_name = $_FILES['cover_image']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Verifică extensia
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($file_ext, $allowed_exts)) {
                echo json_encode(['success' => false, 'message' => 'Formatul imaginii nu este acceptat. Folosiți: ' . implode(', ', $allowed_exts)]);
                exit;
            }
            
            // Generează nume unic pentru fișier
            $new_file_name = 'gallery-' . time() . '-' . uniqid() . '.' . $file_ext;
            $upload_path = '../../assets/img/galleries/';
            
            // Creează directorul dacă nu există
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0755, true);
            }
            
            // Mută fișierul
            if (move_uploaded_file($file_tmp, $upload_path . $new_file_name)) {
                $cover_image = $new_file_name;
            } else {
                echo json_encode(['success' => false, 'message' => 'Eroare la încărcarea imaginii de copertă']);
                exit;
            }
        }
        
        // Inserare în baza de date
        $sql = "INSERT INTO galleries (title, slug, description, cover_image, is_featured, category_id, subcategory_id) 
                VALUES (:title, :slug, :description, :cover_image, :is_featured, :category_id, :subcategory_id)";
                
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':cover_image', $cover_image);
        $stmt->bindParam(':is_featured', $is_featured);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':subcategory_id', $subcategory_id);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Galeria a fost adăugată cu succes']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Eroare la adăugarea galeriei: ' . $e->getMessage()]);
    }
    exit;
}

// Detalii galerie
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    try {
        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID-ul galeriei nu a fost specificat']);
            exit;
        }
        
        $gallery_id = (int)$_GET['id'];
        
        $sql = "SELECT g.*, 
                c.name as category_name, 
                s.name as subcategory_name
                FROM galleries g
                LEFT JOIN gallery_categories c ON g.category_id = c.id
                LEFT JOIN gallery_subcategories s ON g.subcategory_id = s.id
                WHERE g.id = :id";
                
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $gallery_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $gallery = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$gallery) {
            echo json_encode(['success' => false, 'message' => 'Galeria nu a fost găsită']);
            exit;
        }
        
        echo json_encode(['success' => true, 'message' => 'Galerie încărcată cu succes', 'data' => $gallery]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Eroare la încărcarea galeriei: ' . $e->getMessage()]);
    }
    exit;
}

// Actualizare galerie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    try {
        if (!isset($_POST['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID-ul galeriei nu a fost specificat']);
            exit;
        }
        
        // Verifică dacă avem titlul
        if (!isset($_POST['title']) || trim($_POST['title']) === '') {
            echo json_encode(['success' => false, 'message' => 'Titlul galeriei este obligatoriu']);
            exit;
        }
        
        $gallery_id = (int)$_POST['id'];
        $title = trim($_POST['title']);
        $description = isset($_POST['description']) ? trim($_POST['description']) : null;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $category_id = isset($_POST['category_id']) && !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $subcategory_id = isset($_POST['subcategory_id']) && !empty($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : null;
        
        // Verifică dacă galeria există
        $stmt = $conn->prepare("SELECT slug, cover_image FROM galleries WHERE id = :id");
        $stmt->bindParam(':id', $gallery_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $gallery = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$gallery) {
            echo json_encode(['success' => false, 'message' => 'Galeria nu a fost găsită']);
            exit;
        }
        
        // Actualizare slug dacă s-a specificat
        $slug = $gallery['slug'];
        if (isset($_POST['slug']) && trim($_POST['slug']) !== '') {
            $new_slug = generateSlug($_POST['slug']);
            
            if ($new_slug !== $slug) {
                // Verifică dacă noul slug este unic
                $stmt = $conn->prepare("SELECT id FROM galleries WHERE slug = :slug AND id != :id");
                $stmt->bindParam(':slug', $new_slug);
                $stmt->bindParam(':id', $gallery_id, PDO::PARAM_INT);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $new_slug = $new_slug . '-' . time(); // Adaugă timestamp pentru a face slug-ul unic
                }
                
                $slug = $new_slug;
            }
        }
        
        // Procesare imagine copertă, dacă există
        $cover_image = $gallery['cover_image'];
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['cover_image']['tmp_name'];
            $file_name = $_FILES['cover_image']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Verifică extensia
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($file_ext, $allowed_exts)) {
                echo json_encode(['success' => false, 'message' => 'Formatul imaginii nu este acceptat. Folosiți: ' . implode(', ', $allowed_exts)]);
                exit;
            }
            
            // Generează nume unic pentru fișier
            $new_file_name = 'gallery-' . time() . '-' . uniqid() . '.' . $file_ext;
            $upload_path = '../../assets/img/galleries/';
            
            // Creează directorul dacă nu există
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0755, true);
            }
            
            // Mută fișierul
            if (move_uploaded_file($file_tmp, $upload_path . $new_file_name)) {
                // Șterge vechea imagine dacă există
                if ($cover_image && file_exists($upload_path . $cover_image)) {
                    unlink($upload_path . $cover_image);
                }
                
                $cover_image = $new_file_name;
            } else {
                echo json_encode(['success' => false, 'message' => 'Eroare la încărcarea imaginii de copertă']);
                exit;
            }
        }
        
        // Actualizare în baza de date
        $sql = "UPDATE galleries 
                SET title = :title, 
                    slug = :slug, 
                    description = :description, 
                    cover_image = :cover_image, 
                    is_featured = :is_featured,
                    category_id = :category_id,
                    subcategory_id = :subcategory_id,
                    updated_at = CURRENT_TIMESTAMP 
                WHERE id = :id";
                
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $gallery_id, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':cover_image', $cover_image);
        $stmt->bindParam(':is_featured', $is_featured);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':subcategory_id', $subcategory_id);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Galeria a fost actualizată cu succes']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Eroare la actualizarea galeriei: ' . $e->getMessage()]);
    }
    exit;
}

// Ștergere galerie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    try {
        if (!isset($_POST['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID-ul galeriei nu a fost specificat']);
            exit;
        }
        
        $gallery_id = (int)$_POST['id'];
        
        // Verifică dacă galeria există și obține imaginea de copertă
        $stmt = $conn->prepare("SELECT cover_image FROM galleries WHERE id = :id");
        $stmt->bindParam(':id', $gallery_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $gallery = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$gallery) {
            echo json_encode(['success' => false, 'message' => 'Galeria nu a fost găsită']);
            exit;
        }
        
        // Șterge imaginea de copertă dacă există
        if ($gallery['cover_image'] && file_exists('../../assets/img/galleries/' . $gallery['cover_image'])) {
            unlink('../../assets/img/galleries/' . $gallery['cover_image']);
        }
        
        // Șterge galeria din baza de date
        $stmt = $conn->prepare("DELETE FROM galleries WHERE id = :id");
        $stmt->bindParam(':id', $gallery_id, PDO::PARAM_INT);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Galeria a fost ștearsă cu succes']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Eroare la ștergerea galeriei: ' . $e->getMessage()]);
    }
    exit;
}

// Răspuns pentru cereri necunoscute
echo json_encode(['success' => false, 'message' => 'Acțiune necunoscută sau metodă nepermisă']);
?>