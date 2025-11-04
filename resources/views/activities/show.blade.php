@extends('layout')
@section('content')

<div class="page-container">
    <div class="page-header">
        <div class="page-title">
            <h1>{{ $activity->title }}</h1>
            <div class="breadcrumb">
                <a href="{{ route('projects.show',$activity->project) }}" class="link">{{ $activity->project->title }}</a>
                <i class="fas fa-chevron-right"></i>
                <span>{{ $activity->title }}</span>
            </div>
        </div>
        @if($user->id === $activity->project->owner_id)
            <div class="page-actions">
                <a class="btn btn-secondary" href="{{ route('activities.edit',[$activity->project,$activity]) }}">
                    <i class="fas fa-edit"></i> Modifier
                </a>
                <a class="btn btn-primary" href="{{ route('tasks.create',$activity) }}">
                    <i class="fas fa-plus"></i> Ajouter une tâche
                </a>
            </div>
        @endif
    </div>

    <div class="grid grid-2">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> Détails de l'activité</h3>
            </div>
            <div class="card-content">
                <div class="info-section">
                    <div class="info-item">
                        <label>Description</label>
                        <p>{{ $activity->description ?: 'Aucune description' }}</p>
                    </div>
                    <div class="info-row">
                        <div class="info-item">
                            <label>Statut</label>
                            <span class="badge badge-{{ $activity->status }}">{{ ucfirst(str_replace('_', ' ', $activity->status)) }}</span>
                        </div>
                        <div class="info-item">
                            <label>Projet</label>
                            <a href="{{ route('projects.show',$activity->project) }}" class="link">{{ $activity->project->title }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-tasks"></i> Tâches ({{ $activity->tasks->count() }})</h3>
            </div>
            <div class="card-content">
                @if($activity->tasks->count() > 0)
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Assignés</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activity->tasks as $task)
                                    <tr>
                                        <td>
                                            <div class="task-title">{{ $task->title }}</div>
                                            @if($task->due_date)
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="assignees">
                                                @forelse($task->assignees as $assignee)
                                                    <span class="assignee-badge">{{ $assignee->name }}</span>
                                                    @if(!$loop->last), @endif
                                                @empty
                                                    <span class="text-muted">Non assigné</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $task->status }}">
                                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                @if($user->id === $activity->project->owner_id)
                                                    <a class="btn btn-sm btn-secondary" href="{{ route('tasks.edit',[$activity,$task]) }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($task->status === 'completed_by_assignee')
                                                        <form method="POST" action="{{ route('tasks.finalize',$task) }}" class="inline-form">
                                                            @csrf
                                                            <button class="btn btn-sm btn-success" title="Finaliser">
                                                                <i class="fas fa-check-double"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif

                                                {{-- Les managers voient le bouton basculer statut --}}
                                                @if($user->id === $activity->project->owner_id)
                                                    <form method="POST" action="{{ route('tasks.toggle_progress',$task) }}" class="inline-form">
                                                        @csrf
                                                        <button class="btn btn-sm btn-info" title="Basculer statut">
                                                            <i class="fas fa-sync"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                {{-- Les membres ne voient que le bouton "Marquer comme terminé" --}}
                                                {{-- Ce bouton est caché quand la tâche est finalisée --}}
                                                @if($task->assignees->contains('id',$user->id) &&
                                                     $user->id !== $activity->project->owner_id &&
                                                     $task->status !== 'finalized' &&
                                                     $task->status !== 'completed_by_assignee' &&
                                                     $task->status !== 'completed')
                                                    <form method="POST" action="{{ route('tasks.complete_by_assignee',$task) }}" class="inline-form">
                                                        @csrf
                                                        <button class="btn btn-sm btn-warning" title="Marquer comme terminé">
                                                            <i class="fas fa-flag"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-tasks"></i>
                        <p>Aucune tâche dans cette activité</p>
                        @if($user->id === $activity->project->owner_id)
                            <a class="btn btn-primary" href="{{ route('tasks.create',$activity) }}">
                                <i class="fas fa-plus"></i> Créer la première tâche
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* Variables héritées du dashboard */
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
    --gray-800: #1f2937;
    --border-radius: 8px;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --transition: all 0.2s ease-in-out;
}

