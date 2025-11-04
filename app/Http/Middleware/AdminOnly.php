<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        \Log::info('=== DEBUG MIDDLEWARE ADMIN ===');
        \Log::info('User existe : ' . ($user ? 'OUI' : 'NON'));
        \Log::info('User ID : ' . ($user ? $user->id : 'N/A'));
        \Log::info('Role brut : [' . ($user ? $user->role : 'N/A') . ']');
        \Log::info('Role === admin : ' . ($user && $user->role === 'admin' ? 'OUI' : 'NON'));
        \Log::info('==============================');

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Accès interdit : vous devez être un administrateur pour accéder à cette ressource.');
        }

        \Log::info('SUCCÈS : Accès administrateur autorisé');
        return $next($request);
    }
}
