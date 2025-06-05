/**
 * TimePointer - Script principal unifié
 * Intègre :
 * - app.js (initialisation DOM)
 * - historique.js (gestion des tours)
 * - pointage.js (chronométrage)
 * - settings.js (thème et préférences)
 */

document.addEventListener('DOMContentLoaded', () => {
    // ==================== ÉLÉMENTS DOM ====================
    const timerDisplay = document.getElementById('timer');
    const startBtn = document.getElementById('startBtn');
    const pauseBtn = document.getElementById('pauseBtn');
    const resetBtn = document.getElementById('resetBtn');
    const lapBtn = document.getElementById('lapBtn');
    const lapsContainer = document.getElementById('laps');
    const themeCheckbox = document.getElementById('theme-checkbox');
    const themeText = document.getElementById('theme-text');

    // ==================== ÉTAT GLOBAL ====================
    let state = {
        startTime: 0,
        elapsedTime: 0,
        timerInterval: null,
        isRunning: false,
        lapCount: 1,
        theme: localStorage.getItem('theme') || 'light'
    };

    // ==================== FONCTIONS DU CHRONO ====================
    const formatTime = (time) => {
        const date = new Date(time);
        return [
            date.getUTCHours().toString().padStart(2, '0'),
            date.getUTCMinutes().toString().padStart(2, '0'),
            date.getUTCSeconds().toString().padStart(2, '0'),
            Math.floor(date.getUTCMilliseconds() / 10).toString().padStart(2, '0')
        ].join(':').replace(/:([^:]*)$/, '.$1');
    };

    const updateTimer = () => {
        const currentTime = Date.now();
        state.elapsedTime = currentTime - state.startTime;
        timerDisplay.textContent = formatTime(state.elapsedTime);
    };

    const startTimer = () => {
        if (state.isRunning) return;
        
        state.isRunning = true;
        state.startTime = Date.now() - state.elapsedTime;
        state.timerInterval = setInterval(updateTimer, 10);
    };

    const pauseTimer = () => {
        if (!state.isRunning) return;
        
        state.isRunning = false;
        clearInterval(state.timerInterval);
    };

    const resetTimer = () => {
        pauseTimer();
        state.elapsedTime = 0;
        state.lapCount = 1;
        timerDisplay.textContent = '00:00:00.00';
        lapsContainer.innerHTML = '';
    };

    // ==================== GESTION DES TOURS ====================
    const recordLap = () => {
        if (!state.isRunning) return;
        
        const lapTime = document.createElement('div');
        lapTime.className = 'lap-time';
        lapTime.textContent = `Tour ${state.lapCount++}: ${formatTime(state.elapsedTime)}`;
        lapsContainer.prepend(lapTime);

        // Sauvegarde optionnelle via API
        // saveLapToAPI(state.elapsedTime);
    };

    // ==================== GESTION DU THÈME ====================
    const applyTheme = () => {
        document.documentElement.setAttribute('data-theme', state.theme);
        themeCheckbox.checked = state.theme === 'dark';
        themeText.textContent = state.theme === 'dark' ? 'Mode clair' : 'Mode sombre';
    };

    const toggleTheme = () => {
        state.theme = state.theme === 'dark' ? 'light' : 'dark';
        localStorage.setItem('theme', state.theme);
        applyTheme();
    };

    // ==================== ÉVÉNEMENTS ====================
    startBtn.addEventListener('click', startTimer);
    pauseBtn.addEventListener('click', pauseTimer);
    resetBtn.addEventListener('click', resetTimer);
    lapBtn.addEventListener('click', recordLap);
    themeCheckbox.addEventListener('change', toggleTheme);

    // ==================== INITIALISATION ====================
    const init = () => {
        // Restauration du thème
        applyTheme();

        // Service Worker (PWA)
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/service-worker.js')
                .then(reg => console.log('SW enregistré:', reg.scope))
                .catch(err => console.log('Échec SW:', err));
        }

        // Exemple: Charger les tours depuis l'API au démarrage
        // fetchLapsFromAPI().then(laps => { ... });
    };

    init();
});

// ==================== FONCTIONS API (EXEMPLE) ====================
async function saveLapToAPI(time) {
    try {
        const response = await fetch('/api/laps', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                time: time,
                formatted: formatTime(time),
                date: new Date().toISOString()
            })
        });
        return await response.json();
    } catch (error) {
        console.error("Erreur API:", error);
    }
}

async function fetchLapsFromAPI() {
    try {
        const response = await fetch('/api/laps');
        return await response.json();
    } catch (error) {
        console.error("Erreur API:", error);
        return [];
    }
}