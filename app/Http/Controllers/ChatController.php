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
            if (strpos($message, ';') === 0) {
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
     * Handle command-based messages (starting with ;)
     */
    protected function handleCommand(string $message)
    {
        $parts = explode(' ', $message, 2);
        $command = strtolower($parts[0]);
        $parameters = $parts[1] ?? '';

        switch ($command) {
            case ';help':
                return $this->showHelp();
            case ';create-activity':
                return $this->createActivityCommand($parameters);
            case ';list-projects':
                return $this->listProjectsCommand();
            case ';project-status':
                return $this->projectStatusCommand($parameters);
            default:
                return response()->json([
                    'reply' => "Commande inconnue : {$command}. Utilisez ;help pour voir la liste des commandes disponibles."
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
                'reply' => 'Je n\'ai pas pu traiter votre demande. Utilisez ;help pour voir les commandes disponibles.'
            ]);
        }
    }

    /**
     * Check if message is requesting activity creation
     */
    protected function isActivityCreationRequest(string $message): bool
    {
        $keywords = [
            'crée', 'créer', 'create', 'nouvelle activité', 'new activity',
            'ajoute', 'ajouter', 'add activity', 'activité pour'
        ];

        $lowerMessage = strtolower($message);
        foreach ($keywords as $keyword) {
            if (strpos($lowerMessage, $keyword) !== false) {
                return true;
            }
        }

        return false;
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
                'reply' => 'Bonjour ! 👋 Je suis votre assistant IA pour la gestion de projets. Comment puis-je vous aider aujourd\'hui ?'
            ]);
        }

        // How are you
        if (strpos($lowerMessage, 'ça va') !== false || strpos($lowerMessage, 'comment ça va') !== false ||
            strpos($lowerMessage, 'how are you') !== false || strpos($lowerMessage, 'comment vas-tu') !== false) {
            return response()->json([
                'reply' => 'Je vais très bien, merci ! 😊 Je suis prêt à vous aider avec vos projets. Que souhaitez-vous faire ?'
            ]);
        }

        // Thanks
        if (strpos($lowerMessage, 'merci') !== false || strpos($lowerMessage, 'thanks') !== false ||
            strpos($lowerMessage, 'thank you') !== false) {
            return response()->json([
                'reply' => 'De rien ! 😊 N\'hésitez pas si vous avez besoin d\'aide avec vos projets.'
            ]);
        }

        // Goodbye
        if (strpos($lowerMessage, 'au revoir') !== false || strpos($lowerMessage, 'bye') !== false ||
            strpos($lowerMessage, 'bonne journée') !== false || strpos($lowerMessage, 'à bientôt') !== false ||
            strpos($lowerMessage, 'see you') !== false) {
            return response()->json([
                'reply' => 'Au revoir ! 👋 Passez une excellente journée. À bientôt !'
            ]);
        }

        // Nice to meet you
        if (strpos($lowerMessage, 'enchanté') !== false || strpos($lowerMessage, 'nice to meet you') !== false) {
            return response()->json([
                'reply' => 'Enchanté également ! 🤝 Je suis ravi de vous aider avec vos projets de gestion.'
            ]);
        }

        // Default casual response
        return response()->json([
            'reply' => 'Ravi de discuter avec vous ! 💬 Je suis là pour vous aider avec la gestion de vos projets. Que puis-je faire pour vous ?'
        ]);
    }

    /**
     * Handle activity creation from natural language
     */
    protected function handleActivityCreation(string $message)
    {
        // Extract project information if mentioned
        $projectId = $this->extractProjectFromMessage($message);

        if (!$projectId) {
            return response()->json([
                'reply' => 'Pour créer une activité, veuillez spécifier le projet. Exemple: "Crée une activité de développement pour le projet Site Web"'
            ]);
        }

        // Verify user has access to the project
        $project = Project::find($projectId);
        if (!$project) {
            return response()->json([
                'reply' => 'Projet non trouvé.'
            ]);
        }

        $this->authorize('manager', $project);

        // Use Gemini to extract activity details from the message
        $activityData = $this->extractActivityData($message, $project);

        if (!$activityData) {
            return response()->json([
                'reply' => 'Je n\'ai pas pu extraire les informations nécessaires. Veuillez utiliser le format: "Crée une activité [nom] pour le projet [nom du projet]"'
            ]);
        }

        try {
            // Create the activity
            $activity = Activity::create([
                'title' => $activityData['title'],
                'description' => $activityData['description'],
                'project_id' => $project->id,
                'status' => 'in_progress',
                'due_date' => $project->due_date
            ]);

            // Create tasks if provided
            if (!empty($activityData['tasks'])) {
                foreach ($activityData['tasks'] as $taskData) {
                    Task::create([
                        'title' => $taskData['title'],
                        'description' => $taskData['description'] ?? '',
                        'activity_id' => $activity->id,
                        'priority' => $taskData['priority'] ?? 'medium',
                        'status' => 'pending',
                        'estimated_hours' => $taskData['estimated_hours'] ?? null,
                        'due_date' => $project->due_date
                    ]);
                }
            }

            $taskCount = count($activityData['tasks'] ?? []);
            $reply = "✅ Activité créée avec succès : **{$activity->title}**\n";
            $reply .= "📋 Projet: {$project->title}\n";
            if ($taskCount > 0) {
                $reply .= "📝 {$taskCount} tâche(s) créée(s)";

                return response()->json([
                    'reply' => $reply,
                    'activity' => $activity,
                    'project' => $project
                ]);
            }

        } catch (Exception $e) {
            Log::error('Activity creation failed', [
                'message' => $message,
                'project_id' => $projectId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'reply' => 'Erreur lors de la création de l\'activité. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * Extract project ID from message
     */
    protected function extractProjectFromMessage(string $message): ?int
    {
        // Look for project mentions in the message
        $projects = Project::where('owner_id', auth()->id())->get();

        foreach ($projects as $project) {
            if (strpos(strtolower($message), strtolower($project->title)) !== false) {
                return $project->id;
            }
        }

        return null;
    }

    /**
     * Use Gemini to extract activity data from natural language
     */
    protected function extractActivityData(string $message, Project $project): ?array
    {
        $prompt = "Extrait les informations d'activité suivantes de ce message utilisateur. Réponds uniquement en JSON:

Message: \"{$message}\"

Format de réponse JSON:
{
    \"title\": \"nom de l'activité\",
    \"description\": \"description de l'activité\",
    \"tasks\": [
        {
            \"title\": \"nom de la tâche\",
            \"description\": \"description de la tâche\",
            \"priority\": \"high|medium|low\",
            \"estimated_hours\": 4
        }
    ]
}";

        try {
            $response = $this->geminiService->chat($prompt);
            $parsed = json_decode($response, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($parsed['title'])) {
                return $parsed;
            }
        } catch (Exception $e) {
            Log::error('Activity data extraction failed', [
                'message' => $message,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Show available commands
     */
    protected function showHelp()
    {
        $helpText = "**🤖 Commandes disponibles :**\n\n";
        $helpText .= "** Gestion des activités :**\n";
        $helpText .= "• `;create-activity [nom]` - Créer une nouvelle activité\n";
        $helpText .= "• `;list-projects` - Lister tous vos projets\n";
        $helpText .= "• `;project-status [nom]` - Voir le statut d'un projet\n\n";
        $helpText .= "** Conversation naturelle :**\n";
        $helpText .= "• Dites simplement \"Crée une activité de développement pour le projet Site Web\"\n";
        $helpText .= "• \"Ajoute une tâche de design au projet Mobile App\"\n\n";
        $helpText .= "**Exemples :**\n";
        $helpText .= "• \"Crée une activité de développement frontend pour le projet E-commerce\"\n";
        $helpText .= "• `;create-activity Développement API`\n";

        return response()->json([
            'reply' => $helpText
        ]);
    }

    /**
     * Create activity via command
     */
    protected function createActivityCommand(string $parameters)
    {
        if (empty(trim($parameters))) {
            return response()->json([
                'reply' => "Usage: ;create-activity [nom de l'activité]\nExemple: ;create-activity Développement Frontend"
            ]);
        }

        // For now, just acknowledge - full implementation would need project context
        return response()->json([
            'reply' => "Pour créer une activité, veuillez spécifier le projet dans votre message.\nExemple: \"Crée une activité {$parameters} pour le projet [nom du projet]\""
        ]);
    }

    /**
     * List user projects
     */
    protected function listProjectsCommand()
    {
        $projects = Project::where('owner_id', auth()->id())->get();

        if ($projects->isEmpty()) {
            return response()->json([
                'reply' => "Aucun projet trouvé. Créez d'abord un projet."
            ]);
        }

        $list = "**📋 Vos projets :**\n";
        foreach ($projects as $project) {
            $activityCount = $project->activities->count();
            $completedTasks = $project->activities->sum(fn($a) => $a->tasks->whereIn('status', ['completed', 'finalized'])->count());
            $totalTasks = $project->activities->sum(fn($a) => $a->tasks->count());
            $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

            $list .= "• **{$project->title}** ({$project->status}) - {$activityCount} activités, {$progress}% terminé\n";
        }

        return response()->json([
            'reply' => $list
        ]);
    }

    /**
     * Show project status
     */
    protected function projectStatusCommand(string $parameters)
    {
        if (empty(trim($parameters))) {
            return response()->json([
                'reply' => "Usage: ;project-status [nom du projet]"
            ]);
        }

        $project = Project::where('owner_id', auth()->id())
                         ->where('title', 'like', '%' . trim($parameters) . '%')
                         ->first();

        if (!$project) {
            return response()->json([
                'reply' => "Projet non trouvé: {$parameters}"
            ]);
        }

        $activityCount = $project->activities->count();
        $completedTasks = $project->activities->sum(fn($a) => $a->tasks->whereIn('status', ['completed', 'finalized'])->count());
        $totalTasks = $project->activities->sum(fn($a) => $a->tasks->count());
        $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        $status = "**📊 Statut du projet: {$project->title}**\n";
        $status .= "• Statut: {$project->status}\n";
        $status .= "• Activités: {$activityCount}\n";
        $status .= "• Tâches terminées: {$completedTasks}/{$totalTasks}\n";
        $status .= "• Progression: {$progress}%\n";

        if ($project->due_date) {
            $status .= "• Échéance: " . \Carbon\Carbon::parse($project->due_date)->format('d/m/Y') . "\n";
        }

        return response()->json([
            'reply' => $status
        ]);
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
