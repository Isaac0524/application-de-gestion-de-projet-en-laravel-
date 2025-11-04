<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Subtask;

class SubtaskController extends Controller
{
    protected function ensureAssigneeOrManager(Request $request, Task $task): void
    {
        $user = $request->user();
        $isAssignee = $task->assignees()->where('users.id',$user->id)->exists();
        $isManager = $user->id === $task->activity->project->owner_id || $user->isManager();
        if (!$isAssignee && !$isManager) abort(403);
    }

    public function store(Task $task, Request $request)
    {
        $this->ensureAssigneeOrManager($request, $task);
        $data = $request->validate([
            'title'=>'required|string|max:255',
            'description'=>'nullable|string',
            'due_date'=>'nullable|date'
        ]);
        $data['user_id'] = $request->user()->id;
        $data['status'] = 'open';
        $task->subtasks()->create($data);
        return back()->with('ok','Sous-tâche ajoutée');
    }

    public function update(Subtask $subtask, Request $request)
    {
        $task = $subtask->task()->with('assignees')->first();
        $user = $request->user();
        $isOwner = $subtask->user_id === $user->id;
        $isManager = $user->id === $task->activity->project->owner_id || $user->isManager();
        if (!$isOwner && !$isManager) abort(403);

        $data = $request->validate([
            'title'=>'required|string|max:255',
            'description'=>'nullable|string',
            'due_date'=>'nullable|date',
            'status'=>'required|in:open,in_progress,completed'
        ]);
        $subtask->update($data);
        return back()->with('ok','Sous-tâche mise à jour');
    }

    public function toggle(Subtask $subtask, Request $request)
    {
        $task = $subtask->task()->with('assignees')->first();
        $user = $request->user();
        $isOwner = $subtask->user_id === $user->id;
        $isManager = $user->id === $task->activity->project->owner_id || $user->isManager();
        if (!$isOwner && !$isManager) abort(403);

        $subtask->status = $subtask->status === 'completed' ? 'open' : 'completed';
        $subtask->save();
        return back()->with('ok','Statut sous-tâche mis à jour');
    }

    public function destroy(Subtask $subtask, Request $request)
    {
        $task = $subtask->task()->with('assignees')->first();
        $user = $request->user();
        $isOwner = $subtask->user_id === $user->id;
        $isManager = $user->id === $task->activity->project->owner_id || $user->isManager();
        if (!$isOwner && !$isManager) abort(403);

        $subtask->delete();
        return back()->with('ok','Sous-tâche supprimée');
    }
}
