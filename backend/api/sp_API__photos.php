<?php
// Verificare autorizare
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Content-Type: application/json');
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
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Eroare la conexiunea cu baza de date: ' . $e->getMessage()]);
    exit;
}

// Definirea directoarelor pentru fișiere încărcate
$upload_base_dir = '../../uploads/photos/';
$upload_original_dir = $upload_base_dir . 'originals/';
$upload_large_dir = $upload_base_dir . 'large/';
$upload_medium_dir = $upload_base_dir . 'medium/';
$upload_thumb_dir = $upload_base_dir . 'thumbnails/';
$upload_web_dir = $upload_base_dir . 'web/';

// Asigură existența directoarelor
$dirs = [$upload_base_dir, $upload_original_dir, $upload_large_dir, $upload_medium_dir, $upload_thumb_dir, $upload_web_dir];
foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Funcția pentru generarea unui nume de fișier unic
function generateUniqueFilename($original_filename) {
    $extension = pathinfo($original_filename, PATHINFO_EXTENSION);
    $base_name = pathinfo($original_filename, PATHINFO_FILENAME);
    
    // Curăță numele de bază pentru a elimina caractere speciale
    $base_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $base_name);
    
    // Dacă numele de bază e gol după curățare, folosim un nume generic
    if (empty($base_name)) {
        $base_name = 'image';
    }
    
    // Generează un nume unic folosind timestamp și un număr random
    $unique_name = $base_name . '-' . time() . '-' . mt_rand(1000, 9999) . '.' . $extension;
    
    return $unique_name;
}

// Funcția pentru redimensionarea imaginilor
function resizeImage($source_path, $dest_path, $max_width, $max_height, $quality = 90) {
    list($width, $height, $type) = getimagesize($source_path);
    
    // Calculează noile dimensiuni păstrând raportul de aspect
    $ratio = min($max_width / $width, $max_height / $height);
    $new_width = $width * $ratio;
    $new_height = $height * $ratio;
    
    // Creează imagine nouă
    $new_image = imagecreatetruecolor($new_width, $new_height);
    
    // Handle transparency for PNG images
    if ($type == IMAGETYPE_PNG) {
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
        $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
        imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
    }
    
    // Încarcă imaginea sursă în funcție de tip
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($source_path);
            break;
        default:
            return false;
    }
    
    // Redimensionează imaginea
    imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // Salvează noua imagine
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($new_image, $dest_path, $quality);
            break;
        case IMAGETYPE_PNG:
            $png_quality = floor(($quality - 10) / 10);
            $png_quality = max(0, min(9, $png_quality));
            imagepng($new_image, $dest_path, $png_quality);
            break;
        case IMAGETYPE_GIF:
            imagegif($new_image, $dest_path);
            break;
    }
    
    // Eliberează memoria
    imagedestroy($source_image);
    imagedestroy($new_image);
    
    return true;
}

