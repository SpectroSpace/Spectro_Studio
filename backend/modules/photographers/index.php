<?php
// Verifică dacă este inclus din admin_dashboard.php
if (!defined('IS_ADMIN_DASHBOARD')) {
    header('Location: ../../spec_admin__index.php');
    exit;
}

// Stabilim conexiunea la bază de date dacă nu există deja
if (!isset($conn)) {
    define('IS_AUTHORIZED_ACCESS', true);
    require_once '../../spec_admin__db__credentials.php';
}

// Obținem lista de categorii pentru formular
$categories = [];
try {
    $stmt = $conn->query("SELECT id, name FROM gallery_categories ORDER BY order_index, name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Eroare silențioasă - vom gestiona în interfață
}

// Obține toate țările (putem adăuga mai multe în viitor)
$countries = [
    'Romania' => 'România',
    'Moldova' => 'Moldova',
    'Bulgaria' => 'Bulgaria',
    'Ukraine' => 'Ucraina',
    'Hungary' => 'Ungaria',
    'Serbia' => 'Serbia',
    'Slovakia' => 'Slovacia',
    'Italy' => 'Italia',
    'Spain' => 'Spania',
    'France' => 'Franța',
    'UK' => 'Regatul Unit',
    'Germany' => 'Germania',
    'USA' => 'SUA',
    'Canada' => 'Canada',
    'Other' => 'Altă țară'
];

// Dicționar cu orașele pentru fiecare țară
$cities_by_country = [
    'Romania' => [
        'Bucuresti' => 'București',
        'Cluj-Napoca' => 'Cluj-Napoca',
        'Timisoara' => 'Timișoara',
        'Iasi' => 'Iași',
        'Constanta' => 'Constanța',
        'Craiova' => 'Craiova',
        'Brasov' => 'Brașov',
        'Galati' => 'Galați',
        'Oradea' => 'Oradea',
        'Ploiesti' => 'Ploiești',
        'Braila' => 'Brăila',
        'Arad' => 'Arad',
        'Pitesti' => 'Pitești',
        'Sibiu' => 'Sibiu',
        'Bacau' => 'Bacău',
        'Targu-Mures' => 'Târgu Mureș',
        'Baia-Mare' => 'Baia Mare',
        'Buzau' => 'Buzău',
        'Satu-Mare' => 'Satu Mare',
        'Botosani' => 'Botoșani',
        'Suceava' => 'Suceava',
        'Piatra-Neamt' => 'Piatra Neamț',
        'Drobeta-Turnu-Severin' => 'Drobeta-Turnu Severin',
        'Alta-localitate' => 'Altă localitate'
    ],
    'Moldova' => [
        'Chisinau' => 'Chișinău',
        'Balti' => 'Bălți',
        'Tiraspol' => 'Tiraspol',
        'Bender' => 'Bender',
        'Cahul' => 'Cahul',
        'Ungheni' => 'Ungheni',
        'Soroca' => 'Soroca',
        'Orhei' => 'Orhei',
        'Alta-localitate' => 'Altă localitate'
    ],
    'Bulgaria' => [
        'Sofia' => 'Sofia',
        'Plovdiv' => 'Plovdiv',
        'Varna' => 'Varna',
        'Burgas' => 'Burgas',
        'Ruse' => 'Ruse',
        'Stara-Zagora' => 'Stara Zagora',
        'Pleven' => 'Pleven',
        'Alta-localitate' => 'Altă localitate'
    ],
    'Ukraine' => [
        'Kiev' => 'Kiev',
        'Harkov' => 'Harkov',
        'Odesa' => 'Odesa',
        'Dnipro' => 'Dnipro',
        'Donetsk' => 'Donetsk',
        'Lviv' => 'Lviv',
        'Alta-localitate' => 'Altă localitate'
    ],
    'Hungary' => [
        'Budapesta' => 'Budapesta',
        'Debrecen' => 'Debrecen',
        'Szeged' => 'Szeged',
        'Miskolc' => 'Miskolc',
        'Pecs' => 'Pecs',
        'Alta-localitate' => 'Altă localitate'
    ],
    'Serbia' => [
        'Belgrad' => 'Belgrad',
        'Novi-Sad' => 'Novi Sad',
        'Nis' => 'Niš',
        'Alta-localitate' => 'Altă localitate'
    ],
    'Slovakia' => [
        'Bratislava' => 'Bratislava',
        'Kosice' => 'Košice',
        'Presov' => 'Prešov',
        'Alta-localitate' => 'Altă localitate'
    ],
    'Italy' => [
        'Roma' => 'Roma',
        'Milano' => 'Milano',
        'Napoli' => 'Napoli',
        'Torino' => 'Torino',
        'Palermo' => 'Palermo',
        'Bologna' => 'Bologna',
        'Firenze' => 'Florența',
        'Genova' => 'Genova',
        'Alta-localitate' => 'Altă localitate'
    ],
    'Spain' => [
        'Madrid' => 'Madrid',
        'Barcelona' => 'Barcelona',
        'Valencia' => 'Valencia',
        'Sevilla' => 'Sevilia',
        'Zaragoza' => 'Zaragoza',
        'Malaga' => 'Málaga',
        'Alta-localitate' => 'Altă localitate'
    ],
    'France' => [
        'Paris' => 'Paris',
        'Marseille' => 'Marsilia',
        'Lyon' => 'Lyon',
        'Toulouse' => 'Toulouse',
        'Nice' => 'Nisa',
        'Nantes' => 'Nantes',
        'Strasbourg' => 'Strasbourg',
        'Alta-localitate' => 'Altă localitate'
    ],
    'UK' => [
        'London' => 'Londra',
        'Birmingham' => 'Birmingham',
        'Manchester' => 'Manchester',
        'Glasgow' => 'Glasgow',
        'Liverpool' => 'Liverpool',
        'Edinburgh' => 'Edinburgh',
        'Alta-localitate' => 'Altă localitate'
    ],
    'Germany' => [
        'Berlin' => 'Berlin',
        'Hamburg' => 'Hamburg',
        'Munchen' => 'München',
        'Koln' => 'Köln',
        'Frankfurt' => 'Frankfurt',
        'Stuttgart' => 'Stuttgart',
        'Dusseldorf' => 'Düsseldorf',
        'Dortmund' => 'Dortmund',
        'Alta-localitate' => 'Altă localitate'
    ],
    'USA' => [
        'New-York' => 'New York',
        'Los-Angeles' => 'Los Angeles',
        'Chicago' => 'Chicago',
        'Houston' => 'Houston',
        'Phoenix' => 'Phoenix',
        'Philadelphia' => 'Philadelphia',
        'San-Antonio' => 'San Antonio',
        'San-Diego' => 'San Diego',
        'Dallas' => 'Dallas',
        'San-Francisco' => 'San Francisco',
        'Alta-localitate' => 'Altă localitate'
    ],
    'Canada' => [
        'Toronto' => 'Toronto',
        'Montreal' => 'Montreal',
        'Vancouver' => 'Vancouver',
        'Calgary' => 'Calgary',
        'Edmonton' => 'Edmonton',
        'Ottawa' => 'Ottawa',
        'Quebec' => 'Quebec',
        'Alta-localitate' => 'Altă localitate'
    ],
    'Other' => [
        'Alta-localitate' => 'Altă localitate'
    ]
];

// Verificăm dacă avem un mesaj în sesiune și îl afișăm
$message = '';
$messageType = '';

if (isset($_SESSION['photographer_message']) && isset($_SESSION['photographer_message_type'])) {
    $message = $_SESSION['photographer_message'];
    $messageType = $_SESSION['photographer_message_type'];
    
    // Ștergem mesajele din sesiune după ce le-am preluat
    unset($_SESSION['photographer_message']);
    unset($_SESSION['photographer_message_type']);
}

// Procesare formular dacă a fost trimis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'add') {
            // Adăugare fotograf nou
            $name = trim($_POST['name']);
            $slug = trim($_POST['slug']);
            
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
            
            // Încarcă poza dacă există
            $profile_image = '';
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                // Folosim calea absolută către directorul uploads
                $uploadBaseDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/photographers/';
                $relativePathDB = '/uploads/photographers/';
                
                // Pentru dezvoltare locală, putem folosi o abordare alternativă
                if (!file_exists($uploadBaseDir)) {
                    // Folosim calea relativă pentru dezvoltare locală
                    $uploadBaseDir = dirname(__FILE__) . '/../../../uploads/photographers/';
                    $relativePathDB = '/uploads/photographers/';
                }
                
                // Asigură existența directorului
                if (!file_exists($uploadBaseDir)) {
                    mkdir($uploadBaseDir, 0755, true);
                }
                
                // Generează nume unic pentru fișier
                $fileExtension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                $fileName = $slug . '-' . time() . '.' . $fileExtension;
                $targetFile = $uploadBaseDir . $fileName;
                
                // Procesează și salvează imaginea
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
                    $profile_image = $relativePathDB . $fileName;
                    // Adăugăm log pentru debugging
                    error_log("Imagine încărcată cu succes: " . $targetFile);
                } else {
                    error_log("Eroare la încărcarea imaginii: " . $targetFile . " - Error: " . $_FILES['profile_image']['error']);
                }
            }
            
            // Pregătește interogarea SQL
            $stmt = $conn->prepare("
                INSERT INTO photographers (name, slug, bio, country, city, profile_image, email, phone, website, facebook, instagram, experience_years, is_featured, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            // Execută interogarea
            $stmt->execute([
                $name,
                $slug,
                $_POST['bio'] ?? null,
                $_POST['country'] ?? null,
                $_POST['city'] ?? null,
                $profile_image,
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
            
            // Adaugă categoriile de specializare
            if (isset($_POST['specialization']) && is_array($_POST['specialization'])) {
                $stmtCategories = $conn->prepare("
                    INSERT INTO photographer_categories (photographer_id, category_id) 
                    VALUES (?, ?)
                ");
                
                foreach ($_POST['specialization'] as $category_id) {
                    $stmtCategories->execute([$photographer_id, $category_id]);
                }
            }
            
            // Setăm mesajul în sesiune
            $_SESSION['photographer_message'] = 'Fotograful a fost adăugat cu succes!';
            $_SESSION['photographer_message_type'] = 'success';
            
            // Redirect pentru a evita resubmiterea formularului la refresh
            header('Location: ' . $_SERVER['PHP_SELF'] . '?tab=photographers&action=added&id=' . $photographer_id);
            exit;
            
        } elseif ($_POST['action'] === 'edit' && isset($_POST['id'])) {
            // Editare fotograf existent
            $photographer_id = (int)$_POST['id'];
            $name = trim($_POST['name']);
            $slug = trim($_POST['slug']);
            
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
            
            // Încarcă poza nouă dacă există
            $profile_image = $_POST['existing_profile_image'] ?? '';
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                // Folosim calea absolută către directorul uploads
                $uploadBaseDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/photographers/';
                $relativePathDB = '/uploads/photographers/';
                
                // Pentru dezvoltare locală, putem folosi o abordare alternativă
                if (!file_exists($uploadBaseDir)) {
                    // Folosim calea relativă pentru dezvoltare locală
                    $uploadBaseDir = dirname(__FILE__) . '/../../../uploads/photographers/';
                    $relativePathDB = '/uploads/photographers/';
                }
                
                // Asigură existența directorului
                if (!file_exists($uploadBaseDir)) {
                    mkdir($uploadBaseDir, 0755, true);
                }
                
                // Generează nume unic pentru fișier
                $fileExtension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                $fileName = $slug . '-' . time() . '.' . $fileExtension;
                $targetFile = $uploadBaseDir . $fileName;
                
                // Procesează și salvează imaginea nouă
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
                    // Șterge imaginea veche dacă există
                    if (!empty($profile_image) && file_exists($_SERVER['DOCUMENT_ROOT'] . $profile_image)) {
                        unlink($_SERVER['DOCUMENT_ROOT'] . $profile_image);
                    }
                    
                    $profile_image = $relativePathDB . $fileName;
                    // Adăugăm log pentru debugging
                    error_log("Imagine încărcată cu succes: " . $targetFile);
                } else {
                    error_log("Eroare la încărcarea imaginii: " . $targetFile . " - Error: " . $_FILES['profile_image']['error']);
                }
            }
            
            // Pregătește interogarea SQL
            $stmt = $conn->prepare("
                UPDATE photographers 
                SET name = ?, slug = ?, bio = ?, country = ?, city = ?, profile_image = ?, 
                    email = ?, phone = ?, website = ?, facebook = ?, instagram = ?, 
                    experience_years = ?, is_featured = ?, is_active = ? 
                WHERE id = ?
            ");
            
            // Execută interogarea
            $stmt->execute([
                $name,
                $slug,
                $_POST['bio'] ?? null,
                $_POST['country'] ?? null,
                $_POST['city'] ?? null,
                $profile_image,
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
            
            // Actualizează categoriile de specializare
            // Mai întâi șterge categoriile existente
            $stmtDeleteCats = $conn->prepare("DELETE FROM photographer_categories WHERE photographer_id = ?");
            $stmtDeleteCats->execute([$photographer_id]);
            
            // Adaugă noile categorii selectate
            if (isset($_POST['specialization']) && is_array($_POST['specialization'])) {
                $stmtCategories = $conn->prepare("
                    INSERT INTO photographer_categories (photographer_id, category_id) 
                    VALUES (?, ?)
                ");
                
                foreach ($_POST['specialization'] as $category_id) {
                    $stmtCategories->execute([$photographer_id, $category_id]);
                }
            }
            
            // Setăm mesajul în sesiune
            $_SESSION['photographer_message'] = 'Fotograful a fost actualizat cu succes!';
            $_SESSION['photographer_message_type'] = 'success';
            
            // Redirect pentru a evita resubmiterea formularului la refresh
            header('Location: ' . $_SERVER['PHP_SELF'] . '?tab=photographers&action=updated&id=' . $photographer_id);
            exit;
            
        } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
            // Ștergere fotograf
            $photographer_id = (int)$_POST['id'];
            
            // Obține calea imaginii pentru a o șterge
            $stmt = $conn->prepare("SELECT profile_image FROM photographers WHERE id = ?");
            $stmt->execute([$photographer_id]);
            $profile_image = $stmt->fetchColumn();
            
            // Șterge fotograful din baza de date
            $stmt = $conn->prepare("DELETE FROM photographers WHERE id = ?");
            $stmt->execute([$photographer_id]);
            
            // Șterge și imaginea
            if (!empty($profile_image) && file_exists($_SERVER['DOCUMENT_ROOT'] . $profile_image)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $profile_image);
            }
            
            // Setăm mesajul în sesiune
            $_SESSION['photographer_message'] = 'Fotograful a fost șters cu succes!';
            $_SESSION['photographer_message_type'] = 'success';
            
            // Redirect pentru a evita resubmiterea formularului la refresh
            header('Location: ' . $_SERVER['PHP_SELF'] . '?tab=photographers&action=deleted');
            exit;
        }
    } catch (PDOException $e) {
        // Setăm mesajul de eroare în sesiune
        $_SESSION['photographer_message'] = 'Eroare: ' . $e->getMessage();
        $_SESSION['photographer_message_type'] = 'error';
        
        // Redirect
        header('Location: ' . $_SERVER['PHP_SELF'] . '?tab=photographers&error=1');
        exit;
    }
}

// Obține lista de fotografi pentru tabel
$photographers = [];
try {
    // SOLUȚIE DEFINITIVĂ PENTRU DUPLICARE
    // 1. Obținem lista de ID-uri unice
    $stmt = $conn->query("SELECT DISTINCT id FROM photographers ORDER BY id");
    $photographer_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // 2. Pentru fiecare ID, obținem doar un singur rând
    $photographers = [];
    foreach ($photographer_ids as $id) {
        $stmt = $conn->prepare("SELECT * FROM photographers WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $photographer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($photographer) {
            $photographers[] = $photographer;
        }
    }
    
    // 3. Sortăm fotografii alfabetic după nume
    usort($photographers, function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });
    
    // Log pentru debugging
    error_log("SOLUȚIE FINALĂ: Număr total de fotografi după deduplicare: " . count($photographers));
    foreach ($photographers as $photographer) {
        error_log("SOLUȚIE FINALĂ: Fotograf ID: " . $photographer['id'] . ", Nume: " . $photographer['name']);
    }
    
    // Obține specializările pentru fotografi
    foreach ($photographers as &$photographer) {
        $stmtCats = $conn->prepare("
            SELECT DISTINCT gc.id, gc.name 
            FROM photographer_categories pc
            JOIN gallery_categories gc ON pc.category_id = gc.id
            WHERE pc.photographer_id = ?
        ");
        $stmtCats->execute([$photographer['id']]);
        $photographer['specializations'] = $stmtCats->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $message = 'Eroare la obținerea listei de fotografi: ' . $e->getMessage();
    $messageType = 'error';
    error_log("Eroare SQL în modulul photographer: " . $e->getMessage());
}
?>

<div class="module-header">
    <h2>Management Fotografi</h2>
    <button id="toggle-photographer-form" class="btn btn-primary">Adaugă fotograf nou</button>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Formular adăugare/editare fotograf -->
<div id="photographer-form-container" style="display: none; width: 100%" class="form-gradient-container">
    <div class="admin-form">
        <h3 id="form-title" class="form-title">Adaugă Fotograf Nou</h3>
        
        <form id="photographer-form" method="post" enctype="multipart/form-data">
            <input type="hidden" id="photographer-action" name="action" value="add">
            <input type="hidden" id="photographer-id" name="id" value="">
            <input type="hidden" id="existing-profile-image" name="existing_profile_image" value="">
            
            <div class="form-row">
                <div class="form-group form-group-half">
                    <label for="name">Nume fotograf <span class="required">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                
                <div class="form-group form-group-half">
                    <label for="slug">Slug (opțional - se generează automat)</label>
                    <input type="text" id="slug" name="slug" class="form-control">
                </div>
            </div>
            
            <div class="form-group">
                <label for="bio">Biografie / Descriere</label>
                <textarea id="bio" name="bio" class="form-control" rows="4"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group form-group-half">
                    <label for="country">Țara <span class="required">*</span></label>
                    <select id="country" name="country" class="form-control" required>
                        <option value="">-- Selectează țara --</option>
                        <?php foreach ($countries as $code => $countryName): ?>
                            <option value="<?php echo $code; ?>"><?php echo $countryName; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group form-group-half">
                    <label for="city">Orașul <span class="required">*</span></label>
                    <select id="city" name="city" class="form-control" required>
                        <option value="">-- Selectează orașul --</option>
                        <!-- Orașele vor fi încărcate dinamic cu JavaScript -->
                    </select>
                    <input type="text" id="custom_city" name="custom_city" class="form-control" style="display: none; margin-top: 5px;" placeholder="Introduceți numele orașului">
                </div>
            </div>
            
            <div class="form-group">
                <label for="profile_image">Imagine profil <span class="required">*</span></label>
                <input type="file" id="profile_image" name="profile_image" class="form-control" accept="image/*" required>
                <div id="profile-image-preview" class="img-preview" style="display: none;">
                    <img id="profile-image-preview-img" src="" alt="Preview" style="max-width: 200px;">
                </div>
            </div>
            
            <div class="form-group">
                <label for="specialization">Specializări <span class="required">*</span></label>
                <div class="checkbox-group specialization-checkboxes">
                    <?php if (empty($categories)): ?>
                        <p class="form-text text-warning">Nu există categorii disponibile.</p>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="specialization[]" value="<?php echo $category['id']; ?>" class="specialization-checkbox">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div id="specialization-error" class="error-message" style="display: none; color: #F44336;">Selectați cel puțin o specializare.</div>
            </div>
            
            <div class="form-row">
                <div class="form-group form-group-half">
                    <label for="experience_years">Ani de experiență</label>
                    <input type="number" id="experience_years" name="experience_years" class="form-control" min="0">
                </div>
                
                <div class="form-group form-group-half">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group form-group-half">
                    <label for="phone">Telefon <span class="required">*</span></label>
                    <input type="text" id="phone" name="phone" class="form-control" required>
                </div>
                
                <div class="form-group form-group-half">
                    <label for="website">Website</label>
                    <input type="text" id="website" name="website" class="form-control">
                </div>
            </div>
            
            <div class="form-group">
                <div class="checkbox-inline">
                    <input type="checkbox" id="is_featured" name="is_featured" value="1">
                    <label for="is_featured">Fotograf promovat</label>
                </div>
                <div class="checkbox-inline">
                    <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                    <label for="is_active">Fotograf activ</label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Salvează</button>
                <button type="button" id="cancel-photographer-form" class="btn btn-secondary">Anulează</button>
                <button type="button" id="delete-photographer" class="btn btn-danger" style="display: none;">Șterge</button>
            </div>
        </form>
    </div>
</div>

<!-- Tabel fotografi -->
<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Imagine</th>
                <th>Nume</th>
                <th>Specializare</th>
                <th>Email</th>
                <th>Telefon</th>
                <th>Locație</th>
                <th>Status</th>
                <th>Acțiuni</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($photographers)): ?>
                <tr>
                    <td colspan="9">Nu există fotografi înregistrați.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($photographers as $photographer): ?>
                    <tr>
                        <td><?php echo $photographer['id']; ?></td>
                        <td>
                            <?php if (!empty($photographer['profile_image'])): ?>
                                <?php
                                // Fix image path construction - ensure we always use the correct relative path from current directory
                                $imagePath = $photographer['profile_image'];
                                // Asigură că imaginea este afișată corect indiferent de cum începe calea
                                $correctPath = (strpos($imagePath, '/') === 0) ? '../..' . $imagePath : '../../' . $imagePath;
                                ?>
                                <img src="<?php echo htmlspecialchars($correctPath); ?>" alt="<?php echo htmlspecialchars($photographer['name']); ?>" style="max-width: 60px; max-height: 60px; object-fit: cover;">
                            <?php else: ?>
                                <div class="no-image">Fără imagine</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($photographer['name']); ?>
                            <?php if ($photographer['is_featured'] == 1): ?>
                                <span class="badge featured-badge">Promovat</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($photographer['specializations'])): ?>
                                <?php 
                                    $specializationNames = array_map(function($spec) {
                                        return $spec['name'];
                                    }, $photographer['specializations']);
                                    echo htmlspecialchars(implode(', ', $specializationNames));
                                ?>
                            <?php else: ?>
                                Nespecificat
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($photographer['email'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($photographer['phone'] ?? 'N/A'); ?></td>
                        <td>
                            <?php 
                                $location = [];
                                if (!empty($photographer['city'])) {
                                    $location[] = $photographer['city'];
                                }
                                if (!empty($photographer['country'])) {
                                    $location[] = isset($countries[$photographer['country']]) ? 
                                        $countries[$photographer['country']] : 
                                        $photographer['country'];
                                }
                                echo !empty($location) ? htmlspecialchars(implode(', ', $location)) : 'N/A';
                            ?>
                        </td>
                        <td>
                            <?php if ($photographer['is_active'] == 1): ?>
                                <span class="badge active-badge">Activ</span>
                            <?php else: ?>
                                <span class="badge inactive-badge">Inactiv</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-photographer" data-id="<?php echo $photographer['id']; ?>" title="Editează">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-photographer" data-id="<?php echo $photographer['id']; ?>" data-name="<?php echo htmlspecialchars($photographer['name']); ?>" title="Șterge">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal confirmare ștergere -->
<div id="delete-modal" class="modal">
    <div class="modal-content">
        <h3>Confirmare ștergere</h3>
        <p>Sigur doriți să ștergeți fotograful <strong id="delete-photographer-name"></strong>?</p>
        <p class="warning">Această acțiune este ireversibilă!</p>
        
        <form id="delete-form" method="post">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" id="delete-photographer-id" name="id" value="">
            
            <div class="form-actions">
                <button type="submit" class="btn btn-danger">Da, șterge</button>
                <button type="button" class="btn btn-secondary close-modal">Anulează</button>
            </div>
        </form>
    </div>
</div>

<style>
.specialization-checkboxes {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.badge {
    display: inline-block;
    padding: 3px 6px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: bold;
    margin-left: 5px;
}

.featured-badge {
    background-color: var(--color-accent);
    color: var(--color-primary);
}

.active-badge {
    background-color: #4CAF50;
    color: white;
}

.inactive-badge {
    background-color: #F44336;
    color: white;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
}

.modal-content {
    position: relative;
    background-color: var(--color-bg-light);
    margin: 10% auto;
    padding: 20px;
    border-radius: 8px;
    width: 80%;
    max-width: 500px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
}

.warning {
    color: #F44336;
    font-weight: bold;
}

.no-image {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #333;
    color: #aaa;
    font-size: 10px;
    text-align: center;
    border-radius: 4px;
}

.img-preview {
    margin-top: 10px;
}

#photographer-form-container {
    width: 100%;
    margin-bottom: 20px;
    box-sizing: border-box;
    overflow: hidden;
}

#photographer-form {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    overflow: visible;
}

.module-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.form-group, .form-row {
    width: 100%;
    box-sizing: border-box;
}

.form-control {
    width: 100%;
    box-sizing: border-box;
    max-width: 100%;
}
</style>

<script>
// Definim o funcție sigură pentru a face cereri HTTP
const fetchAPI = function(url, options) {
    return window.fetch(url, options || {});
};

document.addEventListener('DOMContentLoaded', function() {
    const photographerForm = document.getElementById('photographer-form-container');
    const toggleFormBtn = document.getElementById('toggle-photographer-form');
    const cancelFormBtn = document.getElementById('cancel-photographer-form');
    const deleteBtn = document.getElementById('delete-photographer');
    const formTitle = document.getElementById('form-title');
    const form = document.getElementById('photographer-form');
    
    // Definirea dicționarului de orașe pentru fiecare țară
    const citiesByCountry = {
        'Romania': {
            'Bucuresti': 'București',
            'Cluj-Napoca': 'Cluj-Napoca',
            'Timisoara': 'Timișoara',
            'Iasi': 'Iași',
            'Constanta': 'Constanța',
            'Craiova': 'Craiova',
            'Brasov': 'Brașov',
            'Galati': 'Galați',
            'Oradea': 'Oradea',
            'Ploiesti': 'Ploiești',
            'Braila': 'Brăila',
            'Arad': 'Arad',
            'Pitesti': 'Pitești',
            'Sibiu': 'Sibiu',
            'Bacau': 'Bacău',
            'Targu-Mures': 'Târgu Mureș',
            'Baia-Mare': 'Baia Mare',
            'Buzau': 'Buzău',
            'Satu-Mare': 'Satu Mare',
            'Botosani': 'Botoșani',
            'Suceava': 'Suceava',
            'Piatra-Neamt': 'Piatra Neamț',
            'Drobeta-Turnu-Severin': 'Drobeta-Turnu Severin',
            'Alta-localitate': 'Altă localitate'
        },
        'Moldova': {
            'Chisinau': 'Chișinău',
            'Balti': 'Bălți',
            'Tiraspol': 'Tiraspol',
            'Bender': 'Bender',
            'Cahul': 'Cahul',
            'Ungheni': 'Ungheni',
            'Soroca': 'Soroca',
            'Orhei': 'Orhei',
            'Alta-localitate': 'Altă localitate'
        },
        'Bulgaria': {
            'Sofia': 'Sofia',
            'Plovdiv': 'Plovdiv',
            'Varna': 'Varna',
            'Burgas': 'Burgas',
            'Ruse': 'Ruse',
            'Stara-Zagora': 'Stara Zagora',
            'Pleven': 'Pleven',
            'Alta-localitate': 'Altă localitate'
        },
        'Ukraine': {
            'Kiev': 'Kiev',
            'Harkov': 'Harkov',
            'Odesa': 'Odesa',
            'Dnipro': 'Dnipro',
            'Donetsk': 'Donetsk',
            'Lviv': 'Lviv',
            'Alta-localitate': 'Altă localitate'
        },
        'Hungary': {
            'Budapesta': 'Budapesta',
            'Debrecen': 'Debrecen',
            'Szeged': 'Szeged',
            'Miskolc': 'Miskolc',
            'Pecs': 'Pecs',
            'Alta-localitate': 'Altă localitate'
        },
        'Serbia': {
            'Belgrad': 'Belgrad',
            'Novi-Sad': 'Novi Sad',
            'Nis': 'Niš',
            'Alta-localitate': 'Altă localitate'
        },
        'Slovakia': {
            'Bratislava': 'Bratislava',
            'Kosice': 'Košice',
            'Presov': 'Prešov',
            'Alta-localitate': 'Altă localitate'
        },
        'Italy': {
            'Roma': 'Roma',
            'Milano': 'Milano',
            'Napoli': 'Napoli',
            'Torino': 'Torino',
            'Palermo': 'Palermo',
            'Bologna': 'Bologna',
            'Firenze': 'Florența',
            'Genova': 'Genova',
            'Alta-localitate': 'Altă localitate'
        },
        'Spain': {
            'Madrid': 'Madrid',
            'Barcelona': 'Barcelona',
            'Valencia': 'Valencia',
            'Sevilla': 'Sevilia',
            'Zaragoza': 'Zaragoza',
            'Malaga': 'Málaga',
            'Alta-localitate': 'Altă localitate'
        },
        'France': {
            'Paris': 'Paris',
            'Marseille': 'Marsilia',
            'Lyon': 'Lyon',
            'Toulouse': 'Toulouse',
            'Nice': 'Nisa',
            'Nantes': 'Nantes',
            'Strasbourg': 'Strasbourg',
            'Alta-localitate': 'Altă localitate'
        },
        'UK': {
            'London': 'Londra',
            'Birmingham': 'Birmingham',
            'Manchester': 'Manchester',
            'Glasgow': 'Glasgow',
            'Liverpool': 'Liverpool',
            'Edinburgh': 'Edinburgh',
            'Alta-localitate': 'Altă localitate'
        },
        'Germany': {
            'Berlin': 'Berlin',
            'Hamburg': 'Hamburg',
            'Munchen': 'München',
            'Koln': 'Köln',
            'Frankfurt': 'Frankfurt',
            'Stuttgart': 'Stuttgart',
            'Dusseldorf': 'Düsseldorf',
            'Dortmund': 'Dortmund',
            'Alta-localitate': 'Altă localitate'
        },
        'USA': {
            'New-York': 'New York',
            'Los-Angeles': 'Los Angeles',
            'Chicago': 'Chicago',
            'Houston': 'Houston',
            'Phoenix': 'Phoenix',
            'Philadelphia': 'Philadelphia',
            'San-Antonio': 'San Antonio',
            'San-Diego': 'San Diego',
            'Dallas': 'Dallas',
            'San-Francisco': 'San Francisco',
            'Alta-localitate': 'Altă localitate'
        },
        'Canada': {
            'Toronto': 'Toronto',
            'Montreal': 'Montreal',
            'Vancouver': 'Vancouver',
            'Calgary': 'Calgary',
            'Edmonton': 'Edmonton',
            'Ottawa': 'Ottawa',
            'Quebec': 'Quebec',
            'Alta-localitate': 'Altă localitate'
        },
        'Other': {
            'Alta-localitate': 'Altă localitate'
        }
    };
    
    // Funcție pentru actualizarea listei de orașe în funcție de țara selectată
    function updateCityList() {
        const countrySelector = document.getElementById('country');
        const citySelector = document.getElementById('city');
        const customCityInput = document.getElementById('custom_city');
        const selectedCountry = countrySelector.value;
        const previouslySelectedCity = citySelector.value;
        
        // Golim lista actuală de orașe
        citySelector.innerHTML = '<option value="">-- Selectează orașul --</option>';
        
        // Dacă nu a fost selectată o țară, nu facem nimic
        if (!selectedCountry) {
            return;
        }
        
        // Obținem lista de orașe pentru țara selectată
        const cities = citiesByCountry[selectedCountry] || { 'Alta-localitate': 'Altă localitate' };
        
        // Adăugăm orașele la dropdown
        for (const [value, label] of Object.entries(cities)) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            citySelector.appendChild(option);
        }
        
        // Încercăm să restabilim selecția anterioară sau să selectăm "Altă localitate" pentru alte orașe
        if (previouslySelectedCity && Object.keys(cities).includes(previouslySelectedCity)) {
            citySelector.value = previouslySelectedCity;
        } else if (previouslySelectedCity) {
            citySelector.value = 'Alta-localitate';
            customCityInput.value = previouslySelectedCity;
            customCityInput.style.display = 'block';
        } else {
            customCityInput.style.display = 'none';
        }
    }
    
    // Handler pentru managementul câmpului de "Altă localitate"
    function handleCustomCity() {
        const citySelector = document.getElementById('city');
        const customCityInput = document.getElementById('custom_city');
        
        if (citySelector.value === 'Alta-localitate') {
            customCityInput.style.display = 'block';
            customCityInput.focus();
        } else {
            customCityInput.style.display = 'none';
        }
    }
    
    // Adăugăm evenimentele pentru selecție
    document.getElementById('country').addEventListener('change', function() {
        updateCityList();
        handleCustomCity();
    });
    
    document.getElementById('city').addEventListener('change', handleCustomCity);
    
    // Inițializăm lista de orașe la încărcarea paginii
    updateCityList();
    
    // Modificăm validarea formularului pentru a gestiona câmpul de "Altă localitate"
    form.addEventListener('submit', function(e) {
        const citySelector = document.getElementById('city');
        const customCityInput = document.getElementById('custom_city');
        
        // Dacă utilizatorul a selectat "Altă localitate", dar nu a introdus un oraș, oprim trimiterea
        if (citySelector.value === 'Alta-localitate' && !customCityInput.value.trim()) {
            e.preventDefault();
            alert('Vă rugăm să introduceți numele orașului.');
            customCityInput.focus();
            return false;
        }
        
        // Dacă a fost introdusă o localitate personalizată, o utilizăm în loc de selecția din dropdown
        if (citySelector.value === 'Alta-localitate' && customCityInput.value.trim()) {
            // Creăm un câmp ascuns pentru a transmite orașul personalizat
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'city';
            hiddenInput.value = customCityInput.value.trim();
            
            // Înlocuim valoarea din dropdown
            citySelector.name = 'city_selection';  // Schimbăm numele pentru a evita conflicte
            
            // Adăugăm câmpul ascuns la formular
            form.appendChild(hiddenInput);
        }
        
        // Verificăm dacă există cel puțin o specializare selectată
        const specializationCheckboxes = document.querySelectorAll('.specialization-checkbox:checked');
        const specializationError = document.getElementById('specialization-error');
        
        if (specializationCheckboxes.length === 0) {
            e.preventDefault(); // Oprim trimiterea formularului
            specializationError.style.display = 'block';
            
            // Scroll către mesajul de eroare
            specializationError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            return false;
        } else {
            specializationError.style.display = 'none';
        }
        
        // Verificăm imaginea de profil la adăugare nouă
        const action = document.getElementById('photographer-action').value;
        const profileImage = document.getElementById('profile_image');
        const existingImage = document.getElementById('existing-profile-image').value;
        
        if (action === 'add' && profileImage.files.length === 0) {
            e.preventDefault(); // Oprim trimiterea formularului
            alert('Vă rugăm să selectați o imagine de profil.');
            profileImage.focus();
            return false;
        } else if (action === 'edit' && profileImage.files.length === 0 && existingImage === '') {
            e.preventDefault(); // Oprim trimiterea formularului
            alert('Vă rugăm să selectați o imagine de profil.');
            profileImage.focus();
            return false;
        }
        
        // Toate validările au trecut, formularul poate fi trimis
        return true;
    });
    
    // Funcție pentru resetarea formularului
    function resetForm() {
        form.reset();
        document.getElementById('photographer-action').value = 'add';
        document.getElementById('photographer-id').value = '';
        document.getElementById('existing-profile-image').value = '';
        document.getElementById('profile-image-preview').style.display = 'none';
        document.getElementById('specialization-error').style.display = 'none';
        document.getElementById('custom_city').style.display = 'none';
        formTitle.textContent = 'Adaugă Fotograf Nou';
        deleteBtn.style.display = 'none';
        
        // Reset specializări
        const specializationCheckboxes = document.querySelectorAll('input[name="specialization[]"]');
        specializationCheckboxes.forEach(checkbox => checkbox.checked = false);
        
        // Reinițializăm lista de orașe
        updateCityList();
    }
    
    // Toggle formular
    toggleFormBtn.addEventListener('click', function() {
        resetForm();
        if (photographerForm.style.display === 'none') {
            photographerForm.style.display = 'block';
            toggleFormBtn.textContent = 'Ascunde formularul';
        } else {
            photographerForm.style.display = 'none';
            toggleFormBtn.textContent = 'Adaugă fotograf nou';
        }
    });
    
    // Anulare formular
    cancelFormBtn.addEventListener('click', function() {
        photographerForm.style.display = 'none';
        toggleFormBtn.textContent = 'Adaugă fotograf nou';
    });
    
    // Preview imagine
    document.getElementById('profile_image').addEventListener('change', function(e) {
        const preview = document.getElementById('profile-image-preview');
        const previewImg = document.getElementById('profile-image-preview-img');
        const file = e.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });
    
    // Editare fotograf
    const editButtons = document.querySelectorAll('.edit-photographer');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const photographerId = this.getAttribute('data-id');
            
            // Resetăm formularul și schimbăm acțiunea
            resetForm();
            document.getElementById('photographer-action').value = 'edit';
            document.getElementById('photographer-id').value = photographerId;
            formTitle.textContent = 'Editează Fotograf';
            deleteBtn.style.display = 'block';
            
            // Obține datele fotografului prin fetchAPI (funcția noastră sigură)
            fetchAPI(`/backend/api/sp_API__photographers.php?action=get&id=${photographerId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const photographer = data.data;
                        
                        // Completează formularul
                        document.getElementById('name').value = photographer.name || '';
                        document.getElementById('slug').value = photographer.slug || '';
                        document.getElementById('bio').value = photographer.bio || '';
                        document.getElementById('email').value = photographer.email || '';
                        document.getElementById('phone').value = photographer.phone || '';
                        document.getElementById('website').value = photographer.website || '';
                        document.getElementById('facebook').value = photographer.facebook || '';
                        document.getElementById('instagram').value = photographer.instagram || '';
                        document.getElementById('experience_years').value = photographer.experience_years || '';
                        document.getElementById('is_featured').checked = photographer.is_featured == 1;
                        document.getElementById('is_active').checked = photographer.is_active == 1;
                        
                        // Setăm țara și apoi selectăm orașul corespunzător
                        const countrySelect = document.getElementById('country');
                        countrySelect.value = photographer.country || '';
                        updateCityList();
                        
                        // Recunoaște orașul din listă sau marchează ca personalizat
                        const citySelect = document.getElementById('city');
                        const customCityInput = document.getElementById('custom_city');
                        const selectedCities = citiesByCountry[photographer.country] || {};
                        
                        // Verificăm dacă orașul este în lista standard sau trebuie adăugat ca personalizat
                        let isStandardCity = false;
                        for (const [cityValue, cityName] of Object.entries(selectedCities)) {
                            if (cityValue === photographer.city || cityName === photographer.city) {
                                citySelect.value = cityValue;
                                isStandardCity = true;
                                break;
                            }
                        }
                        
                        if (!isStandardCity && photographer.city) {
                            citySelect.value = 'Alta-localitate';
                            customCityInput.value = photographer.city;
                            customCityInput.style.display = 'block';
                        }
                        
                        // Salvăm calea imaginii existente
                        if (photographer.profile_image) {
                            document.getElementById('existing-profile-image').value = photographer.profile_image;
                            document.getElementById('profile-image-preview-img').src = photographer.profile_image;
                            document.getElementById('profile-image-preview').style.display = 'block';
                            
                            // Eliminăm atributul "required" pentru imagine profilului dacă există deja o imagine
                            document.getElementById('profile_image').removeAttribute('required');
                        }
                        
                        // Setăm specializările selectate
                        if (photographer.specializations && photographer.specializations.length > 0) {
                            const specializationCheckboxes = document.querySelectorAll('input[name="specialization[]"]');
                            specializationCheckboxes.forEach(checkbox => {
                                if (photographer.specializations.includes(parseInt(checkbox.value))) {
                                    checkbox.checked = true;
                                }
                            });
                        }
                        
                        // Afișăm formularul
                        photographerForm.style.display = 'block';
                        toggleFormBtn.textContent = 'Ascunde formularul';
                    } else {
                        alert('Eroare la încărcarea datelor fotografului: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Eroare de comunicare cu serverul: ' + error);
                });
        });
    });
    
    // Confirmare ștergere fotograf
    const deleteButtons = document.querySelectorAll('.delete-photographer');
    const deleteModal = document.getElementById('delete-modal');
    const closeModalButtons = document.querySelectorAll('.close-modal');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const photographerId = this.getAttribute('data-id');
            const photographerName = this.getAttribute('data-name');
            
            document.getElementById('delete-photographer-id').value = photographerId;
            document.getElementById('delete-photographer-name').textContent = photographerName;
            deleteModal.style.display = 'block';
        });
    });
    
    // Închide modal
    closeModalButtons.forEach(button => {
        button.addEventListener('click', function() {
            deleteModal.style.display = 'none';
        });
    });
    
    // Închide modal când se face click în afara lui
    window.addEventListener('click', function(event) {
        if (event.target === deleteModal) {
            deleteModal.style.display = 'none';
        }
    });
    
    // Butonul de ștergere din formular
    deleteBtn.addEventListener('click', function() {
        const photographerId = document.getElementById('photographer-id').value;
        const photographerName = document.getElementById('name').value;
        
        document.getElementById('delete-photographer-id').value = photographerId;
        document.getElementById('delete-photographer-name').textContent = photographerName;
        deleteModal.style.display = 'block';
    });

    // SOLUȚIE DEFINITIVĂ PENTRU DUPLICARE VIZUALĂ:
    // Această funcție se asigură că fiecare fotograf apare o singură dată în interfață
    function removeDuplicateRows() {
        const table = document.querySelector('.admin-table');
        if (!table) return;
        
        const tbody = table.querySelector('tbody');
        if (!tbody) return;
        
        const rows = tbody.querySelectorAll('tr');
        const seen = new Set();
        
        rows.forEach(row => {
            const idCell = row.querySelector('td:first-child');
            if (!idCell) return;
            
            const photographerId = idCell.textContent.trim();
            
            if (seen.has(photographerId)) {
                // Acest fotograf a fost deja afișat, eliminăm rândul duplicat
                row.remove();
                console.log(`Eliminat rând duplicat pentru fotograful cu ID ${photographerId}`);
            } else {
                // Marcăm acest ID ca fiind deja afișat
                seen.add(photographerId);
            }
        });
        
        console.log(`Total fotografi unici afișați: ${seen.size}`);
    }
    
    // Rulăm funcția după ce tot DOM-ul s-a încărcat
    setTimeout(removeDuplicateRows, 100);
    
    // Funcție pentru ascunderea automată a mesajelor de alertă după 3 secunde
    const alertMessages = document.querySelectorAll('.alert');
    if (alertMessages.length > 0) {
        setTimeout(function() {
            alertMessages.forEach(function(alert) {
                alert.style.opacity = '1';
                
                // Animație fade-out
                let opacity = 1;
                const fadeOut = setInterval(function() {
                    if (opacity <= 0.1) {
                        clearInterval(fadeOut);
                        alert.style.display = 'none';
                    }
                    opacity -= 0.1;
                    alert.style.opacity = opacity;
                }, 50);
            });
        }, 3000);
    }
});
</script>