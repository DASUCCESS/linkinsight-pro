<?php

namespace App\Http\Middleware;

use App\Services\Installer\InstallationState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    public function __construct(protected InstallationState $installationState)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $installed = $this->installationState->isInstalled();

        // Allow installer, assets, and health checks before installation
        if (! $installed) {
            if (! $request->is('installer*') && ! $request->is('storage/*')) {
                return redirect()->route('installer.requirements');
            }

            return $next($request);
        }

        if ($request->is('installer*')) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