// Funcție pentru adăugarea unui watermark
function addWatermark($source_path, $dest_path, $watermark_text = 'Spectro Studio') {
    list($width, $height, $type) = getimagesize($source_path);
    
    // Încarcă imaginea sursă
    switch ($type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($source_path);
            break;
        default:
            return false;
    }
    
    // Setări watermark
    $font_size = max(16, round($width / 30));
    $font_path = __DIR__ . '/../fonts/Montserrat-Regular.ttf';
    if (!file_exists($font_path)) {
        $font_path = 5; // Font intern PHP dacă nu există fontul personalizat
    }
    
    // Culori
    $white = imagecolorallocatealpha($image, 255, 255, 255, 50);
    $black = imagecolorallocatealpha($image, 0, 0, 0, 80);
    
    // Calculează poziția watermark-ului - în colțul dreapta jos
    if (is_string($font_path) && is_readable($font_path)) {
        // Pentru fonturi TrueType
        $text_box = imagettfbbox($font_size, 0, $font_path, $watermark_text);
        $text_width = $text_box[2] - $text_box[0];
        $text_height = $text_box[1] - $text_box[7];
        $x = $width - $text_width - 10;
        $y = $height - 10;
        
        // Adaugă umbra
        imagettftext($image, $font_size, 0, $x + 1, $y + 1, $black, $font_path, $watermark_text);
        // Adaugă textul principal
        imagettftext($image, $font_size, 0, $x, $y, $white, $font_path, $watermark_text);
    } else {
        // Pentru fonturile interne PHP
        $text_width = imagefontwidth($font_path) * strlen($watermark_text);
        $text_height = imagefontheight($font_path);
        $x = $width - $text_width - 10;
        $y = $height - $text_height - 10;
        
        // Adaugă umbra
        imagestring($image, $font_path, $x + 1, $y + 1, $watermark_text, $black);
        // Adaugă textul principal
        imagestring($image, $font_path, $x, $y, $watermark_text, $white);
    }
    
    // Salvează imaginea cu watermark
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($image, $dest_path, 95);
            break;
        case IMAGETYPE_PNG:
            imagepng($image, $dest_path, 8);
            break;
        case IMAGETYPE_GIF:
            imagegif($image, $dest_path);
            break;
    }
    
    // Eliberează memoria
    imagedestroy($image);
    
    return true;
}

