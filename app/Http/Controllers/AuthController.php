<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $data['email'])->first();

        if ($user && $user->status === 'suspended') {
            return back()->withErrors(['email' => 'Votre compte est suspendu. Contactez l\'administrateur.'])->withInput();
        }

        if (Auth::attempt($data, true)) {
            $request->session()->regenerate();

            // Update last_login_at
            $user = Auth::user();
            $user->last_login_at = Carbon::now();
            $user->save();

            // Optionally dispatch the Login event if you want to keep the listener
            event(new Login('web', $user, true));

            // Redirect admin users to users.index, others to dashboard
            if ($user->role === 'admin') {
                return redirect()->intended(route('users.index'));
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Identifiants invalides'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('welcome');
    }
}
