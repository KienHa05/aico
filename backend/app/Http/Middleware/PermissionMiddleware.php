<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  array<int, string>  $permissions
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $permissions = collect($permissions)
            ->flatMap(fn(string $permission) => explode(',', $permission))
            ->map(fn(string $permission) => trim($permission))
            ->filter()
            ->values()
            ->all();

        if ($permissions === []) {
            return response()->json([
                'success' => false,
                'message' => 'Permission is required.',
            ], 403);
        }

        $user = $request->user();

        if ($user === null || ! $user->hasAnyPermission($permissions)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
            ], 403);
        }

        return $next($request);
    }
}
