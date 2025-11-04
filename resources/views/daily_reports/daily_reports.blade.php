@extends('layout')
@section('content')

<style>
/* Manager Daily Reports Styles */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.page-title {
    font-size: 1.875rem;
    font-weight: 700;
    color: #1f2937;
}

.filters-section {
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;
}

.form-group {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
}

.reports-grid {
    display: grid;
    gap: 1.5rem;
}

.report-card {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.report-header {
    padding: 1.5rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.report-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.report-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.report-content {
    padding: 1.5rem;
}

.report-description {
    margin-bottom: 1rem;
    line-height: 1.6;
    color: #374151;
}

.attachments-section {
    margin-top: 1rem;
}

.attachments-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.attachment-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    background: #f3f4f6;
    border-radius: 0.375rem;
    margin-bottom: 0.5rem;
}

.attachment-icon {
    color: #6b7280;
}

.attachment-link {
    color: #3b82f6;
    text-decoration: none;
    font-size: 0.875rem;
}

.attachment-link:hover {
    text-decoration: underline;
}

.report-footer {
    padding: 1rem 1.5rem;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge.approved {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.pending {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.rejected {
    background: #fee2e2;
    color: #991b1b;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-success:hover {
    background: #059669;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}

.stats-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
}

.pagination {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
}

@media (max-width: 768px) {
    .filters-grid {
        grid-template-columns: 1fr;
    }

    .report-meta {
        flex-direction: column;
        gap: 0.5rem;
    }

    .action-buttons {
        flex-direction: column;
    }
}
</style>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Gestion des Rapports Journaliers</h1>
    </div>

    <!-- Statistics Section -->
    <div class="stats-section">
        <div class="stat-card">
            <div class="stat-number">{{ $reports->total() }}</div>
            <div class="stat-label">Total Rapports</div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filters-section">
        <form method="GET" action="{{ route('daily_reports.daily_reports') }}">
            <div class="filters-grid">
                <div class="form-group">
                    <label>Employé</label>
                    <select name="user" class="form-control">
                        <option value="">Tous les employés</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Projet</label>
                    <select name="project" class="form-control">
                        <option value="">Tous les projets</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project') == $project->id ? 'selected' : '' }}>
                                {{ $project->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    <a href="{{ route('daily_reports.daily_reports') }}" class="btn btn-outline">Réinitialiser</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Reports List -->
    <div class="reports-grid">
        @if($reports->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox" style="font-size: 3rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                <p>Aucun rapport trouvé</p>
            </div>
        @else
            @foreach($reports as $report)
                <div class="report-card">
                    <div class="report-header">
                        <h3 class="report-title">{{ $report->user->name }}</h3>
                        <div class="report-meta">
                            <span><i class="fas fa-calendar"></i> {{ $report->report_date->format('d/m/Y') }}</span>
                            <span><i class="fas fa-clock"></i> {{ $report->created_at->format('H:i') }}</span>
                            @if($report->project)
                                <span><i class="fas fa-folder"></i> {{ $report->project->title }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="report-content">
                        <div class="report-description">
                            {{ Str::limit($report->description, 200) }}
                        </div>

                        @if($report->file_path)
                            <div class="attachments-section">
                                <div class="attachments-title">Pièce jointe :</div>
                                <div class="attachment-item">
                                    <i class="fas fa-paperclip attachment-icon"></i>
                                    <a href="{{ Storage::url($report->file_path) }}"
                                       class="attachment-link"
                                       target="_blank">
                                        {{ basename($report->file_path) }}
                                    </a>
                                    <span class="text-sm text-gray-500">
({{ number_format(Storage::disk('public')->size($report->file_path) / 1024, 1) }} KB)
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="report-footer">


                        <div class="action-buttons">
                            <a href="{{ route('daily_reports.show', $report) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Voir
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

@endsection
