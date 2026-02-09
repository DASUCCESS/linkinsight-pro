<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LinkedinProfile;
use Illuminate\Http\Request;

class LinkedinProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $profiles = LinkedinProfile::forUser($user->id)
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->get();

        return view('user.linkedin.profiles.index', compact('profiles'));
    }
}
