<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
{

    protected $fillable = ['name','description','project_id'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * L'équipe appartient à un projet (optionnel)
     */
    public function project()
    {
        return $this->belongsTo(\App\Models\Project::class);
    }

}
