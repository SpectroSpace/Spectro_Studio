<?php
// filepath: f:\SITE SPECTRO STUDIO\backend\install\populate_categories.php
// Script pentru popularea tabelelor de categorii și subcategorii cu structura stabilită

// Definește constanta pentru a permite accesul la fișierul de credențiale
define('IS_AUTHORIZED_ACCESS', true);

// Include fișierul de credențiale pentru baza de date
require_once '../spec_admin__db__credentials.php';

// Funcție pentru crearea slug-urilor
function create_slug($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

// Structura de categorii și subcategorii
$categories = [
    [
        'name' => 'Fotografie de Portret',
        'description' => 'Fotografii ce captează esența și expresivitatea subiecților umani.',
        'subcategories' => [
            ['name' => 'Portrete de Studio', 'description' => 'Portrete realizate în mediu controlat de studio.'],
            ['name' => 'Portrete Corporate/Business', 'description' => 'Fotografii profesionale pentru CV, LinkedIn sau profiluri corporative.'],
            ['name' => 'Portrete High-Key', 'description' => 'Portrete luminoase cu tonuri predominant deschise.'],
            ['name' => 'Portrete Low-Key', 'description' => 'Portrete dramatice cu contrast puternic și tonuri închise.'],
            ['name' => 'Portrete Fine Art', 'description' => 'Portrete conceptuale cu abordare artistică.'],
            ['name' => 'Portrete în Exterior', 'description' => 'Fotografii de portret realizate în locații exterioare.'],
            ['name' => 'Portrete Urbane', 'description' => 'Portrete în mediul urban, utilizând elementele orașului ca fundal.'],
            ['name' => 'Portrete în Natură', 'description' => 'Portrete realizate în medii naturale precum păduri, câmpuri, plaje.'],
            ['name' => 'Portrete Environmental', 'description' => 'Portrete care includ mediul înconjurător al subiectului, relevând povestea acestuia.'],
            ['name' => 'Portrete de Familie', 'description' => 'Fotografii formale sau informale cu grupuri familiale.'],
            ['name' => 'Grupuri Familiale', 'description' => 'Sesiuni foto cu mai multe generații sau ramuri ale familiei.'],
            ['name' => 'Cupluri', 'description' => 'Fotografie de portret cu cupluri, logodnici sau soți.'],
            ['name' => 'Copii', 'description' => 'Fotografii focusate pe captarea personalității și inocenței copiilor.'],
            ['name' => 'Generații Multiple', 'description' => 'Sesiuni foto cu mai multe generații din aceeași familie.']
        ]
    ],
    [
        'name' => 'Fashion',
        'description' => 'Fotografii dedicate modei și frumuseții, evidențiind tendințele și stilul.',
        'subcategories' => [
            ['name' => 'Beauty', 'description' => 'Fotografii cu accent pe machiaj, trăsături faciale și frumusețe.'],
            ['name' => 'Makeup & Skincare', 'description' => 'Imagini cu focus pe machiaj și produse de îngrijire a pielii.'],
            ['name' => 'Hair Styling', 'description' => 'Fotografii care evidențiază coafuri și stiluri de păr.'],
            ['name' => 'Natural Beauty', 'description' => 'Abordare minimalistă care scoate în evidență frumusețea naturală.'],
            ['name' => 'Creative Makeup', 'description' => 'Fotografii cu machiaj artistic, avangardist sau conceptual.'],
            ['name' => 'High Fashion', 'description' => 'Fotografii pentru reviste de modă high-end și designeri de renume.'],
            ['name' => 'Editorial', 'description' => 'Sesiuni foto narative pentru reviste și publicații.'],
            ['name' => 'Haute Couture', 'description' => 'Fotografie dedicată vestimentațiilor de lux și custom-made.'],
            ['name' => 'Runway', 'description' => 'Imagini de la prezentări și defilări de modă.'],
            ['name' => 'Designer Collections', 'description' => 'Fotografii dedicate colecțiilor specifice ale designerilor.'],
            ['name' => 'Commercial Fashion', 'description' => 'Imagini pentru magazine, cataloage și scopuri comerciale.'],
            ['name' => 'Catalog', 'description' => 'Fotografii de produs pentru cataloage tipărite sau online.'],
            ['name' => 'E-commerce', 'description' => 'Imagini optimizate pentru platforme de vânzare online.'],
            ['name' => 'Campaign', 'description' => 'Fotografii pentru campanii publicitare de modă.'],
            ['name' => 'Lookbook', 'description' => 'Colecții de imagini ce prezintă o linie vestimentară completă.'],
            ['name' => 'Street Style', 'description' => 'Fotografii ce captează stiluri vestimentare urbane și tendințe stradale.'],
            ['name' => 'Urban Fashion', 'description' => 'Fashion în contextul mediului urban și al culturii stradale.'],
            ['name' => 'Trend Spotting', 'description' => 'Imagini care documentează apariția și evoluția tendințelor.'],
            ['name' => 'Lifestyle Fashion', 'description' => 'Fotografie de modă în contexte naturale, de viață cotidiană.'],
            ['name' => 'Influencer Style', 'description' => 'Fotografii cu persoane influente în social media și fashion.']
        ]
    ],
    [
        'name' => 'Fotografie de Eveniment',
        'description' => 'Documentarea profesională a evenimentelor speciale și momentelor importante.',
        'subcategories' => [
            ['name' => 'Nunți', 'description' => 'Fotografii de la ceremonii și petreceri de nuntă.'],
            ['name' => 'Pregătiri Mireasă/Mire', 'description' => 'Momentele de pregătire înainte de ceremonie.'],
            ['name' => 'Ceremonia Religioasă', 'description' => 'Fotografii din timpul ceremoniei religioase.'],
            ['name' => 'Ceremonia Civilă', 'description' => 'Documentarea oficializării civile a căsătoriei.'],
            ['name' => 'Recepția', 'description' => 'Fotografii de la petrecerea de nuntă cu invitații.'],
            ['name' => 'Sesiuni Post-Nuntă', 'description' => 'Sesiuni creative după eveniment, inclusiv Trash the Dress.'],
            ['name' => 'Corporate', 'description' => 'Fotografie pentru evenimente din mediul de afaceri.'],
            ['name' => 'Conferințe', 'description' => 'Documentarea conferințelor, prezentărilor și speech-urilor.'],
            ['name' => 'Team Building', 'description' => 'Activități și evenimente dedicate dezvoltării echipelor.'],
            ['name' => 'Gale & Premieri', 'description' => 'Fotografii de la ceremonii de premiere și gale.'],
            ['name' => 'Lansări de Produs', 'description' => 'Evenimente dedicate prezentării noilor produse.'],
            ['name' => 'Social', 'description' => 'Fotografie pentru evenimente sociale și sărbători.'],
            ['name' => 'Petreceri Private', 'description' => 'Fotografii de la petreceri și evenimente private.'],
            ['name' => 'Aniversări', 'description' => 'Documentarea zilelor de naștere și aniversărilor.'],
            ['name' => 'Botezuri', 'description' => 'Fotografii de la ceremonii de botez și petreceri.'],
            ['name' => 'Cununii', 'description' => 'Fotografie pentru ceremonii de cununie civilă sau religioasă.'],
            ['name' => 'Baluri & Bancheturi', 'description' => 'Evenimente formale precum baluri de absolvire sau bancheturi.']
        ]
    ],
    [
        'name' => 'Fotografie Comercială',
        'description' => 'Fotografie profesională pentru afaceri, marketing și publicitate.',
        'subcategories' => [
            ['name' => 'Produs', 'description' => 'Fotografii profesionale pentru prezentarea produselor.'],
            ['name' => 'Bijuterii & Accesorii', 'description' => 'Imagini detaliate pentru bijuterii, ceasuri și accesorii.'],
            ['name' => 'Modă & Vestimentație', 'description' => 'Fotografii de produs pentru articole vestimentare.'],
            ['name' => 'Tehnologie', 'description' => 'Imagini pentru produse electronice și tech.'],
            ['name' => 'Alimente & Băuturi', 'description' => 'Fotografie culinară pentru restaurante și producători.'],
            ['name' => 'Imobiliară', 'description' => 'Fotografie profesională pentru proprietăți și spații.'],
            ['name' => 'Rezidențial', 'description' => 'Imagini pentru apartamente, case și spații de locuit.'],
            ['name' => 'Comercial', 'description' => 'Fotografii pentru spații comerciale și retail.'],
            ['name' => 'Arhitecturală', 'description' => 'Fotografie dedicată clădirilor și structurilor arhitecturale.'],
            ['name' => 'Spații Interioare', 'description' => 'Imagini pentru design interior și decorațiuni.'],
            ['name' => 'Corporativă', 'description' => 'Fotografie pentru nevoi corporate și business.'],
            ['name' => 'Portrete Executive', 'description' => 'Portrete profesionale pentru manageri și executivi.'],
            ['name' => 'Branding Fotografic', 'description' => 'Imagini pentru construirea și susținerea identității de brand.'],
            ['name' => 'Spații de Lucru', 'description' => 'Fotografii pentru birouri și medii de lucru.'],
            ['name' => 'Echipă & Cultura Companiei', 'description' => 'Imagini care reflectă echipa și valorile companiei.']
        ]
    ],
    [
        'name' => 'Artă Fotografică',
        'description' => 'Fotografie cu accent pe expresie artistică și viziune creativă.',
        'subcategories' => [
            ['name' => 'Fine Art', 'description' => 'Fotografie cu valoare artistică, dincolo de aspectul documentar.'],
            ['name' => 'Conceptual', 'description' => 'Imagini bazate pe concepte și idei artistice.'],
            ['name' => 'Abstract', 'description' => 'Fotografie care se îndepărtează de reprezentarea literală.'],
            ['name' => 'Minimalist', 'description' => 'Compoziții simple, cu elemente reduse la esențial.'],
            ['name' => 'Suprarealism', 'description' => 'Fotografii care explorează lumea viselor și a inconștientului.'],
            ['name' => 'Nature & Landscape', 'description' => 'Fotografie artistic dedicată naturii și peisajelor.'],
            ['name' => 'Peisaje Naturale', 'description' => 'Imagini cu peisaje naturale spectaculoase.'],
            ['name' => 'Peisaje Urbane', 'description' => 'Fotografii artistice ale orașelor și arhitecturii urbane.'],
            ['name' => 'Astrofotografie', 'description' => 'Fotografierea cerului nocturn, corpurilor cerești și fenomenelor astronomice.'],
            ['name' => 'Timp Îndelungat', 'description' => 'Tehnici de expunere lungă pentru efecte artistice.'],
            ['name' => 'Alb-Negru', 'description' => 'Fotografie monocromatică artistică.'],
            ['name' => 'Street Photography', 'description' => 'Fotografii spontane din viața stradală.'],
            ['name' => 'Documentar', 'description' => 'Fotografii care documentează realitatea socială sau istorică.'],
            ['name' => 'Portrete Artistice', 'description' => 'Abordări creative ale fotografiei de portret.'],
            ['name' => 'Arhitectură', 'description' => 'Fotografii artistice care evidențiază formele și liniile arhitecturale.']
        ]
    ],
    [
        'name' => 'Fotografie Specializată',
        'description' => 'Genuri fotografice care necesită tehnici sau echipamente specializate.',
        'subcategories' => [
            ['name' => 'Macro', 'description' => 'Fotografii de aproape, detaliate, ale subiectelor mici.'],
            ['name' => 'Natură', 'description' => 'Imagini macro ale plantelor, florilor și elementelor naturale.'],
            ['name' => 'Insecte & Microfaună', 'description' => 'Fotografii detaliate ale insectelor și animalelor mici.'],
            ['name' => 'Abstracte Macro', 'description' => 'Compoziții abstracte folosind tehnici macro.'],
            ['name' => 'Detalii Umane', 'description' => 'Fotografii macro ale trăsăturilor și detaliilor umane.'],
            ['name' => 'Wildlife', 'description' => 'Fotografierea animalelor în habitatul lor natural.'],
            ['name' => 'Fauna Sălbatică', 'description' => 'Imagini cu animale sălbatice terestre.'],
            ['name' => 'Păsări', 'description' => 'Fotografie specializată în captarea păsărilor.'],
            ['name' => 'Subacvatică', 'description' => 'Fotografii realizate în mediul acvatic.'],
            ['name' => 'Comportament Animal', 'description' => 'Imagini care surprind comportamente naturale ale animalelor.'],
            ['name' => 'Lifestyle', 'description' => 'Fotografie care captează momente autentice de viață.'],
            ['name' => 'Travel & Aventură', 'description' => 'Imagini din călătorii și experiențe în jurul lumii.'],
            ['name' => 'Food Photography', 'description' => 'Fotografii artistice ale preparatelor culinare.'],
            ['name' => 'Street Life', 'description' => 'Momente autentice din viața cotidiană urbană.'],
            ['name' => 'Daily Stories', 'description' => 'Fotografie care documentează povești și rutine zilnice.']
        ]
    ],
    [
        'name' => 'Fotografie Tehnică',
        'description' => 'Fotografie bazată pe echipamente și tehnici avansate.',
        'subcategories' => [
            ['name' => 'Aerială', 'description' => 'Fotografii realizate din perspectivă aeriană.'],
            ['name' => 'Drone', 'description' => 'Imagini captate cu ajutorul dronelor.'],
            ['name' => 'Helicopter', 'description' => 'Fotografii realizate din elicoptere.'],
            ['name' => 'Panorame Aeriene', 'description' => 'Compoziții panoramice realizate din aer.'],
            ['name' => 'Timelapse & Video', 'description' => 'Secvențe de imagini care arată trecerea timpului.'],
            ['name' => 'Urbane', 'description' => 'Timelapse-uri ale orașelor și vieții urbane.'],
            ['name' => 'Naturale', 'description' => 'Secvențe temporale ale fenomenelor naturale.'],
            ['name' => 'Astro Timelapse', 'description' => 'Timelapse-uri ale cerului nocturn și fenomenelor astronomice.'],
            ['name' => 'Virtual Reality', 'description' => 'Fotografii pentru experiențe interactive și VR.'],
            ['name' => '360 Panorame', 'description' => 'Imagini panoramice complete, vizualizabile 360°.'],
            ['name' => 'Virtual Tours', 'description' => 'Tururi virtuale interactive pentru spații și locații.'],
            ['name' => 'Interactive Experiences', 'description' => 'Experiențe fotografice interactive și imersive.']
        ]
    ],
    [
        'name' => 'Artistice Specializate',
        'description' => 'Abordări artistice ce folosesc tehnici neconvenționale și experimentale.',
        'subcategories' => [
            ['name' => 'Fotografie Experimentală', 'description' => 'Tehnici neconvenționale și abordări inovatoare.'],
            ['name' => 'Light Painting', 'description' => 'Crearea imaginilor prin mișcarea surselor de lumină în timpul expunerii.'],
            ['name' => 'Multiple Exposure', 'description' => 'Suprapunerea mai multor expuneri într-o singură imagine.'],
            ['name' => 'ICM', 'description' => 'Intentional Camera Movement - mișcarea intenționată a camerei pentru efecte artistice.'],
            ['name' => 'Infraroșu', 'description' => 'Fotografie realizată în spectrul infraroșu.'],
            ['name' => 'Alternative Processes', 'description' => 'Tehnici fotografice alternative sau istorice.'],
            ['name' => 'Cyanotype', 'description' => 'Proces fotografic istoric ce produce imagini în nuanțe de albastru.'],
            ['name' => 'Film Analog', 'description' => 'Fotografie pe film, cu procesare tradițională.'],
            ['name' => 'Vintage Processes', 'description' => 'Tehnici istorice precum dagherotip, ambrotip, etc.'],
            ['name' => 'Digital Mixed Media', 'description' => 'Combinarea fotografiei digitale cu alte medii artistice.']
        ]
    ],
    [
        'name' => 'Tematici Specifice',
        'description' => 'Fotografii organizate după teme, anotimpuri sau momente specifice.',
        'subcategories' => [
            ['name' => 'Anotimp', 'description' => 'Fotografii care captează esența anotimpurilor.'],
            ['name' => 'Primăvară', 'description' => 'Imagini care surprind renașterea naturii și culorile primăverii.'],
            ['name' => 'Vară', 'description' => 'Fotografii cu atmosfera caldă și vibrantă a verii.'],
            ['name' => 'Toamnă', 'description' => 'Imagini cu cromatica bogată și atmosfera nostalgică a toamnei.'],
            ['name' => 'Iarnă', 'description' => 'Fotografii ce captează atmosfera de iarnă și peisaje înzăpezite.'],
            ['name' => 'Momente Zilnice', 'description' => 'Fotografie bazată pe momentele specifice ale zilei.'],
            ['name' => 'Răsărit', 'description' => 'Imagini la răsăritul soarelui cu lumina specifică dimineții.'],
            ['name' => 'Zi', 'description' => 'Fotografii realizate în lumina plină a zilei.'],
            ['name' => 'Apus', 'description' => 'Imagini care surprind apusul și lumina crepusculară.'],
            ['name' => 'Nocturn', 'description' => 'Fotografie nocturnă și în condiții de lumină scăzută.']
        ]
    ]
];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div style='font-family: Montserrat, sans-serif; max-width: 800px; margin: 30px auto; padding: 25px; background-color: #111; color: #fff; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.5);'>";
    echo "<h1 style='color: #E0A80D; margin-bottom: 20px;'>Populare Categorii și Subcategorii</h1>";
    
    // Verifică dacă tabelele există
    try {
        $conn->query("SELECT 1 FROM gallery_categories LIMIT 1");
        $conn->query("SELECT 1 FROM gallery_subcategories LIMIT 1");
    } catch (PDOException $e) {
        echo "<p style='color: #F44336;'>⚠️ Tabelele necesare nu există. Rulați mai întâi script-ul <a href='create_categories_tables.php' style='color: #FF0055; text-decoration: none;'>create_categories_tables.php</a>.</p>";
        echo "</div>";
        exit;
    }
    
    // Goleste tabelele existente pentru a evita duplicatele
    $conn->exec("DELETE FROM gallery_subcategories");
    $conn->exec("DELETE FROM gallery_categories");
    echo "<p>Tabelele au fost curățate pentru a evita duplicatele.</p>";
    
    // Pregătește statement-urile pentru inserare
    $stmt_category = $conn->prepare("INSERT INTO gallery_categories (name, slug, description, order_index) VALUES (:name, :slug, :description, :order_index)");
    $stmt_subcategory = $conn->prepare("INSERT INTO gallery_subcategories (category_id, name, slug, description, order_index) VALUES (:category_id, :name, :slug, :description, :order_index)");
    
    // Adaugă categoriile și subcategoriile
    $order_index = 1;
    foreach ($categories as $category) {
        $category_slug = create_slug($category['name']);
        
        $stmt_category->bindParam(':name', $category['name']);
        $stmt_category->bindParam(':slug', $category_slug);
        $stmt_category->bindParam(':description', $category['description']);
        $stmt_category->bindParam(':order_index', $order_index);
        $stmt_category->execute();
        
        $category_id = $conn->lastInsertId();
        
        echo "<h2 style='color: #4CAF50; margin-top: 20px;'>✅ Categoria '{$category['name']}' adăugată.</h2>";
        echo "<ul>";
        
        // Adaugă subcategoriile pentru această categorie
        $subcat_index = 1;
        foreach ($category['subcategories'] as $subcategory) {
            $subcategory_slug = create_slug($subcategory['name']);
            
            $stmt_subcategory->bindParam(':category_id', $category_id);
            $stmt_subcategory->bindParam(':name', $subcategory['name']);
            $stmt_subcategory->bindParam(':slug', $subcategory_slug);
            $stmt_subcategory->bindParam(':description', $subcategory['description']);
            $stmt_subcategory->bindParam(':order_index', $subcat_index);
            $stmt_subcategory->execute();
            
            echo "<li style='margin-bottom: 5px;'>{$subcategory['name']}</li>";
            $subcat_index++;
        }
        
        echo "</ul>";
        $order_index++;
    }
    
    echo "<p style='color: #E0A80D; font-weight: bold; margin-top: 20px;'>Categoriile și subcategoriile au fost adăugate cu succes în baza de date!</p>";
    echo "<p>Acum puteți utiliza aceste categorii în sistemul de galerii.</p>";
    echo "<p><a href='../spec_admin__index.php' style='color: #FF0055; text-decoration: none; padding: 10px 15px; background-color: rgba(255,255,255,0.1); border-radius: 4px; display: inline-block; margin-top: 10px;'>Înapoi la Panoul de Administrare</a></p>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div style='font-family: Montserrat, sans-serif; max-width: 800px; margin: 30px auto; padding: 20px; background-color: #111; color: #fff; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.5);'>";
    echo "<h1 style='color: #F44336;'>Eroare!</h1>";
    echo "<p style='color: #F44336;'>A apărut o eroare la popularea tabelelor:</p>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>