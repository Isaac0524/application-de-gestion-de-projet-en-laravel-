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

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700">Total Utilisateurs</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700">Membre</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['members'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700">Managers</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['managers'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700">Administrateur</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['admin'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700">Dernière Connexion</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['last_login'] ?? 'Aucune' }}</p>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Répartition des Rôles</h3>
                <canvas id="roleChart"></canvas>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Statut des Utilisateurs</h3>
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Search and Create Button Section -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Search Form -->
                <div class="flex-1">
                    <form id="searchForm" class="flex gap-2">
                        <input type="text" name="search" id="searchInput"
                               placeholder="Rechercher un utilisateur par nom ou email..."
                               class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                        <button type="button" onclick="clearSearch()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            <i class="fas fa-times"></i> Effacer
                        </button>
                    </form>
                </div>

                <!-- Create User Button -->
                <div>
                    <button onclick="openCreateModal()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        <i class="fas fa-plus"></i> Créer un utilisateur
                    </button>
                </div>
            </div>
        </div>


        <!-- Users Table -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Utilisateurs</h2>
            @if ($users->isEmpty())
                <div class="text-center py-8">
                    <i class="fas fa-users text-gray-400 text-4xl mb-2"></i>
                    <h3 class="text-lg font-semibold text-gray-700">Aucun utilisateur</h3>
                    <p class="text-gray-500">Aucun utilisateur n'a été trouvé.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <!-- Only showing the relevant table section for brevity -->
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rôle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dernière Connexion</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="usersTableBody">
                            @foreach ($users as $u)
                                <tr class="user-row" data-name="{{ strtolower($u->name) }}" data-email="{{ strtolower($u->email) }}">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $u->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $u->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($u->role) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $u->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($u->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($u->last_login_at)
                                            {{ \Carbon\Carbon::parse($u->last_login_at)->diffForHumans() }}
                                        @else
                                            Jamais
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex gap-2">
                                            <button
                                                onclick="openEditModal({{ $u->id }}, '{{ $u->name }}', '{{ $u->email }}', '{{ $u->role }}', '{{ $u->status }}')"
                                                class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600"
                                                >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="{{ route('users.reset_password', $u) }}"
                                                onsubmit="return confirm('Confirmer la réinitialisation du mot de passe ?')" data-form-confirmation>
                                                @csrf @method('PUT')
                                                <input type="password" name="password" class="hidden">
                                                <button type="submit"
                                                    class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600"
                                                    {{ $u->id === Auth::id() ? 'disabled' : '' }}>
                                                    <i class="fas fa-key"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Create User Modal -->
        <div id="createModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Créer un utilisateur</h2>
                <form method="POST" action="{{ route('users.store') }}" data-form-confirmation>
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Nom</label>
                        <input type="text" name="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Rôle</label>
                        <select name="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="member">Membre</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Statut</label>
                        <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="active">Actif</option>
                            <option value="suspended">Suspendu</option>
                        </select>
                    </div>
                    <div class="mb-4 relative">
                        <label class="block text-sm font-medium text-gray-700">Mot de passe</label>
                        <input type="password" name="password" id="create-password"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            placeholder="Laisser vide pour générer">
                        <button type="button" onclick="togglePassword('create-password')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5 mt-6">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeCreateModal()"
                            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Annuler</button>
                        <button type="submit"
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Créer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Modifier l'utilisateur</h2>
                <form id="editForm" method="POST" data-form-confirmation>
                    @csrf @method('PUT')
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Nom</label>
                        <input type="text" name="name" id="edit-name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="edit-email"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Rôle</label>
                        <select name="role" id="edit-role"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="member">Membre</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Administrateur</option>

                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Statut</label>
                        <select name="status" id="edit-status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="active">Actif</option>
                            <option value="suspended">Suspendu</option>
                        </select>
                    </div>
                    <div class="mb-4 relative">
                        <label class="block text-sm font-medium text-gray-700">Mot de passe</label>
                        <input type="password" name="password" id="edit-password"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            placeholder="Laisser vide pour ne pas modifier">
                        <button type="button" onclick="togglePassword('edit-password')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5 mt-6">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeEditModal()"
                            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Annuler</button>
                        <button type="submit"
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Chart.js and JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Password toggle
        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = input.nextElementSibling.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Create Modal handling
        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }

        // Edit Modal handling
        function openEditModal(id, name, email, role, status) {
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-email').value = email;
            document.getElementById('edit-role').value = role;
            document.getElementById('edit-status').value = status;
            document.getElementById('editForm').action = `/users/${id}`;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Search functionality
        function searchUsers() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.user-row');

            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const email = row.getAttribute('data-email');

                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            const rows = document.querySelectorAll('.user-row');
            rows.forEach(row => row.style.display = '');
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Search form submission
            document.getElementById('searchForm').addEventListener('submit', function(e) {
                e.preventDefault();
                searchUsers();
            });

            // Real-time search
            document.getElementById('searchInput').addEventListener('input', searchUsers);

            // Close modals when clicking outside
            document.addEventListener('click', function(event) {
                const createModal = document.getElementById('createModal');
                const editModal = document.getElementById('editModal');

                if (event.target === createModal) {
                    closeCreateModal();
                }
                if (event.target === editModal) {
                    closeEditModal();
                }
            });

            // Close modals on escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeCreateModal();
                    closeEditModal();
                }
            });
        });

        // Charts
        const roleChart = new Chart(document.getElementById('roleChart'), {
            type: 'pie',
            data: {
                labels: ['Admin', 'Membres', 'Managers'],
                datasets: [{
                    data: [{{ $stats['admin'] }}, {{ $stats['members'] }}, {{ $stats['managers'] }}],
                    backgroundColor: ['#DC2626', '#3B82F6', '#10B981']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });

        const statusChart = new Chart(document.getElementById('statusChart'), {
            type: 'pie',
            data: {
                labels: ['Actifs', 'Suspendus'],
                datasets: [{
                    data: [{{ $stats['active_users'] }},
                        {{ $stats['total_users'] - $stats['active_users'] }}
                    ],
                    backgroundColor: ['#10B981', '#EF4444']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    </script>
@endsection
