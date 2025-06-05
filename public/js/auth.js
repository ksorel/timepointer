// Gestion de l'authentification
document.getElementById('login-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const matricule = document.getElementById('matricule').value;
    const password = document.getElementById('password').value;
    const submitBtn = this.querySelector('button[type="submit"]');
    
    App.toggleLoading(submitBtn, true);
    
    fetch('/api/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ matricule, password })
    })
    .then(async response => {
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Identifiants incorrects');
        }
        
        if (data.status === 'success') {
            // Stocker les informations d'authentification
            localStorage.setItem('authToken', data.data.token);
            localStorage.setItem('userId', data.data.id);
            localStorage.setItem('userName', `${data.data.prenom} ${data.data.nom}`);
            localStorage.setItem('userInitials', data.data.prenom.charAt(0) + data.data.nom.charAt(0));
            
            // Charger l'application principale
            App.loadMainApp();
        }
    })
    .catch(error => {
        App.showNotification(error.message, 'error');
    })
    .finally(() => {
        App.toggleLoading(submitBtn, false);
    });
});

// Basculer la visibilité du mot de passe
document.querySelector('.toggle-password')?.addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
});

// Mot de passe oublié
document.getElementById('forgot-password')?.addEventListener('click', function(e) {
    e.preventDefault();
    App.showNotification('Veuillez contacter votre administrateur pour réinitialiser votre mot de passe.', 'info');
});

// Déconnexion
function logout() {
    // Supprimer les données locales
    localStorage.removeItem('authToken');
    localStorage.removeItem('userId');
    localStorage.removeItem('userName');
    localStorage.removeItem('userInitials');
    
    // Recharger la page
    window.location.reload();
}

// Exposer la fonction de déconnexion
window.logout = logout;