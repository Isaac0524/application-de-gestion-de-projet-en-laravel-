@extends('layout')
@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding: 1.5rem;">
    <h1 style="font-size: 1.75rem; font-weight: 600; margin-bottom: 1.5rem;">Mon travail</h1>
    <form method="GET" action="{{ route('my.work') }}" style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div style="flex: 1; min-width: 200px; @media (max-width: 600px) { min-width: 100%; }">
            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Filtrer par statut</label>
            <select name="status" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                <option value="">Tous les statuts</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Ouvert</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Terminé</option>
                <option value="completed_by_assignee" {{ request('status') === 'completed_by_assignee' ? 'selected' : '' }}>Terminé par assigné</option>
                <option value="finalized" {{ request('status') === 'finalized' ? 'selected' : '' }}>Finalisé</option>
            </select>
        </div>
        <div style="flex: 1; min-width: 200px; @media (max-width: 600px) { min-width: 100%; }">
            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Rechercher par titre</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..." style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
        </div>
        <div style="flex: 1; min-width: 200px; @media (max-width: 600px) { min-width: 100%; }">
            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Trier par</label>
            <select name="sort_by" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                <option value="due_date" {{ request('sort_by') === 'due_date' ? 'selected' : '' }}>Échéance</option>
                <option value="title" {{ request('sort_by') === 'title' ? 'selected' : '' }}>Titre</option>
                <option value="status" {{ request('sort_by') === 'status' ? 'selected' : '' }}>Statut</option>
            </select>
        </div>
        <div style="flex: 1; min-width: 200px; @media (max-width: 600px) { min-width: 100%; }">
            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Ordre</label>
            <select name="sort_direction" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                <option value="asc" {{ request('sort_direction') === 'asc' ? 'selected' : '' }}>Ascendant</option>
                <option value="desc" {{ request('sort_direction') === 'desc' ? 'selected' : '' }}>Descendant</option>
            </select>
        </div>
        <div style="margin-top: 1rem;">
            <button type="submit" style="display: inline-block; padding: 0.5rem 1rem; border: none; border-radius: 4px; font-size: 0.875rem; font-weight: 500; cursor: pointer; background: #2563eb; color: white; @media (max-width: 600px) { width: 100%; text-align: center; }">Appliquer</button>
        </div>
    </form>
    @forelse($byProject as $projectId => $tasks)
        @php $project = $tasks->first()->activity->project; @endphp
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); margin-bottom: 14px;">
            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.75rem;">{{ $project->title }}</h3>
            <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                <thead>
                    <tr>
                        <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; font-size: 0.875rem; background: #f9fafb;">Activité</th>
                        <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; font-size: 0.875rem; background: #f9fafb;">Tâche</th>
                        <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; font-size: 0.875rem; background: #f9fafb;">Statut</th>
                        <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; font-size: 0.875rem; background: #f9fafb;">Échéance</th>
                        <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; font-size: 0.875rem; background: #f9fafb;"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($tasks->groupBy(fn($t)=>$t->activity->id) as $aid => $group)
                    @foreach($group as $t)
                    <tr>
                        <td style="padding: 0.75rem; border-bottom: 1px solid #eee; font-size: 0.875rem; color: #666;">{{ $t->activity->title }}</td>
                        <td style="padding: 0.75rem; border-bottom: 1px solid #eee;">{{ $t->title }}</td>
                        <td style="padding: 0.75rem; border-bottom: 1px solid #eee;"><span style="display: inline-block; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background: #e5e7eb; color: #374151;">{{ $t->status }}</span></td>
                        <td style="padding: 0.75rem; border-bottom: 1px solid #eee; font-size: 0.875rem; color: #666;">{{ $t->due_date ?: '—' }}</td>
                        <td style="padding: 0.75rem; border-bottom: 1px solid #eee; display: flex; gap: 6px; flex-wrap: wrap;">
                            <a href="{{ route('activities.show',$t->activity) }}" style="display: inline-block; padding: 0.5rem 1rem; border: none; border-radius: 4px; font-size: 0.875rem; font-weight: 500; cursor: pointer; background: #2563eb; color: white; text-decoration: none; @media (max-width: 600px) { width: 100%; text-align: center; }">Activité</a>
                            <a href="{{ route('tasks.show',$t) }}" style="display: inline-block; padding: 0.5rem 1rem; border: none; border-radius: 4px; font-size: 0.875rem; font-weight: 500; cursor: pointer; background: #2563eb; color: white; text-decoration: none; @media (max-width: 600px) { width: 100%; text-align: center; }">Détails</a>
                        </td>
                    </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); font-size: 0.875rem; color: #666;">Aucun élément.</div>
    @endforelse
    <div style="margin-top: 1.5rem;">
        {{ $paginatedTasks->links() }}
    </div>
</div>
@endsection
