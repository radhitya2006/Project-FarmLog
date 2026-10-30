<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if ($user?->role === UserRole::Admin && $role === UserRole::Farmer->value) {
            return to_route('admin.dashboard');
        }

        abort_unless($user?->role->value === $role, 403);

        return $next($request);
    }
}
