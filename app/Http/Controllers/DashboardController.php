<?php

namespace App\Http\Controllers;

use App\Models\Sample;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalApproved = Sample::where('status', 'APPROVED')->count();
        $pendingInput = Sample::where('status', 'DRAFT')->count();
        $recentFailures = Sample::where('status', 'REJECTED')->count();

        $recentActivity = Sample::latest('updated_at')
            ->take(5)
            ->get();

        return view('dashboard', [
            'todayCount' => $totalApproved,
            'pendingCount' => $pendingInput,
            'recentFailures' => $recentFailures,
            'recentActivity' => $recentActivity
        ]);
    }
}
