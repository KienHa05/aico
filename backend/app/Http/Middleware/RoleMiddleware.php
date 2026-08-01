<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        $user = $request->user();

        $roles = collect($roles)
            ->flatMap(fn(string $role) => explode(',', $role))
            ->map(fn(string $role) => trim($role))
            ->filter()
            ->values()
            ->all();

        if (! $user || ! $user->hasAnyRole($roles)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Forbidden.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
