@extends('layout')
@section('content')

<div class="page-container">
    <!-- En-tête du projet -->
    <div class="page-header">
        <div class="page-title">
            <h1>{{ $project->title }}</h1>
            <div class="project-meta">
                <span class="badge badge-{{ $project->status }}">
                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                </span>
                @if ($project->due_date)
                <span class="due-date">
                    <i class="fas fa-calendar"></i>
                    {{ \Carbon\Carbon::parse($project->due_date)->format('d/m/Y') }}
                </span>
                @endif
            </div>
        </div>
        @if ($user->id === $project->owner_id)
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ route('projects.edit', $project) }}">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <button id="analyze-btn" class="btn btn-primary btn-ai" onclick="showConfirmationModal()">
                <i class="fas fa-robot"></i>
                <span id="analyze-btn-text">Analyser avec Gemini AI</span>
            </button>
        </div>
        @endif
    </div>

    <!-- Statistiques du projet -->
    <div class="stats-row">
        <div class="stat-item">
            <div class="stat-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-details">
                <div class="stat-value">{{ $project->activities->count() }}</div>
                <div class="stat-label">Activités</div>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="stat-details">
                <div class="stat-value">
                    {{ $project->activities->sum(fn($a) => $a->tasks->count()) }}
                </div>
                <div class="stat-label">Tâches totales</div>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <div class="stat-value">
                    {{ $project->activities->sum(fn($a) => $a->tasks->whereIn('status', ['completed', 'finalized'])->count()) }}
                </div>
                <div class="stat-label">Terminées</div>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon primary">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-details">
                @php
                    $totalTasks = $project->activities->sum(fn($a) => $a->tasks->count());
                    $completedTasks = $project->activities->sum(fn($a) =>
                        $a->tasks->whereIn('status', ['completed', 'finalized'])->count()
                    );
                    $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                @endphp
                <div class="stat-value">{{ $progress }}%</div>
                <div class="stat-label">Progression</div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="grid grid-2">
        <!-- Détails du projet -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> Détails du projet</h3>
            </div>
            <div class="card-content">
                <div class="info-section">
                    <div class="info-item">
                        <label>Description</label>
                        <p>{{ $project->description ?: 'Aucune description disponible' }}</p>
                    </div>
                    <div class="info-row">
                        <div class="info-item">
                            <label>Gestionnaire</label>
                            <div class="owner-info">
                                <i class="fas fa-user"></i>
                                {{ $project->owner->name }}
                            </div>
                        </div>
                        <div class="info-item">
                            <label>Date de début</label>
                            <div class="date-info">
                                <i class="fas fa-calendar-plus"></i>
                                {{ \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>
                    @if ($project->due_date)
                    <div class="info-item">
                        <label>Échéance</label>
                        <div class="due-date-info {{ \Carbon\Carbon::parse($project->due_date)->isPast() ? 'overdue' : '' }}">
                            <i class="fas fa-flag"></i>
                            {{ \Carbon\Carbon::parse($project->due_date)->format('d/m/Y') }}
                            <small>({{ \Carbon\Carbon::parse($project->due_date)->diffForHumans() }})</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Liste des activités -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-clipboard-list"></i> Activités ({{ $project->activities->count() }})</h3>
                @if ($user->id === $project->owner_id)
                <a class="btn btn-primary btn-sm" href="{{ route('activities.create', $project) }}">
                    <i class="fas fa-plus"></i> Ajouter
                </a>
                @endif
            </div>
            <div class="card-content">
                @if ($project->activities->count() > 0)
                <div class="activities-list">
                    @foreach ($project->activities as $activity)
                    <div class="activity-item">
                        <div class="activity-info">
                            <h4>
                                <a href="{{ route('activities.show', $activity) }}" class="link">
                                    {{ $activity->title }}
                                </a>
                            </h4>
                            <p class="activity-description">
                                {{ Str::limit($activity->description, 100) ?: 'Aucune description' }}
                            </p>
                            <div class="activity-meta">
                                <span class="badge badge-{{ $activity->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $activity->status)) }}
                                </span>
                                <span class="task-count">
                                    <i class="fas fa-tasks"></i>
                                    {{ $activity->tasks->count() }} tâches
                                </span>
                                @if ($activity->tasks->count() > 0)
                                @php
                                    $completedTasks = $activity->tasks->whereIn('status', ['completed', 'finalized'])->count();
                                    $activityProgress = round(($completedTasks / $activity->tasks->count()) * 100);
                                @endphp
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: {{ $activityProgress }}%"></div>
                                    <span class="progress-text">{{ $activityProgress }}%</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="activity-actions">
                            @if ($user->id === $project->owner_id)
                            <a href="{{ route('activities.edit', [$project, $activity]) }}"
                               class="btn btn-sm btn-secondary">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif
                            <a href="{{ route('activities.show', $activity) }}"
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <p>Aucune activité dans ce projet</p>
                    @if ($user->id === $project->owner_id)
                    <a class="btn btn-primary" href="{{ route('activities.create', $project) }}">
                        <i class="fas fa-plus"></i> Créer la première activité
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Tâches récentes -->
    @php
        $recentTasks = $project->activities->flatMap->tasks->sortByDesc('created_at')->take(10);
    @endphp
    @if ($recentTasks->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-tasks"></i> Tâches récentes</h3>
        </div>
        <div class="card-content">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tâche</th>
                            <th>Activité</th>
                            <th>Assignés</th>
                            <th>Statut</th>
                            <th>Échéance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentTasks as $task)
                        <tr>
                            <td>
                                <div class="task-info">
                                    <strong>{{ $task->title }}</strong>
                                    @if ($task->priority !== 'medium')
                                    <span class="priority-indicator priority-{{ $task->priority }}">
                                        {{ $task->priority === 'high' ? '🔴' : '🟡' }}
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('activities.show', $task->activity) }}" class="link">
                                    {{ $task->activity->title }}
                                </a>
                            </td>
                            <td>
                                <div class="assignees">
                                    @forelse($task->assignees as $assignee)
                                    <span class="assignee-badge">{{ $assignee->name }}</span>
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
                                @if ($task->due_date)
                                <div class="due-date-cell {{ \Carbon\Carbon::parse($task->due_date)->isPast() && !in_array($task->status, ['completed', 'finalized']) ? 'overdue' : '' }}">
                                    <i class="fas fa-calendar"></i>
                                    {{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}
                                </div>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal de confirmation -->
<div id="confirmation-modal" class="ai-modal hidden">
    <div class="ai-modal-overlay" onclick="closeConfirmationModal()"></div>
    <div class="ai-modal-content">
        <div class="ai-modal-header">
            <h2>
                <i class="fas fa-exclamation-triangle text-warning"></i>
                Confirmation requise
            </h2>
            <button onclick="closeConfirmationModal()" class="close-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="ai-modal-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <p>L'IA Gemini va analyser votre projet et générer automatiquement des activités et tâches optimisées basées sur votre description.</p>
            </div>
            <div class="feature-list">
                <h4>Ce qui va se passer :</h4>
                <ul>
                    <li><i class="fas fa-check"></i> Analyse du titre et de la description du projet</li>
                    <li><i class="fas fa-check"></i> Génération intelligente d'activités cohérentes</li>
                    <li><i class="fas fa-check"></i> Création automatique de tâches détaillées</li>
                    <li><i class="fas fa-check"></i> Attribution de priorités et estimations</li>
                </ul>
            </div>
        </div>
        <div class="ai-modal-footer">
            <button onclick="closeConfirmationModal()" class="btn btn-secondary">
                <i class="fas fa-times"></i> Annuler
            </button>
            <button onclick="startAnalysis()" class="btn btn-primary btn-ai">
                <i class="fas fa-robot"></i> Lancer l'analyse
            </button>
        </div>
    </div>
</div>

<!-- Modal d'analyse IA -->
<div id="ai-modal" class="ai-modal hidden">
    <div class="ai-modal-overlay"></div>
    <div class="ai-modal-content">
        <!-- État de chargement -->
        <div id="loading-state">
            <div class="loading-container">
                <div class="loading-spinner"></div>
                <h3>Analyse IA en cours...</h3>
                <p>Gemini analyse votre projet et génère des activités optimisées</p>
                <div class="loading-steps">
                    <div class="step active">
                        <i class="fas fa-search"></i>
                        <span>Analyse du projet</span>
                    </div>
                    <div class="step">
                        <i class="fas fa-brain"></i>
                        <span>Génération IA</span>
                    </div>
                    <div class="step">
                        <i class="fas fa-check"></i>
                        <span>Finalisation</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- État des résultats -->
        <div id="results-state" class="hidden">
            <div class="ai-modal-header">
                <h2>
                    <i class="fas fa-robot text-primary"></i>
                    Activités générées par Gemini AI
                </h2>
                <button onclick="closeAIModal()" class="close-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="ai-modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <p>Voici les activités et tâches suggérées. Vous pouvez les modifier avant de les valider.</p>
                </div>
                <div id="generated-activities"></div>
            </div>
            <div class="ai-modal-footer">
                <button onclick="closeAIModal()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button onclick="validateActivities()" class="btn btn-primary">
                    <i class="fas fa-check"></i>
                    <span id="validate-btn-text">Valider et créer</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary: #3b82f6;
    --secondary: #6b7280;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #06b6d4;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --radius: 8px;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
}

