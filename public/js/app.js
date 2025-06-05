// Initialisation de l'application
document.addEventListener('DOMContentLoaded', () => {
    // Vérifier si l'utilisateur est déjà connecté
    if (localStorage.getItem('authToken')) {
        loadMainApp();
    } else {
        // Initialiser le service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/service-worker.js')
                .then(registration => {
                    console.log('ServiceWorker enregistré avec succès');
                })
                .catch(err => {
                    console.log('Erreur ServiceWorker :', err);
                });
        }
    }
});

// Charger l'application principale
function loadMainApp() {
    const appContainer = document.getElementById('app-container');
    
    // Structure de base de l'application
    appContainer.innerHTML = `
        <div class="main-app">
            <header class="app-header">
                <div class="header-content">
                    <h1>K-OIL Pointage</h1>
                    <button id="logout-btn" class="btn-icon" title="Déconnexion">
                        <span class="icon">🚪</span>
                    </button>
                </div>
                <nav class="main-nav">
                    <button class="nav-btn active" data-page="pointage">Pointage</button>
                    <button class="nav-btn" data-page="historique">Historique</button>
                    <button class="nav-btn" data-page="profil">Profil</button>
                </nav>
            </header>
            
            <main class="app-main">
                <div id="pointage-page" class="page active"></div>
                <div id="historique-page" class="page"></div>
                <div id="profil-page" class="page"></div>
            </main>
        </div>
    `;
    
    // Initialiser les composants
    initNavigation();
    initPointagePage();
    initHistoriquePage();
    initProfilPage();
    
    // Gestion de la déconnexion
    document.getElementById('logout-btn').addEventListener('click', logout);
}

// Navigation principale
function initNavigation() {
    const navButtons = document.querySelectorAll('.nav-btn');
    
    navButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Mettre à jour l'onglet actif
            navButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            // Afficher la page correspondante
            const pageId = `${button.dataset.page}-page`;
            document.querySelectorAll('.app-main .page').forEach(page => {
                page.classList.remove('active');
            });
            document.getElementById(pageId).classList.add('active');
            
            // Recharger les données si nécessaire
            if (pageId === 'historique-page') {
                loadHistorique();
            } else if (pageId === 'profil-page') {
                loadProfil();
            }
        });
    });
}

// Templates pour les différentes pages
const templates = {
    pointage: `
        <div class="pointage-container">
            <div class="date-display">
                <h2 id="current-date"></h2>
                <p id="current-schedule"></p>
            </div>
            
            <div class="pointage-card matin">
                <h3>Matin</h3>
                <div class="pointage-times">
                    <div class="time-display">
                        <span class="time-label">Arrivée:</span>
                        <span id="heure-arrivee-matin" class="time-value">--:--</span>
                    </div>
                    <div class="time-display">
                        <span class="time-label">Départ:</span>
                        <span id="heure-depart-matin" class="time-value">--:--</span>
                    </div>
                </div>
                
                <div class="pointage-actions">
                    <button id="arrivee-matin-btn" class="pointage-btn">
                        <span class="icon">⏱️</span> Arrivée
                    </button>
                    <button id="depart-matin-btn" class="pointage-btn">
                        <span class="icon">🚪</span> Départ
                    </button>
                </div>
            </div>
            
            <div class="pointage-card aprem">
                <h3>Après-midi</h3>
                <div class="pointage-times">
                    <div class="time-display">
                        <span class="time-label">Arrivée:</span>
                        <span id="heure-arrivee-aprem" class="time-value">--:--</span>
                    </div>
                    <div class="time-display">
                        <span class="time-label">Départ:</span>
                        <span id="heure-depart-aprem" class="time-value">--:--</span>
                    </div>
                </div>
                
                <div class="pointage-actions">
                    <button id="arrivee-aprem-btn" class="pointage-btn">
                        <span class="icon">⏱️</span> Arrivée
                    </button>
                    <button id="depart-aprem-btn" class="pointage-btn">
                        <span class="icon">🚪</span> Départ
                    </button>
                </div>
            </div>
            
            <div class="comment-section">
                <label for="pointage-comment">Commentaire (optionnel):</label>
                <textarea id="pointage-comment" rows="2"></textarea>
                <button id="save-comment-btn" class="btn-secondary">Enregistrer</button>
            </div>
        </div>
    `,
    
    historique: `
        <div class="historique-container">
            <div class="filter-controls">
                <div class="date-filter">
                    <label for="start-date">De:</label>
                    <input type="date" id="start-date">
                    
                    <label for="end-date">À:</label>
                    <input type="date" id="end-date">
                    
                    <button id="apply-filter" class="btn-secondary">Appliquer</button>
                </div>
            </div>
            
            <div class="historique-list" id="historique-list">
                <div class="loading-indicator">
                    <div class="spinner"></div>
                    <p>Chargement de l'historique...</p>
                </div>
            </div>
        </div>
    `,
    
    profil: `
        <div class="profil-container">
            <div class="profil-header">
                <div class="avatar">${localStorage.getItem('userInitials')}</div>
                <h2 id="profil-name"></h2>
                <p id="profil-matricule"></p>
            </div>
            
            <div class="profil-details">
                <div class="detail-item">
                    <span class="detail-label">Email:</span>
                    <span id="profil-email" class="detail-value"></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Date d'embauche:</span>
                    <span id="profil-embauche" class="detail-value"></span>
                </div>
            </div>
            
            <div class="profil-actions">
                <button id="change-password-btn" class="btn-secondary">Changer le mot de passe</button>
                <button id="edit-profil-btn" class="btn-secondary">Modifier le profil</button>
            </div>
        </div>
        
        <!-- Modals -->
        <div id="password-modal" class="modal hidden">
            <div class="modal-content">
                <h3>Changer le mot de passe</h3>
                <form id="change-password-form">
                    <div class="form-group">
                        <label for="current-password">Mot de passe actuel</label>
                        <input type="password" id="current-password" required>
                    </div>
                    <div class="form-group">
                        <label for="new-password">Nouveau mot de passe</label>
                        <input type="password" id="new-password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm-password">Confirmer le nouveau mot de passe</label>
                        <input type="password" id="confirm-password" required>
                    </div>
                    <div class="form-actions">
                        <button type="button" id="cancel-password-change" class="btn-secondary">Annuler</button>
                        <button type="submit" class="btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    `
};

// Fonctions utilitaires
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

function toggleLoading(button, isLoading) {
    const spinner = button.querySelector('.spinner');
    const text = button.querySelector('.btn-text');
    
    if (isLoading) {
        button.disabled = true;
        spinner.classList.remove('hidden');
        text.classList.add('hidden');
    } else {
        button.disabled = false;
        spinner.classList.add('hidden');
        text.classList.remove('hidden');
    }
}

// Export des fonctions pour les autres fichiers
window.App = {
    loadMainApp,
    showNotification,
    toggleLoading,
    templates
};