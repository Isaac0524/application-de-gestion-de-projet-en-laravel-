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

            // En cas d'erreur, retourner une structure de base
            $defaultStructure = $this->getDefaultProjectStructure($project->title, $project->description);

            return response()->json([
                'success' => true,
                'analysis' => $defaultStructure,
                'message' => 'Structure de base générée (analyse IA indisponible)',
                'warning' => 'L\'analyse IA n\'a pas pu être effectuée. Une structure de base a été utilisée à la place.'
            ]);
        }
    }

    /**
     * Demander des recommandations et ajustements pour un projet
     */
    public function recommendAdjustments(Request $request, Project $project)
    {
        try {
            // Construire le payload du projet (informations complètes)
            $project->load('activities.tasks.assignees', 'team.users');

            $activities = [];
            foreach ($project->activities as $activity) {
                $tasks = [];
                foreach ($activity->tasks as $task) {
                    $tasks[] = [
                        'title' => $task->title,
                        'description' => $task->description,
                        'status' => $task->status,
                        'priority' => $task->priority,
                        'estimated_hours' => $task->estimated_hours,
                        'assignees' => $task->assignees->pluck('name')->toArray()
                    ];
                }

                $activities[] = [
                    'title' => $activity->title,
                    'description' => $activity->description,
                    'status' => $activity->status,
                    'tasks' => $tasks
                ];
            }

            $totalTasks = $project->activities->sum(fn($a) => $a->tasks->count());
            $completedTasks = $project->activities->sum(fn($a) => $a->tasks->whereIn('status', ['completed', 'finalized'])->count());
            $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

            $teamMembers = [];
            if ($project->team) {
                $teamMembers = $project->team->users->pluck('name')->toArray();
            }

            $payload = [
                'title' => $project->title,
                'description' => $project->description,
                'start_date' => $project->start_date,
                'due_date' => $project->due_date,
                'status' => $project->status,
                'progress' => $progress,
                'owner' => $project->owner->name ?? null,
                'team_members' => $teamMembers,
                'activities' => $activities
            ];

            // Appeler GeminiService pour obtenir les recommandations
            $recommendations = $this->geminiService->recommendAdjustments($payload);

            return response()->json([
                'success' => true,
                'recommendations' => $recommendations
            ]);

        } catch (Exception $e) {
            Log::error('Erreur lors de la demande de recommandations', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la génération des recommandations: ' . $e->getMessage()
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

    /**
     * Générer une structure de base standard pour le projet
     * (Utilisée en cas d'erreur de l'analyse IA)
     */
    private function getDefaultProjectStructure(string $projectTitle, ?string $projectDescription): array
    {
        return [
            'success' => true,
            'activities' => [
                [
                    'title' => 'Analyse et Conception',
                    'description' => 'Phase de planification et de conception du projet. Définition des objectifs, des besoins, et de l\'architecture globale.',
                    'tasks' => [
                        [
                            'title' => 'Définir les objectifs du projet',
                            'description' => 'Clarifier et documenter les objectifs principaux, les livrables attendus, et les critères de succès.',
                            'priority' => 'high',
                            'estimated_hours' => 4
                        ],
                        [
                            'title' => 'Analyser les besoins et exigences',
                            'description' => 'Identifier et documenter tous les besoins fonctionnels et non-fonctionnels du projet.',
                            'priority' => 'high',
                            'estimated_hours' => 8
                        ],
                        [
                            'title' => 'Concevoir l\'architecture',
                            'description' => 'Proposer une architecture et un plan détaillé pour le projet.',
                            'priority' => 'high',
                            'estimated_hours' => 6
                        ],
                        [
                            'title' => 'Planifier les ressources',
                            'description' => 'Identifier les ressources nécessaires (équipe, outils, budget, temps).',
                            'priority' => 'medium',
                            'estimated_hours' => 3
                        ]
                    ]
                ],
                [
                    'title' => 'Développement et Réalisation',
                    'description' => 'Phase de réalisation des fonctionnalités et de la mise en œuvre technique du projet.',
                    'tasks' => [
                        [
                            'title' => 'Configurer l\'environnement de développement',
                            'description' => 'Mettre en place les outils, les environnements, et les dépendances nécessaires.',
                            'priority' => 'high',
                            'estimated_hours' => 4
                        ],
                        [
                            'title' => 'Développer les fonctionnalités principales',
                            'description' => 'Implémenter les fonctionnalités core et les modules principaux du projet.',
                            'priority' => 'high',
                            'estimated_hours' => 24
                        ],
                        [
                            'title' => 'Intégrer les modules',
                            'description' => 'Intégrer les différents modules et composants développés.',
                            'priority' => 'high',
                            'estimated_hours' => 8
                        ],
                        [
                            'title' => 'Optimisation et refactoring',
                            'description' => 'Optimiser le code, améliorer les performances, et nettoyer le code legacy.',
                            'priority' => 'medium',
                            'estimated_hours' => 6
                        ]
                    ]
                ],
                [
                    'title' => 'Tests et Assurance Qualité',
                    'description' => 'Phase de validation et de test pour assurer la qualité et la stabilité du projet.',
                    'tasks' => [
                        [
                            'title' => 'Effectuer les tests unitaires',
                            'description' => 'Créer et exécuter les tests unitaires pour valider les composants individuels.',
                            'priority' => 'high',
                            'estimated_hours' => 8
                        ],
                        [
                            'title' => 'Effectuer les tests d\'intégration',
                            'description' => 'Tester l\'intégration entre les différents modules et composants.',
                            'priority' => 'high',
                            'estimated_hours' => 8
                        ],
                        [
                            'title' => 'Effectuer les tests de performance',
                            'description' => 'Valider les performances, la scalabilité, et la charge du système.',
                            'priority' => 'medium',
                            'estimated_hours' => 4
                        ],
                        [
                            'title' => 'Créer la documentation de test',
                            'description' => 'Documenter les cas de test, les résultats, et les recommandations.',
                            'priority' => 'medium',
                            'estimated_hours' => 3
                        ]
                    ]
                ],
                [
                    'title' => 'Finalisation et Livraison',
                    'description' => 'Phase de préparation finale, déploiement, et livraison du projet.',
                    'tasks' => [
                        [
                            'title' => 'Préparer la documentation utilisateur',
                            'description' => 'Rédiger les guides d\'utilisation, les manuels, et la documentation pour les utilisateurs finaux.',
                            'priority' => 'medium',
                            'estimated_hours' => 6
                        ],
                        [
                            'title' => 'Préparer le déploiement',
                            'description' => 'Configurer l\'environnement de production et préparer le plan de déploiement.',
                            'priority' => 'high',
                            'estimated_hours' => 4
                        ],
                        [
                            'title' => 'Livrer et déployer',
                            'description' => 'Effectuer le déploiement en production et assurer la transition en douceur.',
                            'priority' => 'high',
                            'estimated_hours' => 3
                        ],
                        [
                            'title' => 'Recettes finales et validation',
                            'description' => 'Effectuer les recettes avec les stakeholders et valider la conformité.',
                            'priority' => 'medium',
                            'estimated_hours' => 4
                        ]
                    ]
                ]
            ]
        ];
    }
}
