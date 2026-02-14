<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LinkedinSyncJob;
use Illuminate\Http\Request;

class LinkedinSyncJobsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $status = $request->query('status');
        $type   = $request->query('type');
        $source = $request->query('source');
        $q      = $request->query('q');

        $jobs = LinkedinSyncJob::query()
            ->where('user_id', $user->id)
            ->when($status, fn ($qb) => $qb->where('status', $status))
            ->when($type, fn ($qb) => $qb->where('type', $type))
            ->when($source, fn ($qb) => $qb->where('source', $source))
            ->when($q, function ($qb) use ($q) {
                $qb->where(function ($qq) use ($q) {
                    $qq->where('error_message', 'like', '%' . $q . '%')
                       ->orWhere('type', 'like', '%' . $q . '%')
                       ->orWhere('source', 'like', '%' . $q . '%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('user.linkedin.sync-jobs.index', compact('jobs'));
    }
}
