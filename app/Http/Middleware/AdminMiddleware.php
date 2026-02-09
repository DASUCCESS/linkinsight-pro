<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized');
        }

        if (! ($user->is_admin || $user->role === 'admin')) {
            abort(403, 'Unauthorized – Admin access only');
        }

        if ($user->status !== 'active') {
            abort(403, 'Account inactive');
        }

        return $next($request);
    }
}
