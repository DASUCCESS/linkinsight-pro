<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LinkedinProfile;
use Illuminate\Http\Request;

class LinkedinProfileController extends Controller
{
    public function index(Request $request)
    {
        $user   = $request->user();
        $search = $request->query('q');

        $query = LinkedinProfile::forUser($user->id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('headline', 'like', '%' . $search . '%')
                    ->orWhere('public_url', 'like', '%' . $search . '%');
            });
        }

        $profiles = $query
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('user.linkedin.profiles.index', compact('profiles', 'search'));
    }
}
