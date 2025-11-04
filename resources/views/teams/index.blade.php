@extends('layout')
@section('content')
<div style="display: grid; gap: 1.5rem; padding: 1.5rem; max-width: 1200px; margin: 0 auto; grid-template-columns: 1fr;">
    <!-- Gestion des équipes -->
    <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Équipes</h2>
        <button onclick="openModal('createTeamModal')" style="padding: 0.5rem 1rem; background:#2563eb; color:white; border:none; border-radius:4px; cursor:pointer;">+ Nouvelle équipe</button>

        <table style="width:100%; border-collapse:collapse; margin-top:1rem;">
            <thead>
                <tr>
                    <th style="padding:0.75rem; text-align:left; border-bottom:1px solid #eee; font-weight:600; font-size:0.875rem; background:#f9fafb;">Nom</th>
                    <th style="padding:0.75rem; text-align:left; border-bottom:1px solid #eee; font-weight:600; font-size:0.875rem; background:#f9fafb;">Membres</th>
                    <th style="padding:0.75rem; text-align:right; border-bottom:1px solid #eee;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($teams as $t)
                <tr>
                    <td style="padding:0.75rem; border-bottom:1px solid #eee;">{{ $t->name }}</td>
                    <td style="padding:0.75rem; border-bottom:1px solid #eee; font-size:0.875rem; color:#666;">
                        @forelse($t->users as $u)
                            {{ $u->name }}@if(!$loop->last), @endif
                        @empty —
                        @endforelse
                    </td>
                    <td style="padding:0.75rem; border-bottom:1px solid #eee; text-align:right;">
                        <button onclick="openAttachModal({{ $t->id }})" style="padding:0.25rem 0.75rem; background:#10b981; color:white; border:none; border-radius:4px; cursor:pointer;">Ajouter</button>
                        <button onclick="openDetachModal({{ $t->id }})" style="padding:0.25rem 0.75rem; background:#b91c1c; color:white; border:none; border-radius:4px; cursor:pointer;">Retirer</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal création équipe -->
<div id="createTeamModal" class="modal">
  <div class="modal-content">
    <h3>Créer une équipe</h3>
    <form method="POST" action="{{ route('teams.store') }}">
        @csrf
        <label>Nom</label>
        <input type="text" name="name" required>
        <label>Description</label>
        <input type="text" name="description">
        <div class="modal-actions">
            <button type="submit" class="btn-primary">Créer</button>
            <button type="button" onclick="closeModal('createTeamModal')" class="btn-secondary">Annuler</button>
        </div>
    </form>
  </div>
</div>

<!-- Modal attach -->
<div id="attachModal" class="modal">
  <div class="modal-content">
    <h3>Ajouter un membre</h3>
    <form method="POST" action="{{ route('teams.attach', 0) }}" id="attachForm">
        @csrf
        <input type="hidden" name="team_id" id="attach_team_id">
        <label>Membre</label>
        <select name="user_id" required>
            @foreach($users as $u)
                @if($u->role === 'member')
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                @endif
            @endforeach
        </select>
        <div class="modal-actions">
            <button type="submit" class="btn-primary">Ajouter</button>
            <button type="button" onclick="closeModal('attachModal')" class="btn-secondary">Annuler</button>
        </div>
    </form>
  </div>
</div>

<!-- Modal detach -->
<div id="detachModal" class="modal">
  <div class="modal-content">
    <h3>Retirer un membre</h3>
    <form method="POST" action="{{ route('teams.detach', 0) }}" id="detachForm">
        @csrf
        <input type="hidden" name="team_id" id="detach_team_id">
        <label>Membre</label>
        <select name="user_id" required>
            @foreach($users as $u)
                @if($u->role === 'member')
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                @endif
            @endforeach
        </select>
        <div class="modal-actions">
            <button type="submit" class="btn-danger">Retirer</button>
            <button type="button" onclick="closeModal('detachModal')" class="btn-secondary">Annuler</button>
        </div>
    </form>
  </div>
</div>

<style>
/* Styles modals */
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; }
.modal-content { background:white; padding:1.5rem; border-radius:8px; width:90%; max-width:400px; }
.modal-content h3 { margin-bottom:1rem; }
.modal-content label { display:block; margin-top:0.5rem; font-size:0.9rem; font-weight:500; }
.modal-content input, .modal-content select { width:100%; padding:0.5rem; margin-top:0.25rem; border:1px solid #ddd; border-radius:4px; }
.modal-actions { margin-top:1rem; display:flex; gap:0.5rem; justify-content:flex-end; }
.btn-primary { background:#2563eb; color:white; border:none; padding:0.5rem 1rem; border-radius:4px; cursor:pointer; }
.btn-secondary { background:#6b7280; color:white; border:none; padding:0.5rem 1rem; border-radius:4px; cursor:pointer; }
.btn-danger { background:#b91c1c; color:white; border:none; padding:0.5rem 1rem; border-radius:4px; cursor:pointer; }
</style>

<script>
function openModal(id){ document.getElementById(id).style.display='flex'; }
function closeModal(id){ document.getElementById(id).style.display='none'; }

function openAttachModal(teamId){
    document.getElementById('attach_team_id').value = teamId;
    document.getElementById('attachForm').action = '/teams/'+teamId+'/attach';
    openModal('attachModal');
}
function openDetachModal(teamId){
    document.getElementById('detach_team_id').value = teamId;
    document.getElementById('detachForm').action = '/teams/'+teamId+'/detach';
    openModal('detachModal');
}
</script>
@endsection
