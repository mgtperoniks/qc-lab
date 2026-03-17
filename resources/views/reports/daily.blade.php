@extends('layouts.modern', ['title' => 'Daily QC Report'])

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Daily QC Report</h1>
        <p class="text-slate-500 mt-1">Summary of testing activities for {{ $date->format('d F Y') }}.</p>
    </div>
    <div class="flex items-center gap-3">
        <form method="GET" action="{{ route('reports.daily') }}" id="dateFilterForm" class="flex items-center gap-2">
            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mr-1">Tampilkan Tanggal:</label>
            <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" 
                   onchange="document.getElementById('dateFilterForm').submit()"
                   class="rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold text-slate-700 dark:text-white focus:ring-primary shadow-sm">
        </form>
    </div>
</div>

{{-- KPI Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-sm border border-slate-100 dark:border-slate-800 transition-all hover:shadow-md">
        <div class="flex items-center gap-4">
            <div class="h-12 w-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined">analytics</span>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Samples</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['total'] }}</p>
            </div>
        </div>
    </div>
    <div class="rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-sm border border-slate-100 dark:border-slate-800 transition-all hover:shadow-md">
        <div class="flex items-center gap-4">
            <div class="h-12 w-12 rounded-xl bg-green-500/10 flex items-center justify-center text-green-600">
                <span class="material-symbols-outlined">verified</span>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Approved</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['approved'] }}</p>
            </div>
        </div>
    </div>
    <div class="rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-sm border border-slate-100 dark:border-slate-800 transition-all hover:shadow-md">
        <div class="flex items-center gap-4">
            <div class="h-12 w-12 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-600">
                <span class="material-symbols-outlined">pending_actions</span>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Pending/Draft</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['pending'] }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
    {{-- Detailed List --}}
    <div class="xl:col-span-3 space-y-6">
        <div class="rounded-xl bg-white dark:bg-slate-900 shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h2 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">list_alt</span>
                    Aktivitas Pengujian
                </h2>
                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Daily Activity Log</span>
            </div>
            <div class="overflow-x-auto">
                <table id="dailyTable" class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50">
                            <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">ID</th>
                            <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Report / Heat</th>
                            <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Grade</th>
                            <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Material Status</th>
                            <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($samples as $s)
                        <tr>
                            <td class="px-6 py-4 text-xs font-medium text-slate-400">#{{ $s->id }}</td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-slate-900 dark:text-white">QC-{{ $s->report_no ?: 'DRAFT' }}</p>
                                <p class="text-[10px] font-mono text-primary font-bold">{{ $s->heat_no ?: 'No Heat' }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-700 dark:text-slate-300">
                                {{ $s->grade }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusClasses = [
                                        'APPROVED' => 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400',
                                        'SUBMITTED' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
                                        'REJECTED' => 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400',
                                        'DRAFT' => 'bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                                    ];
                                    $cls = $statusClasses[strtoupper($s->status)] ?? $statusClasses['DRAFT'];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-tighter {{ $cls }}">
                                    {{ $s->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center">
                                    <a href="{{ route('reports.pdf', $s) }}?inline=1" target="_blank" class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-primary transition-all">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                                Tidak ada data pengujian pada tanggal ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Side Analysis --}}
    <div class="xl:col-span-1 space-y-6">
        <div class="rounded-xl bg-white dark:bg-slate-900 shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                <h2 class="font-bold text-slate-900 dark:text-white flex items-center gap-2 text-sm uppercase tracking-wider">
                    <span class="material-symbols-outlined text-primary text-[20px]">donut_small</span>
                    Grade Mix
                </h2>
            </div>
            <div class="p-6">
                @if($samples->isEmpty())
                    <p class="text-xs text-slate-400 italic text-center py-4">No data available for analysis.</p>
                @else
                    <div class="space-y-4">
                        @foreach($gradeDistribution as $grade => $data)
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ $grade }}</span>
                                <span class="text-[10px] font-black text-slate-400 tracking-widest">{{ $data['count'] }} Tests</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-primary h-full transition-all duration-500" style="width: {{ $data['percentage'] }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.min.js') }}"></script>
<link href="{{ asset('vendor/datatables/dataTables.dataTables.min.css') }}" rel="stylesheet">
<script>
$(function() {
    $('#dailyTable').DataTable({
        pageLength: 10,
        order: [[0,'desc']],
        dom: 'tp', // elements selection
        language: { search: "" }
    });
});
</script>
@endpush
