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

.btn {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    text-decoration: none;
    display: inline-block;
    font-weight: 500;
    margin-right: 0.5rem;
}

.btn-primary { background: #3b82f6; color: white; }
.btn-secondary { background: #6b7280; color: white; }
.btn-danger { background: #ef4444; color: white; }

.report-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.meta-item {
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 0.375rem;
}

.meta-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.25rem;
}

.meta-value {
    color: #6b7280;
}

.description {
    background: #f9fafb;
    padding: 1rem;
    border-radius: 0.375rem;
    margin-bottom: 1.5rem;
    white-space: pre-wrap;
}

.file-attachment {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    background: #f3f4f6;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
}

.file-icon {
    margin-right: 0.5rem;
    color: #3b82f6;
}

/* Aperçu fichier */
.file-preview {
    margin-top: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    overflow: hidden;
    position: relative;
}
.file-preview img {
    max-width: 100%;
    display: block;
    object-fit: contain;
}
.file-preview iframe {
    width: 100%;
    height: 500px;
    border: none;
}
.file-preview-error {
    padding: 1rem;
    color: #ef4444;
    text-align: center;
    background: #fef2f2;
    border-radius: 0.375rem;
}
@media (max-width: 768px) {
    .report-meta {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>Détail du rapport journalier</h2>
        </div>
        <div class="card-body">
            <div class="report-meta">
                <div class="meta-item">
                    <div class="meta-label">Date du rapport</div>
                    <div class="meta-value">{{ $report->report_date->format('d/m/Y') }}</div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Projet</div>
                    <div class="meta-value">{{ $report->project ? $report->project->title : 'Sans projet' }}</div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Auteur</div>
                    <div class="meta-value">{{ $report->user->name }}</div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Créé le</div>
                    <div class="meta-value">{{ $report->created_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <h3>Description du travail effectué</h3>
            <div class="description">
                {{ $report->description }}
            </div>

            @if($report->hasFile())
                <h3>Fichier joint</h3>
                <div class="file-attachment">
                    <i class="fas fa-file file-icon"></i>
                    <span>{{ $report->file_name }}</span>
                    <a href="{{ route('daily_reports.download', $report) }}" class="btn btn-primary btn-sm ml-auto">
                        <i class="fas fa-download"></i> Télécharger
                    </a>
                </div>

                {{-- Aperçu du fichier --}}
                <div class="file-preview">
                    @php
                        $ext = strtolower(pathinfo($report->file_name, PATHINFO_EXTENSION));
                    @endphp

                    @if(in_array($ext, ['jpg','jpeg','png','gif','webp']))
                        <img src="{{ route('daily_reports.preview', $report) }}" alt="Aperçu image" onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=file-preview-error>Erreur lors du chargement de l\'aperçu de l\'image.</div>'">
                    @elseif($ext === 'pdf')
                        <iframe src="{{ route('daily_reports.preview', $report) }}" title="Aperçu PDF"></iframe>
                    @else
                        <div class="file-preview-error">
                            Aucun aperçu disponible pour ce type de fichier.
                        </div>
                    @endif
                </div>
            @endif

            <div class="mt-4">
                <a href="{{ auth()->user()->id === $report->user_id ? route('daily_reports.my_day') : route('daily_reports.daily_reports') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
