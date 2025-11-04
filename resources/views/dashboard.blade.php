@extends('layout')
@section('content')

<div class="dashboard">
    <div class="dashboard-header">
        <h1>Tableau de bord</h1>
        <div class="user-info">
            <span class="user-role {{ $user->isAdmin() ? 'admin' : ($user->isManager() ? 'manager' : 'member') }}">
                {{ $user->isAdmin() ? 'Admin' : ($user->isManager() ? 'Manager' : 'Membre') }}
            </span>
        </div>
    </div>

    <!-- Section Alertes pour les managers -->
    @if($user->isManager() && isset($alerts))
    <div class="alerts-section">
        @php
            $totalAlerts = count($alerts['starting_today']) + count($alerts['due_soon']) + count($alerts['overdue']);
        @endphp

        @if($totalAlerts > 0)
        <div class="card">
            <div class="card-header">
                <h3>Alertes Projets</h3>
                <span class="badge badge-warning">{{ $totalAlerts }}</span>
            </div>
            <div class="card-content">
                <!-- Alertes de début de projet -->
                @if(count($alerts['starting_today']) > 0)
                <div class="alert-card alert-card-starting">
                    <div class="alert-card-header">
                        <div class="alert-card-title">
                            <i class="fas fa-play-circle" style="color: #10b981;"></i>
                            Démarrage aujourd'hui
                            <span class="alert-card-count">{{ count($alerts['starting_today']) }}</span>
                        </div>
                    </div>
                    <ul class="alert-card-list">
                        @foreach($alerts['starting_today'] as $project)
                        <li class="alert-card-item">
                            <span class="alert-project-name">{{ $project->title }}</span>
                            <span class="alert-project-date">Aujourd'hui</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Alertes d'échéance proche -->
                @if(count($alerts['due_soon']) > 0)
                <div class="alert-card alert-card-due">
                    <div class="alert-card-header">
                        <div class="alert-card-title">
                            <i class="fas fa-clock" style="color: #f59e0b;"></i>
                            Échéance proche <span class="alert-card-count">{{ count($alerts['due_soon']) }}</span>
                        </div>
                    </div>
                    <ul class="alert-card-list">
                        @foreach($alerts['due_soon'] as $project)
                        <li class="alert-card-item">
                            <span class="alert-project-name">{{ $project->title }}</span>
                            <span class="alert-project-date">
                                {{ \Carbon\Carbon::parse($project->due_date)->diffForHumans() }}
                            </span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif


                <!-- Alertes de retard -->
                @if(count($alerts['overdue']) > 0)
                <div class="alert-card alert-card-overdue">
                    <div class="alert-card-header">
                        <div class="alert-card-title">
                            <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>
                            Projets en retard
                            <span class="alert-card-count">{{ count($alerts['overdue']) }}</span>
                        </div>
                    </div>
                    <ul class="alert-card-list">
                        @foreach($alerts['overdue'] as $project)
                        <li class="alert-card-item">
                            <span class="alert-project-name">{{ $project->title }}</span>
                            <span class="alert-project-date">
                                Retard: {{ \Carbon\Carbon::parse($project->due_date)->diffForHumans() }}
                            </span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>

        </div>
        @endif

    </div>
    <br>
    @endif
    <!-- Statistiques -->
    <div class="stats-grid">
        @if($user->isManager())
            <div class="stat-card primary">
                <div class="stat-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['total_projects'] }}</h3>
                    <p>Projets totaux</p>
                    <small>+{{ $stats['projects_this_month'] }} ce mois</small>
                </div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['completed_projects'] }}</h3>
                    <p>Projets terminés</p>
                    <small>{{ round(($stats['completed_projects'] / max($stats['total_projects'], 1)) * 100, 1) }}% du total</small>
                </div>
            </div>
            <div class="stat-card info">
                <div class="stat-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['total_tasks'] }}</h3>
                    <p>Tâches totales</p>
                    <small>{{ $stats['total_activities'] }} activités</small>
                </div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['total_users'] }}</h3>
                    <p>Utilisateurs</p>
                    <small>Total dans l'équipe</small>
                </div>
            </div>
        @else
            <div class="stat-card primary">
                <div class="stat-icon">
                    <i class="fas fa-folder"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['my_projects'] }}</h3>
                    <p>Mes projets</p>
                </div>
            </div>
            <div class="stat-card info">
                <div class="stat-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['my_tasks'] }}</h3>
                    <p>Mes tâches</p>
                </div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['tasks_by_specific_status']['open'] ?? 0 }}</h3>
                    <p>Ouvertes</p>
                </div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="fas fa-check"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['tasks_by_specific_status']['finalized'] ?? 0 }}</h3>
                    <p>Finalisées</p>
                </div>
            </div>
        @endif
    </div>

    @if($user->isManager())
        <!-- Charts pour les managers -->
        <div class="grid grid-2">
            <div class="card">
                <div class="card-header">
                    <h3>Répartition des projets</h3>
                </div>
                <div class="card-content">
                    <div class="chart-container">
                        <canvas id="projectsChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h3>Tâches effectuées par les membres</h3>
                </div>
                <div class="card-content">
                    <div class="chart-container">
                        <canvas id="tasksByMemberChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanban Board -->
        <div class="card kanban-container">
            <div class="card-header">
                <h3>Vue Kanban des projets</h3>
            </div>
            <div class="card-content">
                <div class="kanban-board">
                    <div class="kanban-column">
                        <div class="kanban-header pending">
                            <h4>En Attente </h4>
                            <span class="count">{{ count($projectsKanban['pending']) }}</span>
                        </div>
                        <div class="kanban-items">
                            @foreach($projectsKanban['pending'] as $project)
                                <div class="kanban-item">
                                    <h5><a href="{{ route('projects.show', $project) }}">{{ $project->title }}</a></h5>
                                    <p class="project-owner"><i class="fas fa-user"></i> {{ $project->owner->name }}</p>
                                    @if($project->due_date)
                                        <p class="due-date"><i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($project->due_date)->format('d/m/Y') }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="kanban-column">
                        <div class="kanban-header in-progress">
                            <h4>En cours</h4>
                            <span class="count">{{ count($projectsKanban['in_progress']) }}</span>
                        </div>
                        <div class="kanban-items">
                            @foreach($projectsKanban['in_progress'] as $project)
                                <div class="kanban-item">
                                    <h5><a href="{{ route('projects.show', $project) }}">{{ $project->title }}</a></h5>
                                    <p class="project-owner"><i class="fas fa-user"></i> {{ $project->owner->name }}</p>
                                    @if($project->due_date)
                                        <p class="due-date"><i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($project->due_date)->format('d/m/Y') }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="kanban-column">
                        <div class="kanban-header completed">
                            <h4>Terminés</h4>
                            <span class="count">{{ count($projectsKanban['completed']) }}</span>
                        </div>
                        <div class="kanban-items">
                            @foreach($projectsKanban['completed'] as $project)
                                <div class="kanban-item">
                                    <h5><a href="{{ route('projects.show', $project) }}">{{ $project->title }}</a></h5>
                                    <p class="project-owner"><i class="fas fa-user"></i> {{ $project->owner->name }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="kanban-column">
                        <div class="kanban-header archived">
                            <h4>Archivés</h4>
                            <span class="count">{{ count($projectsKanban['archived']) }}</span>
                        </div>
                        <div class="kanban-items">
                            @foreach($projectsKanban['archived'] as $project)
                                <div class="kanban-item">
                                    <h5><a href="{{ route('projects.show', $project) }}">{{ $project->title }}</a></h5>
                                    <p class="project-owner"><i class="fas fa-user"></i> {{ $project->owner->name }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Contenu principal -->
    <div class="grid {{ $user->isManager() ? 'grid-3' : 'grid-2' }}">
        @if($user->isMember())
        <div class="card">
            <div class="card-header">
                <h3>Mes dernières tâches</h3>
            </div>
            <div class="card-content">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tâche</th>
                                <th>Activité</th>
                                <th>Projet</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myTasks as $task)
                                <tr>
                                    <td>{{ $task->title }}</td>
                                    <td><a href="{{ route('activities.show', $task->activity) }}" class="link">{{ $task->activity->title }}</a></td>
                                    <td><a href="{{ route('projects.show', $task->activity->project) }}" class="link">{{ $task->activity->project->title }}</a></td>
                                    <td><span class="badge badge-{{ $task->status }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Aucune tâche assignée</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3>Mes projets</h3>
                @if($user->isManager())
                <a class="btn btn-primary btn-sm" href="{{ route('projects.create') }}"><i class="fas fa-plus"></i> Nouveau projet</a>
                @endif
            </div>
            <div class="card-content">
                <div class="project-list">
                    @forelse($projects as $project)
                        <div class="project-item">
                            <div class="project-info">
                                <h4><a href="{{ route('projects.show', $project) }}" class="link">{{ $project->title }}</a></h4>
                                <p class="project-meta">
                                    <span class="badge badge-{{ $project->status }}">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                                    <span class="separator">•</span>
                                    <span>{{ $project->activities_count }} activités</span>
                                    <span class="separator">•</span>
                                    <span>{{ $project->activities->sum(function ($a) {return $a->tasks->count();}) }} tâches</span>
                                </p>
                            </div>
                            @if($project->due_date)
                                <div class="project-date">
                                    <small class="text-muted"><i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($project->due_date)->format('d/m/Y') }}</small>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="empty-state">
                            <p class="text-muted">Aucun projet disponible</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        @if(count($upcomingDeadlines) > 0)
        <div class="card">
            <div class="card-header">
                <h3>Échéances à venir</h3>
                <span class="badge badge-warning">{{ count($upcomingDeadlines) }}</span>
            </div>
            <div class="card-content">
                <div class="deadline-list">
                    @foreach($upcomingDeadlines as $item)
                        <div class="deadline-item">
                            @if($user->isManager() && $item instanceof \App\Models\Project)
                                <div class="deadline-info">
                                    <h5><a href="{{ route('projects.show', $item) }}" class="link">{{ $item->title }}</a></h5>
                                    <p class="text-muted">Projet</p>
                                </div>
                                <div class="deadline-date">
                                    <span class="date">{{ \Carbon\Carbon::parse($item->due_date)->format('d/m') }}</span>
                                    <small class="days-left">{{ \Carbon\Carbon::parse($item->due_date)->diffForHumans() }}</small>
                                </div>
                            @else
                                <div class="deadline-info">
                                    <h5>{{ $item->title }}</h5>
                                    <p class="text-muted">{{ $item->activity->project->title }}</p>
                                </div>
                                <div class="deadline-date">
                                    <span class="date">{{ \Carbon\Carbon::parse($item->due_date)->format('d/m') }}</span>
                                    <small class="days-left">{{ \Carbon\Carbon::parse($item->due_date)->diffForHumans() }}</small>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @if($user->isManager() && count($recentActivities) > 0)
        <div class="card">
            <div class="card-header">
                <h3>Activités récentes</h3>
            </div>
            <div class="card-content">
                <div class="activity-list">
                    @foreach($recentActivities as $activity)
                        <div class="activity-item">
                            <div class="activity-info">
                                <h5><a href="{{ route('activities.show', $activity) }}" class="link">{{ $activity->title }}</a></h5>
                                <p class="text-muted">{{ $activity->project->title }} • {{ $activity->tasks->count() }} tâches</p>
                            </div>
                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@if($user->isManager())
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart des projets
    const projectsCtx = document.getElementById('projectsChart').getContext('2d');
    new Chart(projectsCtx, {
        type: 'doughnut',
        data: {
            labels: ['en attente ','En cours', 'Terminés', 'Archivés'],
            datasets: [{
                data: [
                    {{ $stats['projects_by_status']['pending'] ?? 0 }},
                    {{ $stats['projects_by_status']['in_progress'] ?? 0 }},
                    {{ $stats['projects_by_status']['completed'] ?? 0 }},
                    {{ $stats['projects_by_status']['archived'] ?? 0 }}
                ],
                backgroundColor: ['#f0a910','#3b82f6', '#10b981', '#6b7280'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Chart des tâches
    const tasksByMemberCtx = document.getElementById('tasksByMemberChart').getContext('2d');
    new Chart(tasksByMemberCtx, {
        type: 'bar',
        data: {
            labels: ['en attente ','ouvert', 'en cours', 'finalisé'],
            datasets: [{
                data: [
                    {{ $stats['tasks_by_status']['pending'] ?? 0 }},
                    {{ $stats['tasks_by_status']['open'] ?? 0 }},
                    {{ $stats['tasks_by_status']['in_progress'] ?? 0 }},
                    {{ $stats['tasks_by_status']['finalized'] ?? 0 }}
                ],
                backgroundColor: ['#086964','#f59e0b', '#3b82f6', '#10b981'],
                borderRadius: 3,
                borderSkipped: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Initialisation des alertes
    @if(isset($alerts))
    window.projectAlerts = @json($alerts);
    @endif
});
</script>
@endif

<style>
/* Variables CSS pour la cohérence du style */
:root {
    --primary-color: #5D5CDE;
    --success-color: #10b981;
    --warning-color: #e29f2a;
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

/* Dashboard Layout */
.dashboard {
    padding: 0;
    max-width: none;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--gray-200);
}

.dashboard-header h1 {
    margin: 0;
    font-size: 28px;
    color: var(--gray-800);
    font-weight: 700;
}

.user-info .user-role {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.user-role.manager {
    background: var(--primary-color);
    color: white;
}

.user-role.member {
    background: var(--info-color);
    color: white;
}

/* Stats Grid - Responsive */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.stat-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 24px;
    box-shadow: var(--shadow-sm);
    border-left: 4px solid;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-card.primary { border-left-color: var(--primary-color); }
.stat-card.success { border-left-color: var(--success-color); }
.stat-card.warning { border-left-color: var(--warning-color); }
.stat-card.info { border-left-color: var(--info-color); }

.stat-icon {
    font-size: 28px;
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gray-100);
    border-radius: 12px;
    color: var(--gray-600);
}

.stat-content h3 {
    font-size: 28px;
    font-weight: 700;
    margin: 0;
    color: var(--gray-800);
}

.stat-content p {
    margin: 4px 0 0 0;
    color: var(--gray-600);
    font-size: 14px;
    font-weight: 500;
}

.stat-content small {
    color: var(--gray-500);
    font-size: 12px;
}

/* Grid System - Responsive */
.grid {
    display: grid;
    gap: 24px;
    margin-bottom: 32px;
}

.grid-2 {
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
}

.grid-3 {
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
}

/* Card Component */
.card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    transition: var(--transition);
}



.card-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--gray-50);
}

.card-header h3 {
    margin: 0;
    color: var(--gray-800);
    font-size: 16px;
    font-weight: 600;
}

.card-content {
    padding: 24px;
}

/* Chart Container */
.chart-container {
    height: 200px;
    position: relative;
}

/* Kanban Board - Responsive */
.kanban-container .card-content {
    padding: 0;
}

.kanban-board {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1px;
    background: var(--gray-200);
    min-height: 400px;
}

.kanban-column {
    background: rgb(241, 237, 237);
    display: flex;
    flex-direction: column;
}

.kanban-header {
    padding: 16px 20px;
    font-weight: 600;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--gray-200);
}

.kanban-header h4 {
    margin: 0;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.kanban-header .count {
    background: rgba(255, 255, 255, 0.3);
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    min-width: 24px;
    text-align: center;
}
.kanban-header.pending { background: var(--warning-color); color: white; }
.kanban-header.in-progress { background: var(--primary-color); color: white; }
.kanban-header.completed { background: var(--success-color); color: white; }
.kanban-header.archived { background: var(--gray-500); color: white; }

.kanban-items {
    flex: 1;
    padding: 12px;
    overflow-y: auto;
}

.kanban-item {
    background: var(--gray-50);
    padding: 16px;
    margin-bottom: 12px;
    border-radius: var(--border-radius);
    transition: var(--transition);
    border: 1px solid var(--gray-200);
}

.kanban-item:hover {
    background: white;
    box-shadow: var(--shadow-sm);
    border-color: var(--gray-300);
}

.kanban-item h5 {
    margin: 0 0 8px 0;
    font-size: 14px;
    font-weight: 600;
}

.kanban-item p {
    margin: 4px 0;
    font-size: 12px;
    color: var(--gray-600);
}

/* Table Component - Responsive */
.table-container {
    overflow-x: auto;
    border-radius: var(--border-radius);
    border: 1px solid var(--gray-200);
}

.table {
    width: 100%;
    border-collapse: collapse;
    min-width: 600px;
}

.table th,
.table td {
    text-align: left;
    padding: 12px 16px;
    border-bottom: 1px solid var(--gray-200);
}

.table th {
    font-weight: 600;
    color: var(--gray-700);
    background: var(--gray-50);
    font-size: 14px;
}

.table td {
    font-size: 14px;
}

.table tbody tr:hover {
    background: var(--gray-50);
}

/* Badge Component */
.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    text-transform: capitalize;
}

.badge-pending { background: #fef3c7; color: #f0a910; }
.badge-in_progress { background: #dbeafe; color: #1e40af; }
.badge-completed { background: #d1fae5; color: #065f46; }
.badge-archived { background: var(--gray-200); color: var(--gray-600); }
.badge-warning { background: #fef3c7; color: #92400e; }

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
    gap: 8px;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: #4c4bc7;
    transform: translateY(-1px);
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

/* Link Component */
.link {
    color: var(--primary-color);
    text-decoration: none;
    transition: var(--transition);
}

.link:hover {
    color: #4c4bc7;
    text-decoration: underline;
}

/* Project List */
.project-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.project-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 16px 0;
    border-bottom: 1px solid var(--gray-200);
}

.project-item:last-child {
    border-bottom: none;
}

.project-info h4 {
    margin: 0 0 6px 0;
    font-size: 15px;
    font-weight: 600;
}

.project-meta {
    margin: 0;
    font-size: 12px;
    color: var(--gray-600);
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.separator {
    color: var(--gray-400);
}

/* Deadline List */
.deadline-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.deadline-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
    border-bottom: 1px solid var(--gray-200);
}

.deadline-item:last-child {
    border-bottom: none;
}

.deadline-info h5 {
    margin: 0 0 4px 0;
    font-size: 14px;
    font-weight: 600;
}

.deadline-date {
    text-align: right;
    flex-shrink: 0;
}

.deadline-date .date {
    font-weight: 600;
    color: var(--warning-color);
    font-size: 14px;
}

.deadline-date .days-left {
    display: block;
    color: var(--gray-500);
    font-size: 11px;
    margin-top: 2px;
}

/* Activity List */
.activity-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.activity-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 16px 0;
    border-bottom: 1px solid var(--gray-200);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-info h5 {
    margin: 0 0 4px 0;
    font-size: 14px;
    font-weight: 600;
}

/* Utility Classes */
.text-center { text-align: center; }
.text-muted { color: var(--gray-500); }
.empty-state {
    text-align: center;
    padding: 40px 20px;
}

/* Icon Spacing */
.stat-icon i,
.project-owner i,
.due-date i,
.deadline-date i {
    margin-right: 6px;
    color: var(--gray-500);
}

/* Responsive Design */
@media (max-width: 1200px) {
    .grid-3 {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    }
}

@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        gap: 16px;
        align-items: flex-start;
    }

    .dashboard-header h1 {
        font-size: 24px;
    }

    .stats-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .grid-2, .grid-3 {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .kanban-board {
        grid-template-columns: 1fr;
        gap: 16px;
        background: transparent;
    }

    .project-item {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }

    .deadline-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .deadline-date {
        text-align: left;
    }

    .activity-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .card-header {
        padding: 16px 20px;
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }

    .card-content {
        padding: 16px 20px;
    }

    .stat-card {
        padding: 20px;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        gap: 12px;
    }

    .stat-card {
        padding: 16px;
        flex-direction: column;
        text-align: center;
        gap: 12px;
    }

    .project-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }

    .separator {
        display: none;
    }
}
</style>

@endsection
