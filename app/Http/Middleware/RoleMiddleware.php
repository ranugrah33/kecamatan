<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        if ($user->role !== $role) {
            // Redirect based on actual role
            if ($user->isAdmin()) {
                return redirect('/admin/dashboard');
            } elseif ($user->isMasyarakat()) {
                return redirect('/masyarakat/dashboard');
            }
            return redirect('/'); // Fallback
        }

        return $next($request);
    }
}
