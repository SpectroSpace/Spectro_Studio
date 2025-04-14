<?php
// Include modulul de mentenanță
require_once 'backend/spec_admin__maintenance.php';

// Stabilim conexiunea la bază de date
define('IS_AUTHORIZED_ACCESS', true);
require_once 'backend/spec_admin__db__credentials.php';

// Obținem lista de fotografi activi și evidențiați
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obține fotografii activi, ordonați după is_featured (întâi cei evidențiați) și nume
    $stmt = $conn->query("
        SELECT p.*, GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as specialization_list 
        FROM photographers p 
        LEFT JOIN photographer_categories pc ON p.id = pc.photographer_id
        LEFT JOIN gallery_categories c ON pc.category_id = c.id
        WHERE p.is_active = 1 
        GROUP BY p.id
        ORDER BY p.is_featured DESC, p.name ASC
    ");
    
    $photographers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    // În caz de eroare, inițializăm un array gol
    $photographers = [];
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fotografi Profesioniști - Spectro Studio</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;700;800&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1><a href="index.php">Spectro Studio</a></h1>
                </div>
                <nav class="main-nav">
                    <ul>
                        <li><a href="index.php">Acasă</a></li>
                        <li><a href="gallery.php">Galerii</a></li>
                        <li class="active"><a href="photographers.php">Fotografi</a></li>
                        <li><a href="about.php">Despre noi</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </nav>
                <button class="mobile-menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Page Hero -->
    <section class="page-hero">
        <div class="container">
            <h1 class="page-title">Fotografi <span class="accent-text">Profesioniști</span></h1>
            <p class="page-subtitle">Cunoaște echipa noastră de experți în fotografie</p>
        </div>
    </section>

    <!-- Photographers Section -->
    <section class="ss-photographers section">
        <div class="container">
            <?php if (isset($error_message)): ?>
                <div class="ss-alert ss-alert--error">
                    <p>Ne pare rău, a apărut o eroare la încărcarea fotografilor. Vă rugăm să încercați mai târziu.</p>
                </div>
            <?php elseif (empty($photographers)): ?>
                <div class="ss-empty-state">
                    <h3>Niciun fotograf disponibil momentan</h3>
                    <p>Reveniți în curând pentru a vedea echipa noastră de fotografi profesioniști.</p>
                </div>
            <?php else: ?>
                <div class="ss-filter-bar">
                    <div class="ss-search">
                        <input type="text" id="photographer-search" placeholder="Caută fotograf..." class="ss-search__input">
                        <button class="ss-search__button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="ss-filters">
                        <select id="specialization-filter" class="ss-filter__select">
                            <option value="">Toate specializările</option>
                            <?php 
                            // Obține toate specializările unice
                            $all_specializations = [];
                            foreach ($photographers as $photographer) {
                                if (!empty($photographer['specialization_list'])) {
                                    $specs = explode(', ', $photographer['specialization_list']);
                                    foreach ($specs as $spec) {
                                        $all_specializations[$spec] = $spec;
                                    }
                                }
                            }
                            
                            // Sortează alfabetic și afișează
                            asort($all_specializations);
                            foreach ($all_specializations as $spec) {
                                echo '<option value="' . htmlspecialchars($spec) . '">' . htmlspecialchars($spec) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="ss-photographers__grid" id="photographers-grid">
                    <?php foreach ($photographers as $photographer): ?>
                        <div class="ss-photographer-card" data-specializations="<?php echo htmlspecialchars($photographer['specialization_list'] ?? ''); ?>">
                            <div class="ss-photographer-card__image">
                                <?php if (!empty($photographer['profile_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($photographer['profile_image']); ?>" alt="<?php echo htmlspecialchars($photographer['name']); ?>">
                                <?php else: ?>
                                    <div class="ss-placeholder-image">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4H12v3.5a.5.5 0 0 1-1 0v-4z"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($photographer['is_featured']): ?>
                                    <span class="ss-badge ss-badge--featured">Recomandat</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="ss-photographer-card__info">
                                <h3 class="ss-photographer-card__name"><?php echo htmlspecialchars($photographer['name']); ?></h3>
                                
                                <?php if (!empty($photographer['specialization_list'])): ?>
                                    <p class="ss-photographer-card__specialization">
                                        <?php echo htmlspecialchars($photographer['specialization_list']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($photographer['city']) && !empty($photographer['country'])): ?>
                                    <p class="ss-photographer-card__location">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                                        </svg>
                                        <?php echo htmlspecialchars($photographer['city'] . ', ' . $photographer['country']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($photographer['experience_years'])): ?>
                                    <p class="ss-photographer-card__experience">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022l-.074.997zm2.004.45a7.003 7.003 0 0 0-.985-.299l.219-.976c.383.086.76.2 1.126.342l-.36.933zm1.37.71a7.01 7.01 0 0 0-.439-.27l.493-.87a8.025 8.025 0 0 1 .979.654l-.615.789a6.996 6.996 0 0 0-.418-.302zm1.834 1.79a6.99 6.99 0 0 0-.653-.796l.724-.69c.27.285.52.59.747.91l-.818.576zm.744 1.352a7.08 7.08 0 0 0-.214-.468l.893-.45a7.976 7.976 0 0 1 .45 1.088l-.95.313a7.023 7.023 0 0 0-.179-.483zm.53 2.507a6.991 6.991 0 0 0-.1-1.025l.985-.17c.067.386.106.778.116 1.17l-1 .025zm-.131 1.538c.033-.17.06-.339.081-.51l.993.123a7.957 7.957 0 0 1-.23 1.155l-.964-.267c.046-.165.086-.332.12-.501zm-.952 2.379c.184-.29.346-.594.486-.908l.914.405c-.16.36-.345.706-.555 1.038l-.845-.535zm-.964 1.205c.122-.122.239-.248.35-.378l.758.653a8.073 8.073 0 0 1-.401.432l-.707-.707z"/>
                                            <path d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zm0 13A6 6 0 1 1 14 8a6 6 0 0 1-6 6z"/>
                                            <path d="M8 4.5a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5H4.5a.5.5 0 0 1 0-1h3V5a.5.5 0 0 1 .5-.5z"/>
                                        </svg>
                                        <?php echo htmlspecialchars($photographer['experience_years']); ?> ani experiență
                                    </p>
                                <?php endif; ?>
                                
                                <div class="ss-photographer-card__actions">
                                    <a href="photographer-detail.php?slug=<?php echo htmlspecialchars($photographer['slug']); ?>" class="ss-btn ss-btn--primary ss-btn--sm">Vezi profil</a>
                                    
                                    <?php if (!empty($photographer['email'])): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($photographer['email']); ?>" class="ss-btn ss-btn--outline ss-btn--sm">
                                            Contact
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($photographer['instagram']) || !empty($photographer['facebook'])): ?>
                                    <div class="ss-photographer-card__social">
                                        <?php if (!empty($photographer['instagram'])): ?>
                                            <a href="<?php echo htmlspecialchars($photographer['instagram']); ?>" target="_blank" class="ss-social-link ss-social-link--instagram">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.917 3.917 0 0 0-1.417.923A3.927 3.927 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.916 3.916 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.926 3.926 0 0 0-.923-1.417A3.911 3.911 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0h.003zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599.28.28.453.546.598.92.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.47 2.47 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.478 2.478 0 0 1-.92-.598 2.48 2.48 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233 0-2.136.008-2.388.046-3.231.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045v.002zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92zm-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217zm0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334z"/>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($photographer['facebook'])): ?>
                                            <a href="<?php echo htmlspecialchars($photographer['facebook']); ?>" target="_blank" class="ss-social-link ss-social-link--facebook">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Contact CTA -->
    <section class="contact-cta section">
        <div class="container">
            <h2>Vrei să devii fotograf <span class="accent-text">Spectro Studio?</span></h2>
            <p>Trimite-ne un mesaj pentru a discuta despre o posibilă colaborare</p>
            <a href="contact.php?subject=Colaborare fotograf" class="btn btn-primary">Contactează-ne</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h2>Spectro Studio</h2>
                    <p>Fotografie profesională de calitate</p>
                </div>
                <div class="footer-links">
                    <h3>Navigare</h3>
                    <ul>
                        <li><a href="index.php">Acasă</a></li>
                        <li><a href="gallery.php">Galerii</a></li>
                        <li><a href="photographers.php">Fotografi</a></li>
                        <li><a href="about.php">Despre noi</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Contact</h3>
                    <p>Email: contact@spectrostudio.ro</p>
                    <p>Telefon: +40 753 469 839</p>
                    <p>Adresă: Str. Exemplu nr. 10, București</p>
                </div>
                <div class="footer-social">
                    <h3>Social Media</h3>
                    <div class="social-icons">
                        <a href="#" class="social-icon">
                            <img src="assets/img/icons/facebook.svg" alt="Facebook">
                        </a>
                        <a href="#" class="social-icon">
                            <img src="assets/img/icons/instagram.svg" alt="Instagram">
                        </a>
                        <a href="#" class="social-icon">
                            <img src="assets/img/icons/pinterest.svg" alt="Pinterest">
                        </a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Spectro Studio. Toate drepturile rezervate.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/he_base__javascript.js"></script>
    
    <script>
        // Funcție pentru filtrarea fotografilor
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('photographer-search');
            const specializationFilter = document.getElementById('specialization-filter');
            const photographersGrid = document.getElementById('photographers-grid');
            const photographerCards = document.querySelectorAll('.ss-photographer-card');
            
            // Funcție de filtrare
            function filterPhotographers() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedSpecialization = specializationFilter.value.toLowerCase();
                
                photographerCards.forEach(card => {
                    const name = card.querySelector('.ss-photographer-card__name').textContent.toLowerCase();
                    const specializations = (card.dataset.specializations || '').toLowerCase();
                    
                    const matchesSearch = name.includes(searchTerm);
                    const matchesSpecialization = !selectedSpecialization || specializations.includes(selectedSpecialization);
                    
                    if (matchesSearch && matchesSpecialization) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Verifică dacă sunt fotografi vizibili
                const visibleCards = [...photographerCards].filter(card => card.style.display !== 'none');
                
                if (visibleCards.length === 0) {
                    // Afișează mesaj "niciun rezultat"
                    if (!document.querySelector('.ss-no-results')) {
                        const noResults = document.createElement('div');
                        noResults.className = 'ss-no-results';
                        noResults.innerHTML = '<h3>Niciun rezultat găsit</h3><p>Încercați alte criterii de căutare.</p>';
                        photographersGrid.appendChild(noResults);
                    }
                } else {
                    // Elimină mesajul "niciun rezultat" dacă există
                    const noResults = document.querySelector('.ss-no-results');
                    if (noResults) {
                        noResults.remove();
                    }
                }
            }
            
            // Atașează evenimentele
            if (searchInput) {
                searchInput.addEventListener('input', filterPhotographers);
            }
            
            if (specializationFilter) {
                specializationFilter.addEventListener('change', filterPhotographers);
            }
        });
    </script>
</body>
</html>