/* Page Layout */
.page-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
    gap: 20px;
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
}

.breadcrumb i {
    font-size: 12px;
    color: var(--gray-400);
}

.page-actions {
    display: flex;
    gap: 12px;
}

/* Grid System */
.grid {
    display: grid;
    gap: 24px;
    margin-bottom: 24px;
}

.grid-2 { grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); }

/* Card Component */
.card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    transition: var(--transition);
}

.card:hover {
    box-shadow: var(--shadow-md);
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
    padding: 20px;
}

/* Info Section */
.info-section {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.info-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.info-item label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--gray-600);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.info-item p {
    margin: 0;
    color: var(--gray-800);
    line-height: 1.5;
}

/* Table Component */
.table-container {
    overflow-x: auto;
    margin: -20px;
    padding: 20px;
}

.table {
    width: 100%;
    border-collapse: collapse;
    min-width: 600px;
}

.table th,
.table td {
    text-align: left;
    padding: 12px 8px;
    border-bottom: 1px solid var(--gray-200);
}

.table th {
    font-weight: 600;
    color: var(--gray-700);
    background: var(--gray-100);
    font-size: 14px;
}

.table td {
    font-size: 14px;
}

.task-title {
    font-weight: 500;
    margin-bottom: 2px;
}

/* Assignees */
.assignees {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.assignee-badge {
    background: var(--primary-color);
    color: white;
    padding: 2px 6px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}

.inline-form {
    display: inline;
}

/* Button Component */
.btn {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    border-radius: var(--border-radius);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: var(--transition);
    border: none;
    cursor: pointer;
    gap: 6px;
    white-space: nowrap;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

.btn-secondary {
    background: var(--gray-200);
    color: var(--gray-700);
}

.btn-secondary:hover {
    background: var(--gray-300);
}

.btn-success {
    background: var(--success-color);
    color: white;
}

.btn-success:hover {
    background: #059669;
}

.btn-warning {
    background: var(--warning-color);
    color: white;
}

.btn-warning:hover {
    background: #d97706;
}

.btn-info {
    background: var(--info-color);
    color: white;
}

.btn-info:hover {
    background: #0891b2;
}

.btn-sm {
    padding: 6px 10px;
    font-size: 12px;
}

/* Badge Component */
.badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    text-transform: capitalize;
}

.badge-open { background: #fef3c7; color: #92400e; }
.badge-pending { background: #fef3c7; color: #92400e; }
.badge-in_progress { background: #dbeafe; color: #1e40af; }
.badge-completed_by_assignee { background: #fed7aa; color: #9a3412; }
.badge-completed { background: #d1fae5; color: #065f46; }
.badge-finalized { background: #d1fae5; color: #065f46; }
.badge-archived { background: var(--gray-200); color: var(--gray-600); }

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

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--gray-500);
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    color: var(--gray-400);
}

.empty-state p {
    margin: 0 0 16px 0;
    font-size: 16px;
}

/* Utility Classes */
.text-muted {
    color: var(--gray-500);
    font-size: 12px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-container {
        padding: 16px;
    }

    .page-header {
        flex-direction: column;
        align-items: stretch;
    }

    .page-actions {
        justify-content: flex-end;
    }

    .grid-2 {
        grid-template-columns: 1fr;
    }

    .info-row {
        grid-template-columns: 1fr;
    }

    .table-container {
        margin: -16px;
        padding: 16px;
    }

    .action-buttons {
        flex-direction: column;
    }

    .btn {
        justify-content: center;
        width: 100%;
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

    .breadcrumb {
        font-size: 12px;
        flex-wrap: wrap;
    }
}
</style>

<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

@endsection
