<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GeminiService
{
    protected $apiKey;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');

        if (!$this->apiKey) {
            throw new Exception('GEMINI_API_KEY is not configured in .env file');
        }
    }

    /**
     * Analyser un projet et retourner les activités et tâches générées
     */
    public function analyzeProject(string $title, ?string $description, ?string $startDate, ?string $endDate, ?string $priority, ?string $status, array $existingActivities = [], int $currentProgress = 0): array
    {
        try {
            Log::info('🚀 Starting Gemini analysis', [
                'title' => $title,
                'description' => $description
            ]);

            $prompt = $this->buildAnalysisPrompt($title, $description, $startDate, $endDate, $priority, $status, $existingActivities, $currentProgress);

            $endpoint = "{$this->baseUrl}/gemini-2.5-flash:generateContent?key={$this->apiKey}";

            Log::info('📡 Sending request to Gemini', [
                'endpoint' => str_replace($this->apiKey, 'HIDDEN', $endpoint),
                'prompt_length' => strlen($prompt)
            ]);

            $response = Http::withoutVerifying()
                ->timeout(120)
                ->post($endpoint, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 4096,
                        'responseMimeType' => 'application/json'
                    ]
                ]);

            // Log du statut HTTP
            Log::info('📥 Received response from Gemini', [
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);

            if (!$response->successful()) {
                Log::error('❌ Gemini API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'headers' => $response->headers()
                ]);
                throw new Exception('Erreur API Gemini (Status ' . $response->status() . '): ' . $response->body());
            }

            $data = $response->json();

            Log::info('📦 Full Gemini response structure', [
                'response' => json_encode($data, JSON_PRETTY_PRINT)
            ]);

            if (!isset($data['candidates'])) {
                Log::error('❌ No candidates in response', ['data' => $data]);
                throw new Exception('Réponse Gemini invalide: pas de candidates');
            }

            if (!isset($data['candidates'][0])) {
                Log::error('❌ No first candidate', ['candidates' => $data['candidates']]);
                throw new Exception('Réponse Gemini invalide: candidates vide');
            }

            if (!isset($data['candidates'][0]['content'])) {
                Log::error('❌ No content in candidate', ['candidate' => $data['candidates'][0]]);
                throw new Exception('Réponse Gemini invalide: pas de content dans candidate');
            }

            if (!isset($data['candidates'][0]['content']['parts'])) {
                Log::error('❌ No parts in content', ['content' => $data['candidates'][0]['content']]);
                throw new Exception('Réponse Gemini invalide: pas de parts dans content');
            }

            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                Log::error('❌ No text in parts', ['parts' => $data['candidates'][0]['content']['parts']]);
                throw new Exception('Réponse Gemini invalide: pas de text dans parts');
            }

            $textResponse = $data['candidates'][0]['content']['parts'][0]['text'];

            Log::info('📝 Raw Gemini text response', [
                'text' => $textResponse,
                'length' => strlen($textResponse)
            ]);

            // Parser la réponse
            $parsedResponse = $this->parseGeminiResponse($textResponse);

            Log::info('✅ Successfully parsed Gemini response', [
                'activities_count' => count($parsedResponse['activities'] ?? [])
            ]);

            return $parsedResponse;

        } catch (Exception $e) {
            Log::error('❌ GeminiService::analyzeProject error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'title' => $title
            ]);

            throw $e;
        }
    }

    /**
     * Construire le prompt d'analyse pour Gemini
     */
    private function buildAnalysisPrompt(string $title, ?string $description, ?string $startDate, ?string $endDate, ?string $priority, ?string $status, array $existingActivities = [], int $currentProgress = 0): string
    {
        $description = $description ?: 'Aucune description fournie';

        // Construire le contexte des activités existantes
        $existingContext = "";
        if (!empty($existingActivities)) {
            $existingContext = "\n\nACTIVITÉS EXISTANTES ({$currentProgress}% terminé) :\n";
            foreach ($existingActivities as $index => $activity) {
                $existingContext .= ($index + 1) . ". {$activity['title']} (Statut: {$activity['status']})\n";
                $existingContext .= "   Description: {$activity['description']}\n";
                if (!empty($activity['tasks'])) {
                    $existingContext .= "   Tâches:\n";
                    foreach ($activity['tasks'] as $taskIndex => $task) {
                        $existingContext .= "   - {$task['title']} (Statut: {$task['status']}, Priorité: {$task['priority']}" .
                            (isset($task['estimated_hours']) ? ", {$task['estimated_hours']}h estimées" : "") . ")\n";
                        if (!empty($task['description'])) {
                            $existingContext .= "     Description: {$task['description']}\n";
                        }
                    }
                }
                $existingContext .= "\n";
            }
        }

        return "Tu es un expert en gestion de projet. Analyse ce projet et génère des activités et tâches SPÉCIFIQUES et CONCRÈTES en tenant compte de l'état actuel du projet.

PROJET :
Titre: {$title}
Description: {$description}" . ($startDate ? "\nDate de début: {$startDate}" : "") . ($endDate ? "\nDate de fin: {$endDate}" : "") . ($priority ? "\nPriorité: {$priority}" : "") . ($status ? "\nStatut: {$status}" : "") . $existingContext . "

INSTRUCTIONS CRITIQUES :
1. Analyse l'état actuel du projet et la progression ({$currentProgress}%)
2. Si des activités existent déjà, propose des activités dans la CONTINUITÉ logique du projet
3. Évite de proposer des activités similaires ou redondantes avec celles existantes
4. Génère 2 à 4 NOUVELLES activités SPÉCIFIQUES qui complètent le projet
5. Chaque activité doit avoir 2 à 4 tâches CONCRÈTES et actionnables
6. Les titres doivent être clairs et professionnels
7. Les estimations d'heures doivent être réalistes (2h à 20h)
8. Les priorités doivent être bien distribuées (high/medium/low)
9. Considère les dépendances logiques avec les activités existantes

RÉPONDS UNIQUEMENT AVEC CE FORMAT JSON (aucun texte avant ou après) :
{
    \"activities\": [
        {
            \"title\": \"Titre spécifique de la nouvelle activité\",
            \"description\": \"Description claire expliquant pourquoi cette activité complète le projet\",
            \"tasks\": [
                {
                    \"title\": \"Titre de la tâche\",
                    \"description\": \"Description de la tâche\",
                    \"priority\": \"high\",
                    \"estimated_hours\": 8
                }
            ]
        }
    ]
}";
    }

    /**
     * Parser la réponse de Gemini pour extraire le JSON
     */
    private function parseGeminiResponse(string $response): array
    {
        Log::info('🔍 Starting to parse response', [
            'response_preview' => substr($response, 0, 200) . '...'
        ]);

        // Nettoyer la réponse
        $cleanResponse = trim($response);

        // Supprimer les blocs de code markdown
        $cleanResponse = preg_replace('/^```json\s*/m', '', $cleanResponse);
        $cleanResponse = preg_replace('/^```\s*/m', '', $cleanResponse);
        $cleanResponse = preg_replace('/```$/m', '', $cleanResponse);

        // Supprimer tout texte avant le premier {
        if (preg_match('/\{/', $cleanResponse, $matches, PREG_OFFSET_CAPTURE)) {
            $startPos = $matches[0][1];
            $cleanResponse = substr($cleanResponse, $startPos);
        }

        // Supprimer tout texte après le dernier }
        if (preg_match('/\}[^\}]*$/s', $cleanResponse, $matches, PREG_OFFSET_CAPTURE)) {
            $endPos = $matches[0][1] + 1;
            $cleanResponse = substr($cleanResponse, 0, $endPos);
        }

        Log::info('🧹 Cleaned response', [
            'cleaned' => $cleanResponse
        ]);

        // Tenter de décoder le JSON
        $parsed = json_decode($cleanResponse, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('❌ JSON decode failed', [
                'error' => json_last_error_msg(),
                'json' => $cleanResponse
            ]);

            throw new Exception('Réponse JSON invalide de Gemini: ' . json_last_error_msg() . ' - Response: ' . substr($cleanResponse, 0, 500));
        }

        // Valider la structure
        if (!isset($parsed['activities'])) {
            Log::error('❌ No activities key in parsed response', [
                'parsed' => $parsed
            ]);
            throw new Exception('La réponse ne contient pas de clé "activities"');
        }

        if (!is_array($parsed['activities'])) {
            Log::error('❌ Activities is not an array', [
                'activities' => $parsed['activities']
            ]);
            throw new Exception('Les activités ne sont pas au format tableau');
        }

        if (empty($parsed['activities'])) {
            Log::warning('⚠️ No activities generated', [
                'parsed' => $parsed
            ]);
            throw new Exception('Aucune activité générée par Gemini');
        }

        // Valider chaque activité
        foreach ($parsed['activities'] as $index => $activity) {
            if (!isset($activity['title']) || !isset($activity['tasks'])) {
                Log::error('❌ Invalid activity structure', [
                    'index' => $index,
                    'activity' => $activity
                ]);
                throw new Exception("L'activité #{$index} est invalide");
            }

            if (!is_array($activity['tasks'])) {
                Log::error('❌ Tasks is not an array', [
                    'index' => $index,
                    'tasks' => $activity['tasks']
                ]);
                throw new Exception("Les tâches de l'activité #{$index} ne sont pas au format tableau");
            }
        }

        Log::info('✅ Response validated successfully', [
            'activities_count' => count($parsed['activities']),
            'total_tasks' => array_sum(array_map(fn($a) => count($a['tasks']), $parsed['activities']))
        ]);

        return $parsed;
    }

    /**
     * Tester la connexion à l'API Gemini
     */
    public function testConnection(): array
    {
        try {
            Log::info('🧪 Testing Gemini API connection');

            $endpoint = "{$this->baseUrl}/gemini-2.5-flash:generateContent?key={$this->apiKey}";

            $response = Http::withoutVerifying()
                ->timeout(30)
                ->post($endpoint, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => 'Réponds simplement "OK" en JSON: {"status": "OK"}']
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'maxOutputTokens' => 100,
                        'responseMimeType' => 'application/json'
                    ]
                ]);

            Log::info('🧪 Test response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'body' => $response->json()
            ];

        } catch (Exception $e) {
            Log::error('🧪 Test failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
