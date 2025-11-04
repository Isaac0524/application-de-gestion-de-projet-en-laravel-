<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use App\Models\Activity;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Données communes
        $myTasks = $user->assignedTasks()
                ->with('activity.project')
                ->latest()
                ->take(10)
                ->get();
        // Projets de l'utilisateur
        $projects = $user->isManager()
            ? Project::with(['activities', 'activities.tasks'])
                ->withCount(['activities', 'tasks'])
                ->where('owner_id', $user->id)
                ->latest()
                ->get()
            : Project::with(['activities', 'activities.tasks'])
                ->withCount(['activities', 'tasks'])
                ->whereHas('activities.tasks.assignees', function ($query) use ($user) {
                    $query->where('users.id', $user->id);
                })
                ->latest()
                ->get();

        // Données spécifiques aux managers
        $stats = [];
        $projectsKanban = [];
        $recentActivities = [];
        $upcomingDeadlines = [];

        if ($user->isManager()) {
            // Statistiques générales
            $stats = [
                'total_projects' => Project::count(),
                'total_activities' => Activity::count(),
                'total_tasks' => Task::count(),
                'total_users' => User::count(),
                'projects_by_status' => Project::selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')->pluck('count', 'status')->toArray(),
                'tasks_by_status' => Task::selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')->pluck('count', 'status')->toArray(),
                'tasks_by_specific_status' => [
                    'open' => Task::where('status', 'open')->count(),
                    'in_progress' => Task::where('status', 'in_progress')->count(),
                    'completed_by_assignee' => Task::where('status', 'completed_by_assignee')->count(),
                    'finalized' => Task::where('status', 'finalized')->count(),
                ],
                'projects_this_month' => Project::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)->count(),
                'completed_projects' => Project::where('status', 'completed')->count(),
                'tasks_by_member' => User::where('role', 'member')
                    ->withCount(['assignedTasks as completed_tasks' => function ($query) {
                        $query->where('status', 'completed');
                    }])
                    ->withCount(['assignedTasks as in_progress_tasks' => function ($query) {
                        $query->where('status', 'in_progress');
                    }])
                    ->get()
                    ->map(function ($user) {
                        return [
                            'name' => $user->name,
                            'completed_tasks' => $user->completed_tasks,
                            'in_progress_tasks' => $user->in_progress_tasks,
                        ];
                    })
                    ->toArray(),
            ];

            // Projets pour Kanban
            $projectsKanban = [

                'pending' => Project::with('owner')->where('status', 'pending')->latest()->get(),
                'in_progress' => Project::with('owner')->where('status', 'in_progress')->latest()->get(),
                'completed' => Project::with('owner')->where('status', 'completed')->latest()->get(),
                'archived' => Project::with('owner')->where('status', 'archived')->latest()->get(),
            ];

            // Activités récentes
            $recentActivities = Activity::with(['project', 'tasks'])
                ->latest()->limit(5)->get();

            // Échéances à venir
            $upcomingDeadlines = Project::whereNotNull('due_date')
                ->where('due_date', '>=', now())
                ->where('due_date', '<=', now()->addDays(7))
                ->where('status', '!=', 'completed')
                ->orderBy('due_date')
                ->get();

            // Alertes système
            $alerts = [
                // Alertes vertes: projets qui commencent aujourd'hui
                'starting_today' => Project::where('status', 'pending')
                    ->whereDate('start_date', now()->toDateString())
                    ->with('owner')
                    ->get(),

                // Alertes rouges: projets en retard (1 semaine avant échéance)
                'due_soon' => Project::whereNotNull('due_date')
                    ->where('due_date', '>=', now())
                    ->where('due_date', '<=', now()->addDays(7))
                    ->where('status', '!=', 'completed')
                    ->with('owner')
                    ->get(),

                // Projets déjà en retard (échéance passée)
                'overdue' => Project::whereNotNull('due_date')
                    ->where('due_date', '<', now())
                    ->where('status', '!=', 'completed')
                    ->with('owner')
                    ->get()
            ];
        } else {
            // Statistiques pour les membres
            $userProjectIds = $projects->pluck('id');
            $stats = [
                'my_projects' => $projects->count(),
                'my_tasks' => $user->assignedTasks()->count(),
                'pending_tasks' => $user->assignedTasks()->where('status', 'pending')->count(),
                'in_progress_tasks' => $user->assignedTasks()->where('status', 'in_progress')->count(),
                'completed_tasks' => $user->assignedTasks()->where('status', 'completed')->count(),
                'tasks_by_specific_status' => [
                    'open' => $user->assignedTasks()->where('status', 'open')->count(),
                    'in_progress' => $user->assignedTasks()->where('status', 'in_progress')->count(),
                    'completed_by_assignee' => $user->assignedTasks()->where('status', 'completed_by_assignee')->count(),
                    'finalized' => $user->assignedTasks()->where('status', 'finalized')->count(),
                ],
            ];

            // Échéances des tâches de l'utilisateur
            $upcomingDeadlines = Task::whereHas('assignees', fn($q) => $q->where('users.id', $user->id))
                ->whereNotNull('due_date')
                ->where('due_date', '>=', now())
                ->where('due_date', '<=', now()->addDays(7))
                ->where('status', '!=', 'completed')
                ->with('activity.project')
                ->orderBy('due_date')
                ->get();
        }

        return view('dashboard', compact(
            'myTasks',
            'projects',
            'user',
            'stats',
            'projectsKanban',
            'recentActivities',
            'upcomingDeadlines',
            'alerts'
        ));
    }
}
