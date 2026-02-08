<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        // Static demo numbers for now. Later, wire real analytics.
        $stats = [
            'today_money' => 53000,
            'today_users' => 2300,
            'new_clients' => 3462,
            'sales' => 103430,
        ];

        return view('admin.dashboard.index', compact('stats'));
    }
}
