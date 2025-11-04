@extends('layout')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Alerts -->
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <!-- Header -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Gestion des Gestionnaires de Projet</h1>
            <p class="text-gray-600">Modifiez les gestionnaires assignés aux projets</p>
        </div>

        <!-- Projects Table -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Liste des Projets</h2>

            @if ($projects->isEmpty())
                <div class="text-center py-8">
                    <i class="fas fa-folder-open text-gray-400 text-4xl mb-2"></i>
                    <h3 class="text-lg font-semibold text-gray-700">Aucun projet</h3>
                    <p class="text-gray-500">Aucun projet n'a été trouvé.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Projet</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Gestionnaire Actuel</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date de création</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($projects as $project)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ $project->title }}</div>
                                        <div class="text-sm text-gray-500">{{ Str::limit($project->description, 50) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                                                    {{ strtoupper(substr($project->owner->name, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $project->owner->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $project->owner->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $project->status === 'in_progress' ? 'bg-green-100 text-green-800' :
                                               ($project->status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                               ($project->status === 'completed' ? 'bg-blue-100 text-blue-800' :
                                               'bg-gray-100 text-gray-800')) }}">
                                            @if ($project->status === 'in_progress')
                                                EN COURS
                                            @elseif ($project->status === 'pending')
                                                EN ATTENTE
                                            @elseif ($project->status === 'completed')
                                                TERMINÉ
                                            @else
                                                {{ strtoupper($project->status) }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $project->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <!-- Change Manager Button -->
                                        <button onclick="openChangeManagerModal({{ $project->id }}, '{{ $project->title }}', {{ $project->owner_id }})"
                                            class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                            <i class="fas fa-exchange-alt"></i> Changer
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Change Manager Modal -->
    <div id="changeManagerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Changer le gestionnaire</h2>
            <form id="changeManagerForm" method="POST" data-form-confirmation>
                @csrf
                @method('PUT')
                <input type="hidden" name="project_id" id="modal-project-id">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Projet</label>
                    <p class="text-lg font-semibold text-gray-900" id="modal-project-title"></p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nouveau gestionnaire</label>
                    <select name="new_manager_id" id="new_manager_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="">Sélectionner un gestionnaire</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->name }} ({{ $manager->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeChangeManagerModal()"
                        class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Annuler</button>
                    <button type="submit"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openChangeManagerModal(projectId, projectTitle, currentManagerId) {
            document.getElementById('modal-project-id').value = projectId;
            document.getElementById('modal-project-title').textContent = projectTitle;

            // Set the current manager as selected by default
            const select = document.getElementById('new_manager_id');
            select.value = currentManagerId;

            // Update form action
            document.getElementById('changeManagerForm').action = `/projects/${projectId}/change-manager`;

            document.getElementById('changeManagerModal').classList.remove('hidden');
        }

        function closeChangeManagerModal() {
            document.getElementById('changeManagerModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('changeManagerModal');
            if (event.target === modal) {
                closeChangeManagerModal();
            }
        });

        // Close modal on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeChangeManagerModal();
            }
        });

        // Initialize form confirmation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('changeManagerForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const managerId = document.getElementById('new_manager_id').value;
                    if (!managerId) {
                        e.preventDefault();
                        alert('Veuillez sélectionner un gestionnaire.');
                    }
                });
            }
        });
    </script>

    <style>
        .container {
            max-width: 1200px;
        }

        table {
            border-collapse: separate;
            border-spacing: 0;
        }

        th {
            background-color: #f9fafb;
        }

        tr:hover {
            background-color: #f8f9fa;
        }
    </style>
@endsection
