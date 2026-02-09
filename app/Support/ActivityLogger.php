<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ActivityLogger
{
    public static function log(string $action, array $meta = [], ?Request $request = null): void
    {
        $request = $request ?: request();

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'action'     => $action,
            'meta'       => $meta ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);
    }
}
