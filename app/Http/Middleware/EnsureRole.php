<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::error('Unauthenticated.', 401);
        }

        $current = $user->role->value;

        if (! in_array($current, $roles, true)) {
            return ApiResponse::error('You do not have permission to perform this action.', 403);
        }

        return $next($request);
    }
}
