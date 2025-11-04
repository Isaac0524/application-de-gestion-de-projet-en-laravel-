<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::with('users')->orderBy('name')->get();
        $users = User::orderBy('name')->get();
        return view('teams.index', compact('teams','users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'description'=>'nullable|string'
        ]);
        Team::create($data);
        return back()->with('ok','Équipe créée');
    }

    public function attachUser(Team $team, Request $request)
    {
        $request->validate(['user_id'=>'required|exists:users,id']);
        $team->users()->syncWithoutDetaching([$request->user_id]);
        return back()->with('ok','Membre ajouté');
    }

    public function detachUser(Team $team, Request $request)
    {
        $request->validate(['user_id'=>'required|exists:users,id']);
        $team->users()->detach($request->user_id);
        return back()->with('ok','Membre retiré');
    }
}
