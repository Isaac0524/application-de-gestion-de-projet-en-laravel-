<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Services\AIClientService;
use Carbon\Carbon;
class ProjectController extends Controller
{
    protected $aiService;

    public function __construct(AIClientService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $projects = $user->isManager()
            ? Project::where('owner_id',$user->id)->latest()->paginate(10)
            : Project::whereHas('activities.tasks.assignees', fn($q)=>$q->where('users.id',$user->id))->latest()->paginate(10);
        return view('projects.index', compact('projects','user'));
    }

    public function create(Request $request)
    {
        if (!$request->user()->isManager()) abort(403);
        return view('projects.create');
    }

    public function store(Request $request)
    {
        if (!$request->user()->isManager()) abort(403);
        $data = $request->validate([
            'title'=>'required|string|max:255|unique:projects,title',
            'description'=>'nullable|string',
            'start_date'=>'required|date|after_or_equal:today',
            'due_date'=>'required|date|after:start_date'
        ]);
        $data['owner_id'] = $request->user()->id;

        // Set status based on start date
        $startDate = Carbon::parse($data['start_date']);
        $data['status'] = $startDate->isToday() ? 'in_progress' : 'pending';

        $project = Project::create($data);
        return redirect()->route('projects.show',$project)->with('ok','Projet créé');
    }

    public function show(Project $project, Request $request)
    {
        $project->load('activities.tasks.assignees');
        $user = $request->user();
        if (!$user->isManager() && $project->owner_id !== $user->id) {
            $allowed = $project->activities()->whereHas('tasks.assignees', fn($q)=>$q->where('users.id',$user->id))->exists();
            if (!$allowed) abort(403);
        }
        return view('projects.show', compact('project','user'));
    }

    /**
     * Analyser le projet avec IA et générer des activités/tâches
     */
    public function analyzeProject(Project $project, Request $request)
    {
        // Vérifier les permissions
        if ($project->owner_id !== $request->user()->id) {
            abort(403, 'Non autorisé');
        }

        try {
            // Appeler l'API AI pour analyser le projet
            $analysis = $this->aiService->analyzeProject(
                $project->title,
                $project->description ?? ''
            );

            if (!$analysis['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'analyse du projet'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'project' => $project,
                'analysis' => $analysis['analysis'],
                'message' => 'Analyse terminée avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'analyse du projet', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'analyse du projet: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer les activités et tâches après validation utilisateur
     */
    public function createActivitiesFromAnalysis(Project $project, Request $request)
    {
        // Vérifier les permissions
        if ($project->owner_id !== $request->user()->id) {
            abort(403, 'Non autorisé');
        }

        $request->validate([
            'activities' => 'required|array|min:1',
            'activities.*.title' => 'required|string|max:255',
            'activities.*.description' => 'nullable|string',
            'activities.*.tasks' => 'required|array|min:1',
            'activities.*.tasks.*.title' => 'required|string|max:255',
            'activities.*.tasks.*.description' => 'nullable|string',
            'activities.*.tasks.*.priority' => 'required|in:low,medium,high',
            'activities.*.tasks.*.estimated_hours' => 'nullable|integer|min:1'
        ]);

        try {
            $result = $this->aiService->createActivitiesAndTasks(
                $project,
                $request->activities
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'activities_count' => count($result['activities']),
                    'redirect_url' => route('projects.show', $project)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création des activités/tâches', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(Project $project, Request $request)
    {
        if ($project->owner_id !== $request->user()->id) abort(403);
        return view('projects.edit', compact('project'));
    }

    private function allActivitiesCompleted(Project $project): bool
    {
        $activities = $project->activities;
        if ($activities->isEmpty()) {
            return false;
        }

        return $activities->every(function ($activity) {
            return $activity->status === 'completed';
        });
    }

    public function checkAndReopenProject(Project $project): void
    {
        $activities = $project->activities;

        if ($activities->isEmpty()) {
            return;
        }

        $inProgressActivities = $activities->filter(function ($activity) {
            return $activity->status === 'in_progress';
        })->count();

        if ($inProgressActivities > 0 && $project->status !== 'in_progress') {
            $project->status = 'in_progress';
            $project->save();
        }
    }

    public function update(Project $project, Request $request)
    {
        if ($project->owner_id !== $request->user()->id) abort(403);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date|after_or_equal:today',
            'due_date'    => 'required|date|after:start_date',
            'status'      => 'required|in:pending,in_progress,archived,completed'
        ]);

        // Vérifier si on veut archiver un projet pas encore terminé
        if ($data['status'] === 'archived' && $project->status !== 'completed') {
            return back()->withErrors([
                'status' => 'Un projet ne peut être archivé que s\'il est d\'abord marqué comme terminé.'
            ])->withInput();
        }

        // Forcer le statut en fonction de la date de début
        $startDate = Carbon::parse($data['start_date']);
        if ($data['status'] !== 'archived') { // on ne touche pas si on archive volontairement
            $data['status'] = $startDate->isToday() ? 'in_progress' : 'pending';
        }

        $project->update($data);

        return redirect()->route('projects.show', $project)
                        ->with('ok', 'Projet mis à jour');
    }

}
