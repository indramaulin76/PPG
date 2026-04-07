<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminLevel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  - Allowed roles for this route
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')->withErrors([
                'access' => 'Anda harus login terlebih dahulu.'
            ]);
        }

        $user = auth()->user();

        // Developer always has access
        if ($user->role === \App\Models\User::ROLE_DEVELOPER) {
            return $next($request);
        }

        // Check if user is active
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'access' => 'Akun Anda tidak aktif. Hubungi administrator.'
            ]);
        }

        // Check if user's role is in the allowed roles
        if (!in_array($user->role, $roles)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki hak akses untuk halaman ini.');
        }

        return $next($request);
    }
}
