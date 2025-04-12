<?php
// filepath: f:\SITE SPECTRO STUDIO\backend\install\init_database.php
// Acest script creează tabelele necesare în baza de date

// Definește constanta pentru a permite accesul la fișierul de credențiale
define('IS_AUTHORIZED_ACCESS', true);

// Include fișierul de credențiale
require_once '../spec_admin__db__credentials.php';

// Conectare la baza de date
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Conectat cu succes la baza de date.<br>";
    
    // Creează tabelul pentru galerii
    $sql_galleries = "
    CREATE TABLE IF NOT EXISTS galleries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE,
        description TEXT,
        cover_image VARCHAR(255),
        category_id INT NULL,
        subcategory_id INT NULL,
        is_featured TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql_galleries);
    echo "Tabelul 'galleries' a fost creat cu succes.<br>";
    
    // Creează tabelul pentru fotografi
    $sql_photographers = "
    CREATE TABLE IF NOT EXISTS photographers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE,
        bio TEXT,
        photo VARCHAR(255),
        email VARCHAR(255),
        phone VARCHAR(50),
        website VARCHAR(255),
        social_media JSON,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql_photographers);
    echo "Tabelul 'photographers' a fost creat cu succes.<br>";
    
    // Verifică dacă tabelul images există și îl recreează cu noua structură
    try {
        $conn->query("SELECT 1 FROM images LIMIT 1");
        // Tabelul există deja, îl redenumim pentru backup
        $conn->exec("RENAME TABLE images TO images_backup_" . time());
        echo "Tabelul 'images' existent a fost redenumit în 'images_backup_[timestamp]' pentru siguranță.<br>";
    } catch(PDOException $e) {
        // Tabelul nu există, continuăm cu crearea
    }
    
    // Crează tabelul pentru imagini cu noua structură
    $sql_images = "
    CREATE TABLE images (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql_images);
    echo "Tabelul 'images' a fost creat cu succes cu noua structură.<br>";
    
    // Creează tabelul pentru fotografii în galerii (păstrat pentru compatibilitate)
    $sql_gallery_photos = "
    CREATE TABLE IF NOT EXISTS gallery_photos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        gallery_id INT NOT NULL,
        title VARCHAR(255),
        description TEXT,
        image_path VARCHAR(255) NOT NULL,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (gallery_id) REFERENCES galleries(id) ON DELETE CASCADE
    )";
    
    $conn->exec($sql_gallery_photos);
    echo "Tabelul 'gallery_photos' a fost creat cu succes.<br>";
    
    echo "<br>Inițializarea bazei de date s-a terminat cu succes!";
    
} catch(PDOException $e) {
    echo "Eroare la inițializarea bazei de date: " . $e->getMessage();
}
?>