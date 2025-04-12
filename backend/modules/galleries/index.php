<?php
// filepath: backend/modules/galleries/index.php
// Verifică dacă este inclus din admin_dashboard.php
if (!defined('IS_ADMIN_DASHBOARD')) {
    define('IS_ADMIN_DASHBOARD', true);
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
    // Eroare silențioasă - vom gestiona în interfață dacă nu există categorii
}
?>

<div class="module-header">
    <h2>Galerii</h2>
    <button id="add-gallery-btn" class="btn btn-primary">Adaugă galerie nouă</button>
</div>

<div id="gallery-message" class="alert" style="display: none;"></div>

<!-- Formular adăugare/editare galerie -->
<div id="gallery-form-container" style="display: none;" class="form-gradient-container">
    <div class="admin-form">
        <h3 id="form-title">Adaugă galerie nouă</h3>
        
        <form id="gallery-form" enctype="multipart/form-data">
            <input type="hidden" id="gallery-id" name="id" value="">
            
            <div class="form-group">
                <label for="title">Titlu galerie</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="slug">Slug (URL) - lăsați gol pentru generare automată</label>
                <input type="text" id="slug" name="slug">
            </div>
            
            <div class="form-row">
                <div class="form-group form-group-half">
                    <label for="category_id">Categorie</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="">-- Selectează categoria --</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group form-group-half">
                    <label for="subcategory_id">Subcategorie</label>
                    <select id="subcategory_id" name="subcategory_id" class="form-control" disabled>
                        <option value="">-- Selectează mai întâi categoria --</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Descriere</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>
            
            <div class="form-group">
                <label for="cover_image">Imagine copertă</label>
                <input type="file" id="cover_image" name="cover_image" accept="image/*">
                <div id="image-preview" class="img-preview" style="display: none;">
                    <img id="preview-img" src="" alt="Preview">
                </div>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="is_featured" name="is_featured" value="1">
                    Afișează în secțiunea Featured
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Salvează</button>
                <button type="button" id="cancel-btn" class="btn btn-secondary">Anulează</button>
            </div>
        </form>
    </div>
</div>

<!-- Lista de galerii -->
<div class="galleries-list">
    <table class="admin-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Imagine copertă</th>
                <th>Titlu</th>
                <th>Categorie</th>
                <th>Nr. fotografii</th>
                <th>Featured</th>
                <th>Acțiuni</th>
            </tr>
        </thead>
        <tbody id="galleries-table-body">
            <tr>
                <td colspan="7">Se încarcă galeriile...</td>
            </tr>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Încarcă lista de galerii
    loadGalleries();
    
    // Afișare/ascundere formular de adăugare
    document.getElementById('add-gallery-btn').addEventListener('click', function() {
        const formContainer = document.getElementById('gallery-form-container');
        if (formContainer.style.display === 'none' || !formContainer.style.display) {
            // Deschidere formular
            document.getElementById('form-title').textContent = 'Adaugă galerie nouă';
            document.getElementById('gallery-form').reset();
            document.getElementById('gallery-id').value = '';
            document.getElementById('image-preview').style.display = 'none';
            document.getElementById('subcategory_id').disabled = true;
            document.getElementById('subcategory_id').innerHTML = '<option value="">-- Selectează mai întâi categoria --</option>';
            formContainer.style.display = 'block';
            this.textContent = 'Ascunde formularul';
        } else {
            // Închidere formular
            formContainer.style.display = 'none';
            this.textContent = 'Adaugă galerie nouă';
        }
    });
    
    // Anulare formular
    document.getElementById('cancel-btn').addEventListener('click', function() {
        document.getElementById('gallery-form').reset();
        document.getElementById('gallery-form-container').style.display = 'none';
        document.getElementById('add-gallery-btn').textContent = 'Adaugă galerie nouă';
    });
    
    // Preview imagine
    document.getElementById('cover_image').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('image-preview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Încarcă subcategorii când se schimbă categoria
    document.getElementById('category_id').addEventListener('change', function() {
        const categoryId = this.value;
        const subcategorySelect = document.getElementById('subcategory_id');
        
        // Resetăm lista de subcategorii
        subcategorySelect.innerHTML = '<option value="">-- Selectează subcategoria --</option>';
        subcategorySelect.disabled = true;
        
        if (!categoryId) {
            return;
        }
        
        // Încarcă subcategoriile pentru categoria selectată
        fetch('/backend/api/sp_API__galleries.php?action=get_subcategories&category_id=' + categoryId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    subcategorySelect.disabled = false;
                    
                    if (data.data.length > 0) {
                        data.data.forEach(subcategory => {
                            const option = document.createElement('option');
                            option.value = subcategory.id;
                            option.textContent = subcategory.name;
                            subcategorySelect.appendChild(option);
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching subcategories:', error);
            });
    });
    
    // Procesare formular
    document.getElementById('gallery-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const galleryId = document.getElementById('gallery-id').value;
        
        if (galleryId) {
            formData.append('action', 'edit');
        } else {
            formData.append('action', 'add');
        }
        
        // Corectăm calea către API
        fetch('/backend/api/sp_API__galleries.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                document.getElementById('gallery-form').reset();
                document.getElementById('gallery-form-container').style.display = 'none';
                document.getElementById('add-gallery-btn').textContent = 'Adaugă galerie nouă';
                loadGalleries();
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showMessage('Eroare la procesarea cererii: ' + error, 'error');
        });
    });
});

