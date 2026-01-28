<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    public function handle(Request $request, Closure $next): Response
    {
        $installed = config('installer.installed');

        // Allow installer, assets, and health checks before installation
        if (! $installed) {
            if (! $request->is('installer*') && ! $request->is('storage/*')) {
                return redirect()->route('installer.requirements');
            }
        }

        return $next($request);
    }
}
