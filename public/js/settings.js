// Initialisation des paramètres
function initSettings() {
    // Charger les horaires au démarrage
    loadHoraires();
}

// Charger tous les horaires
function loadHoraires() {
    const token = localStorage.getItem('authToken');
    
    fetch('/api/settings/horaires', {
        headers: { 'Authorization': `Bearer ${token}` }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            localStorage.setItem('horaires', JSON.stringify(data.data));
        }
    })
    .catch(error => {
        console.error('Erreur lors du chargement des horaires:', error);
    });
}

// Obtenir l'horaire pour un jour spécifique
function getHoraireForDay(dayOfWeek) {
    const horaires = JSON.parse(localStorage.getItem('horaires') || '[]');
    return horaires.find(h => h.jour_semaine == dayOfWeek);
}

// Mettre à jour les horaires (pour admin)
function updateHoraires(newHoraires) {
    const token = localStorage.getItem('authToken');
    const submitBtn = document.getElementById('save-horaires-btn');
    
    if (submitBtn) App.toggleLoading(submitBtn, true);
    
    fetch('/api/settings/horaires', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify(newHoraires)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            App.showNotification('Horaires mis à jour avec succès');
            localStorage.setItem('horaires', JSON.stringify(newHoraires));
        } else {
            throw new Error(data.message || 'Erreur lors de la mise à jour');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        App.showNotification(error.message, 'error');
    })
    .finally(() => {
        if (submitBtn) App.toggleLoading(submitBtn, false);
    });
}

// Initialiser les paramètres au chargement
initSettings();

// Exposer les fonctions nécessaires
window.getHoraireForDay = getHoraireForDay;
window.updateHoraires = updateHoraires;