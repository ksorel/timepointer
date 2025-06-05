// Initialisation de la page profil
function initProfilPage() {
    const profilPage = document.getElementById('profil-page');
    if (!profilPage) return;
    
    profilPage.innerHTML = App.templates.profil;
    loadProfil();
    setupProfilEvents();
}

// Charger le profil
function loadProfil() {
    const token = localStorage.getItem('authToken');
    const userId = localStorage.getItem('userId');
    
    fetch(`/api/employees/${userId}`, {
        headers: { 'Authorization': `Bearer ${token}` }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            displayProfil(data.data);
        } else {
            throw new Error(data.message || 'Erreur lors du chargement du profil');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        App.showNotification(error.message, 'error');
    });
}

// Afficher le profil
function displayProfil(employee) {
    document.getElementById('profil-name').textContent = `${employee.prenom} ${employee.nom}`;
    document.getElementById('profil-matricule').textContent = `Matricule: ${employee.matricule}`;
    document.getElementById('profil-email').textContent = employee.email || 'Non renseigné';
    
    if (employee.date_embauche) {
        const embaucheDate = new Date(employee.date_embauche);
        document.getElementById('profil-embauche').textContent = embaucheDate.toLocaleDateString('fr-FR');
    }
}

// Configurer les événements du profil
function setupProfilEvents() {
    // Changement de mot de passe
    document.getElementById('change-password-btn')?.addEventListener('click', () => {
        document.getElementById('password-modal').classList.remove('hidden');
    });
    
    document.getElementById('cancel-password-change')?.addEventListener('click', () => {
        document.getElementById('password-modal').classList.add('hidden');
    });
    
    document.getElementById('change-password-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        changePassword();
    });
    
    // Modification du profil (à implémenter)
    document.getElementById('edit-profil-btn')?.addEventListener('click', () => {
        App.showNotification('Fonctionnalité à venir', 'info');
    });
}

// Changer le mot de passe
function changePassword() {
    const token = localStorage.getItem('authToken');
    const userId = localStorage.getItem('userId');
    
    const currentPassword = document.getElementById('current-password').value;
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    if (newPassword !== confirmPassword) {
        App.showNotification('Les mots de passe ne correspondent pas', 'error');
        return;
    }
    
    const submitBtn = document.querySelector('#change-password-form button[type="submit"]');
    App.toggleLoading(submitBtn, true);
    
    fetch(`/api/employees/${userId}/change-password`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
            current_password: currentPassword,
            new_password: newPassword
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            App.showNotification('Mot de passe changé avec succès');
            document.getElementById('password-modal').classList.add('hidden');
            this.reset();
        } else {
            throw new Error(data.message || 'Erreur lors du changement de mot de passe');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        App.showNotification(error.message, 'error');
    })
    .finally(() => {
        App.toggleLoading(submitBtn, false);
    });
}

// Exposer les fonctions nécessaires
window.initProfilPage = initProfilPage;
window.loadProfil = loadProfil;