.page-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 24px;
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

.project-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.page-actions {
    display: flex;
    gap: 12px;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.stat-item {
    background: white;
    border-radius: var(--radius);
    padding: 20px;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.2s;
}

.stat-item:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--gray-100);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: var(--primary);
}

.stat-icon.success {
    background: #d1fae5;
    color: var(--success);
}

.stat-icon.primary {
    background: #dbeafe;
    color: var(--primary);
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--gray-800);
}

.stat-label {
    font-size: 12px;
    color: var(--gray-600);
    text-transform: uppercase;
}

.grid {
    display: grid;
    gap: 24px;
    margin-bottom: 24px;
}

.grid-2 {
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
}

.card {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.card-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--gray-200);
    background: var(--gray-50);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-content {
    padding: 20px;
}

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
    margin-bottom: 4px;
}

.owner-info, .date-info {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--gray-700);
}

.due-date-info {
    display: flex;
    align-items: center;
    gap: 6px;
}

.due-date-info.overdue {
    color: var(--danger);
}

.activities-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.activity-item {
    border: 1px solid var(--gray-200);
    border-radius: var(--radius);
    padding: 16px;
    display: flex;
    justify-content: space-between;
    gap: 16px;
    transition: all 0.2s;
}

.activity-item:hover {
    box-shadow: var(--shadow-sm);
    border-color: var(--gray-300);
}

