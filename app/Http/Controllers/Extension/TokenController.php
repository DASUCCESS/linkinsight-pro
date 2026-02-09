<?php

namespace App\Http\Controllers\Extension;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function __invoke(Request $request)
    {
        $user    = $request->user();
        $appName = config('app.name', 'LinkInsight Pro');

        if (! $user) {
            return response()->json([
                'message'  => 'Not authenticated. Log into '.$appName.' in this browser.',
                'app_name' => $appName,
            ], 401);
        }

        $token = $user->issueExtensionToken();

        return response()->json([
            'token'    => $token,
            'app_name' => $appName,
            'user'     => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}
