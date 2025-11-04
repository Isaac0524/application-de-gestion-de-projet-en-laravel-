@extends('layout')
@section('content')

<style>
/* Responsive Edit Project Styles */
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

form {
    margin: 0;
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

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    background-color: #3b82f6;
    color: white;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: background-color 0.2s;
    text-align: center;
    text-decoration: none;
}

.btn:hover {
    background-color: #2563eb;
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
        <h2>Modifier le projet</h2>
        <form method="POST" action="{{ route('projects.update',$project) }}" data-form-confirmation>
            @csrf @method('PUT')

            <div class="form-group">
                <label>Titre</label>
                <input type="text" name="title" value="{{ old('title',$project->title) }}" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" >{{ old('description',$project->description) }}</textarea>
            </div>

            <div class="form-row">
                <div>
                    <label>Date de début</label>
                    <input type="date" name="start_date" value="{{ old('start_date',$project->start_date) }}" required>
                </div>
                <div>
                    <label>Échéance</label>
                    <input type="date" name="due_date" value="{{ old('due_date',$project->due_date) }}" required>
                </div>
            </div>
            <div class="form-row">
                <div>
                    <label>Statut</label>
                    <select name="status" required>
                        @foreach(['pending'=>'En attente','in_progress'=>'En cours','archived'=>'Archivé'] as $k=>$v)
                            <option value="{{ $k }}" @selected($project->status===$k)>{{ $v }}</option>
                        @endforeach
                    </select>
                    @if($project->status === 'completed')
                        <small style="color: #059669; display: block; margin-top: 0.25rem;">
                            <i>✓ Ce projet est automatiquement marqué comme terminé lorsque toutes les activités sont complétées</i>
                        </small>
                    @endif
                </div>
            </div>

            <div style="margin-top: 2rem; text-align: center;">
                <button class="btn">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

@endsection
