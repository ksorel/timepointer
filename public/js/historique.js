// Initialisation de la page historique
function initHistoriquePage() {
    const historiquePage = document.getElementById('historique-page');
    if (!historiquePage) return;
    
    historiquePage.innerHTML = App.templates.historique;
    setupHistoriqueEvents();
    loadHistorique();
}

// Configurer les événements de l'historique
function setupHistoriqueEvents() {
    // Appliquer les filtres
    document.getElementById('apply-filter')?.addEventListener('click', () => {
        loadHistorique();
    });
    
    // Définir les dates par défaut (ce mois)
    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    
    document.getElementById('start-date').valueAsDate = firstDay;
    document.getElementById('end-date').valueAsDate = lastDay;
}

// Charger l'historique
function loadHistorique() {
    const token = localStorage.getItem('authToken');
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    
    const historiqueList = document.getElementById('historique-list');
    if (!historiqueList) return;
    
    historiqueList.innerHTML = `
        <div class="loading-indicator">
            <div class="spinner"></div>
            <p>Chargement de l'historique...</p>
        </div>
    `;
    
    fetch(`/api/pointage/history?start_date=${startDate}&end_date=${endDate}`, {
        headers: { 'Authorization': `Bearer ${token}` }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            displayHistorique(data.data);
        } else {
            throw new Error(data.message || 'Erreur lors du chargement de l\'historique');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        historiqueList.innerHTML = `
            <div class="error-message">
                <p>${error.message || 'Erreur lors du chargement de l\'historique'}</p>
                <button id="retry-loading" class="btn-secondary">Réessayer</button>
            </div>
        `;
        
        document.getElementById('retry-loading')?.addEventListener('click', loadHistorique);
    });
}

// Afficher l'historique
function displayHistorique(pointages) {
    const historiqueList = document.getElementById('historique-list');
    if (!historiqueList) return;
    
    if (pointages.length === 0) {
        historiqueList.innerHTML = '<p class="no-data">Aucun pointage trouvé pour cette période</p>';
        return;
    }
    
    let html = '';
    
    pointages.forEach(pointage => {
        const date = new Date(pointage.date_pointage);
        const dateStr = date.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short' });
        
        html += `
            <div class="historique-item">
                <div class="historique-date">${dateStr}</div>
                <div class="historique-times">
                    ${pointage.heure_arrivee_matin ? `<span class="time-entry matin">Matin: ${pointage.heure_arrivee_matin} - ${pointage.heure_depart_matin || '--:--'}</span>` : ''}
                    ${pointage.heure_arrivee_aprem ? `<span class="time-entry aprem">Après-midi: ${pointage.heure_arrivee_aprem} - ${pointage.heure_depart_aprem || '--:--'}</span>` : ''}
                </div>
            </div>
        `;
    });
    
    historiqueList.innerHTML = html;
}

// Exposer les fonctions nécessaires
window.initHistoriquePage = initHistoriquePage;
window.loadHistorique = loadHistorique;