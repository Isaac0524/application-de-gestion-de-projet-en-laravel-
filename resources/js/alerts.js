// Système d'alertes avec notifications toast et sons

class AlertSystem {
    constructor() {
        this.audioContext = null;
        this.isAudioEnabled = true;
        this.init();
    }

    init() {
        this.setupAudioContext();
        this.createToastContainer();
        this.bindEvents();
    }

    setupAudioContext() {
        try {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        } catch (e) {
            console.warn('Web Audio API non supportée:', e);
            this.isAudioEnabled = false;
        }
    }

    createToastContainer() {
        const container = document.createElement('div');
        container.id = 'alert-toast-container';
        container.className = 'alert-toast-container';
        document.body.appendChild(container);
    }

    bindEvents() {
        // Écouter les événements personnalisés pour les alertes
        document.addEventListener('showAlert', (e) => {
            this.showToast(e.detail);
        });

        // Écouter le clic sur les boutons de fermeture
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('alert-close')) {
                e.target.closest('.alert-toast').remove();
            }
        });
    }

    playSound(type) {
        if (!this.isAudioEnabled || !this.audioContext) return;

        try {
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();

            // Configuration du son selon le type d'alerte
            switch(type) {
                case 'success':
                    oscillator.frequency.setValueAtTime(800, this.audioContext.currentTime);
                    oscillator.frequency.setValueAtTime(1200, this.audioContext.currentTime + 0.1);
                    break;
                case 'warning':
                    oscillator.frequency.setValueAtTime(400, this.audioContext.currentTime);
                    oscillator.frequency.setValueAtTime(300, this.audioContext.currentTime + 0.1);
                    oscillator.frequency.setValueAtTime(400, this.audioContext.currentTime + 0.2);
                    break;
                case 'error':
                    oscillator.frequency.setValueAtTime(300, this.audioContext.currentTime);
                    oscillator.frequency.setValueAtTime(200, this.audioContext.currentTime + 0.1);
                    oscillator.frequency.setValueAtTime(300, this.audioContext.currentTime + 0.2);
                    oscillator.frequency.setValueAtTime(200, this.audioContext.currentTime + 0.3);
                    break;
                default:
                    oscillator.frequency.setValueAtTime(600, this.audioContext.currentTime);
            }

            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);

            // Enveloppe ADSR
            gainNode.gain.setValueAtTime(0, this.audioContext.currentTime);
            gainNode.gain.linearRampToValueAtTime(0.3, this.audioContext.currentTime + 0.01);
            gainNode.gain.linearRampToValueAtTime(0, this.audioContext.currentTime + 0.3);

            oscillator.start();
            oscillator.stop(this.audioContext.currentTime + 0.3);
        } catch (e) {
            console.warn('Erreur de lecture audio:', e);
        }
    }

    showToast({ type = 'info', title, message, duration = 5000, playSound = true }) {
        const container = document.getElementById('alert-toast-container');
        const toast = document.createElement('div');
        toast.className = `alert-toast alert-toast-${type}`;

        const icon = this.getIconForType(type);

        toast.innerHTML = `
            <div class="alert-icon">${icon}</div>
            <div class="alert-content">
                <div class="alert-title">${title}</div>
                <div class="alert-message">${message}</div>
            </div>
            <button class="alert-close">&times;</button>
            <div class="alert-progress"></div>
        `;

        container.appendChild(toast);

        // Animation d'entrée
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);

        // Jouer le son
        if (playSound && this.isAudioEnabled) {
            this.playSound(type);
        }

        // Fermeture automatique
        if (duration > 0) {
            const progressBar = toast.querySelector('.alert-progress');
            progressBar.style.animation = `progress ${duration}ms linear`;

            setTimeout(() => {
                this.removeToast(toast);
            }, duration);
        }

        return toast;
    }

    removeToast(toast) {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    getIconForType(type) {
        const icons = {
            success: '✓',
            warning: '⚠',
            error: '✗',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    }

    // Méthodes utilitaires pour les types d'alertes spécifiques
    showProjectStartAlert(project) {
        const event = new CustomEvent('showAlert', {
            detail: {
                type: 'success',
                title: 'Début de projet',
                message: `Le projet "${project.title}" commence aujourd'hui !`,
                playSound: true
            }
        });
        document.dispatchEvent(event);
    }

    showProjectDueSoonAlert(project) {
        const daysLeft = Math.ceil((new Date(project.due_date) - new Date()) / (1000 * 60 * 60 * 24));
        const event = new CustomEvent('showAlert', {
            detail: {
                type: 'warning',
                title: 'Échéance proche',
                message: `Le projet "${project.title}" arrive à échéance dans ${daysLeft} jour(s)`,
                playSound: true
            }
        });
        document.dispatchEvent(event);
    }

    showProjectOverdueAlert(project) {
        const event = new CustomEvent('showAlert', {
            detail: {
                type: 'error',
                title: 'Projet en retard',
                message: `Le projet "${project.title}" est en retard !`,
                playSound: true
            }
        });
        document.dispatchEvent(event);
    }
}

// Initialisation globale
let alertSystem = null;

document.addEventListener('DOMContentLoaded', function() {
    alertSystem = new AlertSystem();

    // Exposer globalement pour un accès facile
    window.alertSystem = alertSystem;

    // Détection automatique des alertes au chargement de la page
    detectAndShowAlerts();
});

function detectAndShowAlerts() {
    // Cette fonction sera appelée par les vues qui ont des données d'alerte
    if (typeof window.projectAlerts !== 'undefined') {
        showProjectAlerts(window.projectAlerts);
    }
}

function showProjectAlerts(alerts) {
    if (!alertSystem) return;

    // Alertes de début de projet (vertes)
    alerts.starting_today?.forEach(project => {
        setTimeout(() => {
            alertSystem.showProjectStartAlert(project);
        }, 1000); // Délai pour éviter le spam
    });

    // Alertes d'échéance proche (rouges/orange)
    alerts.due_soon?.forEach(project => {
        setTimeout(() => {
            alertSystem.showProjectDueSoonAlert(project);
        }, 2000);
    });

    // Alertes de retard (rouges)
    alerts.overdue?.forEach(project => {
        setTimeout(() => {
            alertSystem.showProjectOverdueAlert(project);
        }, 3000);
    });
}

// Export pour les modules ES6
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AlertSystem;
}
