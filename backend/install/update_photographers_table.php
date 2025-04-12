<?php
// Script pentru actualizarea tabelei photographers și adăugarea tabelei photographer_categories

// Definește constanta pentru a permite accesul la fișierul de credențiale
define('IS_AUTHORIZED_ACCESS', true);

// Include fișierul de credențiale pentru baza de date
require_once '../spec_admin__db__credentials.php';

// Conectarea la baza de date
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div style='font-family: Montserrat, sans-serif; max-width: 800px; margin: 30px auto; padding: 25px; background-color: #111; color: #fff; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.5);'>";
    echo "<h1 style='color: #E0A80D; margin-bottom: 20px;'>Actualizare Tabelă Fotografi</h1>";
    
    // Verifică dacă tabela photographers există
    try {
        $conn->query("SELECT 1 FROM photographers LIMIT 1");
        echo "<p style='color: #4CAF50;'>✅ Tabela `photographers` există.</p>";
        
        // Modifică tabela photographers pentru a adăuga noile câmpuri
        $conn->beginTransaction();
        
        // Adaugă câmpul country
        try {
            $conn->exec("ALTER TABLE photographers ADD COLUMN IF NOT EXISTS country VARCHAR(100) NULL");
            echo "<p style='color: #4CAF50;'>✅ Coloana `country` a fost adăugată cu succes.</p>";
        } catch (PDOException $e) {
            echo "<p style='color: #FFA500;'>⚠️ Coloana `country` deja există sau nu a putut fi adăugată: " . $e->getMessage() . "</p>";
        }
        
        // Adaugă câmpul city
        try {
            $conn->exec("ALTER TABLE photographers ADD COLUMN IF NOT EXISTS city VARCHAR(100) NULL");
            echo "<p style='color: #4CAF50;'>✅ Coloana `city` a fost adăugată cu succes.</p>";
        } catch (PDOException $e) {
            echo "<p style='color: #FFA500;'>⚠️ Coloana `city` deja există sau nu a putut fi adăugată: " . $e->getMessage() . "</p>";
        }
        
        // Adaugă câmpul is_active
        try {
            $conn->exec("ALTER TABLE photographers ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1");
            echo "<p style='color: #4CAF50;'>✅ Coloana `is_active` a fost adăugată cu succes.</p>";
        } catch (PDOException $e) {
            echo "<p style='color: #FFA500;'>⚠️ Coloana `is_active` deja există sau nu a putut fi adăugată: " . $e->getMessage() . "</p>";
        }
        
        $conn->commit();
        echo "<p style='color: #4CAF50;'>✅ Tabela `photographers` a fost actualizată cu succes.</p>";
        
    } catch (PDOException $e) {
        echo "<p style='color: #F44336;'>❌ Tabela `photographers` nu există! Se crează tabela...</p>";
        
        // Crează tabela photographers dacă nu există
        $sql_photographers = "
        CREATE TABLE `photographers` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `name` VARCHAR(255) NOT NULL,
          `slug` VARCHAR(255) UNIQUE NOT NULL,
          `bio` TEXT NULL,
          `specialization` VARCHAR(255) NULL,
          `country` VARCHAR(100) NULL,
          `city` VARCHAR(100) NULL,
          `profile_image` VARCHAR(255) NULL,
          `email` VARCHAR(255) NULL,
          `phone` VARCHAR(50) NULL,
          `website` VARCHAR(255) NULL,
          `facebook` VARCHAR(255) NULL,
          `instagram` VARCHAR(255) NULL,
          `experience_years` INT NULL,
          `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
          `is_active` TINYINT(1) NOT NULL DEFAULT 1,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->exec($sql_photographers);
        echo "<p style='color: #4CAF50;'>✅ Tabela `photographers` a fost creată cu succes.</p>";
    }
    
    // Crează tabela photographer_categories dacă nu există
    try {
        $sql_photographer_categories = "
        CREATE TABLE IF NOT EXISTS `photographer_categories` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `photographer_id` INT NOT NULL,
          `category_id` INT NOT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (`photographer_id`) REFERENCES `photographers`(`id`) ON DELETE CASCADE,
          FOREIGN KEY (`category_id`) REFERENCES `gallery_categories`(`id`) ON DELETE CASCADE,
          UNIQUE KEY `unique_photographer_category` (`photographer_id`, `category_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->exec($sql_photographer_categories);
        echo "<p style='color: #4CAF50;'>✅ Tabela `photographer_categories` a fost creată cu succes.</p>";
    } catch (PDOException $e) {
        echo "<p style='color: #F44336;'>❌ Eroare la crearea tabelei `photographer_categories`: " . $e->getMessage() . "</p>";
        
        // Verifică dacă problema este legată de tabela gallery_categories care nu există
        if (strpos($e->getMessage(), 'gallery_categories') !== false) {
            echo "<p style='color: #FFA500;'>⚠️ Este posibil să nu existe tabela `gallery_categories`. Rulați mai întâi scriptul `create_categories_tables.php`.</p>";
        }
    }
    
    echo "<p style='color: #E0A80D; font-weight: bold; margin-top: 20px;'>Actualizarea structurii bazei de date pentru fotografi este completă!</p>";
    echo "<a href='../spec_admin__index.php' style='display: inline-block; margin-top: 15px; padding: 10px 20px; background-color: #E0A80D; color: #111; text-decoration: none; border-radius: 4px; font-weight: bold;'>Înapoi la Admin Panel</a>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div style='font-family: Montserrat, sans-serif; max-width: 800px; margin: 30px auto; padding: 20px; background-color: #111; color: #fff; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.5);'>";
    echo "<h1 style='color: #F44336;'>Eroare!</h1>";
    echo "<p style='color: #F44336;'>A apărut o eroare la actualizarea bazei de date:</p>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>