<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\Task;
use App\Models\User;

class TaskController extends Controller
{
    public function create(Activity $activity, Request $request)
    {
        if ($activity->project->owner_id !== $request->user()->id) abort(403);
        $users = User::orderBy('name')->get();
        return view('tasks.create_edit', ['activity'=>$activity,'task'=>new Task(),'users'=>$users,'assigned'=>[]]);
    }

    public function store(Activity $activity, Request $request)
    {
        if ($activity->project->owner_id !== $request->user()->id) abort(403);

        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($activity) {
                    if ($activity->tasks()->where('title', $value)->exists()) {
                        $fail('Une tâche avec ce nom existe déjà dans cette activité.');
                    }
                }
            ],
            'description'=>'nullable|string',
            'priority'=>'nullable|in:low,medium,high',
            'due_date'=>[
                'nullable',
                'date',
                'after:today'
            ],
            'notes'=>'nullable|string',
            'assignees'=>'array',
            'assignees.*'=>'exists:users,id'
        ]);

        $data['status'] = 'pending';
        $task = $activity->tasks()->create($data);
        if (!empty($data['assignees'])) $task->assignees()->sync($data['assignees']);

        // Refresh activity status automatically
        $this->refreshActivityStatus($activity);

        // Check for delays and send notifications
        $this->checkForDelays($activity, $task);

        return redirect()->route('activities.show',$activity)->with('ok','Tâche créée');
    }

    public function edit(Activity $activity, Task $task, Request $request)
    {
        if ($task->activity_id !== $activity->id) abort(404);
        if ($activity->project->owner_id !== $request->user()->id) abort(403);
        $users = User::orderBy('name')->get();
        $assigned = $task->assignees()->pluck('users.id')->toArray();
        return view('tasks.create_edit', compact('activity','task','users','assigned'));
    }

    public function update(Activity $activity, Task $task, Request $request)
    {
        if ($task->activity_id !== $activity->id) abort(404);
        if ($activity->project->owner_id !== $request->user()->id) abort(403);

        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($activity, $task) {
                    if ($activity->tasks()->where('title', $value)->where('id', '!=', $task->id)->exists()) {
                        $fail('Une tâche avec ce nom existe déjà dans cette activité.');
                    }
                }
            ],
            'description'=>'nullable|string',
            'priority'=>'nullable|in:low,medium,high',
            'due_date'=>[
                'nullable',
                'date',
                'after:today'
            ],
            'notes'=>'nullable|string',
            'assignees'=>'array',
            'assignees.*'=>'exists:users,id'
        ]);

        $task->update($data);
        $task->assignees()->sync($data['assignees'] ?? []);

        // Refresh activity status automatically
        $this->refreshActivityStatus($activity);

        // Check for delays and send notifications
        $this->checkForDelays($activity, $task);

        return redirect()->route('activities.show',$activity)->with('ok','Tâche mise à jour');
    }

    public function show(Task $task, Request $request)
    {
        $task->load('activity.project','assignees','subtasks.user');
        $user = $request->user();
        $project = $task->activity->project;
        if (!$user->isManager() && $project->owner_id !== $user->id) {
            $allowed = $task->activity->tasks()->whereHas('assignees', fn($q)=>$q->where('users.id',$user->id))->exists();
            if (!$allowed) abort(403);
        }
        return view('tasks.show', compact('task','user'));
    }

    public function toggleProgress(Task $task, Request $request)
    {
        $user = $request->user();
        $allowed = $task->assignees()->where('users.id',$user->id)->exists() || $user->id === $task->activity->project->owner_id;
        if (!$allowed) abort(403);
        $task->status = $task->status === 'open' ? 'in_progress' : 'open';
        $task->save();

        // Refresh activity status automatically
        $this->refreshActivityStatus($task->activity);

        return back()->with('ok','Statut ajusté');
    }

    public function markCompleteByAssignee(Task $task, Request $request)
    {
        $user = $request->user();
        $assigned = $task->assignees()->where('users.id',$user->id)->exists();
        if (!$assigned) abort(403);
        $task->status = 'completed_by_assignee';
        $task->save();

        // Refresh activity status automatically
        $this->refreshActivityStatus($task->activity);

        return back()->with('ok','Tâche marquée comme complétée par l\'exécutant');
    }

    public function finalize(Task $task, Request $request)
    {
        if ($request->user()->id !== $task->activity->project->owner_id) abort(403);

        // Une tâche ne peut être finalisée que si elle est déjà complétée par un assigné
        if ($task->status !== 'completed_by_assignee') {
            return back()->with('error', 'Cette tâche doit d\'abord être marquée comme complétée par un membre assigné avant d\'être finalisée.');
        }

        $task->status = 'finalized';
        $task->save();

        // Refresh activity status automatically
        $this->refreshActivityStatus($task->activity);

        // Check for delays and send notifications
        $this->checkForDelays($task->activity, $task);

        return back()->with('ok','Tâche finalisée');
    }

    /**
     * Refresh activity status based on tasks completion
     */
    private function refreshActivityStatus(Activity $activity): void
    {
        $activity->load('tasks');

        if ($activity->tasks->isEmpty()) {
            $activity->status = 'in_progress';
        } else {
            // Vérifier si toutes les tâches sont finalisées
            $allTasksFinalized = $activity->tasks->every(fn($task) => $task->status === 'finalized');

            if ($allTasksFinalized) {
                $activity->status = 'completed';
            } else {
                $activity->status = 'in_progress';
            }
        }

        $activity->save();

        // Automatically check and update parent project status
        $activity->updateProjectStatus();
    }

    /**
     * Check for delays and send notifications
     */
    private function checkForDelays(Activity $activity, Task $task): void
    {
        $now = now();
        $delays = [];

        // Check task delay
        if ($task->due_date && $task->due_date < $now && $task->status !== 'finalized') {
            $delay = $now->diffInDays($task->due_date);
            $delays[] = "La tâche '{$task->title}' est en retard de {$delay} jour(s)";
        }

        // Check activity delay
        if ($activity->due_date && $activity->due_date < $now && $activity->status !== 'completed') {
            $delay = $now->diffInDays($activity->due_date);
            $delays[] = "L'activité '{$activity->title}' est en retard de {$delay} jour(s)";
        }

        // Send notifications if there are delays
        if (!empty($delays)) {
            $this->sendDelayNotifications($activity, $task, $delays);
        }
    }

    /**
     * Send delay notifications to relevant users
     */
    private function sendDelayNotifications(Activity $activity, Task $task, array $delays): void
    {
        $manager = $activity->project->owner;
        $assignees = $task->assignees;

        // Create notification message
        $message = implode("\n", $delays);

        // For manager - get all delays
        session()->flash('delay_alert_manager', $message);

        // For assignees - get task-specific delays
        foreach ($assignees as $assignee) {
            $taskDelay = "La tâche '{$task->title}' est en retard. Veuillez la compléter dès que possible.";
            session()->flash("delay_alert_{$assignee->id}", $taskDelay);
        }
    }
}
