<?php
// filepath: f:\SITE SPECTRO STUDIO\backend\modules\settings\maintenance.php
// Include configuration
if (!defined('IS_ADMIN_DASHBOARD')) {
    define('IS_ADMIN_DASHBOARD', true);
}

// Încărcăm statusul modului de mentenanță din fișier
$config_file = __DIR__ . '/../../../config/maintenance.json';
if (file_exists($config_file)) {
    $maintenance_config = json_decode(file_get_contents($config_file), true);
    $maintenance_mode = isset($maintenance_config['enabled']) ? $maintenance_config['enabled'] : false;
    $maintenance_message = isset($maintenance_config['message']) ? $maintenance_config['message'] : '';
    $maintenance_end_time = isset($maintenance_config['end_time']) ? $maintenance_config['end_time'] : '';
} else {
    $maintenance_mode = false;
    $maintenance_message = 'Site-ul este în mentenanță. Reveniți în curând!';
    $maintenance_end_time = date('Y-m-d\TH:i', strtotime('+24 hours'));
}
?>

<style>
    /* Fix for consistent input/textarea padding */
    #maintenance-form input,
    #maintenance-form textarea {
        padding: 12px 15px;
        width: 100%;
        box-sizing: border-box;
        background-color: var(--color-primary);
        border: 1px solid var(--color-border);
        border-radius: var(--border-radius);
        color: var(--color-secondary);
        font-family: 'Montserrat', sans-serif;
        transition: all var(--transition-fast);
    }
    
    #maintenance-form input:focus,
    #maintenance-form textarea:focus {
        outline: none;
        border-color: var(--color-accent);
        box-shadow: 0 0 0 3px rgba(224, 168, 13, 0.2);
    }
</style>

<div class="settings-section" id="maintenance-settings">
    <h2>Setări Mentenanță</h2>
    
    <div class="current-status <?php echo $maintenance_mode ? 'status-active' : 'status-inactive'; ?>">
        Status curent: <strong><?php echo $maintenance_mode ? 'ACTIVAT' : 'DEZACTIVAT'; ?></strong>
    </div>
    
    <button id="toggle-maintenance-btn" class="btn <?php echo $maintenance_mode ? 'btn-secondary' : 'btn-primary'; ?>">
        <?php echo $maintenance_mode ? 'Dezactivează mentenanța' : 'Activează mentenanța'; ?>
    </button>
    
    <form id="maintenance-form" class="admin-form">
        <div class="form-group">
            <label for="maintenance-message">Mesaj mentenanță:</label>
            <textarea id="maintenance-message" name="message" rows="4"><?php echo htmlspecialchars($maintenance_message); ?></textarea>
            <small class="form-text">Acest mesaj va fi afișat pe pagina de mentenanță.</small>
        </div>
        
        <div class="form-group">
            <label for="maintenance-end-time">Data estimată de finalizare:</label>
            <input type="datetime-local" id="maintenance-end-time" name="end_time" value="<?php echo htmlspecialchars($maintenance_end_time); ?>">
            <small class="form-text">Această dată va fi afișată pe pagina de mentenanță.</small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Salvează setările</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle maintenance button
    document.getElementById('toggle-maintenance-btn').addEventListener('click', function() {
        if (confirm('Sigur doriți să ' + (<?php echo json_encode($maintenance_mode); ?> ? 'dezactivați' : 'activați') + ' modul de mentenanță?')) {
            // Show loading state
            this.disabled = true;
            this.textContent = 'Se procesează...';
            
            // Send request to toggle maintenance mode
            fetch('/backend/api/sp_API__toggle__maintenance.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Eroare: ' + data.message);
                    this.disabled = false;
                    this.textContent = <?php echo json_encode($maintenance_mode ? 'Dezactivează mentenanța' : 'Activează mentenanța'); ?>;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Eroare la procesarea cererii!');
                this.disabled = false;
                this.textContent = <?php echo json_encode($maintenance_mode ? 'Dezactivează mentenanța' : 'Activează mentenanța'); ?>;
            });
        }
    });
    
    // Maintenance settings form
    document.getElementById('maintenance-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(this);
        
        // Send request to save maintenance settings
        fetch('/backend/api/save_maintenance.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Setările de mentenanță au fost salvate cu succes!');
            } else {
                alert('Eroare: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Eroare la procesarea cererii!');
        });
    });
});
</script>