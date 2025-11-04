@extends('layout')
@section('content')
<div style="display: grid; gap: 1.5rem; padding: 1.5rem; max-width: 1200px; margin: 0 auto; grid-template-columns: 1fr; @media (min-width: 768px) { grid-template-columns: repeat(2, 1fr); }">
  <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
    <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">{{ $task->title }}</h2>
    <p style="margin-bottom: 0.5rem;">{{ $task->description }}</p>
    <p style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem;">
      Projet: <a href="{{ route('projects.show',$task->activity->project) }}" style="color: #2563eb; text-decoration: none;">{{ $task->activity->project->title }}</a>
      • Activité: <a href="{{ route('activities.show',$task->activity) }}" style="color: #2563eb; text-decoration: none;">{{ $task->activity->title }}</a>
      • Statut: <span style="display: inline-block; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background: #e5e7eb; color: #374151;">{{ $task->status }}</span>
    </p>
    <p style="font-size: 0.875rem; color: #666;">
      Assignés:
      @forelse($task->assignees as $u) {{ $u->name }}@if(!$loop->last), @endif @empty — @endforelse
    </p>
    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px;">
      @if($user->id === $task->activity->project->owner_id)
        <a class="btn" href="{{ route('tasks.edit',[$task->activity,$task]) }}" style="display: inline-block; padding: 0.5rem 1rem; border: none; border-radius: 4px; font-size: 0.875rem; font-weight: 500; cursor: pointer; background: #2563eb; color: white; text-decoration: none; @media (max-width: 600px) { width: 100%; text-align: center; }">Éditer</a>
      @endif
      @if($task->assignees->contains('id',$user->id) && $task->status!=='finalized')
        <form method="POST" action="{{ route('tasks.complete_by_assignee',$task) }}">@csrf<button style="display: inline-block; padding: 0.5rem 1rem; border: none; border-radius: 4px; font-size: 0.875rem; font-weight: 500; cursor: pointer; background: #dc2626; color: white; @media (max-width: 600px) { width: 100%; text-align: center; }">Je l’ai finie</button></form>
      @endif
      @if($user->id === $task->activity->project->owner_id && $task->status==='completed_by_assignee')
        <form method="POST" action="{{ route('tasks.finalize',$task) }}">@csrf<button style="display: inline-block; padding: 0.5rem 1rem; border: none; border-radius: 4px; font-size: 0.875rem; font-weight: 500; cursor: pointer; background: #16a34a; color: white; @media (max-width: 600px) { width: 100%; text-align: center; }">Finaliser</button></form>
      @endif
    </div>
  </div>
  <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.75rem;">Sous-tâches</h3>
      @if($task->assignees->contains('id',$user->id) || $user->id === $task->activity->project->owner_id)
      <form method="POST" action="{{ route('subtasks.store',$task) }}" style="display: flex; gap: 8px; align-items: flex-end;">
        @csrf
        <div style="flex: 1; min-width: 150px; @media (max-width: 600px) { min-width: 100%; }">
          <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Titre</label>
          <input type="text" name="title" placeholder="Sous-tâche" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
        </div>
        <div style="flex: 1; min-width: 150px; @media (max-width: 600px) { min-width: 100%; }">
          <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Échéance</label>
          <input type="date" name="due_date" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
        </div>
        <div style="flex: 1; min-width: 150px; @media (max-width: 600px) { min-width: 100%; }">
          <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Description</label>
          <input type="text" name="description" placeholder="Optionnel" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
        </div>
        <button style="display: inline-block; padding: 0.5rem 1rem; border: none; border-radius: 4px; font-size: 0.875rem; font-weight: 500; cursor: pointer; background: #2563eb; color: white; @media (max-width: 600px) { width: 100%; text-align: center; }">Ajouter</button>
      </form>
      @endif
    </div>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
      <thead>
        <tr>
          <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; font-size: 0.875rem; background: #f9fafb;">Titre</th>
          <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; font-size: 0.875rem; background: #f9fafb;">Par</th>
          <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; font-size: 0.875rem; background: #f9fafb;">Échéance</th>
          <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; font-size: 0.875rem; background: #f9fafb;">Statut</th>
          <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; font-size: 0.875rem; background: #f9fafb;"></th>
        </tr>
      </thead>
      <tbody>
      @forelse($task->subtasks as $st)
        <tr>
          <td style="padding: 0.75rem; border-bottom: 1px solid #eee;">{{ $st->title }}</td>
          <td style="padding: 0.75rem; border-bottom: 1px solid #eee; font-size: 0.875rem; color: #666;">{{ $st->user->name }}</td>
          <td style="padding: 0.75rem; border-bottom: 1px solid #eee; font-size: 0.875rem; color: #666;">{{ $st->due_date ?: '—' }}</td>
          <td style="padding: 0.75rem; border-bottom: 1px solid #eee;"><span style="display: inline-block; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background: #e5e7eb; color: #374151;">{{ $st->status }}</span></td>
          <td style="padding: 0.75rem; border-bottom: 1px solid #eee; display: flex; gap: 6px; flex-wrap: wrap;">
            @if($st->user_id === $user->id || $user->id === $task->activity->project->owner_id)
              <form method="POST" action="{{ route('subtasks.toggle',$st) }}">@csrf<button style="display: inline-block; padding: 0.5rem 1rem; border: none; border-radius: 4px; font-size: 0.875rem; font-weight: 500; cursor: pointer; background: #2563eb; color: white; @media (max-width: 600px) { width: 100%; text-align: center; }">{{ $st->status==='completed'?'Rouvrir':'Terminer' }}</button></form>
              <details>
                <summary style="cursor: pointer; display: inline-block; padding: 0.5rem 1rem; border-radius: 4px; background: #f3f4f6; font-size: 0.875rem;">Modifier</summary>
                <form method="POST" action="{{ route('subtasks.update',$st) }}" style="margin-top: 8px; padding: 1rem; background: #f9fafb; border: 1px solid #ddd; border-radius: 4px;">
                  @csrf @method('PUT')
                  <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                    <div style="flex: 1; min-width: 200px; @media (max-width: 600px) { min-width: 100%; }">
                      <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Titre</label>
                      <input type="text" name="title" value="{{ $st->title }}" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                    </div>
                    <div style="flex: 1; min-width: 200px; @media (max-width: 600px) { min-width: 100%; }">
                      <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Échéance</label>
                      <input type="date" name="due_date" value="{{ $st->due_date }}" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                    </div>
                  </div>
                  <div style="margin-top: 8px;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Description</label>
                    <input type="text" name="description" value="{{ $st->description }}" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                  </div>
                  <div style="margin-top: 8px;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Statut</label>
                    <select name="status" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                      <option value="open" @selected($st->status==='open')>open</option>
                      <option value="in_progress" @selected($st->status==='in_progress')>in_progress</option>
                      <option value="completed" @selected($st->status==='completed')>completed</option>
                    </select>
                  </div>
                  <div style="margin-top: 8px;">
                    <button style="display: inline-block; padding: 0.5rem 1rem; border: none; border-radius: 4px; font-size: 0.875rem; font-weight: 500; cursor: pointer; background: #2563eb; color: white; @media (max-width: 600px) { width: 100%; text-align: center; }">Enregistrer</button>
                  </div>
                </form>
              </details>
              <form method="POST" action="{{ route('subtasks.destroy',$st) }}">@csrf @method('DELETE')<button style="display: inline-block; padding: 0.5rem 1rem; border: none; border-radius: 4px; font-size: 0.875rem; font-weight: 500; cursor: pointer; background: #b91c1c; color: white; @media (max-width: 600px) { width: 100%; text-align: center; }">Supprimer</button></form>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="5" style="padding: 0.75rem; border-bottom: 1px solid #eee; font-size: 0.875rem; color: #666;">Aucune sous-tâche</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
