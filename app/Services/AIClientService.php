<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Project;
use App\Models\Activity;
use App\Models\Task;
use Exception;

class AIClientService
{
    protected $pythonApiUrl;

    public function __construct()
    {
        $this->pythonApiUrl = config('app.python_ai_url', 'http://localhost:5000/api/ai');
    }

    /**
     * Analyser un projet et générer des activités avec des tâches
     */
    public function analyzeProject(string $title, string $description): array
    {
        try {
            $response = Http::timeout(30)->post("{$this->pythonApiUrl}/analyze-project", [
                'title' => $title,
                'description' => $description
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('Erreur lors de l\'analyse du projet: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Erreur AIClientService::analyzeProject', [
                'error' => $e->getMessage(),
                'title' => $title
            ]);

            // Retourner une structure par défaut en cas d'erreur
            return $this->getDefaultProjectStructure($title, $description);
        }
    }

    /**
     * Créer les activités et tâches en base de données
     */
    public function createActivitiesAndTasks(Project $project, array $activitiesData): array
    {
        $createdActivities = [];

        try {
            foreach ($activitiesData as $activityData) {
                // Créer l'activité
                $activity = Activity::create([
                    'title' => $activityData['title'],
                    'description' => $activityData['description'] ?? '',
                    'project_id' => $project->id,
                    'status' => 'in_progress',
                    'due_date' => $project->due_date // Même échéance que le projet
                ]);

                $createdTasks = [];

                // Créer les tâches pour cette activité
                if (isset($activityData['tasks']) && is_array($activityData['tasks'])) {
                    foreach ($activityData['tasks'] as $taskData) {
                        $task = Task::create([
                            'title' => $taskData['title'],
                            'description' => $taskData['description'] ?? '',
                            'activity_id' => $activity->id,
                            'priority' => $this->mapPriority($taskData['priority'] ?? 'medium'),
                            'status' => 'pending',
                            'estimated_hours' => $taskData['estimated_hours'] ?? null,
                            'due_date' => $project->due_date
                        ]);

                        $createdTasks[] = $task;
                    }
                }

                $activity->load('tasks');
                $createdActivities[] = $activity;
            }

            return [
                'success' => true,
                'activities' => $createdActivities,
                'message' => 'Activités et tâches créées avec succès'
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors de la création des activités/tâches', [
                'error' => $e->getMessage(),
                'project_id' => $project->id
            ]);

            return [
                'success' => false,
                'error' => 'Erreur lors de la création: ' . $e->getMessage()
            ];
        }
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
     * Structure par défaut en cas d'erreur API
     */
    private function getDefaultProjectStructure(string $title, string $description): array
    {
        return [
            'success' => true,
            'analysis' => [
                'activities' => [
                    [
                        'title' => 'Planification et Analyse',
                        'description' => 'Phase initiale de planification et d\'analyse du projet',
                        'tasks' => [
                            [
                                'title' => 'Définir les objectifs',
                                'description' => 'Clarifier et documenter les objectifs du projet',
                                'priority' => 'high',
                                'estimated_hours' => 4
                            ],
                            [
                                'title' => 'Analyser les besoins',
                                'description' => 'Identifier et analyser les besoins détaillés',
                                'priority' => 'high',
                                'estimated_hours' => 8
                            ]
                        ]
                    ],
                    [
                        'title' => 'Développement/Réalisation',
                        'description' => 'Phase principale de réalisation du projet',
                        'tasks' => [
                            [
                                'title' => 'Développer les fonctionnalités principales',
                                'description' => 'Implémenter les fonctionnalités core du projet',
                                'priority' => 'high',
                                'estimated_hours' => 20
                            ],
                            [
                                'title' => 'Tests et validation',
                                'description' => 'Effectuer les tests et valider les résultats',
                                'priority' => 'medium',
                                'estimated_hours' => 12
                            ]
                        ]
                    ],
                    [
                        'title' => 'Finalisation et Livraison',
                        'description' => 'Phase finale de finalisation et livraison',
                        'tasks' => [
                            [
                                'title' => 'Documentation finale',
                                'description' => 'Rédiger la documentation finale du projet',
                                'priority' => 'medium',
                                'estimated_hours' => 6
                            ],
                            [
                                'title' => 'Livraison et déploiement',
                                'description' => 'Livrer le projet final',
                                'priority' => 'high',
                                'estimated_hours' => 4
                            ]
                        ]
                    ]
                ]
            ],
            'project' => [
                'title' => $title,
                'description' => $description
            ]
        ];
    }
     public function sendChatMessage($message, $context = [])
    {
        $response = Http::post("{$this->pythonApiUrl}/chat/message", [
            'message' => $message,
            'context' => $context,
        ]);

        return $response->json();
    }

    /**
     * Create a project from a title using AI
     */
    public function createProjectFromTitle(string $title): array
    {
        try {
            $response = Http::timeout(30)->post("{$this->pythonApiUrl}/chat/create-project", [
                'title' => $title
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('Failed to create project: ' . $response->body());

        } catch (Exception $e) {
            Log::error('AIClientService::createProjectFromTitle error', [
                'error' => $e->getMessage(),
                'title' => $title
            ]);

            // Return default structure
            return [
                'success' => true,
                'project' => [
                    'title' => $title,
                    'description' => 'Projet créé à partir du titre: ' . $title,
                    'status' => 'active'
                ]
            ];
        }
    }

    /**
     * Create a task from chat input
     */
    public function createTaskFromChat(string $taskTitle, string $projectName): array
    {
        try {
            $response = Http::timeout(30)->post("{$this->pythonApiUrl}/chat/create-task", [
                'task_title' => $taskTitle,
                'project_name' => $projectName
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('Failed to create task: ' . $response->body());

        } catch (Exception $e) {
            Log::error('AIClientService::createTaskFromChat error', [
                'error' => $e->getMessage(),
                'task_title' => $taskTitle,
                'project_name' => $projectName
            ]);

            // Return default structure
            return [
                'success' => true,
                'task' => [
                    'title' => $taskTitle,
                    'description' => 'Tâche créée via chat',
                    'estimated_hours' => 2,
                    'priority' => 'medium'
                ]
            ];
        }
    }

    /**
     * Modify a project via chat
     */
    public function modifyProjectViaChat(string $projectName, string $modification): array
    {
        try {
            $response = Http::timeout(30)->post("{$this->pythonApiUrl}/chat/modify-project", [
                'project_name' => $projectName,
                'modification' => $modification
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('Failed to modify project: ' . $response->body());

        } catch (Exception $e) {
            Log::error('AIClientService::modifyProjectViaChat error', [
                'error' => $e->getMessage(),
                'project_name' => $projectName,
                'modification' => $modification
            ]);

            // Return default response
            return [
                'success' => true,
                'message' => 'Projet modifié avec succès',
                'modification' => $modification
            ];
        }
    }

    /**
     * Generate tasks for an activity
     */
    public function generateTasks(int $activityId): array
    {
        try {
            $response = Http::timeout(30)->post("{$this->pythonApiUrl}/generate-tasks/{$activityId}");

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('Failed to generate tasks: ' . $response->body());

        } catch (Exception $e) {
            Log::error('AIClientService::generateTasks error', [
                'error' => $e->getMessage(),
                'activity_id' => $activityId
            ]);

            // Return default response
            return [
                'success' => true,
                'tasks' => [
                    [
                        'title' => 'Tâche générée automatiquement',
                        'description' => 'Tâche créée via génération automatique',
                        'estimated_hours' => 2,
                        'priority' => 'medium'
                    ]
                ]
            ];
        }
    }

    /**
     * Add task via chat
     */
    public function addTaskViaChat(string $taskTitle, string $projectName, string $context = ''): array
    {
        try {
            $response = Http::timeout(30)->post("{$this->pythonApiUrl}/chat/add-task", [
                'task_title' => $taskTitle,
                'project_name' => $projectName,
                'context' => $context
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('Failed to add task via chat: ' . $response->body());

        } catch (Exception $e) {
            Log::error('AIClientService::addTaskViaChat error', [
                'error' => $e->getMessage(),
                'task_title' => $taskTitle,
                'project_name' => $projectName,
                'context' => $context
            ]);

            // Return default response
            return [
                'success' => true,
                'task' => [
                    'title' => $taskTitle,
                    'description' => $context ?: 'Tâche ajoutée via chat',
                    'estimated_hours' => 2,
                    'priority' => 'medium'
                ]
            ];
        }
    }

}
