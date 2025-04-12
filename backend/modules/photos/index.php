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

// Obținem lista de galerii pentru formular
$galleries = [];
try {
    $stmt = $conn->query("SELECT id, title FROM galleries ORDER BY created_at DESC");
    $galleries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Eroare silențioasă - vom gestiona în interfață
}

// Obținem lista de fotografi pentru formular
$photographers = [];
try {
    $stmt = $conn->query("SELECT id, name FROM photographers ORDER BY name ASC");
    $photographers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Eroare silențioasă - vom gestiona în interfață
}
?>

<div class="module-header">
    <h2>Management Fotografii</h2>
    <button id="toggle-upload-form" class="btn btn-primary">Încarcă fotografii noi</button>
</div>

<div id="image-message" class="alert" style="display: none;"></div>

<!-- Formular încărcare fotografii -->
<div id="upload-container" style="display: none;" class="form-gradient-container">
    <div class="admin-form">
        <div class="card">
            <div class="card-header">
                <h3>Încărcare Fotografii</h3>
            </div>
            <div class="card-body">
                <div id="dropzone" class="dropzone">
                    <div class="dz-message">
                        <div class="dropzone-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                        <h4>Trage și plasează fotografii aici</h4>
                        <p>sau</p>
                        <button type="button" id="select-files-btn" class="btn btn-outline-primary">Selectează Fișiere</button>
                        <input type="file" id="file-input" multiple accept="image/*" style="display: none;">
                    </div>
                </div>
                
                <div id="upload-preview" class="upload-preview">
                    <div class="upload-info">
                        <h4>Fotografii selectate: <span id="selected-count">0</span></h4>
                        <button id="clear-selection" class="btn btn-sm btn-outline-secondary" style="display: none;">Golește selecția</button>
                    </div>
                    <div id="preview-container" class="preview-grid"></div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h3>Detalii Fotografii</h3>
            </div>
            <div class="card-body">
                <form id="upload-form" class="admin-form">
                    <div class="form-row">
                        <div class="form-group form-group-half">
                            <label for="gallery_id">Galerie asociată</label>
                            <select id="gallery_id" name="gallery_id" class="form-control">
                                <option value="">-- Selectează galeria --</option>
                                <?php foreach($galleries as $gallery): ?>
                                    <option value="<?php echo $gallery['id']; ?>"><?php echo htmlspecialchars($gallery['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($galleries)): ?>
                                <small class="form-text text-warning">Nu există galerii disponibile. <a href="#galleries" class="tab-link" data-tab="galleries">Adaugă o galerie</a></small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group form-group-half">
                            <label for="photographer_id">Fotograf</label>
                            <select id="photographer_id" name="photographer_id" class="form-control">
                                <option value="">-- Selectează fotograful --</option>
                                <?php foreach($photographers as $photographer): ?>
                                    <option value="<?php echo $photographer['id']; ?>"><?php echo htmlspecialchars($photographer['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($photographers)): ?>
                                <small class="form-text text-warning">Nu există fotografi disponibili. <a href="#photographers" class="tab-link" data-tab="photographers">Adaugă un fotograf</a></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Descriere generală (se va aplica tuturor fotografiilor)</label>
                        <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Opțiuni de procesare</label>
                        <div class="checkbox-group">
                            <label class="checkbox-inline">
                                <input type="checkbox" name="generate_thumbnails" id="generate_thumbnails" checked> Generează miniaturi
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="optimize_images" id="optimize_images" checked> Optimizează imaginile
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="add_watermark" id="add_watermark"> Adaugă watermark
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Dimensiuni de generare</label>
                        <div class="checkbox-group">
                            <label class="checkbox-inline">
                                <input type="checkbox" name="size_thumbnail" id="size_thumbnail" checked> Miniatură (300x200px)
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="size_medium" id="size_medium" checked> Medie (800x600px)
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="size_large" id="size_large" checked> Mare (1200x900px)
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="size_original" id="size_original" checked> Originală (dimensiune completă)
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" id="upload-btn" class="btn btn-primary" disabled>Încarcă Fotografiile</button>
                        <button type="button" id="cancel-upload" class="btn btn-secondary">Anulează</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Lista de fotografii -->
<div id="photos-container">
    <div class="filters-section">
        <div class="filters-row">
            <div class="filter-group">
                <label for="filter-gallery">Galerie:</label>
                <select id="filter-gallery" class="form-control-sm">
                    <option value="">Toate galeriile</option>
                    <?php foreach($galleries as $gallery): ?>
                        <option value="<?php echo $gallery['id']; ?>"><?php echo htmlspecialchars($gallery['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="filter-photographer">Fotograf:</label>
                <select id="filter-photographer" class="form-control-sm">
                    <option value="">Toți fotografii</option>
                    <?php foreach($photographers as $photographer): ?>
                        <option value="<?php echo $photographer['id']; ?>"><?php echo htmlspecialchars($photographer['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="search-photos">Caută:</label>
                <input type="text" id="search-photos" class="form-control-sm" placeholder="Titlu, descriere...">
            </div>
            
            <div class="filter-group ml-auto">
                <button id="apply-filters" class="btn btn-sm btn-primary">Aplică Filtrele</button>
                <button id="reset-filters" class="btn btn-sm btn-secondary">Resetează</button>
            </div>
        </div>
    </div>

    <div id="photos-grid" class="photos-grid">
        <div class="loading-indicator">
            <div class="spinner"></div>
            <p>Se încarcă fotografiile...</p>
        </div>
    </div>
    
    <div id="pagination" class="pagination-container">
        <!-- Paginația va fi adăugată dinamic -->
    </div>
</div>

<!-- Modal pentru editarea detaliilor fotografiei -->
<div id="edit-photo-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Editează Fotografia</h2>
        
        <form id="edit-photo-form" class="admin-form">
            <input type="hidden" id="edit-photo-id" name="id">
            
            <div class="modal-body">
                <div class="edit-photo-preview">
                    <img id="edit-photo-image" src="" alt="Preview">
                </div>
                
                <div class="form-group">
                    <label for="edit-title">Titlu</label>
                    <input type="text" id="edit-title" name="title" class="form-control">
                </div>
                
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label for="edit-gallery">Galerie</label>
                        <select id="edit-gallery" name="gallery_id" class="form-control">
                            <option value="">-- Selectează galeria --</option>
                            <?php foreach($galleries as $gallery): ?>
                                <option value="<?php echo $gallery['id']; ?>"><?php echo htmlspecialchars($gallery['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group form-group-half">
                        <label for="edit-photographer">Fotograf</label>
                        <select id="edit-photographer" name="photographer_id" class="form-control">
                            <option value="">-- Selectează fotograful --</option>
                            <?php foreach($photographers as $photographer): ?>
                                <option value="<?php echo $photographer['id']; ?>"><?php echo htmlspecialchars($photographer['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit-description">Descriere</label>
                    <textarea id="edit-description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Opțiuni</label>
                    <div class="checkbox-group">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="is_featured" id="edit-is-featured"> Fotografie Promovată
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="is_visible" id="edit-is-visible" checked> Vizibilă Public
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Salvează Modificările</button>
                <button type="button" class="btn btn-danger" id="delete-photo-btn">Șterge Fotografia</button>
                <button type="button" class="btn btn-secondary close-modal-btn">Anulează</button>
            </div>
        </form>
    </div>
</div>

<style>
.dropzone {
    min-height: 200px;
    border: 2px dashed rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    padding: 30px;
    text-align: center;
    transition: all 0.3s ease;
    background-color: rgba(0, 0, 0, 0.15);
    cursor: pointer;
    margin-bottom: 20px;
}

.dropzone.dragover {
    border-color: var(--color-accent);
    background-color: rgba(var(--color-accent-rgb), 0.1);
}

.dropzone-icon {
    font-size: 3em;
    color: var(--color-accent);
    margin-bottom: 15px;
}

.dropzone h4 {
    margin-bottom: 10px;
    font-weight: 600;
}

.preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.preview-item {
    position: relative;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    background-color: rgba(0, 0, 0, 0.2);
}

.preview-img {
    width: 100%;
    aspect-ratio: 3/2;
    object-fit: cover;
    display: block;
}

.preview-remove {
    position: absolute;
    top: 5px;
    right: 5px;
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    border-radius: 50%;
    width: 22px;
    height: 22px;
    line-height: 1;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.preview-remove:hover {
    background-color: rgba(220, 53, 69, 0.8);
}

.preview-info {
    padding: 8px;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.7);
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
}

.photos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.photo-card {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    background-color: rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.photo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
}

.photo-img {
    width: 100%;
    aspect-ratio: 3/2;
    object-fit: cover;
    display: block;
}

.photo-info {
    padding: 12px;
}

.photo-title {
    font-weight: 600;
    margin-bottom: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.photo-meta {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.6);
}

.photo-actions {
    position: absolute;
    top: 0;
    right: 0;
    display: flex;
    padding: 8px;
    background: linear-gradient(to bottom, rgba(0,0,0,0.7), transparent);
    width: 100%;
    justify-content: flex-end;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.photo-card:hover .photo-actions {
    opacity: 1;
}

.photo-action-btn {
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    margin-left: 5px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.photo-action-btn:hover {
    background-color: var(--color-accent);
}

.photo-badges {
    position: absolute;
    bottom: 40px;
    left: 0;
    padding: 5px;
    display: flex;
    gap: 5px;
}

.photo-badge {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    color: white;
}

.badge-featured {
    background-color: var(--color-accent);
}

.badge-hidden {
    background-color: #6c757d;
}

.filters-section {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    padding: 15px;
    margin-top: 20px;
}

.filters-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: center;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    overflow: auto;
}

.modal-content {
    background-color: var(--bg-color-secondary);
    margin: 5% auto;
    padding: 30px;
    width: 80%;
    max-width: 900px;
    border-radius: 8px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
    position: relative;
}

.close-modal {
    position: absolute;
    right: 20px;
    top: 20px;
    font-size: 24px;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.5);
}

.close-modal:hover {
    color: var(--color-accent);
}

.modal-body {
    margin-top: 20px;
    margin-bottom: 20px;
}

.edit-photo-preview {
    text-align: center;
    margin-bottom: 20px;
}

.edit-photo-preview img {
    max-width: 100%;
    max-height: 300px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}

.pagination-container {
    display: flex;
    justify-content: center;
    margin-top: 30px;
}

.pagination {
    display: flex;
    list-style: none;
    padding: 0;
    gap: 5px;
}

.pagination li {
    display: inline-block;
}

.pagination a {
    padding: 8px 12px;
    border-radius: 4px;
    background-color: rgba(0, 0, 0, 0.3);
    color: white;
    text-decoration: none;
    transition: all 0.2s ease;
}

.pagination a:hover {
    background-color: rgba(var(--color-accent-rgb), 0.5);
}

.pagination .active a {
    background-color: var(--color-accent);
    color: white;
}

.loading-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 50px;
    grid-column: 1/-1;
}

.spinner {
    border: 4px solid rgba(255, 255, 255, 0.1);
    border-top: 4px solid var(--color-accent);
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin-bottom: 15px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inițializare variabile
    const selectedFiles = new Map(); // Folosim Map pentru a păstra fișierele selectate
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('file-input');
    const previewContainer = document.getElementById('preview-container');
    const uploadBtn = document.getElementById('upload-btn');
    const selectFilesBtn = document.getElementById('select-files-btn');
    const clearSelectionBtn = document.getElementById('clear-selection');
    const selectedCountElement = document.getElementById('selected-count');
    const uploadForm = document.getElementById('upload-form');
    const uploadContainer = document.getElementById('upload-container');
    const toggleUploadBtn = document.getElementById('toggle-upload-form');
    const cancelUploadBtn = document.getElementById('cancel-upload');
    
    // Toggle formular încărcare
    toggleUploadBtn.addEventListener('click', function() {
        if (uploadContainer.style.display === 'none' || !uploadContainer.style.display) {
            uploadContainer.style.display = 'block';
            this.textContent = 'Ascunde formularul';
        } else {
            uploadContainer.style.display = 'none';
            this.textContent = 'Încarcă fotografii noi';
        }
    });
    
    // Anulare încărcare
    cancelUploadBtn.addEventListener('click', function() {
        uploadContainer.style.display = 'none';
        toggleUploadBtn.textContent = 'Încarcă fotografii noi';
        clearFiles();
    });
    
    // Deschide file input când se apasă pe butonul de selecție
    selectFilesBtn.addEventListener('click', function() {
        fileInput.click();
    });
    
    // Actualizează previzualizarea când se selectează fișiere
    fileInput.addEventListener('change', function(e) {
        handleFileSelect(e.target.files);
    });
    
    // Drag & Drop
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
    
    dropzone.addEventListener('dragleave', function() {
        this.classList.remove('dragover');
    });
    
    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('dragover');
        
        if (e.dataTransfer.files.length > 0) {
            handleFileSelect(e.dataTransfer.files);
        }
    });
    
    // Click pe dropzone
    dropzone.addEventListener('click', function() {
        fileInput.click();
    });
    
    // Golește selecția
    clearSelectionBtn.addEventListener('click', clearFiles);
    
    // Procesare încărcare fotografii
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (selectedFiles.size === 0) {
            showMessage('Selectați cel puțin o fotografie pentru încărcare.', 'warning');
            return;
        }
        
        // Disable form controls during upload
        toggleFormControls(false);
        showMessage('Încarcare în curs. Vă rugăm să așteptați...', 'info');
        
        // Construim FormData pentru încărcare
        const formData = new FormData();
        
        // Adăugăm fișierele
        selectedFiles.forEach((file, id) => {
            formData.append('photos[]', file);
        });
        
        // Adăugăm câmpurile formularului
        formData.append('gallery_id', document.getElementById('gallery_id').value);
        formData.append('photographer_id', document.getElementById('photographer_id').value);
        formData.append('description', document.getElementById('description').value);
        
        // Opțiuni de procesare
        formData.append('generate_thumbnails', document.getElementById('generate_thumbnails').checked ? '1' : '0');
        formData.append('optimize_images', document.getElementById('optimize_images').checked ? '1' : '0');
        formData.append('add_watermark', document.getElementById('add_watermark').checked ? '1' : '0');
        
        // Dimensiuni
        formData.append('size_thumbnail', document.getElementById('size_thumbnail').checked ? '1' : '0');
        formData.append('size_medium', document.getElementById('size_medium').checked ? '1' : '0');
        formData.append('size_large', document.getElementById('size_large').checked ? '1' : '0');
        formData.append('size_original', document.getElementById('size_original').checked ? '1' : '0');
        
        // Upload photos
        fetch('/backend/api/sp_API__photos.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(`${data.message} S-au încărcat ${data.uploaded_count} fotografii.`, 'success');
                clearFiles();
                uploadContainer.style.display = 'none';
                toggleUploadBtn.textContent = 'Încarcă fotografii noi';
                loadPhotos(); // Reîncarcă lista de fotografii
            } else {
                showMessage('Eroare la încărcarea fotografiilor: ' + data.message, 'error');
            }
            toggleFormControls(true);
        })
        .catch(error => {
            showMessage('Eroare la comunicarea cu serverul: ' + error, 'error');
            toggleFormControls(true);
        });
    });
    
    // Încarcă inițial fotografiile
    loadPhotos();
    
    // Configurează filtrele
    document.getElementById('apply-filters').addEventListener('click', function() {
        loadPhotos(1); // Reîncarcă prima pagină cu filtrele aplicate
    });
    
    document.getElementById('reset-filters').addEventListener('click', function() {
        document.getElementById('filter-gallery').value = '';
        document.getElementById('filter-photographer').value = '';
        document.getElementById('search-photos').value = '';
        loadPhotos(); // Reîncarcă fără filtre
    });
    
    // Setup modal events
    const modal = document.getElementById('edit-photo-modal');
    const closeModalBtns = document.querySelectorAll('.close-modal, .close-modal-btn');
    
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    });
    
    // Închide modalul când se face click în afara lui
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Formular de editare fotografie
    document.getElementById('edit-photo-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'update');
        
        fetch('/backend/api/sp_API__photos.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                modal.style.display = 'none';
                loadPhotos(); // Reîncarcă fotografiile pentru a vedea modificările
            } else {
                showMessage('Eroare la actualizarea fotografiei: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showMessage('Eroare la comunicarea cu serverul: ' + error, 'error');
        });
    });
    
    // Ștergere fotografie
    document.getElementById('delete-photo-btn').addEventListener('click', function() {
        const photoId = document.getElementById('edit-photo-id').value;
        
        if (!photoId) return;
        
        if (confirm('Sigur doriți să ștergeți această fotografie? Această acțiune este ireversibilă.')) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', photoId);
            
            fetch('/backend/api/sp_API__photos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    modal.style.display = 'none';
                    loadPhotos(); // Reîncarcă fotografiile pentru a vedea modificările
                } else {
                    showMessage('Eroare la ștergerea fotografiei: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('Eroare la comunicarea cu serverul: ' + error, 'error');
            });
        }
    });
    
    // Funcții ajutătoare
    function handleFileSelect(files) {
        if (files.length === 0) return;
        
        // Procesează fiecare fișier
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            
            // Verifică dacă este o imagine
            if (!file.type.match('image.*')) {
                continue;
            }
            
            // Generează ID unic pentru fișier
            const fileId = Date.now() + '-' + Math.random().toString(36).substr(2, 9);
            
            // Adaugă în Map
            selectedFiles.set(fileId, file);
            
            // Creează element de previzualizare
            const previewItem = document.createElement('div');
            previewItem.className = 'preview-item';
            previewItem.dataset.fileId = fileId;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                previewItem.innerHTML = `
                    <img class="preview-img" src="${e.target.result}" alt="${file.name}">
                    <button class="preview-remove" title="Elimină">&times;</button>
                    <div class="preview-info">${file.name}</div>
                `;
                
                // Adaugă event listener pentru butonul de eliminare
                previewItem.querySelector('.preview-remove').addEventListener('click', function() {
                    selectedFiles.delete(fileId);
                    previewItem.remove();
                    updateFileCount();
                });
            };
            
            reader.readAsDataURL(file);
            previewContainer.appendChild(previewItem);
        }
        
        updateFileCount();
    }
    
    function updateFileCount() {
        const count = selectedFiles.size;
        selectedCountElement.textContent = count;
        
        if (count > 0) {
            document.getElementById('upload-preview').style.display = 'block';
            clearSelectionBtn.style.display = 'block';
            uploadBtn.disabled = false;
        } else {
            document.getElementById('upload-preview').style.display = 'none';
            clearSelectionBtn.style.display = 'none';
            uploadBtn.disabled = true;
        }
    }
    
    function clearFiles() {
        selectedFiles.clear();
        previewContainer.innerHTML = '';
        fileInput.value = '';
        updateFileCount();
    }
    
    function toggleFormControls(enabled) {
        const formElements = uploadForm.elements;
        for (let i = 0; i < formElements.length; i++) {
            formElements[i].disabled = !enabled;
        }
    }
    
    function showMessage(message, type) {
        const messageElement = document.getElementById('image-message');
        messageElement.textContent = message;
        messageElement.className = 'alert alert-' + type;
        messageElement.style.display = 'block';
        
        setTimeout(() => {
            messageElement.style.display = 'none';
        }, 5000);
    }
    
    function loadPhotos(page = 1) {
        const photosGrid = document.getElementById('photos-grid');
        const paginationContainer = document.getElementById('pagination');
        
        // Arată indicator de încărcare
        photosGrid.innerHTML = `
            <div class="loading-indicator">
                <div class="spinner"></div>
                <p>Se încarcă fotografiile...</p>
            </div>
        `;
        
        // Construiește query parameters pentru filtre
        const filterGallery = document.getElementById('filter-gallery').value;
        const filterPhotographer = document.getElementById('filter-photographer').value;
        const searchQuery = document.getElementById('search-photos').value;
        
        const queryParams = new URLSearchParams({
            action: 'list',
            page: page
        });
        
        if (filterGallery) queryParams.append('gallery_id', filterGallery);
        if (filterPhotographer) queryParams.append('photographer_id', filterPhotographer);
        if (searchQuery) queryParams.append('search', searchQuery);
        
        // Încarcă fotografiile
        fetch(`/backend/api/sp_API__photos.php?${queryParams.toString()}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderPhotos(data.data, photosGrid);
                    renderPagination(data.pagination, paginationContainer);
                } else {
                    photosGrid.innerHTML = `<div class="empty-state">Nu s-au putut încărca fotografiile. ${data.message}</div>`;
                    paginationContainer.innerHTML = '';
                }
            })
            .catch(error => {
                photosGrid.innerHTML = `<div class="empty-state">Eroare la comunicarea cu serverul: ${error}</div>`;
                paginationContainer.innerHTML = '';
            });
    }
    
    function renderPhotos(photos, container) {
        // Golește containerul
        container.innerHTML = '';
        
        if (photos.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">📷</div>
                    <h3>Nicio fotografie găsită</h3>
                    <p>Nu există fotografii care să corespundă criteriilor de filtrare.</p>
                </div>
            `;
            return;
        }
        
        // Adaugă fiecare fotografie
        photos.forEach(photo => {
            const photoCard = document.createElement('div');
            photoCard.className = 'photo-card';
            
            // Construiește badges dacă este necesar
            let badges = '';
            if (photo.is_featured == 1) {
                badges += '<span class="photo-badge badge-featured">Promovat</span>';
            }
            if (photo.is_visible == 0) {
                badges += '<span class="photo-badge badge-hidden">Ascuns</span>';
            }
            
            photoCard.innerHTML = `
                <img class="photo-img" src="${photo.thumb_url}" alt="${photo.title || 'Fotografie'}">
                <div class="photo-actions">
                    <button class="photo-action-btn edit-btn" title="Editează" data-id="${photo.id}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="photo-action-btn view-btn" title="Vizualizare" data-id="${photo.id}" data-url="${photo.url}">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                ${badges ? `<div class="photo-badges">${badges}</div>` : ''}
                <div class="photo-info">
                    <div class="photo-title">${photo.title || 'Fără titlu'}</div>
                    <div class="photo-meta">
                        ${photo.gallery_title ? `<div>Galerie: ${photo.gallery_title}</div>` : ''}
                        ${photo.photographer_name ? `<div>Fotograf: ${photo.photographer_name}</div>` : ''}
                    </div>
                </div>
            `;
            
            // Adaugă event listeners pentru butoane
            photoCard.querySelector('.edit-btn').addEventListener('click', function() {
                loadPhotoDetails(this.dataset.id);
            });
            
            photoCard.querySelector('.view-btn').addEventListener('click', function() {
                window.open(this.dataset.url, '_blank');
            });
            
            container.appendChild(photoCard);
        });
    }
    
    function renderPagination(pagination, container) {
        if (!pagination || pagination.total_pages <= 1) {
            container.innerHTML = '';
            return;
        }
        
        let paginationHTML = '<ul class="pagination">';
        
        // Previous page
        if (pagination.current_page > 1) {
            paginationHTML += `<li><a href="#" data-page="${pagination.current_page - 1}">&laquo;</a></li>`;
        } else {
            paginationHTML += `<li class="disabled"><a>&laquo;</a></li>`;
        }
        
        // Page numbers
        for (let i = 1; i <= pagination.total_pages; i++) {
            if (i === pagination.current_page) {
                paginationHTML += `<li class="active"><a>${i}</a></li>`;
            } else {
                paginationHTML += `<li><a href="#" data-page="${i}">${i}</a></li>`;
            }
        }
        
        // Next page
        if (pagination.current_page < pagination.total_pages) {
            paginationHTML += `<li><a href="#" data-page="${pagination.current_page + 1}">&raquo;</a></li>`;
        } else {
            paginationHTML += `<li class="disabled"><a>&raquo;</a></li>`;
        }
        
        paginationHTML += '</ul>';
        container.innerHTML = paginationHTML;
        
        // Add click event listeners
        container.querySelectorAll('.pagination a[data-page]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                loadPhotos(parseInt(this.dataset.page));
            });
        });
    }
    
    function loadPhotoDetails(photoId) {
        fetch(`/backend/api/sp_API__photos.php?action=get&id=${photoId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const photo = data.data;
                    
                    // Completează formularul cu datele fotografiei
                    document.getElementById('edit-photo-id').value = photo.id;
                    document.getElementById('edit-title').value = photo.title || '';
                    document.getElementById('edit-description').value = photo.description || '';
                    document.getElementById('edit-gallery').value = photo.gallery_id || '';
                    document.getElementById('edit-photographer').value = photo.photographer_id || '';
                    document.getElementById('edit-is-featured').checked = photo.is_featured == 1;
                    document.getElementById('edit-is-visible').checked = photo.is_visible == 1;
                    
                    // Setează imaginea de previzualizare
                    document.getElementById('edit-photo-image').src = photo.medium_url || photo.url;
                    
                    // Afișează modalul
                    document.getElementById('edit-photo-modal').style.display = 'block';
                } else {
                    showMessage('Eroare la încărcarea detaliilor fotografiei: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('Eroare la comunicarea cu serverul: ' + error, 'error');
            });
    }
});
</script>