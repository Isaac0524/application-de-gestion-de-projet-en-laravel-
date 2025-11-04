@extends('layout')
@section('content')

<div class="page-container">
    <div class="page-header">
        <div class="page-title">
            <h1>{{ $task->exists ? 'Modifier la tâche' : 'Nouvelle tâche' }}</h1>
            <div class="breadcrumb">
                <a href="{{ route('projects.show', $activity->project) }}" class="link">{{ $activity->project->title }}</a>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('activities.show', $activity) }}" class="link">{{ $activity->title }}</a>
                <i class="fas fa-chevron-right"></i>
                <span>{{ $task->exists ? 'Modifier' : 'Nouvelle tâche' }}</span>
            </div>
        </div>
    </div>

    <div class="form-container">
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fas fa-{{ $task->exists ? 'edit' : 'plus' }}"></i>
                    {{ $task->exists ? 'Modification de la tâche' : 'Création d\'une nouvelle tâche' }}
                </h3>
            </div>
            <div class="card-content">
                <form id="task-form" method="POST" action="{{ $task->exists ? route('tasks.update',[$activity,$task]) : route('tasks.store',$activity) }}" class="task-form" data-form-confirmation>
                    @csrf
                    @if($task->exists) @method('PUT') @endif

                    <!-- Titre et Priorité -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">
                                Titre <span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                value="{{ old('title',$task->title) }}"
                                placeholder="Entrez le titre de la tâche"
                                class="form-control {{ $errors->has('title') ? 'error' : '' }}"
                                required
                            >
                            @error('title')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="priority">
                                <i class="fas fa-flag"></i>
                                Priorité
                            </label>
                            <select id="priority" name="priority" class="form-control">
                                @foreach(['low'=>'Basse','medium'=>'Moyenne','high'=>'Haute'] as $key => $value)
                                    <option value="{{ $key }}" @selected(old('priority',$task->priority)===$key)>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="description">
                            <i class="fas fa-align-left"></i>
                            Description
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            placeholder="Décrivez la tâche en détail..."
                            class="form-control textarea-lg {{ $errors->has('description') ? 'error' : '' }}"
                            rows="4"
                        >{{ old('description',$task->description) }}</textarea>
                        @error('description')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Échéance et Assignation -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="due_date">
                                <i class="fas fa-calendar"></i>
                                Échéance
                            </label>
                            <input
                                type="date"
                                id="due_date"
                                name="due_date"
                                value="{{ old('due_date',$task->due_date) }}"
                                class="form-control"
                            >
                        </div>
                        <div class="form-group">
                            <label for="assignees">
                                <i class="fas fa-users"></i>
                                Assigner à
                            </label>
                            <select
                                id="assignees"
                                name="assignees[]"
                                multiple
                                class="form-control select-multiple"
                                size="4"
                            >
                                @foreach($users as $user)
                                    @if($user->role === 'member')
                                        <option
                                            value="{{ $user->id }}"
                                            @selected(in_array($user->id, old('assignees',$assigned)))
                                            data-avatar="{{ $user->name[0] }}"
                                        >
                                            {{ $user->name }} ({{ ucfirst($user->role) }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <div class="form-help" style="color: #10b981">
                                <i class="fas fa-info-circle"></i>
                                Selectionner un membre pour executer la dite tache
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="form-group">
                        <label for="notes">
                            <i class="fas fa-sticky-note"></i>
                            Notes additionnelles
                        </label>
                        <textarea
                            id="notes"
                            name="notes"
                            placeholder="Ajoutez des notes ou commentaires..."
                            class="form-control"
                            rows="3"
                        >{{ old('notes',$task->notes) }}</textarea>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <a href="{{ route('activities.show', $activity) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-{{ $task->exists ? 'save' : 'plus' }}"></i>
                            {{ $task->exists ? 'Enregistrer les modifications' : 'Créer la tâche' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Assignés sélectionnés -->
        <div class="selected-assignees" id="selected-assignees" style="display: none;">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-users"></i> Assigné sélectionné</h3>
                </div>
                <div class="card-content">
                    <div class="assignees-preview" id="assignees-preview"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Variables héritées */
:root {
    --primary-color: #3b82f6;
    --secondary-color: #6b7280;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --info-color: #06b6d4;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --border-radius: 8px;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --transition: all 0.2s ease-in-out;
}

/* Page Layout */
.page-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    margin-bottom: 24px;
}

.page-title h1 {
    margin: 0 0 8px 0;
    color: var(--gray-800);
    font-size: 28px;
    font-weight: 700;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: var(--gray-600);
    flex-wrap: wrap;
}

.breadcrumb i {
    font-size: 12px;
    color: var(--gray-400);
}

/* Form Container */
.form-container {
    display: grid;
    gap: 24px;
}

/* Card Component */
.card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.card-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--gray-200);
    background: var(--gray-100);
}

.card-header h3 {
    margin: 0;
    color: var(--gray-800);
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-content {
    padding: 24px;
}

/* Form Styles */
.task-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 600;
    color: var(--gray-700);
}

.required {
    color: var(--danger-color);
}

.form-control {
    padding: 10px 12px;
    border: 2px solid var(--gray-200);
    border-radius: var(--border-radius);
    font-size: 14px;
    transition: var(--transition);
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgb(59 130 246 / 0.1);
}

.form-control.error {
    border-color: var(--danger-color);
}

.form-control.error:focus {
    border-color: var(--danger-color);
    box-shadow: 0 0 0 3px rgb(239 68 68 / 0.1);
}

.textarea-lg {
    resize: vertical;
    min-height: 100px;
}

.select-multiple {
    padding: 8px;
}

.select-multiple option {
    padding: 6px 8px;
    border-radius: 4px;
    margin-bottom: 2px;
}

.select-multiple option:checked {
    background: var(--primary-color);
    color: white;
}

.form-help {
    font-size: 12px;
    color: var(--gray-500);
    display: flex;
    align-items: center;
    gap: 4px;
}

.error-message {
    font-size: 12px;
    color: var(--danger-color);
    display: flex;
    align-items: center;
    gap: 4px;
}

.error-message::before {
    content: "⚠";
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 20px;
    border-top: 1px solid var(--gray-200);
    margin-top: 20px;
}

/* Button Component */
.btn {
    display: inline-flex;
    align-items: center;
    padding: 10px 20px;
    border-radius: var(--border-radius);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: var(--transition);
    border: none;
    cursor: pointer;
    gap: 8px;
    white-space: nowrap;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.btn-secondary {
    background: var(--gray-200);
    color: var(--gray-700);
}

.btn-secondary:hover {
    background: var(--gray-300);
}

/* Link Component */
.link {
    color: var(--primary-color);
    text-decoration: none;
    transition: var(--transition);
}

.link:hover {
    color: #2563eb;
    text-decoration: underline;
}

/* Selected Assignees */
.selected-assignees {
    margin-top: 20px;
}

.assignees-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.assignee-preview {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    background: var(--primary-color);
    color: white;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.assignee-avatar {
    width: 20px;
    height: 20px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 600;
}

/* Form Enhancement */
.form-group:has(.form-control:focus) label {
    color: var(--primary-color);
}

/* Loading State */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-container {
        padding: 16px;
    }

    .form-row {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .card-content {
        padding: 20px;
    }

    .form-actions {
        flex-direction: column-reverse;
    }

    .btn {
        width: 100%;
        justify-content: center;
    }

    .breadcrumb {
        flex-wrap: wrap;
    }
}

@media (max-width: 480px) {
    .page-container {
        padding: 12px;
    }

    .page-title h1 {
        font-size: 24px;
    }

    .card-content {
        padding: 16px;
    }

    .task-form {
        gap: 16px;
    }
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeIn 0.3s ease-out;
}
</style>

<script>
// Script pour prévisualiser les assignés sélectionnés
document.addEventListener('DOMContentLoaded', function() {
    const assigneesSelect = document.getElementById('assignees');
    const selectedAssignees = document.getElementById('selected-assignees');
    const assigneesPreview = document.getElementById('assignees-preview');

    function updateAssigneesPreview() {
        const selected = Array.from(assigneesSelect.selectedOptions);

        if (selected.length > 0) {
            assigneesPreview.innerHTML = selected.map(option => `
                <div class="assignee-preview">
                    <div class="assignee-avatar">${option.dataset.avatar}</div>
                    <span>${option.text.split(' (')[0]}</span>
                </div>
            `).join('');
            selectedAssignees.style.display = 'block';
        } else {
            selectedAssignees.style.display = 'none';
        }
    }

    assigneesSelect.addEventListener('change', updateAssigneesPreview);
    updateAssigneesPreview(); // Initial call
});

</script>

<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

@endsection
