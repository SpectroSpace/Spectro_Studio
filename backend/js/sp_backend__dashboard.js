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
        document.querySelector(`.tab-link[data-tab="${tabId}"]`).classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }
    
    // Maintenance Mode Toggle
    const maintenanceToggle = document.getElementById('toggle-maintenance');
    if (maintenanceToggle) {
        maintenanceToggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Send request to toggle maintenance mode
            fetch('/backend/api/sp_API__toggle__maintenance.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Eroare la schimbarea modului de mentenanță: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Eroare:', error);
                    alert('Eroare la procesarea cererii!');
                });
        });
    }
    
    // Handle Settings Form
    const settingsForm = document.getElementById('settings-form');
    if (settingsForm) {
        settingsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            
            // Send request to save settings
            fetch('/backend/api/save_settings.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Setările au fost salvate cu succes!');
                    } else {
                        alert('Eroare la salvarea setărilor: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Eroare:', error);
                    alert('Eroare la procesarea cererii!');
                });
        });
    }

    // Handle sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebarToggle');
    const dashboardContainer = document.querySelector('.dashboard-container');
    
    // Check if there's a saved preference
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    
    // Apply initial state based on saved preference
    if (sidebarCollapsed) {
        dashboardContainer.classList.add('sidebar-collapsed');
    }
    
    // Add toggle functionality
    sidebarToggle.addEventListener('click', function() {
        dashboardContainer.classList.toggle('sidebar-collapsed');
        
        // Save preference to localStorage
        const isCollapsed = dashboardContainer.classList.contains('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    });
});