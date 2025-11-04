<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    protected $fillable = ['title','description','owner_id','status','due_date','start_date'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class,'owner_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
    public function tasks()
    {
        return $this->hasManyThrough(Task::class, Activity::class, 'project_id', 'activity_id');
    }

    /**
     * Check if all activities are completed and update project status
     */
    public function checkAndUpdateCompletion(): void
    {
        $activities = $this->activities;

        if ($activities->isEmpty()) {
            return;
        }

        $allCompleted = $activities->every(function ($activity) {
            return $activity->status === 'completed';
        });

        if ($allCompleted && $this->status !== 'completed') {
            $this->status = 'completed';
            $this->save();
        }
    }

    /**
     * Check if project should be in progress based on start date
     */
    public function shouldBeInProgress(): bool
    {
        if (!$this->start_date) {
            return true;
        }

        return now()->gte($this->start_date);
    }

    /**
     * Get the current status considering start date
     */
    public function getCurrentStatus(): string
    {
        if ($this->status === 'pending' && $this->shouldBeInProgress()) {
            return 'in_progress';
        }

        return $this->status;
    }

    /**
     * Check if all activities are completed
     */
    public function allActivitiesCompleted(): bool
    {
        $activities = $this->activities;

        if ($activities->isEmpty()) {
            return false;
        }

        return $activities->every(function ($activity) {
            return $activity->status === 'completed';
        });
    }

    /**
     * Vérifie et remet le projet à "en cours" si au moins une activité est en cours
     */
    public function checkAndReopenIfNeeded(): void
    {
        $activities = $this->activities;

        if ($activities->isEmpty()) {
            return;
        }

        // Compter les activités en cours
        $inProgressActivities = $activities->filter(function ($activity) {
            return $activity->status === 'in_progress';
        })->count();

        // Si au moins une activité est en cours, remettre le projet à "en cours"
        if ($inProgressActivities > 0 && $this->status !== 'in_progress') {
            $this->status = 'in_progress';
            $this->save();
        }
    }

}
