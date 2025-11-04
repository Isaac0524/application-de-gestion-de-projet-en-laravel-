@extends('layout')
@section('content')
    <style>
        /* Responsive Project Dashboard Styles */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        .projects-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f9fafb;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
            border-radius: 0.375rem;
        }

        .projects-header h1 {
            font-size: 2.25rem;
            font-weight: bold;
            color: #1f2937;
            margin: 0;
        }

        .search-bar {
            position: relative;
            max-width: 400px;
            width: 100%;
        }

        .search-bar input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            outline: none;
            transition: all 0.2s;
        }

        .search-bar input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-bar i {
            position: absolute;
            top: 50%;
            left: 0.75rem;
            transform: translateY(-50%);
            color: #6b7280;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.375rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2563eb;
        }

        .dashboard {
            display: flex;
            gap: 1.5rem;
        }

        .projects-list {
            flex: 2;
            min-width: 0;
        }

        .project-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            transition: all 0.2s;
        }

        .project-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .project-info {
            flex: 1;
        }

        .project-info h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 0.5rem 0;
        }

        .project-meta {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background-color: #6366f1;
            border-radius: 4px;
            transition: width 0.3s ease-in-out;
        }

        .team-members {
            display: flex;
            gap: 0.25rem;
            align-items: center;
        }

        .team-member {
            width: 32px;
            height: 32px;
            background-color: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .project-details {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 0.375rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .badge-in_progress {
            background-color: #10b981;
            color: white;
        }

        .badge-pending {
            background-color: #f59e0b;
            color: white;
        }


        .badge-completed {
            background-color: #6366f1;
            color: white;
        }

        .budget {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .calendar-container {
            flex: 1;
            min-width: 300px;
        }

        #calendar {
            max-width: 100%;
            height: 600px;
            background: #f9fafb;
            border-radius: 0.5rem;
            padding: 1rem;
        }

        .fc {
            --fc-border-color: transparent;
            --fc-daygrid-event-dot-width: 8px;
            --fc-daygrid-event-dot-height: 8px;
        }

        .fc-daygrid-day-number {
            color: #374151;
        }

        .fc-daygrid-day:hover .fc-daygrid-day-number {
            background-color: #e5e7eb;
            border-radius: 50%;
        }

        .fc-daygrid-day-top {
            margin-bottom: 0.25rem;
        }

        .fc-daygrid-dot {
            border: none;
            background-color: #ef4444;
            margin: 0;
        }

        .fc-daygrid-event-dot {
            border: none;
            background-color: #ef4444;
            width: 6px;
            height: 6px;
            margin-top: 2px;
        }

        .fc-daygrid-day:hover .fc-daygrid-event-dot {
            background-color: #dc2626;
        }

        .fc .fc-daygrid-day:hover {
            background: rgba(229, 231, 235, 0.5);
            border-radius: 0.25rem;
        }

        .fc-daygrid-day.fc-day-today {
            background: rgba(59, 130, 246, 0.1);
            border-radius: 0.25rem;
        }

        .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
            color: #3b82f6;
            font-weight: 600;
        }

        /* Tooltip Styles */
        .fc-daygrid-day {
            position: relative;
        }

        .fc-daygrid-event-dot-tooltip {
            visibility: hidden;
            width: 120px;
            background-color: #1f2937;
            color: #fff;
            text-align: center;
            border-radius: 0.25rem;
            padding: 0.25rem 0.5rem;
            position: absolute;
            z-index: 1;
            bottom: 100%;
            left: 50%;
            margin-left: -60px;
            font-size: 0.75rem;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .fc-daygrid-day:hover .fc-daygrid-event-dot-tooltip {
            visibility: visible;
            opacity: 1;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .dashboard {
                flex-direction: column;
            }

            .calendar-container {
                order: 2;
            }

            .projects-list {
                order: 1;
            }

            #calendar {
                height: 400px;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: stretch;
            }

            .header h1 {
                font-size: 1.75rem;
            }

            .btn,
            .search-bar {
                width: 100%;
                justify-content: center;
            }

            .project-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .project-details {
                text-align: left;
            }

            .team-members {
                margin-top: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0.75rem;
            }

            .project-card {
                padding: 1rem;
            }

            #calendar {
                height: 300px;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: [
                    @foreach ($projects as $project)
                        @if ($project->due_date)
                            {
                                title: '{{ $project->title }}',
                                start: '{{ \Carbon\Carbon::parse($project->due_date)->format('Y-m-d') }}',
                                allDay: true,
                                display: 'list-item',
                                className: 'fc-event-dot'
                            },
                        @endif
                    @endforeach
                ],
                eventDidMount: function(info) {
                    const dot = info.el.querySelector('.fc-daygrid-event-dot');
                    if (dot) {
                        const tooltip = document.createElement('div');
                        tooltip.className = 'fc-daygrid-event-dot-tooltip';
                        tooltip.textContent = info.event.title;
                        info.el.parentElement.appendChild(tooltip);
                    }
                },
                dayMaxEvents: true,
                moreLinkClick: 'popover'
            });
            calendar.render();

            const searchInput = document.querySelector('#project-search');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                document.querySelectorAll('.project-card').forEach(card => {
                    const title = card.querySelector('.project-info h3').textContent.toLowerCase();
                    card.style.display = title.includes(searchTerm) ? '' : 'none';
                });
            });
        });
    </script>

    <div class="container">
        <div class="projects-header">
            <h1>Projets Récents</h1>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" id="project-search" placeholder="Rechercher un projet...">
                </div>
                @if ($user->isManager())
                    <a class="btn btn-primary" href="{{ route('projects.create') }}">
                        <i class="fas fa-plus"></i> Nouveau Projet
                    </a>
                @endif
            </div>
        </div>

        <div class="dashboard">
            <div class="projects-list">
                @foreach ($projects as $project)
                    <div class="project-card">
                        <!-- Première ligne: Titre à gauche, Statut à droite -->
                        <h3 style="font-size: 1.125rem; font-weight: 600; color: #1f2937; margin: 0 0 0.25rem 0;">
                            {{ $project->title }}
                            <span class="badge badge-{{ $project->status }}"
                                style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; margin-left: 0.5rem;">
                                @if ($project->status === 'in_progress')
                                    EN COURS
                                @elseif ($project->status === 'in_revision')
                                    EN RÉVISION
                                @elseif ($project->status === 'pending')
                                    EN ATTENTE
                                @elseif ($project->status === 'completed')
                                    TERMINÉ
                                @elseif ($project->status === 'archived')
                                    ARCHIVÉ
                                @else
                                    {{ strtoupper($project->status) }}
                                @endif
                            </span>
                        </h3>
                        <div>
                            <a href="{{ route('projects.show', $project->id) }}">
                                <i class="fas fa-eye" style="color: #3b82f6;"></i>
                            </a>
                            @if ($user->isManager())
                            <a href="{{ route('projects.edit', $project->id) }}" style="margin-left: 0.5rem;">
                                <i class="fas fa-edit" style="color: #f59e0b;"></i>
                            </a>
                            @endif
                        </div>

                        <!-- Deuxième ligne: Barre de progression complète -->
                        <div style="margin: 1rem 0;">
                            <?php
                            $totalTasks = $project->activities->flatMap->tasks->count();
                            $completedTasks = $project->activities->flatMap->tasks->where('status', 'finalized')->count();
                            $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                            ?>
                            <div style="margin-bottom: 0.25rem;">
                                <span style="font-size: 0.875rem; color: #374151; font-weight: 500;">{{ $progress }}%
                                    complété</span>
                            </div>
                            <div class="progress-bar" style="height: 8px; border-radius: 4px;">
                                <div class="progress-bar-fill"
                                    style="width: {{ $progress }}%; background-color: #6366f1; border-radius: 4px;">
                                </div>
                            </div>
                        </div>

                        <!-- Troisième ligne: Badges des membres à gauche, Budget à droite -->
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div class="team-members">
                                @foreach ($project->activities->flatMap->tasks->flatMap->assignees->unique('id')->take(4) as $assignee)
                                    <div class="team-member" title="{{ $assignee->name }}"
                                        style="width: 32px; height: 32px; font-size: 0.75rem; font-weight: 600; background-color: #ef4444;">
                                        {{ strtoupper(substr($assignee->name, 0, 2)) }}
                                    </div>
                                @endforeach
                                @if ($project->activities->flatMap->tasks->flatMap->assignees->count() > 4)
                                    <div class="team-member"
                                        title="{{ $project->activities->flatMap->tasks->flatMap->assignees->count() - 4 }} autres"
                                        style="width: 32px; height: 32px; font-size: 0.75rem; font-weight: 600; background-color: #ef4444;">
                                        +{{ $project->activities->flatMap->tasks->flatMap->assignees->count() - 4 }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
                @if ($projects->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <h3>Aucun projet</h3>
                        <p>Aucun projet n'a été trouvé. Créez un nouveau projet pour commencer.</p>
                    </div>
                @endif
            </div>

            <div class="calendar-container">
                <div class="card">
                    <div class="card-content">
                        <h2 style="font-size: 1.5rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem;">Calendrier des
                            échéances</h2>
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
