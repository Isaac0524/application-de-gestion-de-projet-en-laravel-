<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\User;

use Illuminate\Support\Facades\Storage;

class DailyReportController extends Controller
{
    /**
     * Display daily reports for the authenticated user (My Day)
     */
    public function myDay(Request $request)
    {
        $user = $request->user();
        $reports = DailyReport::with('project')
            ->byUser($user->id)
            ->orderBy('report_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('daily_reports.my_day', compact('reports'));
    }

    /**
     * Display daily reports for managers (Rapport Journalier)
     */
    public function dailyReports(Request $request)
    {
        $user = $request->user();

        // Only managers can see all reports
        if (!$user->isManager()) {
            abort(403);
        }

        $query = DailyReport::with(['user', 'project']);


        // Filter by project
        if ($request->filled('project')) {
            $query->byProject($request->project);
        }

        // Filter by user
        if ($request->filled('user')) {
            $query->byUser($request->user);
        }

        $reports = $query->orderBy('report_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $projects = Project::where('owner_id', $user->id)->orderBy('title')->get();
        $users =User::where('role', 'member')->orderBy('name')->get();

        return view('daily_reports.daily_reports', compact('reports', 'projects', 'users'));
    }
    public function preview(DailyReport $report)
    {
        if (!$report->hasFile()) {
            abort(404, 'Fichier non trouvé.');
        }

        $filePath = storage_path('app/public/' . $report->file_path); // Assurez-vous que le chemin est correct
        $extension = strtolower(pathinfo($report->file_name, PATHINFO_EXTENSION));

        // Définir les types MIME pour l'aperçu
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
        ];

        if (!array_key_exists($extension, $mimeTypes)) {
            abort(400, 'Type de fichier non pris en charge pour l\'aperçu.');
        }

        return response()->file($filePath, [
            'Content-Type' => $mimeTypes[$extension],
            'Content-Disposition' => 'inline; filename="' . $report->file_name . '"',
        ]);
    }

    /**
     * Show form to create a new daily report
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $projects = $user->isManager()
            ? Project::where('owner_id', $user->id)->orderBy('title')->get()
            : Project::whereHas('activities.tasks.assignees', fn($q) => $q->where('users.id', $user->id))
                ->orWhereHas('activities', fn($q) => $q->whereHas('tasks', fn($q) => $q->whereHas('assignees', fn($q) => $q->where('users.id', $user->id))))
                ->orderBy('title')
                ->get();

        return view('daily_reports.create', compact('projects'));
    }

    /**
     * Store a new daily report
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'description' => 'required|string|min:10',
            'project_id' => 'nullable|exists:projects,id',
            'report_date' => 'required|date',
            'file' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,txt'
        ]);

        $data['user_id'] = $request->user()->id;

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('daily_reports', $filename, 'public');

            $data['file_path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
        }

        DailyReport::create($data);

        return redirect()->route('daily_reports.my_day')
            ->with('ok', 'Rapport journalier enregistré avec succès !');
    }

    /**
     * Display a specific daily report
     */
    public function show(DailyReport $report, Request $request)
    {
        $user = $request->user();

        // Check permissions
        if ($user->id !== $report->user_id && !$user->isManager()) {
            abort(403);
        }

        return view('daily_reports.show', compact('report'));
    }

    /**
     * Download the attached file
     */
    public function download(DailyReport $report, Request $request)
    {
        $user = $request->user();

        // Check permissions
        if ($user->id !== $report->user_id && !$user->isManager()) {
            abort(403);
        }

        if (!$report->hasFile()) {
            abort(404);
        }

        return Storage::disk('public')->download($report->file_path, $report->file_name);
    }

    /**
     * Show form to edit a daily report
     */
    public function edit(DailyReport $report, Request $request)
    {
        $user = $request->user();

        // Only the owner can edit
        if ($user->id !== $report->user_id) {
            abort(403);
        }

        $projects = $user->isManager()
            ? Project::where('owner_id', $user->id)->orderBy('title')->get()
            : Project::whereHas('activities.tasks.assignees', fn($q) => $q->where('users.id', $user->id))
                ->orWhereHas('activities', fn($q) => $q->whereHas('tasks', fn($q) => $q->whereHas('assignees', fn($q) => $q->where('users.id', $user->id))))
                ->orderBy('title')
                ->get();

        return view('daily_reports.edit', compact('report', 'projects'));
    }

    /**
     * Update a daily report
     */
    public function update(Request $request, DailyReport $report)
    {
        $user = $request->user();

        // Only the owner can update
        if ($user->id !== $report->user_id) {
            abort(403);
        }

        $data = $request->validate([
            'description' => 'required|string|min:10',
            'project_id' => 'nullable|exists:projects,id',
            'report_date' => 'required|date',
            'file' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,txt'
        ]);

        // Handle file update
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($report->hasFile()) {
                Storage::disk('public')->delete($report->file_path);
            }

            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('daily_reports', $filename, 'public');

            $data['file_path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
        }

        $report->update($data);

        return redirect()->route('daily_reports.show', $report)
            ->with('ok', 'Rapport journalier mis à jour avec succès !');
    }



    public function destroy(DailyReport $report, Request $request)
    {
        $user = $request->user();

        // Only the owner or manager can delete
        if ($user->id !== $report->user_id && !$user->isManager()) {
            abort(403);
        }

        // Delete file if exists
        if ($report->hasFile()) {
            Storage::disk('public')->delete($report->file_path);
        }

        $report->delete();

        return back()->with('ok', 'Rapport journalier supprimé avec succès !');
    }
}
