<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LinkedinAnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        protected LinkedinAnalyticsService $analyticsService
    ) {}

    public function index(Request $request)
    {
        $from = $request->query('from');
        $to   = $request->query('to');

        $summary = $this->analyticsService->getSystemSummary($from, $to);

        return view('admin.analytics.index', compact('summary'));
    }
}
