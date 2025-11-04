<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = ['project_id','title','description','status','due_date'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function refreshStatus(): void
    {
        $total = $this->tasks()->count();
        $finalized = $this->tasks()->where('status','finalized')->count();

        // Automatically set to completed when all tasks are finalized
        if ($total > 0 && $finalized === $total) {
            $this->status = 'completed';
        } else {
            $this->status = 'in_progress';
        }

        $this->save();

        // Check for delays when status changes
        if ($this->status === 'completed') {
            $this->checkForDelays();
        }

        // Automatically check and update parent project status
        $this->updateProjectStatus();
    }

    /**
     * Automatically update parent project status when activity status changes
     */
    public function updateProjectStatus(): void
    {
        $project = $this->project;
        if (!$project) {
            return;
        }

        $allActivities = $project->activities;

        // Skip if no activities
        if ($allActivities->isEmpty()) {
            return;
        }

        // Compter les activités en cours
        $inProgressActivities = $allActivities->filter(function ($activity) {
            return $activity->status === 'in_progress';
        })->count();

        // Compter les activités terminées
        $completedActivities = $allActivities->filter(function ($activity) {
            return $activity->status === 'completed';
        })->count();

        // Si au moins une activité est en cours, remettre le projet à "en cours"
        if ($inProgressActivities > 0 && $project->status !== 'in_progress') {
            $project->status = 'in_progress';
            $project->save();
        }
        // Si toutes les activités sont terminées, marquer le projet comme terminé
        elseif ($completedActivities === $allActivities->count() && $project->status !== 'completed') {
            $project->status = 'completed';
            $project->save();
        }
    }

    /**
     * Check for activity delays
     */
    public function checkForDelays(): void
    {
        $now = now();

        if ($this->due_date && $this->due_date < $now && $this->status !== 'completed') {
            $delay = $now->diffInDays($this->due_date);

            // Store in session for display
            session()->flash('delay_alert', [
                'type' => 'activity',
                'name' => $this->title,
                'delay' => $delay
            ]);
        }
    }

}
