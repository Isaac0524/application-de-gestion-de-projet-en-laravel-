@extends('layout')
@section('content')

<style>
/* Responsive My Day Styles */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem;
}

.card {
    max-width: 100%;
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
    text-decoration: none;
    text-align: center;
}

.btn:hover {
    background-color: #2563eb;
}

.btn-success {
    background-color: #059669;
}

.btn-success:hover {
    background-color: #047857;
}

.btn-danger {
    background-color: #dc2626;
}

.btn-danger:hover {
    background-color: #b91c1c;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
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

/* Responsive Design */
@media (max-width: 768px) {
    .card {
        margin: 1rem;
        padding: 1.5rem;
    }

    h2 {
        font-size: 1.5rem;
    }

    .table {
        font-size: 0.875rem;
    }

    .table th,
    .table td {
        padding: 0.5rem;
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Mon rapport journalier</h2>
            <a href="{{ route('daily_reports.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Ajouter un rapport
            </a>
        </div>

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

@endsection
</final_file_content>
