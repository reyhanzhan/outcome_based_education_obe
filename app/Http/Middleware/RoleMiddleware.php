<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Pisahkan string roles berdasarkan tanda | dan konversi ke lowercase
        $allowedRoles = array_map('strtolower', explode('|', $roles));

        if (!in_array(strtolower($user->role), $allowedRoles)) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}