<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagerOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        \Log::info('=== DEBUG MIDDLEWARE MANAGER ===');
        \Log::info('User existe : ' . ($user ? 'OUI' : 'NON'));
        \Log::info('User ID : ' . ($user ? $user->id : 'N/A'));
        \Log::info('Role brut : [' . ($user ? $user->role : 'N/A') . ']');
        \Log::info('Role === manager : ' . ($user && $user->role === 'manager' ? 'OUI' : 'NON'));
        \Log::info('===============================');

        if (!$user || $user->role !== 'manager') {
            abort(403, 'Accès interdit : vous devez être un manager pour accéder à cette ressource.');
        }

        \Log::info('SUCCÈS : Accès manager autorisé');
        return $next($request);
    }
}
