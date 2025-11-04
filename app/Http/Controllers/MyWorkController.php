<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MyWorkController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = $user->assignedTasks()->with('activity.project');

        // Filter by status
        if ($request->has('status') && in_array($request->status, ['open', 'in_progress', 'completed', 'completed_by_assignee', 'finalized'])) {
            $query->where('status', $request->status);
        }

        // Search by title
        if ($request->has('search') && $request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Sort by column
        $sortBy = $request->input('sort_by', 'due_date');
        $sortDirection = $request->input('sort_direction', 'asc');
        if (in_array($sortBy, ['due_date', 'title', 'status'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('due_date', 'asc');
        }

        // Paginate results
        $paginatedTasks = $query->paginate(10)->appends($request->query());
        $byProject = $paginatedTasks->getCollection()->groupBy(fn($t) => $t->activity->project->id);

        return view('mywork.index', compact('byProject', 'paginatedTasks'));
    }
}
