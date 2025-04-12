<?php
// filepath: f:\SITE SPECTRO STUDIO\index.php
// Include modulul de mentenanță
require_once 'backend/spec_admin__maintenance.php';

// Restul codului HTML pentru pagina principală
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spectro Studio - Fotografie Profesională</title>
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
                        <li class="active"><a href="index.php">Acasă</a></li>
                        <li><a href="gallery.php">Galerii</a></li>
                        <li><a href="photographers.php">Fotografi</a></li>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h2 class="display-heading">Fotografie <span class="accent">profesională</span></h2>
            <p class="hero-text">Capturăm momentele tale speciale cu pasiune și creativitate</p>
            <div class="hero-buttons">
                <a href="gallery.php" class="btn btn-primary">Vezi galeriile</a>
                <a href="contact.php" class="btn btn-secondary">Contactează-ne</a>
            </div>
        </div>
    </section>

    <!-- Featured Galleries -->
    <section class="featured-galleries section">
        <div class="container">
            <div class="section-header">
                <h2>Galerii <span class="accent-text">recente</span></h2>
                <p>Explorează cele mai noi proiecte fotografice</p>
            </div>
            <div class="galleries-grid">
                <!-- Gallery items will be loaded here -->
                <div class="gallery-item">
                    <div class="gallery-image">
                        <img src="assets/img/placeholder-1.jpg" alt="Galerie 1">
                    </div>
                    <div class="gallery-info">
                        <h3>Ceremonie nuntă</h3>
                        <p>28 fotografii</p>
                    </div>
                </div>
                <div class="gallery-item">
                    <div class="gallery-image">
                        <img src="assets/img/placeholder-2.jpg" alt="Galerie 2">
                    </div>
                    <div class="gallery-info">
                        <h3>Portrete studio</h3>
                        <p>14 fotografii</p>
                    </div>
                </div>
                <div class="gallery-item">
                    <div class="gallery-image">
                        <img src="assets/img/placeholder-3.jpg" alt="Galerie 3">
                    </div>
                    <div class="gallery-info">
                        <h3>Peisaje urbane</h3>
                        <p>22 fotografii</p>
                    </div>
                </div>
            </div>
            <div class="section-footer">
                <a href="gallery.php" class="btn btn-primary">Vezi toate galeriile</a>
            </div>
        </div>
    </section>

    <!-- Photographers Section -->
    <section class="photographers section dark-section">
        <div class="container">
            <div class="section-header">
                <h2>Echipa <span class="accent-text">noastră</span></h2>
                <p>Fotografi profesioniști cu experiență</p>
            </div>
            <div class="photographers-grid">
                <div class="photographer-card">
                    <div class="photographer-image">
                        <img src="assets/img/photographer-1.jpg" alt="Fotograf 1">
                    </div>
                    <h3>Andrei Popescu</h3>
                    <p>Fotograf de nuntă</p>
                </div>
                <div class="photographer-card">
                    <div class="photographer-image">
                        <img src="assets/img/photographer-2.jpg" alt="Fotograf 2">
                    </div>
                    <h3>Maria Ionescu</h3>
                    <p>Portretist</p>
                </div>
                <div class="photographer-card">
                    <div class="photographer-image">
                        <img src="assets/img/photographer-3.jpg" alt="Fotograf 3">
                    </div>
                    <h3>Mihai Stanciu</h3>
                    <p>Fotograf de evenimente</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-preview section">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>Despre <span class="accent-text">Spectro Studio</span></h2>
                    <p>Cu peste 10 ani de experiență în industria fotografică, oferim servicii de cea mai înaltă calitate pentru a surprinde cele mai importante momente din viața dumneavoastră.</p>
                    <p>Echipa noastră de fotografi profesioniști combină expertiza tehnică cu viziunea artistică pentru a crea fotografii care spun o poveste.</p>
                    <a href="about.php" class="btn btn-secondary">Află mai multe</a>
                </div>
                <div class="about-image">
                    <img src="assets/img/studio.jpg" alt="Spectro Studio">
                </div>
            </div>
        </div>
    </section>

    <!-- Contact CTA -->
    <section class="contact-cta section">
        <div class="container">
            <h2>Pregătit să <span class="accent-text">colaborăm?</span></h2>
            <p>Contactează-ne astăzi pentru a discuta despre proiectul tău fotografic</p>
            <a href="contact.php" class="btn btn-primary">Contactează-ne</a>
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
</body>
</html>