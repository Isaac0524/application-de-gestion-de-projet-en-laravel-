<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;
use Carbon\Carbon;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'managers' => User::where('role', 'manager')->count(),
            'admin' => User::where('role', 'admin')->count(),
            'members' => User::where('role', 'member')->count(),
            'last_login' => User::whereNotNull('last_login_at')->orderBy('last_login_at', 'desc')->first()?->last_login_at?->diffForHumans()
        ];
        return view('users.index', compact('users', 'stats'));
    }

        public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => ['required', Rule::in(['admin','member', 'manager'])],
            'password' => 'nullable|string|min:6',
            'status' => ['required', Rule::in(['active', 'suspended'])]
        ]);

        $password = $data['password'] ?: Str::password(10);
        $hashedPassword = Hash::make($password);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'status' => $data['status'],
            'password' => $hashedPassword,
        ]);

        return back()->with('success', 'Compte créé: ' . $user->email . ' / Mot de passe: ' . $hashedPassword);
    }
    public function update(User $user, Request $request)
    {


        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['member', 'manager'])],
            'status' => ['required', Rule::in(['active', 'suspended'])],
            'password' => 'nullable|string|min:6'
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'status' => $data['status'],
            'password' => $data['password'] ? Hash::make($data['password']) : $user->password,
        ]);

        return back()->with('success', 'Utilisateur mis à jour: ' . $user->email);
    }

    public function resetPassword(User $user, Request $request)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas réinitialiser votre propre mot de passe.');
        }

        $request->validate(['password' => 'nullable|string|min:6']);
        $new = $request->input('password') ?: Str::password(10);
        $user->password = Hash::make($new);
        $user->save();

        // Send password via email
        \Mail::to($user->email)->send(new \App\Mail\PasswordResetMail($user, $new));

        // Show masked password on page
        $maskedPassword = substr($new, 0, 3) . str_repeat('*', strlen($new) - 6) . substr($new, -3);

        return back()->with('success', 'Le nouveau mot de passe a été envoyé à l\'adresse email de ' . $user->email . '. Mot de passe masqué: ' . $maskedPassword);
    }

    public function changeRole(User $user, Request $request)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas modifier votre propre rôle.');
        }

        $data = $request->validate(['role' => ['required', Rule::in(['member', 'manager'])]]);
        $user->role = $data['role'];
        $user->save();

        return back()->with('success', 'Rôle mis à jour pour ' . $user->email);
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $email = $user->email;
        $user->delete();
        return back()->with('success', 'Utilisateur supprimé: ' . $email);
    }

    /**
     * Afficher la gestion des gestionnaires de projet
     */
    public function projectManagers()
    {
        // Récupérer tous les projets avec leurs propriétaires actuels
        $projects = Project::with('owner')->latest()->get();

        // Récupérer tous les managers actifs
        $managers = User::where('role', 'manager')
                      ->where('status', 'active')
                      ->orderBy('name')
                      ->get();

        return view('users.project_managers', compact('projects', 'managers'));
    }

    /**
     * Changer le gestionnaire d'un projet
     */
    public function changeProjectManager(Request $request, Project $project)
    {
        $data = $request->validate([
            'new_manager_id' => 'required|exists:users,id'
        ]);

        // Vérifier que le nouvel utilisateur est bien un manager
        $newManager = User::findOrFail($data['new_manager_id']);
        if ($newManager->role !== 'manager') {
            return back()->with('error', 'Le nouvel utilisateur doit être un manager.');
        }

        // Vérifier que le manager est actif
        if ($newManager->status !== 'active') {
            return back()->with('error', 'Le manager doit être actif pour être assigné à un projet.');
        }

        // Changer le propriétaire du projet
        $oldManager = $project->owner;
        $project->owner_id = $data['new_manager_id'];
        $project->save();

        return back()->with('success',
            "Gestionnaire changé: " .
            "Projet '{$project->title}' " .
            "de '{$oldManager->name}' à '{$newManager->name}'"
        );
    }
}
