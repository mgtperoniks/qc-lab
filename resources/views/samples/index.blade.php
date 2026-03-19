@extends('layouts.modern', ['title' => 'Daftar Chemical Testing'])

@push('head')
    <link href="{{ asset('vendor/datatables/dataTables.dataTables.min.css') }}" rel="stylesheet">
    <style>
        .dt-container .dt-search input {
            @apply rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm;
        }
        .dt-container .dt-length select {
            @apply rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm;
        }
        table.dataTable {
            @apply border-collapse w-full !important;
        }
        table.dataTable thead th {
            @apply bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-[10px] font-bold uppercase tracking-wider px-3 py-2 border-b border-slate-100 dark:border-slate-800 !important;
        }
        table.dataTable tbody td {
            @apply px-3 py-2 text-[12px] border-b border-slate-100 dark:border-slate-800 !important;
        }
        /* CLOAK FIX: Hilangkan table sebelum DataTable inisialisasi agar tidak melompat */
        #samplesTable:not(.dataTable) {
            opacity: 0;
            visibility: hidden;
        }
    </style>
@endpush

@section('content')
  @if(session('ok'))  <div class="alert alert-success">{{ session('ok') }}</div> @endif
  @if(session('err')) <div class="alert alert-danger">{{ session('err') }}</div> @endif

<div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Chemical Testing (Spectrometer)</h1>
        <p class="text-slate-500 mt-1">Daftar hasil pengujian komposisi kimia material.</p>
    </div>
    <div class="flex gap-3">
        @role('Approver')
            <a href="{{ route('approvals.index') }}" class="flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-green-700">
                <span class="material-symbols-outlined text-[18px]">rule</span>
                Antrian Persetujuan
            </a>
        @endrole
        @role('Operator')
            <a href="{{ route('samples.create') }}" class="flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-primary/90">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                Input Sample Baru
            </a>
        @endrole
    </div>
</div>

{{-- FILTER BAR --}}
<div class="rounded-xl bg-white dark:bg-slate-900 p-6 shadow-sm border border-slate-100 dark:border-slate-800 mb-8">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Tanggal Dari</label>
            <input type="date" id="fltFrom" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm focus:ring-primary">
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Sampai</label>
            <input type="date" id="fltTo" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm focus:ring-primary">
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Grade</label>
            <select id="fltGrade" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm focus:ring-primary">
                <option value="">(Semua Grade)</option>
                <option>304</option>
                <option>316</option>
                <option>1.4308</option>
                <option>1.4408</option>
            </select>
        </div>
        <div class="flex justify-end">
             <button id="btnClearFilter" class="text-sm font-medium text-slate-500 hover:text-primary transition-colors">Clear Filters</button>
        </div>
    </div>
</div>

<div class="rounded-xl bg-white dark:bg-slate-900 shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table id="samplesTable" class="w-full text-left transition-opacity duration-300">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Report No / Heat No</th>
                    <th>Grade / Standard</th>
                    <th>Test Date</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                {{-- Data dimuat via AJAX --}}
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/dayjs/dayjs.min.js') }}"></script>
<script>
$(function() {
    const dt = $('#samplesTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: "{{ route('samples.data') }}",
            data: function(d) {
                d.grade = $('#fltGrade').val();
                d.from  = $('#fltFrom').val();
                d.to    = $('#fltTo').val();
            }
        },
        columns: [
            { data: 'id' },
            { data: 'report_no' },
            { data: 'grade' },
            { data: 'test_date' },
            { data: 'status' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[0,'desc']],
        layout: {
            topStart: 'search',
            topEnd: 'pageLength',
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        language: {
            processing: '<div class="absolute inset-0 bg-white/50 z-10 flex items-center justify-center"><span class="material-symbols-outlined animate-spin text-primary">sync</span></div>'
        }
    });

    // Re-draw table on filtering
    $('#fltGrade, #fltFrom, #fltTo').on('change', () => dt.draw());

    $('#btnClearFilter').on('click', function() {
        $('#fltFrom, #fltTo, #fltGrade').val('');
        dt.search('').columns().search('').draw();
    });
});
</script>
@endpush