// Funcția pentru încărcarea galeriilor
function loadGalleries() {
    // Corectăm calea către API
    fetch('/backend/api/sp_API__galleries.php?action=list')
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('galleries-table-body');
            tableBody.innerHTML = '';
            
            if (data.success && data.data.length > 0) {
                data.data.forEach(gallery => {
                    const row = document.createElement('tr');
                    
                    const idCell = document.createElement('td');
                    idCell.textContent = gallery.id;
                    row.appendChild(idCell);
                    
                    const imageCell = document.createElement('td');
                    if (gallery.cover_image) {
                        const img = document.createElement('img');
                        img.src = `../../../assets/img/galleries/${gallery.cover_image}`;
                        img.alt = gallery.title;
                        img.style.width = '50px';
                        img.style.height = '50px';
                        img.style.objectFit = 'cover';
                        imageCell.appendChild(img);
                    } else {
                        imageCell.textContent = 'Fără imagine';
                    }
                    row.appendChild(imageCell);
                    
                    const titleCell = document.createElement('td');
                    titleCell.textContent = gallery.title;
                    row.appendChild(titleCell);
                    
                    const categoryCell = document.createElement('td');
                    categoryCell.textContent = gallery.category_name || '-';
                    row.appendChild(categoryCell);
                    
                    const photoCountCell = document.createElement('td');
                    photoCountCell.textContent = gallery.photo_count || '0';
                    row.appendChild(photoCountCell);
                    
                    const featuredCell = document.createElement('td');
                    featuredCell.innerHTML = gallery.is_featured == 1 ? 
                        '<span class="badge bg-success">Da</span>' : 
                        '<span class="badge bg-secondary">Nu</span>';
                    row.appendChild(featuredCell);
                    
                    const actionsCell = document.createElement('td');
                    actionsCell.className = 'text-end';
                    
                    const editBtn = document.createElement('button');
                    editBtn.className = 'btn btn-sm btn-primary me-2';
                    editBtn.innerHTML = '<i class="bi bi-pencil-square"></i>';
                    editBtn.title = 'Editează';
                    editBtn.addEventListener('click', () => editGallery(gallery.id));
                    actionsCell.appendChild(editBtn);
                    
                    const deleteBtn = document.createElement('button');
                    deleteBtn.className = 'btn btn-sm btn-danger';
                    deleteBtn.innerHTML = '<i class="bi bi-trash"></i>';
                    deleteBtn.title = 'Șterge';
                    deleteBtn.addEventListener('click', () => deleteGallery(gallery.id));
                    actionsCell.appendChild(deleteBtn);
                    
                    row.appendChild(actionsCell);
                    tableBody.appendChild(row);
                });
            } else {
                const row = document.createElement('tr');
                const cell = document.createElement('td');
                cell.colSpan = 7;
                cell.textContent = 'Nu există galerii disponibile.';
                cell.className = 'text-center';
                row.appendChild(cell);
                tableBody.appendChild(row);
            }
        })
        .catch(error => {
            console.error('Error loading galleries:', error);
        });
}

// Funcția pentru afișarea mesajelor
function showMessage(message, type) {
    const messageElement = document.getElementById('gallery-message');
    messageElement.textContent = message;
    messageElement.className = 'alert alert-' + type;
    messageElement.style.display = 'block';
    
    setTimeout(() => {
        messageElement.style.display = 'none';
    }, 5000);
}

// Funcția pentru editarea unei galerii
function editGallery(id) {
    // Corectăm calea către API
    fetch(`/backend/api/sp_API__galleries.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const gallery = data.data;
                
                document.getElementById('form-title').textContent = 'Editează galeria';
                document.getElementById('gallery-id').value = gallery.id;
                document.getElementById('title').value = gallery.title;
                document.getElementById('slug').value = gallery.slug || '';
                document.getElementById('description').value = gallery.description || '';
                document.getElementById('is_featured').checked = gallery.is_featured == 1;
                
                // Setăm categoria și declanșăm evenimentul change pentru a încărca subcategoriile
                const categorySelect = document.getElementById('category_id');
                if (gallery.category_id) {
                    categorySelect.value = gallery.category_id;
                    
                    // Declanșăm evenimentul change pentru a încărca subcategoriile
                    const changeEvent = new Event('change');
                    categorySelect.dispatchEvent(changeEvent);
                    
                    // După ce subcategoriile au fost încărcate, setăm subcategoria selectată
                    if (gallery.subcategory_id) {
                        setTimeout(() => {
                            document.getElementById('subcategory_id').value = gallery.subcategory_id;
                        }, 500);
                    }
                }
                
                if (gallery.cover_image) {
                    document.getElementById('preview-img').src = `../../../assets/img/galleries/${gallery.cover_image}`;
                    document.getElementById('image-preview').style.display = 'block';
                } else {
                    document.getElementById('image-preview').style.display = 'none';
                }
                
                document.getElementById('gallery-form-container').style.display = 'block';
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showMessage('Eroare la încărcarea galeriei: ' + error, 'error');
        });
}

// Funcția pentru ștergerea unei galerii
function deleteGallery(id) {
    if (confirm('Sigur doriți să ștergeți această galerie? Toate fotografiile asociate vor fi șterse!')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        
        // Corectăm calea către API
        fetch('/backend/api/sp_API__galleries.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                loadGalleries();
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showMessage('Eroare la ștergerea galeriei: ' + error, 'error');
        });
    }
}

// Funcția pentru gestionarea fotografiilor dintr-o galerie
function managePhotos(id) {
    // Vom implementa această funcționalitate ulterior
    alert('Această funcționalitate va fi implementată în curând!');
}
</script>