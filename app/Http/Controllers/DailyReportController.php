<?php

namespace App\Http\Controllers;

use App\Models\Sample;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        $dateStr = $request->get('date', now()->format('Y-m-d'));
        $date = Carbon::parse($dateStr);

        // Get all samples for the selected date
        $samples = Sample::whereDate('test_date', $date)
            ->with(['spectroResult', 'tensileTest', 'hardnessTest'])
            ->orderBy('id', 'desc')
            ->get();

        // Statistics
        $stats = [
            'total' => $samples->count(),
            'approved' => $samples->where('status', 'APPROVED')->count(),
            'pending' => $samples->whereIn('status', ['DRAFT', 'SUBMITTED', 'REJECTED'])->count(),
        ];

        // Grade distribution
        $gradeDistribution = $samples->groupBy('grade')
            ->map(function ($group) use ($stats) {
                return [
                    'count' => $group->count(),
                    'percentage' => $stats['total'] > 0 ? round(($group->count() / $stats['total']) * 100) : 0
                ];
            })->sortDesc();

        return view('reports.daily', compact('samples', 'stats', 'date', 'gradeDistribution'));
    }
}
