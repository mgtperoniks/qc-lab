@extends('layouts.modern')

@section('content')
<!-- Welcome Section -->
<div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">QC Management Dashboard</h1>
        <p class="text-slate-500 mt-1">Flange & Fitting Production Line • Lab Unit 04</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('samples.create') }}" class="flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-primary/90">
            <span class="material-symbols-outlined text-[18px]">add_circle</span>
            New Spectrometer Test
        </a>
        <button class="flex items-center gap-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm font-bold text-slate-700 dark:text-white shadow-sm hover:bg-slate-50">
            <span class="material-symbols-outlined text-[18px]">build</span>
            New Mechanical Test
        </button>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <!-- Approved Card -->
    <div class="rounded-xl bg-white dark:bg-slate-900 p-4 shadow-sm border border-slate-100 dark:border-slate-800 flex items-center gap-4 transition-all hover:shadow-md">
        <div class="rounded-lg bg-green-50 dark:bg-green-900/20 p-2.5 text-green-600 flex-shrink-0">
            <span class="material-symbols-outlined !text-[24px]">inventory_2</span>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-xs font-bold text-green-600 mb-0.5 uppercase tracking-wider">Approved</p>
            <div class="flex items-baseline gap-2">
                <h3 class="text-2xl font-bold text-slate-900 dark:text-white leading-none">{{ $todayCount }}</h3>
                <span class="text-[10px] text-slate-400 font-medium">Total</span>
            </div>
        </div>
    </div>

    <!-- Draft Card -->
    <div class="rounded-xl bg-white dark:bg-slate-900 p-4 shadow-sm border border-slate-100 dark:border-slate-800 flex items-center gap-4 transition-all hover:shadow-md">
        <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 p-2.5 text-amber-600 flex-shrink-0">
            <span class="material-symbols-outlined !text-[24px]">pending_actions</span>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-xs font-bold text-amber-600 mb-0.5 uppercase tracking-wider">Draft</p>
            <div class="flex items-baseline gap-2">
                <h3 class="text-2xl font-bold text-slate-900 dark:text-white leading-none">{{ $pendingCount }}</h3>
                <span class="text-[10px] text-slate-400 font-medium">Pending</span>
            </div>
        </div>
    </div>

    <!-- Rejected Card -->
    <div class="rounded-xl bg-white dark:bg-slate-900 p-4 shadow-sm border border-slate-100 dark:border-slate-800 flex items-center gap-4 transition-all hover:shadow-md">
        <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-2.5 text-red-600 flex-shrink-0">
            <span class="material-symbols-outlined !text-[24px]">error_outline</span>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-xs font-bold text-red-600 mb-0.5 uppercase tracking-wider">Rejected</p>
            <div class="flex items-baseline gap-2">
                <h3 class="text-2xl font-bold text-slate-900 dark:text-white leading-none">{{ $recentFailures }}</h3>
                <span class="text-[10px] text-slate-400 font-medium">Total</span>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Table -->
<div class="rounded-xl bg-white dark:bg-slate-900 shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <h2 class="font-bold text-slate-900 dark:text-white">Recent Activity</h2>
        <a href="{{ route('samples.index') }}" class="text-sm font-medium text-primary hover:underline">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-xs font-bold uppercase tracking-wider">
                    <th class="px-6 py-4">Test ID / Heat No</th>
                    <th class="px-6 py-4">Grade / Standard</th>
                    <th class="px-6 py-4">Customer</th>
                    <th class="px-6 py-4">Timestamp</th>
                    <th class="px-6 py-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($recentActivity as $act)
                <tr>
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-primary">{{ $act->report_no ?: 'DRAFT' }}</p>
                        <p class="text-xs text-slate-400">Heat: {{ $act->heat_no ?: '-' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $act->grade }}</p>
                        <p class="text-xs text-slate-400">{{ $act->standard }}</p>
                    </td>
                    <td class="px-6 py-4 text-sm">{{ $act->customer ?: '-' }}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">
                        {{ $act->updated_at->format('H:i') }}
                        <span class="block text-[10px] text-slate-400">{{ $act->updated_at->format('d M') }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $statusClasses = match($act->status) {
                                'APPROVED' => 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400',
                                'REJECTED' => 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400',
                                'SUBMITTED' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
                                default => 'bg-slate-50 text-slate-700 dark:bg-slate-800 dark:text-slate-400',
                            };
                            $dotClasses = match($act->status) {
                                'APPROVED' => 'bg-green-600',
                                'REJECTED' => 'bg-red-600',
                                'SUBMITTED' => 'bg-blue-600',
                                default => 'bg-slate-400',
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1 rounded-full {{ $statusClasses }} px-2.5 py-0.5 text-xs font-bold">
                            <span class="h-1.5 w-1.5 rounded-full {{ $dotClasses }}"></span>
                            {{ $act->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-slate-500 italic">No recent activity found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Action Shortcut Bar -->
<div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="p-6 rounded-xl bg-primary/5 border border-primary/10 flex items-center gap-4">
        <div class="h-12 w-12 rounded-lg bg-primary text-white flex items-center justify-center">
            <span class="material-symbols-outlined">analytics</span>
        </div>
        <div>
            <h4 class="font-bold text-slate-900 dark:text-white">Batch Analysis Report</h4>
            <p class="text-sm text-slate-500">Generate a comprehensive MTC for the latest batches.</p>
        </div>
        <button class="ml-auto text-primary font-bold text-sm">Generate</button>
    </div>
    <div class="p-6 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center gap-4">
        <div class="h-12 w-12 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center">
            <span class="material-symbols-outlined">sync</span>
        </div>
        <div>
            <h4 class="font-bold text-slate-900 dark:text-white">ERP Synchronization</h4>
            <p class="text-sm text-slate-500">Push tested records to the main production ERP.</p>
        </div>
        <button class="ml-auto text-slate-600 dark:text-slate-400 font-bold text-sm">Sync Now</button>
    </div>
</div>
@endsection