.activity-info {
    flex: 1;
}

.activity-info h4 {
    margin: 0 0 8px 0;
    font-size: 16px;
}

.activity-description {
    margin: 0 0 8px 0;
    color: var(--gray-600);
    font-size: 14px;
}

.activity-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.task-count {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: var(--gray-600);
}

.progress-bar {
    position: relative;
    width: 80px;
    height: 16px;
    background: var(--gray-200);
    border-radius: 8px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--success);
    transition: width 0.3s;
}

.progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 10px;
    font-weight: 600;
}

.activity-actions {
    display: flex;
    gap: 8px;
}

.btn {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    border-radius: var(--radius);
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    gap: 6px;
    text-decoration: none;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

.btn-primary:disabled {
    background: var(--gray-400);
    cursor: not-allowed;
    transform: none;
}

.btn-secondary {
    background: var(--gray-200);
    color: var(--gray-700);
}

.btn-secondary:hover {
    background: var(--gray-300);
}

.btn-sm {
    padding: 6px 10px;
    font-size: 12px;
}

.btn-ai {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

.btn-ai:hover {
    background: linear-gradient(135deg, #5568d3 0%, #6a4293 100%);
}

.btn-ai::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.btn-ai:hover::before {
    left: 100%;
}

.badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    text-transform: capitalize;
}

.badge-in_progress {
    background: #dbeafe;
    color: #1e40af;
}

.badge-completed, .badge-finalized {
    background: #d1fae5;
    color: #065f46;
}

.badge-pending, .badge-open {
    background: #fef3c7;
    color: #92400e;
}

.badge-archived {
    background: var(--gray-200);
    color: var(--gray-600);
}

.link {
    color: var(--primary);
    text-decoration: none;
}

.link:hover {
    text-decoration: underline;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    color: var(--gray-400);
}

.or-divider {
    margin: 16px 0;
    color: var(--gray-500);
    font-size: 14px;
}

.ai-modal {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.ai-modal.hidden {
    display: none;
}

.ai-modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
}

.ai-modal-content {
    position: relative;
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow-lg);
    max-width: 800px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
}

.ai-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
    border-bottom: 1px solid var(--gray-200);
}

