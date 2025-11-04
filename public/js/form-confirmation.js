/**
 * Form Confirmation Handler
 * Detects unsaved changes and prompts users before leaving forms
 */

class FormConfirmationHandler {
    constructor() {
        this.forms = new Map();
        this.isFormDirty = false;
        this.init();
    }

    init() {
        this.bindEvents();
        this.setupFormTracking();
    }

    bindEvents() {
        // Handle beforeunload event
        window.addEventListener('beforeunload', (e) => {
            if (this.isFormDirty) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        });

        // Handle navigation links
        document.addEventListener('click', (e) => {
            if (e.target.tagName === 'A' && !e.target.hasAttribute('data-ignore-confirmation')) {
                if (this.isFormDirty && !this.confirmNavigation()) {
                    e.preventDefault();
                    return false;
                }
            }
        });

        // Handle form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.hasAttribute('data-form-confirmation')) {
                this.markFormClean();
            }
        });
    }

    setupFormTracking() {
        // Find all forms that need confirmation
        const forms = document.querySelectorAll('[data-form-confirmation]');

        forms.forEach(form => {
            this.trackForm(form);
        });
    }

    trackForm(form) {
        const formKey = this.generateFormKey(form);
        const initialState = this.getFormState(form);

        this.forms.set(formKey, {
            form: form,
            initialState: initialState,
            currentState: initialState
        });

        // Track input changes
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('input', () => this.checkFormChanges(form));
            input.addEventListener('change', () => this.checkFormChanges(form));
        });

        // Track form reset
        form.addEventListener('reset', () => {
            setTimeout(() => {
                this.resetFormState(form);
            }, 0);
        });
    }

    getFormState(form) {
        const formData = new FormData(form);
        const state = {};

        for (let [key, value] of formData.entries()) {
            state[key] = value;
        }

        return JSON.stringify(state);
    }

    checkFormChanges(form) {
        const formKey = this.generateFormKey(form);
        const formData = this.forms.get(formKey);

        if (formData) {
            const currentState = this.getFormState(form);
            const hasChanges = currentState !== formData.initialState;

            this.isFormDirty = hasChanges;
            formData.currentState = currentState;

            // Visual indicator
            this.updateFormVisualState(form, hasChanges);
        }
    }

    resetFormState(form) {
        const formKey = this.generateFormKey(form);
        const formData = this.forms.get(formKey);

        if (formData) {
            const newState = this.getFormState(form);
            formData.initialState = newState;
            formData.currentState = newState;
            this.isFormDirty = false;
            this.updateFormVisualState(form, false);
        }
    }

    updateFormVisualState(form, isDirty) {
        // Add visual indicator for unsaved changes
        const saveButton = form.querySelector('[type="submit"]');
        if (saveButton) {
            if (isDirty) {
                saveButton.classList.add('has-changes');
                saveButton.style.position = 'relative';

                // Add unsaved indicator
                if (!saveButton.querySelector('.unsaved-indicator')) {
                    const indicator = document.createElement('span');
                    indicator.className = 'unsaved-indicator';
                    indicator.innerHTML = ' •';
                    indicator.style.color = '#f59e0b';
                    indicator.style.fontSize = '1.2em';
                    indicator.style.marginLeft = '4px';
                    saveButton.appendChild(indicator);
                }
            } else {
                saveButton.classList.remove('has-changes');
                const indicator = saveButton.querySelector('.unsaved-indicator');
                if (indicator) {
                    indicator.remove();
                }
            }
        }
    }

    generateFormKey(form) {
        return `form-${Math.random().toString(36).substr(2, 9)}`;
    }

    confirmNavigation() {
        return confirm('Vous avez des modifications non enregistrées. Êtes-vous sûr de vouloir quitter cette page ?');
    }

    markFormClean() {
        this.isFormDirty = false;
    }

    // Public method to manually mark a form as clean
    markFormAsClean(form) {
        this.resetFormState(form);
    }

    // Public method to check if any form has unsaved changes
    hasUnsavedChanges() {
        return this.isFormDirty;
    }
}

// Initialize the form confirmation handler
document.addEventListener('DOMContentLoaded', () => {
    window.formConfirmationHandler = new FormConfirmationHandler();
});

// Utility function to manually trigger form state check
function checkFormChanges(formSelector) {
    const form = document.querySelector(formSelector);
    if (form && window.formConfirmationHandler) {
        window.formConfirmationHandler.checkFormChanges(form);
    }
}

// Utility function to mark form as clean
function markFormClean(formSelector) {
    const form = document.querySelector(formSelector);
    if (form && window.formConfirmationHandler) {
        window.formConfirmationHandler.markFormAsClean(form);
    }
}
