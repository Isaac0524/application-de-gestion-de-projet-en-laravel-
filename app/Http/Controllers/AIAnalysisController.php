<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use App\Models\Project;
use App\Models\Activity;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Exception;

class AIAnalysisController extends Controller
{
    use AuthorizesRequests;

    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Analyser un projet avec Gemini et créer les activités/tâches
     */
    public function analyze(Request $request, Project $project)
    {
        // Vérifier que l'utilisateur est le propriétaire du projet
        if ($project->owner_id !== $request->user()->id) {
            abort(403, 'Accès interdit : vous devez être le propriétaire du projet.');
        }

        try {
            // Si des activités sont fournies, les créer directement
            if ($request->has('activities') && is_array($request->activities)) {
                $activitiesCount = $this->createActivitiesAndTasks($project, ['activities' => $request->activities]);

                return response()->json([
                    'success' => true,
                    'activities_count' => $activitiesCount,
                    'message' => 'Activités créées avec succès'
                ]);
            }

            // Récupérer les activités et tâches existantes pour le contexte
            $existingActivities = $project->activities->map(function ($activity) {
                return [
                    'title' => $activity->title,
                    'description' => $activity->description,
                    'status' => $activity->status,
                    'tasks' => $activity->tasks->map(function ($task) {
                        return [
                            'title' => $task->title,
                            'description' => $task->description,
                            'status' => $task->status,
                            'priority' => $task->priority,
                            'estimated_hours' => $task->estimated_hours
                        ];
                    })->toArray()
                ];
            })->toArray();

            // Calculer la progression actuelle
            $totalTasks = $project->activities->sum(fn($a) => $a->tasks->count());
            $completedTasks = $project->activities->sum(fn($a) => $a->tasks->whereIn('status', ['completed', 'finalized'])->count());
            $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

            // Sinon, analyser le projet avec Gemini et retourner les résultats
            $analysis = $this->geminiService->analyzeProject(
                $project->title,
                $project->description,
                $project->start_date,
                $project->due_date,
                $project->priority,
                $project->status,
                $existingActivities,
                $progress
            );

            return response()->json([
                'success' => true,
                'analysis' => $analysis
            ]);

        } catch (Exception $e) {
            Log::error('Erreur lors de l\'analyse IA', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de l\'analyse IA: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer les activités et tâches en base de données
     */
    private function createActivitiesAndTasks(Project $project, array $analysis): int
    {
        if (!isset($analysis['activities']) || !is_array($analysis['activities'])) {
            throw new Exception('Format d\'analyse invalide');
        }

        $activitiesCount = 0;

        foreach ($analysis['activities'] as $activityData) {
            // Créer l'activité
            $activity = Activity::create([
                'title' => $activityData['title'] ?? 'Activité sans titre',
                'description' => $activityData['description'] ?? '',
                'project_id' => $project->id,
                'status' => 'in_progress',
                'due_date' => $project->due_date
            ]);

            $activitiesCount++;

            // Créer les tâches
            if (isset($activityData['tasks']) && is_array($activityData['tasks'])) {
                foreach ($activityData['tasks'] as $taskData) {
                    Task::create([
                        'title' => $taskData['title'] ?? 'Tâche sans titre',
                        'description' => $taskData['description'] ?? '',
                        'activity_id' => $activity->id,
                        'priority' => $this->mapPriority($taskData['priority'] ?? 'medium'),
                        'status' => 'pending',
                        'estimated_hours' => $taskData['estimated_hours'] ?? null,
                        'due_date' => $project->due_date
                    ]);
                }
            }
        }

        return $activitiesCount;
    }

    /**
     * Mapper les priorités
     */
    private function mapPriority(string $priority): string
    {
        $priorityMap = [
            'high' => 'high',
            'medium' => 'medium',
            'low' => 'low',
            'élevée' => 'high',
            'moyenne' => 'medium',
            'faible' => 'low'
        ];

        return $priorityMap[strtolower($priority)] ?? 'medium';
    }
}
