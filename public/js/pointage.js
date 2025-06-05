// Initialisation de la page de pointage
function initPointagePage() {
    const pointagePage = document.getElementById('pointage-page');
    if (!pointagePage) return;
    
    pointagePage.innerHTML = App.templates.pointage;
    
    // Mettre à jour la date actuelle
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('current-date').textContent = now.toLocaleDateString('fr-FR', options);
    
    // Charger les données
    loadHoraireDuJour();
    loadPointageDuJour();
    setupPointageEvents();
}

// Charger l'horaire du jour
function loadHoraireDuJour() {
    const token = localStorage.getItem('authToken');
    
    fetch('/api/settings/horaire-du-jour', {
        headers: { 'Authorization': `Bearer ${token}` }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            updateHoraireDisplay(data.data);
        }
    })
    .catch(error => {
        console.error('Erreur lors du chargement des horaires:', error);
    });
}

// Mettre à jour l'affichage des horaires
function updateHoraireDisplay(horaire) {
    const scheduleElement = document.getElementById('current-schedule');
    if (!scheduleElement || !horaire) return;
    
    let scheduleText = '';
    
    if (horaire.heure_debut_matin && horaire.heure_fin_matin) {
        scheduleText = `Matin: ${horaire.heure_debut_matin} - ${horaire.heure_fin_matin}`;
    }
    
    if (horaire.heure_debut_aprem && horaire.heure_fin_aprem) {
        scheduleText += scheduleText ? ` | Après-midi: ${horaire.heure_debut_aprem} - ${horaire.heure_fin_aprem}` : 
                                      `Après-midi: ${horaire.heure_debut_aprem} - ${horaire.heure_fin_aprem}`;
    }
    
    scheduleElement.textContent = scheduleText || 'Pas d\'horaire défini pour aujourd\'hui';
}

// Charger le pointage du jour
function loadPointageDuJour() {
    const token = localStorage.getItem('authToken');
    
    fetch('/api/pointage/today', {
        headers: { 'Authorization': `Bearer ${token}` }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            updatePointageDisplay(data.data);
        }
    })
    .catch(error => {
        console.error('Erreur lors du chargement du pointage:', error);
    });
}

// Mettre à jour l'affichage du pointage
function updatePointageDisplay(pointage) {
    if (!pointage) return;
    
    const updateField = (id, value) => {
        if (value && document.getElementById(id)) {
            document.getElementById(id).textContent = value;
        }
    };
    
    updateField('heure-arrivee-matin', pointage.heure_arrivee_matin);
    updateField('heure-depart-matin', pointage.heure_depart_matin);
    updateField('heure-arrivee-aprem', pointage.heure_arrivee_aprem);
    updateField('heure-depart-aprem', pointage.heure_depart_aprem);
    
    if (pointage.commentaire && document.getElementById('pointage-comment')) {
        document.getElementById('pointage-comment').value = pointage.commentaire;
    }
}

// Configurer les événements de pointage
function setupPointageEvents() {
    const setupPointageButton = (buttonId, field) => {
        const button = document.getElementById(buttonId);
        if (!button) return;
        
        button.addEventListener('click', () => {
            const now = new Date();
            const timeString = now.toTimeString().substring(0, 5);
            
            // Mettre à jour l'affichage immédiatement
            document.getElementById(`heure-${field}`).textContent = timeString;
            
            // Enregistrer le pointage
            const data = {};
            data[field] = timeString;
            savePointage(data);
        });
    };
    
    // Configurer les boutons de pointage
    setupPointageButton('arrivee-matin-btn', 'arrivee_matin');
    setupPointageButton('depart-matin-btn', 'depart_matin');
    setupPointageButton('arrivee-aprem-btn', 'arrivee_aprem');
    setupPointageButton('depart-aprem-btn', 'depart_aprem');
    
    // Configurer le bouton de commentaire
    const saveCommentBtn = document.getElementById('save-comment-btn');
    if (saveCommentBtn) {
        saveCommentBtn.addEventListener('click', () => {
            const comment = document.getElementById('pointage-comment').value;
            savePointage({ commentaire: comment });
        });
    }
}

// Enregistrer le pointage
function savePointage(data) {
    const token = localStorage.getItem('authToken');
    
    fetch('/api/pointage', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status !== 'success') {
            throw new Error(data.message || 'Erreur lors de l\'enregistrement');
        }
        App.showNotification('Pointage enregistré avec succès');
    })
    .catch(error => {
        console.error('Erreur:', error);
        App.showNotification(error.message || 'Erreur de connexion', 'error');
        
        // TODO: Implémenter une sauvegarde locale pour synchronisation ultérieure
        if (!navigator.onLine) {
            savePointageOffline(data);
        }
    });
}

// Sauvegarde hors ligne (à implémenter)
function savePointageOffline(data) {
    // À compléter avec une logique de sauvegarde locale
    console.log('Sauvegarde hors ligne:', data);
}

// Exposer les fonctions nécessaires
window.initPointagePage = initPointagePage;