<?php
// Inițializarea sesiunii
session_start();

// Definește constanta pentru a permite accesul la fișierul de credențiale
define('IS_AUTHORIZED_ACCESS', true);
define('IS_ADMIN_DASHBOARD', true);

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: spec_admin__login.php');
    exit;
}

// Include fișierul de credențiale pentru a avea acces la conexiunea cu baza de date
require_once 'spec_admin__db__credentials.php';

// Inițializează conexiunea PDO la baza de date
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Eroare de conexiune la baza de date: " . $e->getMessage());
}

// Verifică dacă site-ul este în modul mentenanță
$config_file = __DIR__ . '/../config/maintenance.json';
if (file_exists($config_file)) {
    $maintenance_config = json_decode(file_get_contents($config_file), true);
    $maintenance_mode = isset($maintenance_config['enabled']) ? $maintenance_config['enabled'] : false;
} else {
    $maintenance_mode = false;
}

// Actualizează timestamp-ul ultimei activități pentru a monitoriza sesiunea
$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Spectro Studio</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;700;800&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- CSS -->
    <link rel="stylesheet" href="css/sp_backend__styles.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <h2>Spectro Studio</h2>
                <p>Admin Panel</p>
            </div>
            
            <nav>
                <ul>
                    <li><a href="#dashboard" class="tab-link active" data-tab="dashboard">Dashboard</a></li>
                    <li><a href="#galleries" class="tab-link" data-tab="galleries">Galerii</a></li>
                    <li><a href="#photographers" class="tab-link" data-tab="photographers">Fotografi</a></li>
                    <li><a href="#photos" class="tab-link" data-tab="photos">Fotografii</a></li>
                    <li><a href="#settings" class="tab-link" data-tab="settings">Setări</a></li>
                </ul>
            </nav>
            
            <div class="admin-info">
                <p>Logat ca: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <a href="spec_admin__logout.php" class="logout-btn">Deconectare</a>
            </div>
            
            <?php if($maintenance_mode): ?>
            <div class="maintenance-indicator">
                <a href="#settings" class="tab-link" data-tab="settings" data-settings-tab="maintenance-settings">
                    <span class="maintenance-dot"></span> Site în mentenanță
                </a>
            </div>
            <?php endif; ?>
        </aside>
        
        <!-- Main Content -->
        <main class="content">
            <!-- Toggle Sidebar Button -->
            <div class="sidebar-toggle">
                <button id="sidebarToggle" title="Toggle Sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
            </div>
            
            <!-- Dashboard Tab -->
            <section id="dashboard" class="tab-content active">
                <div class="section-header">
                    <h1>Dashboard</h1>
                </div>
                
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <h3>Galerii</h3>
                        <?php
                        // Numărul de galerii
                        try {
                            $stmt = $conn->query("SELECT COUNT(*) as count FROM galleries");
                            $galleries_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
                        } catch(PDOException $e) {
                            $galleries_count = 0;
                        }
                        ?>
                        <div class="stat-value"><?php echo $galleries_count; ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Fotografii</h3>
                        <?php
                        // Numărul de fotografii
                        try {
                            $stmt = $conn->query("SELECT COUNT(*) as count FROM images");
                            $images_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
                        } catch(PDOException $e) {
                            $images_count = 0;
                        }
                        ?>
                        <div class="stat-value"><?php echo $images_count; ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Fotografi</h3>
                        <?php
                        // Numărul de fotografi
                        try {
                            $stmt = $conn->query("SELECT COUNT(*) as count FROM photographers");
                            $photographers_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
                        } catch(PDOException $e) {
                            $photographers_count = 0;
                        }
                        ?>
                        <div class="stat-value"><?php echo $photographers_count; ?></div>
                    </div>
                </div>
                
                <div class="dashboard-actions">
                    <h2>Acțiuni rapide</h2>
                    
                    <div class="action-buttons">
                        <a href="#galleries" class="btn btn-primary tab-link" data-tab="galleries">Adaugă Galerie</a>
                        <a href="#photographers" class="btn btn-primary tab-link" data-tab="photographers">Adaugă Fotograf</a>
                        <a href="#photos" class="btn btn-primary tab-link" data-tab="photos">Încarcă Fotografii</a>
                        <?php if($maintenance_mode): ?>
                        <a href="#settings" class="btn btn-secondary tab-link" data-tab="settings">Dezactivează Mentenanța</a>
                        <?php else: ?>
                        <a href="#settings" class="btn btn-secondary tab-link" data-tab="settings">Activează Mentenanța</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="recent-activity">
                    <h2>Activitate Recentă</h2>
                    
                    <div class="activity-list">
                        <?php
                        // Afișează cele mai recente activități (galerii, fotografii, etc.)
                        try {
                            $activities = [];
                            
                            // Verificăm dacă tabelele există înainte de a le interoga
                            $tables_exist = true;
                            
                            // Verifică dacă există tabele
                            try {
                                $conn->query("SELECT 1 FROM galleries LIMIT 1");
                                $conn->query("SELECT 1 FROM images LIMIT 1");
                                $conn->query("SELECT 1 FROM photographers LIMIT 1");
                            } catch(PDOException $e) {
                                $tables_exist = false;
                            }
                            
                            if ($tables_exist) {
                                // Galerii recente
                                $stmt = $conn->query("SELECT 'gallery' as type, id, title, created_at FROM galleries ORDER BY created_at DESC LIMIT 3");
                                $galleries = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $activities = array_merge($activities, $galleries);
                                
                                // Imagini recente
                                $stmt = $conn->query("SELECT 'image' as type, id, title, created_at FROM images ORDER BY created_at DESC LIMIT 3");
                                $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $activities = array_merge($activities, $images);
                                
                                // Fotografi recenți
                                $stmt = $conn->query("SELECT 'photographer' as type, id, name as title, created_at FROM photographers ORDER BY created_at DESC LIMIT 3");
                                $photographers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $activities = array_merge($activities, $photographers);
                                
                                // Sortează după data
                                usort($activities, function($a, $b) {
                                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                                });
                                
                                // Limitează la 5 activități
                                $activities = array_slice($activities, 0, 5);
                            }
                            
                            if (count($activities) > 0) {
                                echo '<ul>';
                                foreach ($activities as $activity) {
                                    $type_name = '';
                                    switch ($activity['type']) {
                                        case 'gallery': $type_name = 'Galerie'; break;
                                        case 'image': $type_name = 'Fotografie'; break;
                                        case 'photographer': $type_name = 'Fotograf'; break;
                                    }
                                    
                                    $date = date('d.m.Y H:i', strtotime($activity['created_at']));
                                    echo '<li>';
                                    echo "$type_name: <strong>" . htmlspecialchars($activity['title']) . "</strong> - $date";
                                    echo '</li>';
                                }
                                echo '</ul>';
                            } else {
                                echo '<p>Nu există activități recente.</p>';
                            }
                        } catch (PDOException $e) {
                            echo '<p>Nu s-au putut încărca activitățile recente. Eroare: ' . htmlspecialchars($e->getMessage()) . '</p>';
                        }
                        ?>
                    </div>
                </div>
            </section>
            
            <!-- Galleries Tab -->
            <section id="galleries" class="tab-content">
                <div class="section-header">
                    <h1>Gestionare Galerii</h1>
                </div>
                
                <!-- Include modului pentru galerii -->
                <?php include 'modules/galleries/index.php'; ?>
            </section>
            
            <!-- Photographers Tab -->
            <section id="photographers" class="tab-content">
                <div class="section-header">
                    <h1>Gestionare Fotografi</h1>
                </div>
                
                <!-- Include modului pentru fotografi -->
                <?php include 'modules/photographers/index.php'; ?>
            </section>
            
            <!-- Photos Tab -->
            <section id="photos" class="tab-content">
                <div class="section-header">
                    <h1>Gestionare Fotografii</h1>
                </div>
                
                <!-- Include modului pentru fotografii -->
                <?php include 'modules/photos/index.php'; ?>
            </section>
            
            <!-- Settings Tab -->
            <section id="settings" class="tab-content">
                <div class="section-header">
                    <h1>Setări</h1>
                </div>
                
                <!-- Tab-uri pentru setări -->
                <div class="settings-tabs">
                    <button class="settings-tab-btn active" data-tab="general-settings">Generale</button>
                    <button class="settings-tab-btn" data-tab="maintenance-settings">Mentenanță</button>
                </div>
                
                <!-- Setări generale -->
                <div id="general-settings" class="settings-tab-content active">
                    <h2>Setări Generale</h2>
                    
                    <form id="general-settings-form" class="admin-form">
                        <div class="form-group">
                            <label for="site-title">Titlul Site-ului</label>
                            <input type="text" id="site-title" name="site_title" value="Spectro Studio">
                        </div>
                        
                        <div class="form-group">
                            <label for="site-description">Descriere Site</label>
                            <textarea id="site-description" name="site_description" rows="3">Studio foto profesional</textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact-email">Email Contact</label>
                            <input type="email" id="contact-email" name="contact_email" value="contact@spectrostudio.com">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Salvează Setările</button>
                        </div>
                    </form>
                </div>
                
                <!-- Setări mentenanță -->
                <div id="maintenance-settings" class="settings-tab-content">
                    <?php include 'modules/settings/sp_maintenance.php'; ?>
                </div>
            </section>
        </main>
    </div>

    <!-- JavaScript -->
    <script src="js/sp_backend__dashboard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab Functionality
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');
            
            // Set active tab from localStorage or use default
            const activeTab = localStorage.getItem('activeTab') || 'dashboard';
            setActiveTab(activeTab);
            
            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const tabId = this.getAttribute('data-tab');
                    setActiveTab(tabId);
                    
                    // Save active tab to localStorage
                    localStorage.setItem('activeTab', tabId);
                });
            });
            
            function setActiveTab(tabId) {
                // Remove active class from all tabs
                tabLinks.forEach(link => link.classList.remove('active'));
                tabContents.forEach(tab => tab.classList.remove('active'));
                
                // Set active class to selected tab
                document.querySelectorAll(`.tab-link[data-tab="${tabId}"]`).forEach(link => {
                    link.classList.add('active');
                });
                
                const tabContent = document.getElementById(tabId);
                if (tabContent) {
                    tabContent.classList.add('active');
                }
                
                // If settings tab is active, check if there's a hash for sub-tab
                if (tabId === 'settings') {
                    // Activate maintenance tab if coming from maintenance banner
                    if (document.activeElement && document.activeElement.classList.contains('maintenance-quick-action')) {
                        document.querySelector('.settings-tab-btn[data-tab="maintenance-settings"]').click();
                    }
                }
            }
            
            // Settings Tabs
            const settingsTabs = document.querySelectorAll('.settings-tab-btn');
            const settingsContents = document.querySelectorAll('.settings-tab-content');
            
            settingsTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Eliminăm clasa active de pe toate tab-urile
                    settingsTabs.forEach(t => t.classList.remove('active'));
                    settingsContents.forEach(c => c.classList.remove('active'));
                    
                    // Adăugăm clasa active pe tab-ul curent
                    this.classList.add('active');
                    document.getElementById(this.dataset.tab).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>