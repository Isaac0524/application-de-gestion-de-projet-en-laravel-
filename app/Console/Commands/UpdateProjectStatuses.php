<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use Carbon\Carbon;

class UpdateProjectStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically update project statuses from pending to in_progress based on start_date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        // Find projects that should transition from pending to in_progress
        $projectsToUpdate = Project::where('status', 'pending')
            ->whereNotNull('start_date')
            ->where('start_date', '<=', $today)
            ->get();

        $count = $projectsToUpdate->count();

        if ($count > 0) {
            foreach ($projectsToUpdate as $project) {
                $project->status = 'in_progress';
                $project->save();

                $this->info("Project '{$project->title}' status updated from pending to in_progress");
            }

            $this->info("Successfully updated {$count} project(s)");
        } else {
            $this->info('No projects need status update');
        }

        return Command::SUCCESS;
    }
}