.ai-modal-header h2 {
    margin: 0;
    font-size: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.ai-modal-body {
    padding: 24px;
    max-height: 60vh;
    overflow-y: auto;
}

.ai-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 20px 24px;
    border-top: 1px solid var(--gray-200);
}

.close-btn {
    background: var(--gray-100);
    border: none;
    border-radius: 8px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.close-btn:hover {
    background: var(--gray-200);
}

.alert {
    padding: 16px;
    border-radius: var(--radius);
    margin-bottom: 20px;
    display: flex;
    gap: 12px;
}

.alert-info {
    background: #dbeafe;
    border: 1px solid #3b82f6;
    color: #1e40af;
}

.alert-success {
    background: #d1fae5;
    border: 1px solid #10b981;
    color: #065f46;
}

.feature-list {
    margin-top: 20px;
}

.feature-list h4 {
    margin: 0 0 12px 0;
    font-weight: 600;
}

.feature-list ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.feature-list li {
    padding: 8px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.feature-list i {
    color: var(--success);
}

.loading-container {
    text-align: center;
    padding: 60px 40px;
}

.loading-spinner {
    width: 64px;
    height: 64px;
    border: 4px solid var(--gray-200);
    border-top: 4px solid var(--primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 24px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.loading-container h3 {
    margin: 0 0 8px 0;
    font-size: 20px;
}

.loading-container p {
    color: var(--gray-600);
    margin: 0 0 32px 0;
}

.loading-steps {
    display: flex;
    justify-content: center;
    gap: 24px;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    opacity: 0.4;
    transition: opacity 0.3s;
}

.step.active {
    opacity: 1;
}

.step i {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--gray-200);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.step.active i {
    background: var(--primary);
    color: white;
}

.step span {
    font-size: 12px;
    color: var(--gray-600);
}

.generated-activity {
    border: 1px solid var(--gray-200);
    border-radius: var(--radius);
    padding: 20px;
    background: var(--gray-50);
    margin-bottom: 20px;
}

.activity-header-edit {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 16px;
}

.activity-header-edit input {
    font-size: 18px;
    font-weight: 600;
    border: 1px solid var(--gray-300);
    border-radius: 6px;
    padding: 8px 12px;
    flex: 1;
}

.activity-header-edit textarea {
    border: 1px solid var(--gray-300);
    border-radius: 6px;
    padding: 8px 12px;
    resize: vertical;
    min-height: 60px;
    width: 100%;
    margin-top: 8px;
}

.remove-activity-btn {
    background: var(--danger);
    color: white;
    border: none;
    border-radius: 6px;
    padding: 6px 10px;
    cursor: pointer;
    font-size: 12px;
}

.remove-activity-btn:hover {
    background: #dc2626;
}

.tasks-list {
    margin-top: 16px;
}

.tasks-list h4 {
    margin: 0 0 12px 0;
    font-size: 16px;
    color: var(--gray-700);
}

.task-item {
    background: white;
    border: 1px solid var(--gray-200);
    border-radius: 6px;
    padding: 16px;
    margin-bottom: 12px;
}

.task-header-edit {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 12px;
}

.task-header-edit input {
    font-weight: 500;
    border: 1px solid var(--gray-300);
    border-radius: 4px;
    padding: 6px 10px;
    flex: 1;
    font-size: 14px;
}

.remove-task-btn {
    background: var(--danger);
    color: white;
    border: none;
    border-radius: 4px;
    padding: 4px 8px;
    cursor: pointer;
    font-size: 11px;
}

.task-details-edit {
    display: grid;
    grid-template-columns: 1fr auto auto;
    gap: 12px;
}

.task-details-edit textarea {
    border: 1px solid var(--gray-300);
    border-radius: 4px;
    padding: 6px 10px;
    resize: vertical;
    min-height: 40px;
    font-size: 13px;
}

.task-details-edit select,
.task-details-edit input[type="number"] {
    border: 1px solid var(--gray-300);
    border-radius: 4px;
    padding: 6px 10px;
    font-size: 13px;
}

.add-task-btn {
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 6px;
    padding: 8px 16px;
    cursor: pointer;
    font-size: 13px;
    margin-top: 12px;
}

.add-task-btn:hover {
    background: #2563eb;
}

.table-container {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
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
    background: var(--gray-50);
    font-size: 14px;
}

.table td {
    font-size: 14px;
}

.task-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.assignees {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.assignee-badge {
    background: var(--primary);
    color: white;
    padding: 2px 6px;
    border-radius: 12px;
    font-size: 11px;
}

.due-date-cell {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
}

.due-date-cell.overdue {
    color: var(--danger);
    font-weight: 600;
}

.text-muted {
    color: var(--gray-500);
    font-size: 12px;
}

.text-warning {
    color: var(--warning);
}

.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1100;
    max-width: 400px;
    padding: 16px;
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.notification-success {
    background: #d1fae5;
    border: 1px solid var(--success);
    color: #065f46;
}

.notification-error {
    background: #fee2e2;
    border: 1px solid var(--danger);
    color: #991b1b;
}

.notification-info {
    background: #dbeafe;
    border: 1px solid var(--primary);
    color: #1e40af;
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
}

.notification-close {
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    opacity: 0.7;
}

.notification-close:hover {
    opacity: 1;
}

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

    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }

    .info-row {
        grid-template-columns: 1fr;
    }

    .activity-item {
        flex-direction: column;
    }

    .task-details-edit {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
let currentProjectId = {{ $project->id }};
let generatedActivities = [];

function showConfirmationModal() {
    document.getElementById('confirmation-modal').classList.remove('hidden');
}

function closeConfirmationModal() {
    document.getElementById('confirmation-modal').classList.add('hidden');
}

function startAnalysis() {
    closeConfirmationModal();
    analyzeProject();
}

function analyzeProject() {
    const modal = document.getElementById('ai-modal');
    const loadingState = document.getElementById('loading-state');
    const resultsState = document.getElementById('results-state');
    const analyzeBtn = document.getElementById('analyze-btn');
    const analyzeBtnText = document.getElementById('analyze-btn-text');

    // Désactiver le bouton
    analyzeBtn.disabled = true;
    analyzeBtnText.textContent = 'Analyse en cours...';

    // Afficher le modal avec loading
    modal.classList.remove('hidden');
    loadingState.classList.remove('hidden');
    resultsState.classList.add('hidden');

    // Animation des étapes
    animateLoadingSteps();

    // Appel API
    fetch(`/projects/${currentProjectId}/analyze`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        analyzeBtn.disabled = false;
        analyzeBtnText.textContent = 'Analyser avec Gemini AI';

        if (data.success && data.analysis && data.analysis.activities) {
            generatedActivities = data.analysis.activities;
            displayGeneratedActivities(generatedActivities);
            loadingState.classList.add('hidden');
            resultsState.classList.remove('hidden');
        } else {
            closeAIModal();
            showNotification('Erreur: ' + (data.message || 'Format de réponse invalide'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        analyzeBtn.disabled = false;
        analyzeBtnText.textContent = 'Analyser avec Gemini AI';
        closeAIModal();
        showNotification('Erreur lors de l\'analyse du projet. Vérifiez la console.', 'error');
    });
}

function animateLoadingSteps() {
    const steps = document.querySelectorAll('.loading-steps .step');
    let currentStep = 0;

    const interval = setInterval(() => {
        if (currentStep < steps.length) {
            steps.forEach(step => step.classList.remove('active'));
            steps[currentStep].classList.add('active');
            currentStep++;
        } else {
            clearInterval(interval);
        }
    }, 2000);
}

function displayGeneratedActivities(activities) {
    const container = document.getElementById('generated-activities');
    container.innerHTML = '';

    activities.forEach((activity, activityIndex) => {
        const activityHtml = `
            <div class="generated-activity" data-activity-index="${activityIndex}">
                <div class="activity-header-edit">
                    <div style="flex: 1;">
                        <input
                            type="text"
                            value="${escapeHtml(activity.title)}"
                            placeholder="Titre de l'activité"
                            onchange="updateActivityTitle(${activityIndex}, this.value)"
                        />
                        <textarea
                            placeholder="Description de l'activité"
                            onchange="updateActivityDescription(${activityIndex}, this.value)"
                        >${escapeHtml(activity.description || '')}</textarea>
                    </div>
                    <button class="remove-activity-btn" onclick="removeActivity(${activityIndex})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="tasks-list">
                    <h4><i class="fas fa-tasks"></i> Tâches (${activity.tasks ? activity.tasks.length : 0})</h4>
                    <div class="tasks-container" id="tasks-${activityIndex}">
                        ${activity.tasks ? activity.tasks.map((task, taskIndex) => generateTaskHtml(task, activityIndex, taskIndex)).join('') : ''}
                    </div>
                    <button class="add-task-btn" onclick="addNewTask(${activityIndex})">
                        <i class="fas fa-plus"></i> Ajouter une tâche
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', activityHtml);
    });
}

function generateTaskHtml(task, activityIndex, taskIndex) {
    return `
        <div class="task-item" data-task-index="${taskIndex}">
            <div class="task-header-edit">
                <input
                    type="text"
                    value="${escapeHtml(task.title)}"
                    placeholder="Titre de la tâche"
                    onchange="updateTaskTitle(${activityIndex}, ${taskIndex}, this.value)"
                />
                <button class="remove-task-btn" onclick="removeTask(${activityIndex}, ${taskIndex})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="task-details-edit">
                <textarea
                    placeholder="Description de la tâche"
                    onchange="updateTaskDescription(${activityIndex}, ${taskIndex}, this.value)"
                >${escapeHtml(task.description || '')}</textarea>
                <select onchange="updateTaskPriority(${activityIndex}, ${taskIndex}, this.value)">
                    <option value="low" ${task.priority === 'low' ? 'selected' : ''}>Faible</option>
                    <option value="medium" ${task.priority === 'medium' ? 'selected' : ''}>Moyenne</option>
                    <option value="high" ${task.priority === 'high' ? 'selected' : ''}>Élevée</option>
                </select>
                <input
                    type="number"
                    placeholder="Heures"
                    value="${task.estimated_hours || ''}"
                    min="1"
                    style="width: 80px;"
                    onchange="updateTaskHours(${activityIndex}, ${taskIndex}, this.value)"
                />
            </div>
        </div>
    `;
}

function updateActivityTitle(activityIndex, value) {
    if (generatedActivities[activityIndex]) {
        generatedActivities[activityIndex].title = value;
    }
}

function updateActivityDescription(activityIndex, value) {
    if (generatedActivities[activityIndex]) {
        generatedActivities[activityIndex].description = value;
    }
}

function updateTaskTitle(activityIndex, taskIndex, value) {
    if (generatedActivities[activityIndex]?.tasks[taskIndex]) {
        generatedActivities[activityIndex].tasks[taskIndex].title = value;
    }
}

function updateTaskDescription(activityIndex, taskIndex, value) {
    if (generatedActivities[activityIndex]?.tasks[taskIndex]) {
        generatedActivities[activityIndex].tasks[taskIndex].description = value;
    }
}

function updateTaskPriority(activityIndex, taskIndex, value) {
    if (generatedActivities[activityIndex]?.tasks[taskIndex]) {
        generatedActivities[activityIndex].tasks[taskIndex].priority = value;
    }
}

function updateTaskHours(activityIndex, taskIndex, value) {
    if (generatedActivities[activityIndex]?.tasks[taskIndex]) {
        generatedActivities[activityIndex].tasks[taskIndex].estimated_hours = value ? parseInt(value) : null;
    }
}

function addNewTask(activityIndex) {
    if (generatedActivities[activityIndex]) {
        const newTask = {
            title: '',
            description: '',
            priority: 'medium',
            estimated_hours: null
        };

        generatedActivities[activityIndex].tasks = generatedActivities[activityIndex].tasks || [];
        generatedActivities[activityIndex].tasks.push(newTask);

        const taskIndex = generatedActivities[activityIndex].tasks.length - 1;
        const tasksContainer = document.getElementById(`tasks-${activityIndex}`);
        tasksContainer.insertAdjacentHTML('beforeend', generateTaskHtml(newTask, activityIndex, taskIndex));
    }
}

function removeTask(activityIndex, taskIndex) {
    if (generatedActivities[activityIndex]?.tasks[taskIndex]) {
        generatedActivities[activityIndex].tasks.splice(taskIndex, 1);
        displayGeneratedActivities(generatedActivities);
    }
}

function removeActivity(activityIndex) {
    generatedActivities.splice(activityIndex, 1);
    displayGeneratedActivities(generatedActivities);
}

function validateActivities() {
    const validateBtn = document.querySelector('#results-state .btn-primary');
    const validateBtnText = document.getElementById('validate-btn-text');

    // Validation
    if (generatedActivities.length === 0) {
        showNotification('Vous devez avoir au moins une activité', 'error');
        return;
    }

    for (let i = 0; i < generatedActivities.length; i++) {
        const activity = generatedActivities[i];
        if (!activity.title?.trim()) {
            showNotification(`L'activité ${i + 1} doit avoir un titre`, 'error');
            return;
        }
        if (!activity.tasks || activity.tasks.length === 0) {
            showNotification(`L'activité "${activity.title}" doit avoir au moins une tâche`, 'error');
            return;
        }

        for (let j = 0; j < activity.tasks.length; j++) {
            const task = activity.tasks[j];
            if (!task.title?.trim()) {
                showNotification(`La tâche ${j + 1} de l'activité "${activity.title}" doit avoir un titre`, 'error');
                return;
            }
        }
    }

    validateBtn.disabled = true;
    validateBtnText.textContent = 'Création en cours...';

    fetch(`/projects/${currentProjectId}/analyze`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ activities: generatedActivities })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        validateBtn.disabled = false;
        validateBtnText.textContent = 'Valider et créer';

        if (data.success) {
            closeAIModal();
            showNotification(`${data.activities_count || generatedActivities.length} activités créées avec succès!`, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showNotification('Erreur: ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        validateBtn.disabled = false;
        validateBtnText.textContent = 'Valider et créer';
        showNotification('Erreur lors de la création des activités', 'error');
    });
}

function closeAIModal() {
    document.getElementById('ai-modal').classList.add('hidden');
    document.getElementById('loading-state').classList.remove('hidden');
    document.getElementById('results-state').classList.add('hidden');
    generatedActivities = [];
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="notification-close">
            <i class="fas fa-times"></i>
        </button>
    `;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 5000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

@endsection
