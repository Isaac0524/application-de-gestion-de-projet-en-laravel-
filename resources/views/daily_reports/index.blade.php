@extends('layout')
@section('content')

<style>
.container {
    max-width: 1200px;
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
    display: flex;
    justify-content: space-between;
    align-items: center;
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
}

.btn-primary { background: #3b82f6; color: white; }
.btn-success { background: #10b981; color: white; }
.btn-info { background: #06b6d4; color: white; }
.btn-danger { background: #ef4444; color: white; }

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.table th {
    background-color: #f9fafb;
    font-weight: 600;
}

.table tbody tr:hover {
    background-color: #f9fafb;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}

.empty-state h3 {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
}

.empty-state p {
    margin-bottom: 1rem;
}

.pagination {
    margin-top: 2rem;
    display: flex;
    justify-content: center;
}

.pagination .page-link {
    padding: 0.5rem 0.75rem;
    margin: 0 0.25rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    text-decoration: none;
    color: #374151;
}

.pagination .page-link:hover {
    background-color: #f9fafb;
}

.pagination .active .page-link {
    background-color: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

@media (max-width: 768px) {
    .card {
        margin: 1rem;
        padding: 1.5rem;
    }
}
</style>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>Rapports Journaliers</h2>
            <a href="{{ route('daily_reports.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouveau rapport
            </a>
        </div>
        <div class="card-body">
            @if($reports->isEmpty())
                <div class="empty-state">
                    <h3>Aucun rapport trouvé</h3>
                    <p>Ajoutez votre premier rapport journalier pour commencer.</p>
                    <a href="{{ route('daily_reports.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Créer un rapport
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Projet</th>
                                <th>Description</th>
                                <th>Fichier</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                <tr>
                                    <td>{{ $report->report_date->format('d/m/Y') }}</td>
                                    <td>{{ $report->project ? $report->project->title : 'Sans projet' }}</td>
                                    <td>{{ Str::limit($report->description, 100) }}</td>
                                    <td>
                                        @if($report->hasFile())
                                            <a href="{{ route('daily_reports.download', $report) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('daily_reports.show', $report) }}" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('daily_reports.edit', $report) }}" class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('daily_reports.destroy', $report) }}" method="POST" style="display: inline;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce rapport ?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pagination">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
