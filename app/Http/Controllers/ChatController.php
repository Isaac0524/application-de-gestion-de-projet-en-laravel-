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

class ChatController extends Controller
{
    use AuthorizesRequests;

    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Handle AI chat messages
     */
    public function handleMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'context' => 'array'
        ]);

        $message = trim($request->input('message'));
        $context = $request->input('context', []);

        try {
            // Check if message starts with a command
            if (strpos($message, '/') === 0) {
                return $this->handleCommand($message);
            }

            // Handle natural language conversation
            return $this->handleNaturalLanguage($message, $context);

        } catch (Exception $e) {
            Log::error('ChatController::handleMessage error', [
                'message' => $message,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'reply' => 'Désolé, une erreur est survenue. Veuillez réessayer.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle command-based messages (starting with /)
     */
    protected function handleCommand(string $message)
    {
        $parts = explode(' ', $message, 2);
        $command = strtolower($parts[0]);
        $parameters = $parts[1] ?? '';

        switch ($command) {
            case '/help':
                return $this->showHelp();
            case '/create-project':
                return $this->createProjectCommand($parameters);
            case '/list-projects':
                return $this->listProjectsCommand();
            case '/project-status':
                return $this->projectStatusCommand($parameters);
            default:
                return response()->json([
                    'reply' => $this->formatMessage([
                        'type' => 'error',
                        'content' => "Commande inconnue : <strong>{$command}</strong>",
                        'footer' => "Utilisez <code>/help</code> pour voir les commandes disponibles"
                    ])
                ]);
        }
    }

    /**
     * Handle natural language messages
     */
    protected function handleNaturalLanguage(string $message, array $context)
    {
        // Check for activity creation intent
        if ($this->isActivityCreationRequest($message)) {
            return $this->handleActivityCreation($message);
        }

        // Check for simple greetings and casual conversation
        if ($this->isCasualConversation($message)) {
            return $this->handleCasualConversation($message);
        }

        // General AI conversation
        $prompt = $this->buildChatPrompt($message, $context);

        try {
            $response = $this->geminiService->chat($prompt);

            return response()->json([
                'reply' => $response
            ]);

        } catch (Exception $e) {
            Log::error('Natural language processing failed', [
                'message' => $message,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'reply' => $this->formatMessage([
                    'type' => 'error',
                    'content' => "Je n'ai pas pu traiter votre demande.",
                    'footer' => "Utilisez <code>/help</code> pour voir les commandes disponibles"
                ])
            ]);
        }
    }

    /**
     * Check if message is casual conversation (greetings, etc.)
     */
    protected function isCasualConversation(string $message): bool
    {
        $casualKeywords = [
            'bonjour', 'salut', 'hello', 'hi', 'hey', 'coucou',
            'ça va', 'comment ça va', 'how are you', 'what\'s up',
            'merci', 'thanks', 'thank you', 'au revoir', 'bye',
            'bonne journée', 'good day', 'à bientôt', 'see you',
            'comment vas-tu', 'how do you do', 'enchanté', 'nice to meet you'
        ];

        $lowerMessage = strtolower($message);
        foreach ($casualKeywords as $keyword) {
            if (strpos($lowerMessage, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle casual conversation
     */
    protected function handleCasualConversation(string $message)
    {
        $lowerMessage = strtolower($message);

        // Greetings
        if (strpos($lowerMessage, 'bonjour') !== false || strpos($lowerMessage, 'salut') !== false ||
            strpos($lowerMessage, 'hello') !== false || strpos($lowerMessage, 'hi') !== false ||
            strpos($lowerMessage, 'hey') !== false || strpos($lowerMessage, 'coucou') !== false) {
            return response()->json([
                'reply' => $this->formatMessage([
                    'type' => 'greeting',
                    'icon' => '👋',
                    'content' => "Bonjour ! Je suis votre assistant IA pour la gestion de projets.",
                    'footer' => "Tapez <code>/help</code> pour découvrir les commandes disponibles"
                ])
            ]);
        }

        // How are you
        if (strpos($lowerMessage, 'ça va') !== false || strpos($lowerMessage, 'comment ça va') !== false ||
            strpos($lowerMessage, 'how are you') !== false || strpos($lowerMessage, 'comment vas-tu') !== false) {
            return response()->json([
                'reply' => $this->formatMessage([
                    'type' => 'info',
                    'icon' => '😊',
                    'content' => "Je vais très bien, merci ! Je suis prêt à vous aider avec vos projets.",
                    'footer' => "Que souhaitez-vous faire ?"
                ])
            ]);
        }

        // Thanks
        if (strpos($lowerMessage, 'merci') !== false || strpos($lowerMessage, 'thanks') !== false ||
            strpos($lowerMessage, 'thank you') !== false) {
            return response()->json([
                'reply' => $this->formatMessage([
                    'type' => 'info',
                    'icon' => '😊',
                    'content' => "De rien ! N'hésitez pas si vous avez besoin d'aide avec vos projets."
                ])
            ]);
        }

        // Goodbye
        if (strpos($lowerMessage, 'au revoir') !== false || strpos($lowerMessage, 'bye') !== false ||
            strpos($lowerMessage, 'bonne journée') !== false || strpos($lowerMessage, 'à bientôt') !== false ||
            strpos($lowerMessage, 'see you') !== false) {
            return response()->json([
                'reply' => $this->formatMessage([
                    'type' => 'info',
                    'icon' => '👋',
                    'content' => "Au revoir ! Passez une excellente journée. À bientôt !"
                ])
            ]);
        }

        // Nice to meet you
        if (strpos($lowerMessage, 'enchanté') !== false || strpos($lowerMessage, 'nice to meet you') !== false) {
            return response()->json([
                'reply' => $this->formatMessage([
                    'type' => 'info',
                    'icon' => '🤝',
                    'content' => "Enchanté également ! Je suis ravi de vous aider avec vos projets de gestion."
                ])
            ]);
        }

        // Default casual response
        return response()->json([
            'reply' => $this->formatMessage([
                'type' => 'info',
                'icon' => '💬',
                'content' => "Ravi de discuter avec vous ! Je suis là pour vous aider avec la gestion de vos projets.",
                'footer' => "Que puis-je faire pour vous ?"
            ])
        ]);
    }

    /**
     * Show available commands
     */
    protected function showHelp()
    {
        $helpContent = '
            <div class="ai-help-container">
                <div class="ai-help-header">
                    <span class="ai-help-icon">📚</span>
                    <h4>Commandes disponibles</h4>
                </div>
                
                <div class="ai-help-section">
                    <div class="ai-help-section-title">
                        <span class="icon">🎯</span>
                        <strong>Gestion des projets</strong>
                    </div>
                    <div class=\"ai-help-commands\">
                        <div class=\"ai-help-command\">
                            <code>/create-project [titre] | [description] | [date_debut] | [date_fin]</code>
                            <span>Créer un nouveau projet</span>
                        </div>
                        <div class=\"ai-help-command\">
                            <code>/list-projects</code>
                            <span>Lister tous vos projets</span>
                        </div>
                        <div class=\"ai-help-command\">
                            <code>/project-status [nom]</code>
                            <span>Voir le statut du projet</span>
                        </div>
                    </div>
                </div>
            </div>
        ';

        return response()->json([
            'reply' => $helpContent,
            'isFormatted' => true
        ]);
    }

    /**
     * Create a new project from command
     */
    protected function createProjectCommand(string $parameters)
    {
        if (empty(trim($parameters))) {
            return response()->json([
                'reply' => $this->formatMessage([
                    'type' => 'error',
                    'content' => "Veuillez spécifier les détails du projet",
                    'footer' => "Usage : <code>/create-project [titre] | [description] | [date_debut] | [date_fin]</code><br><em>Format des dates : YYYY-MM-DD</em>"
                ])
            ]);
        }

        try {
            // Parse parameters separated by |
            $parts = array_map('trim', explode('|', $parameters));
            
            $title = $parts[0] ?? null;
            $description = $parts[1] ?? null;
            $startDate = $parts[2] ?? null;
            $endDate = $parts[3] ?? null;

            // Validate title
            if (!$title) {
                return response()->json([
                    'reply' => $this->formatMessage([
                        'type' => 'error',
                        'content' => "Le titre du projet est obligatoire"
                    ])
                ]);
            }

            // Check if project already exists
            if (Project::where('title', $title)->exists()) {
                return response()->json([
                    'reply' => $this->formatMessage([
                        'type' => 'error',
                        'content' => "Un projet avec ce nom existe deja : <strong>" . htmlspecialchars($title) . "</strong>"
                    ])
                ]);
            }

            // Set default dates if not provided
            if (!$startDate) {
                $startDate = now()->toDateString();
            }
            if (!$endDate) {
                $endDate = now()->addDays(30)->toDateString();
            }

            // Validate date format
            try {
                $startDateParsed = \Carbon\Carbon::createFromFormat('Y-m-d', $startDate);
                $endDateParsed = \Carbon\Carbon::createFromFormat('Y-m-d', $endDate);
            } catch (\Exception $e) {
                return response()->json([
                    'reply' => $this->formatMessage([
                        'type' => 'error',
                        'content' => "Format de date invalide",
                        'footer' => "Utilisez le format YYYY-MM-DD (ex: 2025-12-25)"
                    ])
                ]);
            }

            // Validate date logic
            if ($startDateParsed > $endDateParsed) {
                return response()->json([
                    'reply' => $this->formatMessage([
                        'type' => 'error',
                        'content' => "La date de fin doit etre apres la date de debut"
                    ])
                ]);
            }

            // Create project with transaction
            \DB::beginTransaction();
            try {
                $project = Project::create([
                    'title' => $title,
                    'description' => $description,
                    'owner_id' => auth()->id(),
                    'status' => $startDateParsed->isToday() || $startDateParsed->isPast() ? 'in_progress' : 'pending',
                    'start_date' => $startDate,
                    'due_date' => $endDate
                ]);

                // Create associated team
                $team = \App\Models\Team::create([
                    'name' => 'Team - ' . $project->title,
                    'description' => 'Equipe automatique pour le projet: ' . $project->title,
                    'project_id' => $project->id
                ]);

                \DB::commit();

                $successContent = '
                    <div class="ai-project-created">
                        <div class="ai-created-header">
                            <span class="icon">✅</span>
                            <strong>Projet cree avec succes !</strong>
                        </div>
                        <div class="ai-created-details">
                            <div class="ai-created-item">
                                <span class="label">Titre</span>
                                <span class="value">' . htmlspecialchars($title) . '</span>
                            </div>
                            <div class="ai-created-item">
                                <span class="label">Statut</span>
                                <span class="value">' . ($startDateParsed->isToday() || $startDateParsed->isPast() ? 'En cours' : 'En attente') . '</span>
                            </div>
                            <div class="ai-created-item">
                                <span class="label">Debut</span>
                                <span class="value">' . $startDateParsed->format('d/m/Y') . '</span>
                            </div>
                            <div class="ai-created-item">
                                <span class="label">Fin prevue</span>
                                <span class="value">' . $endDateParsed->format('d/m/Y') . '</span>
                            </div>
                            <div class="ai-created-item">
                                <span class="label">Equipe</span>
                                <span class="value">' . htmlspecialchars($team->name) . '</span>
                            </div>
                        </div>
                    </div>
                ';

                return response()->json([
                    'reply' => $successContent,
                    'isFormatted' => true,
                    'project' => [
                        'id' => $project->id,
                        'title' => $project->title,
                        'status' => $project->status
                    ]
                ]);

            } catch (\Exception $e) {
                \DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('ChatController::createProjectCommand error', [
                'parameters' => $parameters,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'reply' => $this->formatMessage([
                    'type' => 'error',
                    'content' => "Erreur lors de la creation du projet",
                    'footer' => "Detail : " . $e->getMessage()
                ])
            ]);
        }
    }

    /**
     * List user projects
     */
    protected function listProjectsCommand()
    {
        $projects = Project::where('owner_id', auth()->id())->get();

        if ($projects->isEmpty()) {
            return response()->json([
                'reply' => $this->formatMessage([
                    'type' => 'info',
                    'icon' => '📋',
                    'content' => "Aucun projet trouvé. Créez d'abord un projet."
                ])
            ]);
        }

        $projectsList = '<div class="ai-projects-list">';
        $projectsList .= '<div class="ai-list-header"><span class="icon">📋</span><strong>Vos projets</strong></div>';
        
        foreach ($projects as $project) {
            $activityCount = $project->activities->count();
            $completedTasks = $project->activities->sum(fn($a) => $a->tasks->whereIn('status', ['completed', 'finalized'])->count());
            $totalTasks = $project->activities->sum(fn($a) => $a->tasks->count());
            $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

            $statusIcon = match($project->status) {
                'completed' => '✅',
                'in_progress' => '🔄',
                'pending' => '⏳',
                default => '📌'
            };

            $projectsList .= '
                <div class="ai-project-item">
                    <div class="ai-project-header">
                        <span class="status-icon">' . $statusIcon . '</span>
                        <strong>' . htmlspecialchars($project->title) . '</strong>
                    </div>
                    <div class="ai-project-stats">
                        <span class="stat"><span class="icon">📊</span>' . $activityCount . ' activités</span>
                        <span class="stat"><span class="icon">✓</span>' . $completedTasks . '/' . $totalTasks . ' tâches</span>
                        <span class="stat"><span class="icon">📈</span>' . $progress . '%</span>
                    </div>
                </div>
            ';
        }
        
        $projectsList .= '</div>';

        return response()->json([
            'reply' => $projectsList,
            'isFormatted' => true
        ]);
    }

    /**
     * Show project status
     */
    protected function projectStatusCommand(string $parameters)
    {
        if (empty(trim($parameters))) {
            return response()->json([
                'reply' => $this->formatMessage([
                    'type' => 'error',
                    'content' => "Veuillez spécifier le nom du projet",
                    'footer' => "Usage : <code>/project-status [nom du projet]</code>"
                ])
            ]);
        }

        $project = Project::where('owner_id', auth()->id())
                         ->where('title', 'like', '%' . trim($parameters) . '%')
                         ->first();

        if (!$project) {
            return response()->json([
                'reply' => $this->formatMessage([
                    'type' => 'error',
                    'content' => "Projet non trouvé : <strong>" . htmlspecialchars($parameters) . "</strong>"
                ])
            ]);
        }

        $activityCount = $project->activities->count();
        $completedTasks = $project->activities->sum(fn($a) => $a->tasks->whereIn('status', ['completed', 'finalized'])->count());
        $totalTasks = $project->activities->sum(fn($a) => $a->tasks->count());
        $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        $statusIcon = match($project->status) {
            'completed' => '✅',
            'in_progress' => '🔄',
            'pending' => '⏳',
            default => '📌'
        };

        $statusContent = '
            <div class="ai-project-status">
                <div class="ai-status-header">
                    <span class="status-icon">' . $statusIcon . '</span>
                    <h4>' . htmlspecialchars($project->title) . '</h4>
                </div>
                <div class="ai-status-details">
                    <div class="ai-status-item">
                        <span class="label">Statut</span>
                        <span class="value">' . htmlspecialchars($project->status) . '</span>
                    </div>
                    <div class="ai-status-item">
                        <span class="label">Activités</span>
                        <span class="value">' . $activityCount . '</span>
                    </div>
                    <div class="ai-status-item">
                        <span class="label">Tâches terminées</span>
                        <span class="value">' . $completedTasks . '/' . $totalTasks . '</span>
                    </div>
                    <div class="ai-status-item">
                        <span class="label">Progression</span>
                        <span class="value progress">' . $progress . '%</span>
                    </div>';
        
        if ($project->due_date) {
            $statusContent .= '
                    <div class="ai-status-item">
                        <span class="label">Échéance</span>
                        <span class="value">' . \Carbon\Carbon::parse($project->due_date)->format('d/m/Y') . '</span>
                    </div>';
        }
        
        $statusContent .= '
                </div>
            </div>
        ';

        return response()->json([
            'reply' => $statusContent,
            'isFormatted' => true
        ]);
    }

    /**
     * Format message with consistent styling
     */
    protected function formatMessage(array $options): string
    {
        $type = $options['type'] ?? 'info';
        $icon = $options['icon'] ?? '';
        $content = $options['content'] ?? '';
        $footer = $options['footer'] ?? '';

        $typeClass = 'ai-message-' . $type;
        
        $html = '<div class="ai-formatted-message ' . $typeClass . '">';
        
        if ($icon) {
            $html .= '<span class="ai-message-icon">' . $icon . '</span>';
        }
        
        $html .= '<div class="ai-message-text">';
        $html .= '<div class="ai-message-main">' . $content . '</div>';
        
        if ($footer) {
            $html .= '<div class="ai-message-footer">' . $footer . '</div>';
        }
        
        $html .= '</div></div>';
        
        return $html;
    }

    /**
     * Check if message is requesting activity creation
     */
    protected function isActivityCreationRequest(string $message): bool
    {
        $keywords = ['créer une activité', 'crée une activité', 'nouvelle activité', 'ajouter une activité'];
        $lowerMessage = strtolower($message);
        
        foreach ($keywords as $keyword) {
            if (strpos($lowerMessage, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Handle activity creation from natural language
     */
    protected function handleActivityCreation(string $message)
    {
        $projectId = $this->extractProjectFromMessage($message);

        if (!$projectId) {
            return response()->json([
                'reply' => $this->formatMessage([
                    'type' => 'error',
                    'content' => "Pour créer une activité, veuillez spécifier le projet.",
                    'footer' => "Exemple : \"Crée une activité de développement pour le projet Site Web\""
                ])
            ]);
        }

        $project = Project::find($projectId);
        if (!$project) {
            return response()->json([
                'reply' => $this->formatMessage([
                    'type' => 'error',
                    'content' => "Projet non trouvé."
                ])
            ]);
        }

        return response()->json([
            'reply' => $this->formatMessage([
                'type' => 'info',
                'icon' => '🚧',
                'content' => "La création d'activités via l'IA sera bientôt disponible !",
                'footer' => "En attendant, utilisez l'interface de gestion de projets."
            ])
        ]);
    }

    /**
     * Extract project ID from message
     */
    protected function extractProjectFromMessage(string $message): ?int
    {
        $projects = Project::where('owner_id', auth()->id())->get();

        foreach ($projects as $project) {
            if (strpos(strtolower($message), strtolower($project->title)) !== false) {
                return $project->id;
            }
        }

        return null;
    }

    /**
     * Build prompt for general chat
     */
    protected function buildChatPrompt(string $message, array $context): string
    {
        $contextStr = "";
        if (!empty($context)) {
            $contextStr = "Contexte de la conversation:\n";
            foreach ($context as $item) {
                $contextStr .= "- Utilisateur: {$item['user']}\n";
                $contextStr .= "- IA: {$item['bot']}\n";
            }
        }

        return "Tu es un assistant IA spécialisé dans la gestion de projet. Réponds de manière helpful et professionnelle.

{$contextStr}
Message utilisateur: {$message}

Réponds de manière concise et utile. Si l'utilisateur demande quelque chose en rapport avec la gestion de projet, fournis des conseils pratiques.";
    }
}