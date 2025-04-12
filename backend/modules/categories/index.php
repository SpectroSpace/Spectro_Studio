<?php
// Verifică dacă este inclus din admin_dashboard.php
if (!defined('IS_ADMIN_DASHBOARD')) {
    define('IS_ADMIN_DASHBOARD', true);
}

// Stabilim conexiunea la bază de date dacă nu există deja
if (!isset($conn)) {
    define('IS_AUTHORIZED_ACCESS', true);
    require_once '../../spec_admin__db__credentials.php';
}

// Încărcare categorii din baza de date
try {
    $stmt = $conn->query("SELECT * FROM gallery_categories ORDER BY order_index, name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}
?>

<div class="module-header">
    <h2>Administrare Categorii și Subcategorii</h2>
    <div>
        <button id="add-category-btn" class="btn btn-primary">Adaugă Categorie</button>
        <button id="add-subcategory-btn" class="btn btn-secondary">Adaugă Subcategorie</button>
    </div>
</div>

<div id="category-message" class="alert" style="display: none;"></div>

<!-- Tabs pentru a separa categoriile de subcategorii -->
<div class="admin-tabs">
    <button class="tab-btn active" data-tab="categories-tab">Categorii</button>
    <button class="tab-btn" data-tab="subcategories-tab">Subcategorii</button>
</div>

<div id="categories-tab" class="tab-content active">
    <h3>Categorii</h3>
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 30%;">Nume Categorie</th>
                <th style="width: 20%;">Slug</th>
                <th style="width: 25%;">Descriere</th>
                <th style="width: 10%;">Ordine</th>
                <th style="width: 10%;">Acțiuni</th>
            </tr>
        </thead>
        <tbody id="categories-table-body">
            <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="6">Nu există categorii. Adăugați prima categorie folosind butonul de mai sus.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($categories as $index => $category): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($category['name']) ?></td>
                        <td><?= htmlspecialchars($category['slug']) ?></td>
                        <td>
                            <?php 
                            if (!empty($category['description'])) {
                                echo strlen($category['description']) > 50 ? 
                                     htmlspecialchars(substr($category['description'], 0, 50)) . '...' : 
                                     htmlspecialchars($category['description']);
                            } else {
                                echo '<em>Fără descriere</em>';
                            }
                            ?>
                        </td>
                        <td><?= $category['order_index'] ?></td>
                        <td>
                            <button class="btn btn-sm btn-secondary edit-category-btn" 
                                    data-id="<?= $category['id'] ?>"
                                    data-name="<?= htmlspecialchars($category['name']) ?>"
                                    data-slug="<?= htmlspecialchars($category['slug']) ?>"
                                    data-description="<?= htmlspecialchars($category['description']) ?>"
                                    data-order="<?= $category['order_index'] ?>">
                                Editează
                            </button>
                            <button class="btn btn-sm btn-danger delete-category-btn" 
                                    data-id="<?= $category['id'] ?>"
                                    data-name="<?= htmlspecialchars($category['name']) ?>">
                                Șterge
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="subcategories-tab" class="tab-content">
    <h3>Subcategorii</h3>
    <div class="filter-container">
        <label for="filter-category">Filtrare după categorie:</label>
        <select id="filter-category" class="form-control">
            <option value="">Toate categoriile</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 20%;">Subcategorie</th>
                <th style="width: 20%;">Categorie Părinte</th>
                <th style="width: 15%;">Slug</th>
                <th style="width: 20%;">Descriere</th>
                <th style="width: 10%;">Ordine</th>
                <th style="width: 10%;">Acțiuni</th>
            </tr>
        </thead>
        <tbody id="subcategories-table-body">
            <tr>
                <td colspan="7">Se încarcă subcategoriile...</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Formular adăugare/editare categorie -->
<div id="category-form-container" style="display: none;" class="form-gradient-container category-container">
    <div class="admin-form">
        <h3 id="category-form-title" class="form-title">Adaugă Categorie Nouă</h3>
        
        <form id="category-form">
            <input type="hidden" id="category-id" name="id" value="">
            
            <div class="form-group">
                <label for="category-name">Nume Categorie</label>
                <input type="text" id="category-name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="category-slug">Slug (URL) - lăsați gol pentru generare automată</label>
                <input type="text" id="category-slug" name="slug">
            </div>
            
            <div class="form-group">
                <label for="category-description">Descriere</label>
                <textarea id="category-description" name="description" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label for="category-order">Ordine</label>
                <input type="number" id="category-order" name="order_index" value="0" min="0">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Salvează</button>
                <button type="button" id="cancel-category-btn" class="btn btn-secondary">Anulează</button>
            </div>
        </form>
    </div>
</div>

<!-- Formular adăugare/editare subcategorie -->
<div id="subcategory-form-container" style="display: none;" class="form-gradient-container subcategory-container">
    <div class="admin-form">
        <h3 id="subcategory-form-title" class="form-title">Adaugă Subcategorie Nouă</h3>
        
        <form id="subcategory-form">
            <input type="hidden" id="subcategory-id" name="id" value="">
            
            <div class="form-group">
                <label for="subcategory-parent">Categorie Părinte</label>
                <select id="subcategory-parent" name="category_id" required class="form-control">
                    <option value="">-- Selectează categoria părinte --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="subcategory-name">Nume Subcategorie</label>
                <input type="text" id="subcategory-name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="subcategory-slug">Slug (URL) - lăsați gol pentru generare automată</label>
                <input type="text" id="subcategory-slug" name="slug">
            </div>
            
            <div class="form-group">
                <label for="subcategory-description">Descriere</label>
                <textarea id="subcategory-description" name="description" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label for="subcategory-order">Ordine</label>
                <input type="number" id="subcategory-order" name="order_index" value="0" min="0">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Salvează</button>
                <button type="button" id="cancel-subcategory-btn" class="btn btn-secondary">Anulează</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sistem de tab-uri
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Ascunde toate conținuturile de tab
            tabContents.forEach(content => {
                content.classList.remove('active');
            });
            
            // Dezactivează toate butoanele de tab
            tabBtns.forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Activează tab-ul selectat
            document.getElementById(tabId).classList.add('active');
            this.classList.add('active');
            
            // Încarcă subcategoriile când se selectează tab-ul de subcategorii
            if(tabId === 'subcategories-tab') {
                loadSubcategories();
            }
        });
    });
    
    // Încărcarea subcategoriilor
    function loadSubcategories(categoryId = '') {
        const tbody = document.getElementById('subcategories-table-body');
        tbody.innerHTML = '<tr><td colspan="7">Se încarcă subcategoriile...</td></tr>';
        
        let url = '../../api/sp_API__categories.php?action=list_subcategories';
        if (categoryId) {
            url += '&category_id=' + categoryId;
        }
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7">Nu există subcategorii.</td></tr>';
                    } else {
                        let html = '';
                        data.data.forEach((subcategory, index) => {
                            html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${subcategory.name}</td>
                                <td>${subcategory.category_name}</td>
                                <td>${subcategory.slug}</td>
                                <td>${subcategory.description ? (subcategory.description.length > 30 ? subcategory.description.substring(0, 30) + '...' : subcategory.description) : '<em>Fără descriere</em>'}</td>
                                <td>${subcategory.order_index}</td>
                                <td>
                                    <button class="btn btn-sm btn-secondary edit-subcategory-btn" 
                                            data-id="${subcategory.id}"
                                            data-name="${subcategory.name}"
                                            data-slug="${subcategory.slug}"
                                            data-category="${subcategory.category_id}"
                                            data-description="${subcategory.description || ''}"
                                            data-order="${subcategory.order_index}">
                                        Editează
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-subcategory-btn" 
                                            data-id="${subcategory.id}"
                                            data-name="${subcategory.name}">
                                        Șterge
                                    </button>
                                </td>
                            </tr>`;
                        });
                        tbody.innerHTML = html;
                        
                        // Adaugă evenimentele de editare și ștergere
                        addSubcategoryEventListeners();
                    }
                } else {
                    showMessage(data.message, 'error');
                    tbody.innerHTML = '<tr><td colspan="7">Eroare la încărcarea subcategoriilor.</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                tbody.innerHTML = '<tr><td colspan="7">Eroare la încărcarea subcategoriilor.</td></tr>';
            });
    }
    
    // Filtrare subcategorii după categorie
    document.getElementById('filter-category').addEventListener('change', function() {
        loadSubcategories(this.value);
    });
    
    // Funcție pentru afișarea mesajelor
    function showMessage(message, type) {
        const messageElement = document.getElementById('category-message');
        messageElement.textContent = message;
        messageElement.className = 'alert alert-' + type;
        messageElement.style.display = 'block';
        
        // Scroll to message
        messageElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        setTimeout(() => {
            messageElement.style.display = 'none';
        }, 5000);
    }
    
    // Gestionare formulare
    
    // Deschide formularul pentru categorie
    document.getElementById('add-category-btn').addEventListener('click', function() {
        document.getElementById('category-form-title').textContent = 'Adaugă Categorie Nouă';
        document.getElementById('category-form').reset();
        document.getElementById('category-id').value = '';
        document.getElementById('category-form-container').style.display = 'block';
    });
    
    // Deschide formularul pentru subcategorie
    document.getElementById('add-subcategory-btn').addEventListener('click', function() {
        document.getElementById('subcategory-form-title').textContent = 'Adaugă Subcategorie Nouă';
        document.getElementById('subcategory-form').reset();
        document.getElementById('subcategory-id').value = '';
        document.getElementById('subcategory-form-container').style.display = 'block';
    });
    
    // Evenimentele de anulare
    document.getElementById('cancel-category-btn').addEventListener('click', function() {
        document.getElementById('category-form-container').style.display = 'none';
    });
    
    document.getElementById('cancel-subcategory-btn').addEventListener('click', function() {
        document.getElementById('subcategory-form-container').style.display = 'none';
    });
    
    // Procesare formular categorie
    document.getElementById('category-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validare formular
        const categoryName = document.getElementById('category-name').value.trim();
        if (!categoryName) {
            showMessage('Numele categoriei este obligatoriu!', 'error');
            return;
        }
        
        const formData = new FormData(this);
        const categoryId = document.getElementById('category-id').value;
        const isEdit = !!categoryId;
        
        // Confirmarea utilizatorului
        const confirmMessage = isEdit 
            ? `Confirmați modificarea categoriei "${categoryName}"?` 
            : `Confirmați adăugarea categoriei noi "${categoryName}"?`;
            
        if (confirm(confirmMessage)) {
            // Adăugare indicator de încărcare
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = 'Se procesează...';
            submitBtn.disabled = true;
            
            if (isEdit) {
                formData.append('action', 'edit_category');
            } else {
                formData.append('action', 'add_category');
            }
            
            fetch('../../api/sp_API__categories.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    document.getElementById('category-form-container').style.display = 'none';
                    // Reîncarcă pagina pentru a vedea schimbările
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showMessage(data.message, 'error');
                    // Reset button
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                showMessage('Eroare la procesarea cererii: ' + error, 'error');
                // Reset button
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            });
        }
    });
    
    // Procesare formular subcategorie
    document.getElementById('subcategory-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validare formular
        const subcategoryName = document.getElementById('subcategory-name').value.trim();
        const categoryId = document.getElementById('subcategory-parent').value;
        
        if (!subcategoryName) {
            showMessage('Numele subcategoriei este obligatoriu!', 'error');
            return;
        }
        
        if (!categoryId) {
            showMessage('Selectarea categoriei părinte este obligatorie!', 'error');
            return;
        }
        
        const formData = new FormData(this);
        const subcategoryId = document.getElementById('subcategory-id').value;
        const isEdit = !!subcategoryId;
        
        // Confirmarea utilizatorului
        const confirmMessage = isEdit 
            ? `Confirmați modificarea subcategoriei "${subcategoryName}"?` 
            : `Confirmați adăugarea subcategoriei noi "${subcategoryName}"?`;
            
        if (confirm(confirmMessage)) {
            // Adăugare indicator de încărcare
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = 'Se procesează...';
            submitBtn.disabled = true;
            
            if (isEdit) {
                formData.append('action', 'edit_subcategory');
            } else {
                formData.append('action', 'add_subcategory');
            }
            
            fetch('../../api/sp_API__categories.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    document.getElementById('subcategory-form-container').style.display = 'none';
                    // Reîncarcă subcategoriile
                    loadSubcategories(document.getElementById('filter-category').value);
                    
                    // Reset form
                    document.getElementById('subcategory-form').reset();
                } else {
                    showMessage(data.message, 'error');
                    // Reset button
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                showMessage('Eroare la procesarea cererii: ' + error, 'error');
                // Reset button
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            });
        }
    });
    
    // Adaugă evenimentele pentru butoanele de editare și ștergere categorie
    document.querySelectorAll('.edit-category-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const slug = this.dataset.slug;
            const description = this.dataset.description;
            const order = this.dataset.order;
            
            document.getElementById('category-form-title').textContent = 'Editează Categoria';
            document.getElementById('category-id').value = id;
            document.getElementById('category-name').value = name;
            document.getElementById('category-slug').value = slug;
            document.getElementById('category-description').value = description;
            document.getElementById('category-order').value = order;
            
            document.getElementById('category-form-container').style.display = 'block';
        });
    });
    
    document.querySelectorAll('.delete-category-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            
            if (confirm(`Sigur doriți să ștergeți categoria "${name}"? \n\nATENȚIE: Toate subcategoriile asociate vor fi șterse și toate galeriile asociate vor fi modificate!`)) {
                // Disable button and show processing state
                this.disabled = true;
                this.textContent = 'Se șterge...';
                
                const formData = new FormData();
                formData.append('action', 'delete_category');
                formData.append('id', id);
                
                fetch('../../api/sp_API__categories.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        // Reîncarcă pagina pentru a vedea schimbările
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showMessage(data.message, 'error');
                        // Reset button
                        this.disabled = false;
                        this.textContent = 'Șterge';
                    }
                })
                .catch(error => {
                    showMessage('Eroare la procesarea cererii: ' + error, 'error');
                    // Reset button
                    this.disabled = false;
                    this.textContent = 'Șterge';
                });
            }
        });
    });
    
    // Funcție pentru adăugarea evenimentelor pentru subcategorii
    function addSubcategoryEventListeners() {
        document.querySelectorAll('.edit-subcategory-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const slug = this.dataset.slug;
                const category = this.dataset.category;
                const description = this.dataset.description;
                const order = this.dataset.order;
                
                document.getElementById('subcategory-form-title').textContent = 'Editează Subcategoria';
                document.getElementById('subcategory-id').value = id;
                document.getElementById('subcategory-name').value = name;
                document.getElementById('subcategory-slug').value = slug;
                document.getElementById('subcategory-parent').value = category;
                document.getElementById('subcategory-description').value = description;
                document.getElementById('subcategory-order').value = order;
                
                document.getElementById('subcategory-form-container').style.display = 'block';
            });
        });
        
        document.querySelectorAll('.delete-subcategory-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                
                if (confirm(`Sigur doriți să ștergeți subcategoria "${name}"? \n\nATENȚIE: Toate galeriile asociate cu această subcategorie vor fi modificate!`)) {
                    // Disable button and show processing state
                    this.disabled = true;
                    this.textContent = 'Se șterge...';
                    
                    const formData = new FormData();
                    formData.append('action', 'delete_subcategory');
                    formData.append('id', id);
                    
                    fetch('../../api/sp_API__categories.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage(data.message, 'success');
                            // Reîncarcă subcategoriile
                            loadSubcategories(document.getElementById('filter-category').value);
                        } else {
                            showMessage(data.message, 'error');
                            // Reset button
                            this.disabled = false;
                            this.textContent = 'Șterge';
                        }
                    })
                    .catch(error => {
                        showMessage('Eroare la procesarea cererii: ' + error, 'error');
                        // Reset button
                        this.disabled = false;
                        this.textContent = 'Șterge';
                    });
                }
            });
        });
    }
    
    // Add auto slug generation for categories
    document.getElementById('category-name').addEventListener('blur', function() {
        const slugField = document.getElementById('category-slug');
        if (slugField.value === '') {
            slugField.value = generateSlug(this.value);
        }
    });
    
    // Add auto slug generation for subcategories
    document.getElementById('subcategory-name').addEventListener('blur', function() {
        const slugField = document.getElementById('subcategory-slug');
        if (slugField.value === '') {
            slugField.value = generateSlug(this.value);
        }
    });
    
    // Helper function to generate slug
    function generateSlug(text) {
        return text.toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
    }
    
    // Încărcare inițială subcategorii pentru tab-ul de subcategorii
    if (document.querySelector('.tab-btn[data-tab="subcategories-tab"]').classList.contains('active')) {
        loadSubcategories();
    }
});
</script>

<style>
/* Stilizare pentru tab-uri */
.admin-tabs {
    display: flex;
    margin-bottom: 20px;
    border-bottom: 1px solid var(--color-border);
}

.tab-btn {
    background: transparent;
    border: none;
    padding: 12px 20px;
    color: var(--color-secondary);
    cursor: pointer;
    font-weight: 500;
    border-bottom: 3px solid transparent;
    font-family: 'Montserrat', sans-serif;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.tab-btn:hover {
    color: var(--color-accent);
    background-color: rgba(255,255,255,0.05);
}

.tab-btn.active {
    color: var(--color-accent);
    border-bottom: 3px solid var(--color-accent);
    font-weight: 700;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

.filter-container {
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-container label {
    margin-bottom: 0;
    font-weight: 500;
    min-width: 160px;
}

.filter-container select {
    max-width: 300px;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Additional styles for better UX */
.alert {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
    animation: fadeIn 0.3s ease;
    font-weight: 500;
    position: relative;
}

.alert-success {
    background-color: rgba(25, 135, 84, 0.15);
    color: #198754;
    border-left: 4px solid #198754;
}

.alert-error {
    background-color: rgba(220, 53, 69, 0.15);
    color: #dc3545;
    border-left: 4px solid #dc3545;
}

.btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Improve form styling */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border-radius: 4px;
    border: 1px solid var(--color-border);
    background-color: var(--color-bg-alt);
    color: var(--color-text);
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: var(--color-accent);
    outline: none;
    box-shadow: 0 0 0 2px rgba(var(--color-accent-rgb), 0.25);
}
</style>