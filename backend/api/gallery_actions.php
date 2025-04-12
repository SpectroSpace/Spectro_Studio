<?php
// filepath: f:\SITE SPECTRO STUDIO\backend\api\gallery_actions.php
// Procesează acțiunile pentru galerii

// Inițializează sesiunea
session_start();

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acces neautorizat']);
    exit;
}

// Definește constanta pentru a permite accesul la fișierul de credențiale
define('IS_AUTHORIZED_ACCESS', true);

// Include fișierul de credențiale
require_once '../spec_admin__db__credentials.php';

// Conectare la baza de date
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Eroare la conexiunea cu baza de date: ' . $e->getMessage()]);
    exit;
}

// Directoarele pentru încărcare
$upload_dir = '../../assets/img/galleries/';
$upload_url = '../assets/img/galleries/';

// Asigură-te că directorul există
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Determină acțiunea
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'get_galleries':
        // Obține toate galeriile
        try {
            $stmt = $conn->prepare("
                SELECT g.*, COUNT(gp.id) as photo_count 
                FROM galleries g
                LEFT JOIN gallery_photos gp ON g.id = gp.gallery_id
                GROUP BY g.id
                ORDER BY g.created_at DESC
            ");
            $stmt->execute();
            $galleries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Adaugă URL-ul complet pentru imaginile de copertă
            foreach ($galleries as &$gallery) {
                if (!empty($gallery['cover_image'])) {
                    $gallery['cover_image'] = $upload_url . $gallery['cover_image'];
                }
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'galleries' => $galleries]);
        } catch(PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Eroare la obținerea galeriilor: ' . $e->getMessage()]);
        }
        break;
        
    case 'get_gallery':
        // Obține o galerie specifică
        if (!isset($_REQUEST['id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID-ul galeriei nu a fost furnizat']);
            exit;
        }
        
        try {
            $stmt = $conn->prepare("
                SELECT g.*, COUNT(gp.id) as photo_count 
                FROM galleries g
                LEFT JOIN gallery_photos gp ON g.id = gp.gallery_id
                WHERE g.id = :id
                GROUP BY g.id
            ");
            $stmt->bindParam(':id', $_REQUEST['id'], PDO::PARAM_INT);
            $stmt->execute();
            $gallery = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($gallery) {
                if (!empty($gallery['cover_image'])) {
                    $gallery['cover_image'] = $upload_url . $gallery['cover_image'];
                }
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'gallery' => $gallery]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Galeria nu a fost găsită']);
            }
        } catch(PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Eroare la obținerea galeriei: ' . $e->getMessage()]);
        }
        break;
        
    case 'add_gallery':
        // Adaugă o nouă galerie
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Metodă nepermisă']);
            exit;
        }
        
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $cover_image = '';
        
        // Validare
        if (empty($title)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Titlul galeriei este obligatoriu']);
            exit;
        }
        
        // Procesează imaginea de copertă dacă există
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
            $file_info = pathinfo($_FILES['cover']['name']);
            $file_extension = strtolower($file_info['extension']);
            
            // Verifică extensia
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($file_extension, $allowed_extensions)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Tip de fișier nepermis. Sunt permise doar imagini JPG, PNG și GIF.']);
                exit;
            }
            
            // Generează un nume unic pentru fișier
            $file_name = uniqid() . '_cover.' . $file_extension;
            $upload_path = $upload_dir . $file_name;
            
            // Mută fișierul încărcat
            if (move_uploaded_file($_FILES['cover']['tmp_name'], $upload_path)) {
                $cover_image = $file_name;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Eroare la încărcarea imaginii de copertă']);
                exit;
            }
        }
        
        try {
            $stmt = $conn->prepare("INSERT INTO galleries (title, description, cover_image) VALUES (:title, :description, :cover_image)");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':cover_image', $cover_image);
            $stmt->execute();
            
            $gallery_id = $conn->lastInsertId();
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Galeria a fost adăugată cu succes', 'id' => $gallery_id]);
        } catch(PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Eroare la adăugarea galeriei: ' . $e->getMessage()]);
        }
        break;
        
    case 'edit_gallery':
        // Editează o galerie existentă
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Metodă nepermisă']);
            exit;
        }
        
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        
        // Validare
        if (empty($id)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID-ul galeriei nu a fost furnizat']);
            exit;
        }
        
        if (empty($title)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Titlul galeriei este obligatoriu']);
            exit;
        }
        
        try {
            // Obține imaginea de copertă curentă
            $stmt = $conn->prepare("SELECT cover_image FROM galleries WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $gallery = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $cover_image = $gallery['cover_image'] ?? '';
            
            // Procesează imaginea de copertă dacă există una nouă
            if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
                $file_info = pathinfo($_FILES['cover']['name']);
                $file_extension = strtolower($file_info['extension']);
                
                // Verifică extensia
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($file_extension, $allowed_extensions)) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Tip de fișier nepermis. Sunt permise doar imagini JPG, PNG și GIF.']);
                    exit;
                }
                
                // Generează un nume unic pentru fișier
                $file_name = uniqid() . '_cover.' . $file_extension;
                $upload_path = $upload_dir . $file_name;
                
                // Mută fișierul încărcat
                if (move_uploaded_file($_FILES['cover']['tmp_name'], $upload_path)) {
                    // Șterge imaginea veche dacă există
                    if (!empty($cover_image) && file_exists($upload_dir . $cover_image)) {
                        unlink($upload_dir . $cover_image);
                    }
                    
                    $cover_image = $file_name;
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Eroare la încărcarea imaginii de copertă']);
                    exit;
                }
            }
            
            // Actualizează galeria
            $stmt = $conn->prepare("UPDATE galleries SET title = :title, description = :description, cover_image = :cover_image WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':cover_image', $cover_image);
            $stmt->execute();
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Galeria a fost actualizată cu succes']);
        } catch(PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Eroare la actualizarea galeriei: ' . $e->getMessage()]);
        }
        break;
        
    case 'delete_gallery':
        // Șterge o galerie
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Metodă nepermisă']);
            exit;
        }
        
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        // Validare
        if (empty($id)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID-ul galeriei nu a fost furnizat']);
            exit;
        }
        
        try {
            // Obține imaginea de copertă pentru a o șterge
            $stmt = $conn->prepare("SELECT cover_image FROM galleries WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $gallery = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Șterge galeria din baza de date
            $stmt = $conn->prepare("DELETE FROM galleries WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Șterge imaginile asociate cu galeria
            if (!empty($gallery['cover_image']) && file_exists($upload_dir . $gallery['cover_image'])) {
                unlink($upload_dir . $gallery['cover_image']);
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Galeria a fost ștearsă cu succes']);
        } catch(PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Eroare la ștergerea galeriei: ' . $e->getMessage()]);
        }
        break;
        
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Acțiune nerecunoscută']);
        break;
}
?>