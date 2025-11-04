@extends('layout')
@section('content')

<style>
.container {
    max-width: 800px;
    margin: 0 auto;
    padding: 1rem;
}

.card {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.card-body {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 1rem;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

textarea.form-control {
    min-height: 200px;
    resize: vertical;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 0.375rem;
    text-decoration: none;
    display: inline-block;
    font-weight: 500;
    margin-right: 0.5rem;
}

.btn-primary { background: #3b82f6; color: white; }
.btn-secondary { background: #6b7280; color: white; }
.btn-danger { background: #ef4444; color: white; }

.file-info {
    padding: 0.75rem;
    background: #f3f4f6;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
}

.current-file {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem;
    background: #f9fafb;
    border-radius: 0.375rem;
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .container {
        padding: 0.5rem;
    }
}
</style>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>Modifier le rapport journalier</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('daily_reports.update', $report) }}" method="POST" enctype="multipart/form-data" data-form-confirmation>
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="report_date" class="form-label">Date du rapport *</label>
                    <input type="date"
                           class="form-control @error('report_date') is-invalid @enderror"
                           id="report_date"
                           name="report_date"
                           value="{{ old('report_date', $report->report_date->format('Y-m-d')) }}"
                           required>
                    @error('report_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="project_id" class="form-label">Projet</label>
                    <select class="form-control @error('project_id') is-invalid @enderror"
                            id="project_id"
                            name="project_id">
                        <option value="">Sélectionner un projet</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}"
                                    {{ old('project_id', $report->project_id) == $project->id ? 'selected' : '' }}>
                                {{ $project->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('project_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description du travail effectué *</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description"
                              name="description"
                              rows="6"
                              placeholder="Décrivez les tâches effectuées, les défis rencontrés, les solutions apportées..."
                              required>{{ old('description', $report->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="file" class="form-label">Fichier joint</label>

                    @if($report->hasFile())
                        <div class="current-file">
                            <span>
                                <i class="fas fa-file"></i>
                                {{ $report->file_name }}
                            </span>
                            <a href="{{ route('daily_reports.download', $report) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-download"></i> Télécharger
                            </a>
                        </div>
                        <small class="text-muted">
                            Laisser vide pour conserver le fichier actuel
                        </small>
                    @endif

                    <input type="file"
                           class="form-control @error('file') is-invalid @enderror"
                           id="file"
                           name="file"
                           accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt">
                    <small class="text-muted">
                        Formats acceptés : JPG, PNG, PDF, DOC, DOCX, TXT (max 5MB)
                    </small>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                    <a href="{{ route('daily_reports.show', $report) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