// Funcția pentru procesarea și salvarea unei fotografii
function processImage($file, $options, $connection) {
    global $upload_original_dir, $upload_large_dir, $upload_medium_dir, $upload_thumb_dir, $upload_web_dir;
    
    if (!isset($file['tmp_name']) || !file_exists($file['tmp_name'])) {
        return [
            'success' => false,
            'message' => 'Fișier invalid.'
        ];
    }
    
    // Verifică dacă fișierul este o imagine validă
    $file_info = getimagesize($file['tmp_name']);
    if ($file_info === false) {
        return [
            'success' => false,
            'message' => 'Fișierul nu este o imagine validă.'
        ];
    }
    
    // Verifică dacă tipul de imagine este suportat
    $allowed_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF];
    if (!in_array($file_info[2], $allowed_types)) {
        return [
            'success' => false,
            'message' => 'Formatul imaginii nu este suportat. Folosiți JPG, PNG sau GIF.'
        ];
    }
    
    // Generează un nume unic pentru fișier
    $unique_filename = generateUniqueFilename($file['name']);
    $file_extension = pathinfo($unique_filename, PATHINFO_EXTENSION);
    $file_basename = basename($unique_filename, '.' . $file_extension);
    
    $original_path = $upload_original_dir . $unique_filename;
    $large_path = $upload_large_dir . $unique_filename;
    $medium_path = $upload_medium_dir . $unique_filename;
    $thumb_path = $upload_thumb_dir . $unique_filename;
    $web_path = $upload_web_dir . $unique_filename;
    
    // Mută fișierul încărcat
    if (!move_uploaded_file($file['tmp_name'], $original_path)) {
        return [
            'success' => false,
            'message' => 'Eroare la salvarea imaginii originale.'
        ];
    }
    
    // Generează versiunile redimensionate
    $image_paths = [
        'original' => '/uploads/photos/originals/' . $unique_filename,
        'large' => '/uploads/photos/large/' . $unique_filename,
        'medium' => '/uploads/photos/medium/' . $unique_filename,
        'thumbnail' => '/uploads/photos/thumbnails/' . $unique_filename,
        'web' => '/uploads/photos/web/' . $unique_filename,
    ];
    
    // Procesează toate dimensiunile specificate
    $resize_status = [];
    
    // Procesează dimensiune Large (1200x900)
    if (isset($options['size_large']) && $options['size_large']) {
        $resize_status['large'] = resizeImage($original_path, $large_path, 1200, 900, 90);
        
        // Adaugă watermark dacă e specificat
        if ($resize_status['large'] && isset($options['add_watermark']) && $options['add_watermark']) {
            addWatermark($large_path, $large_path);
        }
    }
    
    // Procesează dimensiune Medium (800x600)
    if (isset($options['size_medium']) && $options['size_medium']) {
        $resize_status['medium'] = resizeImage($original_path, $medium_path, 800, 600, 85);
        
        // Adaugă watermark dacă e specificat
        if ($resize_status['medium'] && isset($options['add_watermark']) && $options['add_watermark']) {
            addWatermark($medium_path, $medium_path);
        }
    }
    
    // Procesează dimensiune Thumbnail (300x200)
    if (isset($options['size_thumbnail']) && $options['size_thumbnail']) {
        $resize_status['thumbnail'] = resizeImage($original_path, $thumb_path, 300, 200, 80);
    }
    
    // Procesează dimensiune Web (optimizată pentru web - 1600x1200)
    if (isset($options['size_original']) && $options['size_original']) {
        $resize_status['web'] = resizeImage($original_path, $web_path, 1600, 1200, 90);
        
        // Adaugă watermark dacă e specificat
        if ($resize_status['web'] && isset($options['add_watermark']) && $options['add_watermark']) {
            addWatermark($web_path, $web_path);
        }
    }
    
    // Salvează informațiile în baza de date
    try {
        // Verifică dacă tabelul există, dacă nu, îl creează
        $connection->query("
            CREATE TABLE IF NOT EXISTS images (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255),
                description TEXT,
                filename VARCHAR(255) NOT NULL,
                original_path VARCHAR(255) NOT NULL,
                large_path VARCHAR(255),
                medium_path VARCHAR(255),
                thumbnail_path VARCHAR(255),
                web_path VARCHAR(255),
                gallery_id INT,
                photographer_id INT,
                is_featured TINYINT(1) DEFAULT 0,
                is_visible TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (gallery_id) REFERENCES galleries(id) ON DELETE SET NULL,
                FOREIGN KEY (photographer_id) REFERENCES photographers(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        $title = isset($options['title']) ? trim($options['title']) : pathinfo($file['name'], PATHINFO_FILENAME);
        $description = isset($options['description']) ? trim($options['description']) : '';
        $gallery_id = isset($options['gallery_id']) && !empty($options['gallery_id']) ? (int)$options['gallery_id'] : null;
        $photographer_id = isset($options['photographer_id']) && !empty($options['photographer_id']) ? (int)$options['photographer_id'] : null;
        
        $stmt = $connection->prepare("
            INSERT INTO images 
            (title, description, filename, original_path, large_path, medium_path, thumbnail_path, web_path, gallery_id, photographer_id) 
            VALUES 
            (:title, :description, :filename, :original_path, :large_path, :medium_path, :thumbnail_path, :web_path, :gallery_id, :photographer_id)
        ");
        
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':filename', $unique_filename);
        $stmt->bindParam(':original_path', $image_paths['original']);
        $stmt->bindParam(':large_path', $image_paths['large']);
        $stmt->bindParam(':medium_path', $image_paths['medium']);
        $stmt->bindParam(':thumbnail_path', $image_paths['thumbnail']);
        $stmt->bindParam(':web_path', $image_paths['web']);
        $stmt->bindParam(':gallery_id', $gallery_id, PDO::PARAM_INT);
        $stmt->bindParam(':photographer_id', $photographer_id, PDO::PARAM_INT);
        
        $stmt->execute();
        $image_id = $connection->lastInsertId();
        
        return [
            'success' => true,
            'message' => 'Fotografia a fost procesată cu succes.',
            'image_id' => $image_id,
            'filename' => $unique_filename,
            'paths' => $image_paths,
            'resize_status' => $resize_status
        ];
    } catch (PDOException $e) {
        // În caz de eroare, șterge fișierele create
        if (file_exists($original_path)) unlink($original_path);
        if (file_exists($large_path)) unlink($large_path);
        if (file_exists($medium_path)) unlink($medium_path);
        if (file_exists($thumb_path)) unlink($thumb_path);
        if (file_exists($web_path)) unlink($web_path);
        
        return [
            'success' => false,
            'message' => 'Eroare la salvarea în baza de date: ' . $e->getMessage()
        ];
    }
}

// Setează header-ul pentru răspuns JSON
header('Content-Type: application/json');

// Tratează diferite acțiuni
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'list':
        // Listează fotografiile (cu filtrare și paginare)
        try {
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 20;
            $offset = ($page - 1) * $limit;
            
            // Construiește interogarea SQL de bază
            $sql = "
                SELECT i.* 
                FROM images i
                LEFT JOIN galleries g ON i.gallery_id = g.id
                LEFT JOIN photographers p ON i.photographer_id = p.id
                WHERE 1=1
            ";
            
            // Adaugă condiții pentru filtrare
            $params = [];
            
            if (isset($_GET['gallery_id']) && !empty($_GET['gallery_id'])) {
                $sql .= " AND i.gallery_id = :gallery_id";
                $params[':gallery_id'] = (int)$_GET['gallery_id'];
            }
            
            if (isset($_GET['photographer_id']) && !empty($_GET['photographer_id'])) {
                $sql .= " AND i.photographer_id = :photographer_id";
                $params[':photographer_id'] = (int)$_GET['photographer_id'];
            }
            
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search_term = '%' . $_GET['search'] . '%';
                $sql .= " AND (i.title LIKE :search OR i.description LIKE :search)";
                $params[':search'] = $search_term;
            }
            
            // Adaugă clauza de ordonare
            $order_sql = " ORDER BY i.created_at DESC";
            
            // Obține numărul total de înregistrări pentru paginare
            $count_sql = "SELECT COUNT(*) as count FROM images i 
                          LEFT JOIN galleries g ON i.gallery_id = g.id 
                          LEFT JOIN photographers p ON i.photographer_id = p.id 
                          WHERE 1=1";
            
            // Adaugă condițiile de filtrare și la interogarea de numărare
            if (isset($_GET['gallery_id']) && !empty($_GET['gallery_id'])) {
                $count_sql .= " AND i.gallery_id = :gallery_id";
            }
            
            if (isset($_GET['photographer_id']) && !empty($_GET['photographer_id'])) {
                $count_sql .= " AND i.photographer_id = :photographer_id";
            }
            
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $count_sql .= " AND (i.title LIKE :search OR i.description LIKE :search)";
            }
            
            $count_stmt = $conn->prepare($count_sql);
            foreach ($params as $key => $value) {
                $count_stmt->bindValue($key, $value);
            }
            $count_stmt->execute();
            $total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Construiește interogarea completă cu selecție de coloane
            $final_sql = "SELECT i.*, g.title as gallery_title, p.name as photographer_name 
                         FROM images i
                         LEFT JOIN galleries g ON i.gallery_id = g.id
                         LEFT JOIN photographers p ON i.photographer_id = p.id
                         WHERE 1=1";
                         
            // Adaugă condițiile și ordinea
            if (isset($_GET['gallery_id']) && !empty($_GET['gallery_id'])) {
                $final_sql .= " AND i.gallery_id = :gallery_id";
            }
            
            if (isset($_GET['photographer_id']) && !empty($_GET['photographer_id'])) {
                $final_sql .= " AND i.photographer_id = :photographer_id";
            }
            
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $final_sql .= " AND (i.title LIKE :search OR i.description LIKE :search)";
            }
            
            $final_sql .= $order_sql . " LIMIT :limit OFFSET :offset";
            
            // Execută query-ul principal
            $stmt = $conn->prepare($final_sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Adaugă URL-uri pentru imagini
            foreach ($photos as &$photo) {
                $photo['url'] = isset($photo['web_path']) ? $photo['web_path'] : $photo['original_path'];
                $photo['thumb_url'] = isset($photo['thumbnail_path']) ? $photo['thumbnail_path'] : $photo['url'];
                $photo['medium_url'] = isset($photo['medium_path']) ? $photo['medium_path'] : $photo['url'];
                $photo['large_url'] = isset($photo['large_path']) ? $photo['large_path'] : $photo['url'];
            }
            
            // Calculează informații de paginare
            $total_pages = ceil($total_count / $limit);
            $pagination = [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_items' => $total_count,
                'items_per_page' => $limit
            ];
            
            echo json_encode([
                'success' => true,
                'data' => $photos,
                'pagination' => $pagination
            ]);
        } catch(PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Eroare la obținerea listei de fotografii: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'get':
        // Obține detalii despre o fotografie specifică
        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID-ul fotografiei nu a fost specificat']);
            exit;
        }
        
        try {
            $id = (int)$_GET['id'];
            
            $sql = "
                SELECT i.*, 
                    g.title as gallery_title, 
                    p.name as photographer_name
                FROM images i
                LEFT JOIN galleries g ON i.gallery_id = g.id
                LEFT JOIN photographers p ON i.photographer_id = p.id
                WHERE i.id = :id
            ";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $photo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$photo) {
                echo json_encode(['success' => false, 'message' => 'Fotografia nu a fost găsită']);
                exit;
            }
            
            // Adaugă URL-uri pentru imagini
            $photo['url'] = isset($photo['web_path']) ? $photo['web_path'] : $photo['original_path'];
            $photo['thumb_url'] = isset($photo['thumbnail_path']) ? $photo['thumbnail_path'] : $photo['url'];
            $photo['medium_url'] = isset($photo['medium_path']) ? $photo['medium_path'] : $photo['url'];
            $photo['large_url'] = isset($photo['large_path']) ? $photo['large_path'] : $photo['url'];
            
            echo json_encode(['success' => true, 'data' => $photo]);
        } catch(PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Eroare la obținerea detaliilor fotografiei: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'update':
        // Actualizează detaliile unei fotografii
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodă nepermisă']);
            exit;
        }
        
        if (!isset($_POST['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID-ul fotografiei nu a fost specificat']);
            exit;
        }
        
        try {
            $id = (int)$_POST['id'];
            $title = isset($_POST['title']) ? trim($_POST['title']) : '';
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';
            $gallery_id = isset($_POST['gallery_id']) && !empty($_POST['gallery_id']) ? (int)$_POST['gallery_id'] : null;
            $photographer_id = isset($_POST['photographer_id']) && !empty($_POST['photographer_id']) ? (int)$_POST['photographer_id'] : null;
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_visible = isset($_POST['is_visible']) ? 1 : 0;
            
            // Verifică dacă fotografia există
            $check_stmt = $conn->prepare("SELECT id FROM images WHERE id = :id");
            $check_stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Fotografia nu a fost găsită']);
                exit;
            }
            
            // Actualizează fotografia
            $sql = "
                UPDATE images 
                SET 
                    title = :title,
                    description = :description,
                    gallery_id = :gallery_id,
                    photographer_id = :photographer_id,
                    is_featured = :is_featured,
                    is_visible = :is_visible,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':gallery_id', $gallery_id, PDO::PARAM_INT);
            $stmt->bindParam(':photographer_id', $photographer_id, PDO::PARAM_INT);
            $stmt->bindParam(':is_featured', $is_featured, PDO::PARAM_INT);
            $stmt->bindParam(':is_visible', $is_visible, PDO::PARAM_INT);
            
            $stmt->execute();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Fotografia a fost actualizată cu succes'
            ]);
        } catch(PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Eroare la actualizarea fotografiei: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'delete':
        // Șterge o fotografie
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodă nepermisă']);
            exit;
        }
        
        if (!isset($_POST['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID-ul fotografiei nu a fost specificat']);
            exit;
        }
        
        try {
            $id = (int)$_POST['id'];
            
            // Obține informații despre fotografie pentru a șterge fișierele
            $stmt = $conn->prepare("SELECT * FROM images WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $photo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$photo) {
                echo json_encode(['success' => false, 'message' => 'Fotografia nu a fost găsită']);
                exit;
            }
            
            // Șterge fișierele asociate
            $file_paths = [
                '../../' . $photo['original_path'],
                '../../' . $photo['large_path'],
                '../../' . $photo['medium_path'],
                '../../' . $photo['thumbnail_path'],
                '../../' . $photo['web_path']
            ];
            
            foreach ($file_paths as $path) {
                if (!empty($path) && file_exists($path)) {
                    unlink($path);
                }
            }
            
            // Șterge înregistrarea din baza de date
            $delete_stmt = $conn->prepare("DELETE FROM images WHERE id = :id");
            $delete_stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $delete_stmt->execute();
            
            echo json_encode([
                'success' => true,
                'message' => 'Fotografia a fost ștearsă cu succes'
            ]);
        } catch(PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Eroare la ștergerea fotografiei: ' . $e->getMessage()
            ]);
        }
        break;
        
    default:
        // Procesarea încărcării de fotografii (implicit)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodă nepermisă']);
            exit;
        }
        
        if (!isset($_FILES['photos'])) {
            echo json_encode(['success' => false, 'message' => 'Nu au fost furnizate fotografii pentru încărcare']);
            exit;
        }
        
        $options = [
            'gallery_id' => isset($_POST['gallery_id']) ? $_POST['gallery_id'] : '',
            'photographer_id' => isset($_POST['photographer_id']) ? $_POST['photographer_id'] : '',
            'description' => isset($_POST['description']) ? $_POST['description'] : '',
            'generate_thumbnails' => isset($_POST['generate_thumbnails']) && $_POST['generate_thumbnails'] === '1',
            'optimize_images' => isset($_POST['optimize_images']) && $_POST['optimize_images'] === '1',
            'add_watermark' => isset($_POST['add_watermark']) && $_POST['add_watermark'] === '1',
            'size_thumbnail' => isset($_POST['size_thumbnail']) && $_POST['size_thumbnail'] === '1',
            'size_medium' => isset($_POST['size_medium']) && $_POST['size_medium'] === '1',
            'size_large' => isset($_POST['size_large']) && $_POST['size_large'] === '1',
            'size_original' => isset($_POST['size_original']) && $_POST['size_original'] === '1',
        ];
        
        $results = [];
        $success_count = 0;
        $error_count = 0;
        
        // Restructurează array-ul de fișiere pentru a-l face mai ușor de procesat
        $files = [];
        if (is_array($_FILES['photos']['name'])) {
            $file_count = count($_FILES['photos']['name']);
            
            for ($i = 0; $i < $file_count; $i++) {
                if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
                    $files[] = [
                        'name' => $_FILES['photos']['name'][$i],
                        'type' => $_FILES['photos']['type'][$i],
                        'tmp_name' => $_FILES['photos']['tmp_name'][$i],
                        'error' => $_FILES['photos']['error'][$i],
                        'size' => $_FILES['photos']['size'][$i]
                    ];
                }
            }
        } else {
            // Caz pentru un singur fișier
            if ($_FILES['photos']['error'] === UPLOAD_ERR_OK) {
                $files[] = $_FILES['photos'];
            }
        }
        
        // Procesează fiecare fișier
        foreach ($files as $file) {
            $result = processImage($file, $options, $conn);
            $results[] = $result;
            
            if ($result['success']) {
                $success_count++;
            } else {
                $error_count++;
            }
        }
        
        echo json_encode([
            'success' => $success_count > 0,
            'message' => $success_count > 0 
                ? 'Fotografiile au fost încărcate cu succes.' 
                : 'Nu s-a putut încărca nicio fotografie.',
            'uploaded_count' => $success_count,
            'error_count' => $error_count,
            'total_count' => count($files),
            'results' => $results
        ]);
        break;
}
?>