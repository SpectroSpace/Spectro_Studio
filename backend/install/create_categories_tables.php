<?php
// filepath: f:\SITE SPECTRO STUDIO\backend\install\create_categories_tables.php
// Script pentru crearea tabelelor pentru categorii și subcategorii de galerii

// Definește constanta pentru a permite accesul la fișierul de credențiale
define('IS_AUTHORIZED_ACCESS', true);

// Include fișierul de credențiale pentru baza de date
require_once '../spec_admin__db__credentials.php';

// Conectarea la baza de date
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div style='font-family: Montserrat, sans-serif; max-width: 800px; margin: 30px auto; padding: 25px; background-color: #111; color: #fff; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.5);'>";
    echo "<h1 style='color: #E0A80D; margin-bottom: 20px;'>Creare Structură Categorii și Subcategorii</h1>";
    
    // Creează tabela pentru categorii principale
    $sql_categories = "
    CREATE TABLE IF NOT EXISTS `gallery_categories` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `name` VARCHAR(255) NOT NULL,
      `slug` VARCHAR(255) UNIQUE NOT NULL,
      `description` TEXT,
      `order_index` INT DEFAULT 0,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql_categories);
    echo "<p style='color: #4CAF50;'>✅ Tabelul `gallery_categories` a fost creat cu succes.</p>";
    
    // Creează tabela pentru subcategorii
    $sql_subcategories = "
    CREATE TABLE IF NOT EXISTS `gallery_subcategories` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `category_id` INT NOT NULL,
      `name` VARCHAR(255) NOT NULL,
      `slug` VARCHAR(255) NOT NULL,
      `description` TEXT,
      `order_index` INT DEFAULT 0,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (`category_id`) REFERENCES `gallery_categories`(`id`) ON DELETE CASCADE,
      UNIQUE KEY `unique_subcategory` (`category_id`, `slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql_subcategories);
    echo "<p style='color: #4CAF50;'>✅ Tabelul `gallery_subcategories` a fost creat cu succes.</p>";
    
    // Adaugă câmpul `category_id` și `subcategory_id` în tabela galleries
    $sql_alter_galleries = "
    ALTER TABLE `galleries` 
    ADD COLUMN IF NOT EXISTS `category_id` INT NULL,
    ADD COLUMN IF NOT EXISTS `subcategory_id` INT NULL,
    ADD FOREIGN KEY IF NOT EXISTS (`category_id`) REFERENCES `gallery_categories`(`id`) ON DELETE SET NULL,
    ADD FOREIGN KEY IF NOT EXISTS (`subcategory_id`) REFERENCES `gallery_subcategories`(`id`) ON DELETE SET NULL";
    
    try {
        $conn->exec($sql_alter_galleries);
        echo "<p style='color: #4CAF50;'>✅ Tabela `galleries` a fost actualizată cu succes pentru a include categorii și subcategorii.</p>";
    } catch (PDOException $e) {
        echo "<p style='color: #F44336;'>⚠️ Notă: Tabela galleries nu a putut fi modificată. Asigurați-vă că există deja. Eroare: " . $e->getMessage() . "</p>";
    }
    
    echo "<p style='color: #E0A80D; font-weight: bold; margin-top: 20px;'>Structura tabelelor pentru categorii și subcategorii a fost creată cu succes!</p>";
    echo "<p>Puteți acum încărca datele de categorii folosind scriptul <a href='populate_categories.php' style='color: #FF0055; text-decoration: none;'>populate_categories.php</a>.</p>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div style='font-family: Montserrat, sans-serif; max-width: 800px; margin: 30px auto; padding: 20px; background-color: #111; color: #fff; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.5);'>";
    echo "<h1 style='color: #F44336;'>Eroare!</h1>";
    echo "<p style='color: #F44336;'>A apărut o eroare la crearea tabelelor:</p>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>