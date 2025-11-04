@extends('layout')
@section('content')

<style>
/* Enhanced Responsive Create Project Styles */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem;
}

.card {
    max-width: 820px;
    margin: 2rem auto;
    padding: 2rem;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 0.5rem;
}

h2 {
    font-size: 1.875rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1.5rem;
    text-align: center;
}

.form-group {
    margin-bottom: 1.5rem;
}

label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
}

input[type="text"],
input[type="date"],
textarea,
select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 1rem;
    transition: border-color 0.2s;
    box-sizing: border-box;
}

input[type="text"]:focus,
input[type="date"]:focus,
textarea:focus,
select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

textarea {
    resize: vertical;
    min-height: 120px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-top: 1rem;
}

.buttons {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}

.btn.primary {
    background-color: #3b82f6;
    color: white;
}

.btn.primary:hover {
    background-color: #2563eb;
}

.btn:not(.primary) {
    background-color: #f3f4f6;
    color: #374151;
}

.btn:not(.primary):hover {
    background-color: #e5e7eb;
}

.panel {
    margin-top: 2rem;
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1.5rem;
}

.panel .small {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 1rem;
}

[data-dyn-list] > * + * {
    margin-top: 0.75rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .card {
        margin: 1rem;
        padding: 1.5rem;
    }

    h2 {
        font-size: 1.5rem;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .buttons {
        flex-direction: column;
    }

    .btn {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .card {
        margin: 0.5rem;
        padding: 1rem;
    }

    h2 {
        font-size: 1.25rem;
    }
}
</style>

<div class="container">
    <div class="card">
        <h2>Nouveau projet</h2>
        <form id="project-create" method="POST" action="{{ route('projects.store') }}" data-form-confirmation>
            @csrf
            <div class="form-group">
                <label for="title">Titre</label>
                <input id="title" type="text" name="title" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" ></textarea>
            </div>

            <div class="form-row">
                <div>
                    <label for="start_date">Date de début</label>
                    <input id="start_date" type="date" name="start_date" required>
                </div>
                <div>
                    <label for="due_date">Échéance</label>
                    <input id="due_date" type="date" name="due_date" required>
                </div>
            </div>

            <div class="buttons">
                <button class="btn primary" type="submit">Créer</button>
            </div>
        </form>
    </div>
</div>



@endsection
