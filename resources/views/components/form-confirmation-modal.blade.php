<!-- Form Confirmation Modal -->
<div id="form-confirmation-modal" class="form-confirmation-modal" style="display: none;">
    <div class="form-confirmation-content">
        <div class="form-confirmation-header">
            <h3 class="form-confirmation-title">
                <i class="fas fa-exclamation-triangle" style="color: #f59e0b; margin-right: 8px;"></i>
                Modifications non enregistrées
            </h3>
        </div>
        <div class="form-confirmation-body">
            <p class="form-confirmation-message">
                Vous avez des modifications non enregistrées. Si vous quittez cette page, toutes les modifications seront perdues.
            </p>
        </div>
        <div class="form-confirmation-actions">
            <button type="button" class="form-confirmation-btn cancel" onclick="closeFormConfirmation()">
                <i class="fas fa-times"></i>
                Annuler
            </button>
            <button type="button" class="form-confirmation-btn confirm" onclick="confirmFormNavigation()">
                <i class="fas fa-check"></i>
                Quitter sans sauvegarder
            </button>
        </div>
    </div>
</div>

<script>
// Modal control functions
let pendingNavigation = null;

function showFormConfirmation(navigationCallback) {
    pendingNavigation = navigationCallback;
    const modal = document.getElementById('form-confirmation-modal');
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
}

function closeFormConfirmation() {
    const modal = document.getElementById('form-confirmation-modal');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
        pendingNavigation = null;
    }, 300);
}

function confirmFormNavigation() {
    if (pendingNavigation) {
        pendingNavigation();
    }
    closeFormConfirmation();
}

// Handle clicks outside modal
document.getElementById('form-confirmation-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeFormConfirmation();
    }
});

// Handle Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('form-confirmation-modal').style.display === 'flex') {
        closeFormConfirmation();
    }
});
</script>
