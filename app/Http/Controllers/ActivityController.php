<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Activity;

class ActivityController extends Controller
{
    public function create(Project $project, Request $request)
    {
        if ($project->owner_id !== $request->user()->id) abort(403);
        return view('activities.create_edit', ['project'=>$project, 'activity'=>new Activity()]);
    }

    public function store(Project $project, Request $request)
    {
        if ($project->owner_id !== $request->user()->id) abort(403);

        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($project) {
                    if ($project->activities()->where('title', $value)->exists()) {
                        $fail('Une activité avec ce nom existe déjà dans ce projet.');
                    }
                }
            ],
            'description' => 'nullable|string',
            'due_date' => [
                'nullable',
                'date',
                'after:today'
            ]
        ]);

        $data['status'] = 'in_progress';
        $activity = $project->activities()->create($data);
        return redirect()->route('activities.show',$activity)->with('ok','Activité créée');
    }

    public function show(Activity $activity, Request $request)
    {
        $activity->load('project','tasks.assignees');
        $user = $request->user();
        $project = $activity->project;
        if (!$user->isManager() && $project->owner_id !== $user->id) {
            $allowed = $activity->tasks()->whereHas('assignees', fn($q)=>$q->where('users.id',$user->id))->exists();
            if (!$allowed) abort(403);
        }
        return view('activities.show', compact('activity','user'));
    }

    public function edit(Project $project, Activity $activity, Request $request)
    {
        if ($project->id !== $activity->project_id) abort(404);
        if ($project->owner_id !== $request->user()->id) abort(403);
        return view('activities.create_edit', compact('project','activity'));
    }

    public function update(Project $project, Activity $activity, Request $request)
    {
        if ($project->id !== $activity->project_id) abort(404);
        if ($project->owner_id !== $request->user()->id) abort(403);

        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($project, $activity) {
                    if ($project->activities()->where('title', $value)->where('id', '!=', $activity->id)->exists()) {
                        $fail('Une activité avec ce nom existe déjà dans ce projet.');
                    }
                }
            ],
            'description' => 'nullable|string',
            'due_date' => [
                'nullable',
                'date',
                'after:today'
            ]
        ]);

        // Le statut n'est plus modifiable manuellement - il se met à jour automatiquement
        $activity->update($data);

        // Check for activity delays
        $this->checkActivityDelays($activity);

        return redirect()->route('activities.show',$activity)->with('ok','Activité mise à jour');
    }

    /**
     * Check for activity delays and send notifications
     */
    private function checkActivityDelays(Activity $activity): void
    {
        $now = now();

        if ($activity->due_date && $activity->due_date < $now && $activity->status !== 'completed') {
            $delay = $now->diffInDays($activity->due_date);
            $message = "L'activité '{$activity->title}' est en retard de {$delay} jour(s)";

            session()->flash('delay_alert_manager', $message);
        }
    }
}
