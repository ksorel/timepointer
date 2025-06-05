// Import des modules (si vous utilisez des modules ES)
import { TimeManager } from './timeManager.js';
import { UIManager } from './uiManager.js';
import { StorageManager } from './storageManager.js';

class App {
  constructor() {
    this.timeManager = new TimeManager();
    this.uiManager = new UIManager();
    this.storageManager = new StorageManager();
    
    this.init();
  }
  
  init() {
    document.addEventListener('DOMContentLoaded', () => {
      this.registerServiceWorker();
      this.setupEventListeners();
      this.loadData();
    });
  }
  
  registerServiceWorker() {
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/service-worker.js')
        .then(registration => {
          console.log('SW registered:', registration);
        })
        .catch(error => {
          console.log('SW registration failed:', error);
        });
    }
  }
  
  // ... autres méthodes
}

new App();