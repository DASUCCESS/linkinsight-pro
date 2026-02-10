<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LinkedinProfile;
use App\Models\LinkedinPost;
use App\Models\LinkedinSyncJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today      = Carbon::today();
        $last24h    = Carbon::now()->subDay();
        $last7days  = Carbon::now()->subDays(7);

        $totalUsers      = User::count();
        $totalProfiles   = LinkedinProfile::count();
        $totalPosts      = LinkedinPost::count();
        $syncJobs24h     = LinkedinSyncJob::where('created_at', '>=', $last24h)->count();
        $activeUsers24h  = LinkedinSyncJob::where('created_at', '>=', $last24h)->distinct('user_id')->count('user_id');

        $stats = [
            'total_users'     => $totalUsers,
            'total_profiles'  => $totalProfiles,
            'total_posts'     => $totalPosts,
            'sync_jobs_24h'   => $syncJobs24h,
            'active_users_24h'=> $activeUsers24h,
        ];

        $usersQuery = User::query()
            ->withCount(['linkedinProfiles', 'linkedinPosts'])
            ->with(['linkedinProfiles' => function ($q) {
                $q->select('id', 'user_id', 'name')->orderByDesc('is_primary');
            }]);

        if ($search = $request->query('q')) {
            $usersQuery->where(function ($q2) use ($search) {
                $q2->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $topUsers = $usersQuery
            ->orderByDesc('linkedin_posts_count')
            ->paginate(10)
            ->withQueryString();

        $recentSyncJobs = LinkedinSyncJob::with('user')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.dashboard.index', compact('stats', 'topUsers', 'recentSyncJobs'));
    }
}
