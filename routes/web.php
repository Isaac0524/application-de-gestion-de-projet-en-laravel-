<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\AIAnalysisController;
use App\Http\Controllers\MyWorkController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ChatController;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Log;

Route::get('/dashboard', fn() => redirect()->route('dashboard'))->name('home');

// Public
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class,'showLogin'])->name('login');
    Route::post('/login', [AuthController::class,'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class,'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class,'index'])->name('dashboard');

    // Page "Mon travail" pour les membres
    Route::get('/my/work', [MyWorkController::class,'index'])->name('my.work');

    Route::resource('projects', ProjectController::class)->except(['destroy']);
    Route::get('/projects/{project}/activities/create', [ActivityController::class,'create'])->name('activities.create');
    Route::post('/projects/{project}/activities', [ActivityController::class,'store'])->name('activities.store');
    Route::get('/projects/{project}/activities/{activity}/edit', [ActivityController::class,'edit'])->name('activities.edit');
    Route::put('/projects/{project}/activities/{activity}', [ActivityController::class,'update'])->name('activities.update');

    Route::get('/activities/{activity}', [ActivityController::class,'show'])->name('activities.show');

    Route::get('/activities/{activity}/tasks/create', [TaskController::class,'create'])->name('tasks.create');
    Route::post('/activities/{activity}/tasks', [TaskController::class,'store'])->name('tasks.store');
    Route::get('/activities/{activity}/tasks/{task}/edit', [TaskController::class,'edit'])->name('tasks.edit');
    Route::put('/activities/{activity}/tasks/{task}', [TaskController::class,'update'])->name('tasks.update');

    // Détail d'une tâche (permet de gérer les sous-tâches pour les membres assignés)
    Route::get('/tasks/{task}', [TaskController::class,'show'])->name('tasks.show');

    // Actions de statut sur les tâches
    Route::post('/tasks/{task}/toggle-progress', [TaskController::class,'toggleProgress'])->name('tasks.toggle_progress');
    Route::post('/tasks/{task}/mark-complete', [TaskController::class,'markCompleteByAssignee'])->name('tasks.complete_by_assignee');
    Route::post('/tasks/{task}/finalize', [TaskController::class,'finalize'])->name('tasks.finalize')->middleware('manager.only');

    // Sous-tâches (créées par les membres assignés à la tâche)
    Route::post('/tasks/{task}/subtasks', [SubtaskController::class,'store'])->name('subtasks.store');
    Route::put('/subtasks/{subtask}', [SubtaskController::class,'update'])->name('subtasks.update');
    Route::post('/subtasks/{subtask}/toggle', [SubtaskController::class,'toggle'])->name('subtasks.toggle');
    Route::delete('/subtasks/{subtask}', [SubtaskController::class,'destroy'])->name('subtasks.destroy');

    // Équipes (manager)
    Route::get('/teams', [TeamController::class,'index'])->name('teams.index')->middleware('manager.only');
    Route::post('/teams', [TeamController::class,'store'])->name('teams.store')->middleware('manager.only');
    Route::post('/teams/{team}/attach', [TeamController::class,'attachUser'])->name('teams.attach')->middleware('manager.only');
    Route::post('/teams/{team}/detach', [TeamController::class,'detachUser'])->name('teams.detach')->middleware('manager.only');

    // Gestion des utilisateurs (admin seulement)
    Route::middleware('admin.only')->group(function () {
        Route::get('/users', [UsersController::class,'index'])->name('users.index');
        Route::post('/users', [UsersController::class,'store'])->name('users.store');
        Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
        Route::put('/users/{user}/reset-password', [UsersController::class,'resetPassword'])->name('users.reset_password');
        Route::put('/users/{user}/role', [UsersController::class,'changeRole'])->name('users.change_role');
        Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');

        // Gestion des gestionnaires de projet
        Route::get('/users/project-managers', [UsersController::class, 'projectManagers'])->name('users.project_managers');
        Route::put('/projects/{project}/change-manager', [UsersController::class, 'changeProjectManager'])->name('projects.change_manager');
    });

    // IA - Analyse de projet avec Gemini
    Route::post('/projects/{project}/analyze', [AIAnalysisController::class, 'analyze'])
        ->name('projects.analyze')->middleware('manager.only');

    // IA - Chat avec Gemini
    Route::post('/api/ai/chat/handle-message', [ChatController::class, 'handleMessage'])
        ->name('ai.chat.handle_message');

    // Daily Reports - Rapports Journaliers
    Route::prefix('daily-reports')->group(function () {
        // User-specific routes
        Route::get('/my-day', [DailyReportController::class, 'myDay'])->name('daily_reports.my_day');
        Route::get('/create', [DailyReportController::class, 'create'])->name('daily_reports.create');
        Route::post('/', [DailyReportController::class, 'store'])->name('daily_reports.store');
        Route::get('/{report}', [DailyReportController::class, 'show'])->name('daily_reports.show');
        Route::get('/{report}/edit', [DailyReportController::class, 'edit'])->name('daily_reports.edit');
        Route::put('/{report}', [DailyReportController::class, 'update'])->name('daily_reports.update');
        Route::delete('/{report}', [DailyReportController::class, 'destroy'])->name('daily_reports.destroy');
        Route::get('/{report}/download', [DailyReportController::class, 'download'])->name('daily_reports.download');
        Route::get('/{report}/preview', [DailyReportController::class, 'preview'])->name('daily_reports.preview');
        // Manager routes
        Route::get('/', [DailyReportController::class, 'dailyReports'])->name('daily_reports.daily_reports');
    });

    // ============================================
    // 🧪 ROUTES DE TEST GEMINI (À RETIRER EN PRODUCTION)
    // ============================================

    // Route pour tester la connexion Gemini

}); // Fin du groupe middleware('auth')
    Route::get('/test-gemini-connection', function (GeminiService $gemini) {
        try {
            $result = $gemini->testConnection();

            return response()->json([
                'message' => 'Test de connexion Gemini',
                'result' => $result
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    })->name('test.gemini.connection');

    // Route pour tester l'analyse d'un projet simple
    Route::get('/test-gemini-analysis', function (GeminiService $gemini) {
        try {
            Log::info('🧪 Starting Gemini analysis test');

            $result = $gemini->analyzeProject(
                title: 'Créer un site e-commerce',
                description: 'Développer une plateforme e-commerce moderne avec panier, paiement en ligne et gestion des stocks',
                startDate: '2025-01-01',
                endDate: '2025-06-30',
                priority: 'high',
                status: 'planning'
            );

            return response()->json([
                'success' => true,
                'message' => 'Analyse réussie',
                'activities_count' => count($result['activities'] ?? []),
                'result' => $result
            ]);

        } catch (Exception $e) {
            Log::error('🧪 Test analysis failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    })->name('test.gemini.analysis');

    // Route pour voir les derniers logs
    Route::get('/test-gemini-logs', function () {
        $logFile = storage_path('logs/laravel.log');

        if (!file_exists($logFile)) {
            return response()->json(['error' => 'Log file not found']);
        }

        $logs = file_get_contents($logFile);
        $lines = explode("\n", $logs);
        $lastLines = array_slice($lines, -100); // Dernières 100 lignes

        return response()->json([
            'last_logs' => implode("\n", $lastLines)
        ]);
    })->name('test.gemini.logs');
