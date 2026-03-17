@extends('layouts.modern', ['title' => 'Mechanical Testing'])

@push('head')
    <style>
        .dt-container .dt-search input {
            @apply rounded border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-[12px] py-1 px-2;
        }
        .dt-container .dt-length select {
            @apply rounded border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-[12px] py-1 px-2;
        }
        table.dataTable thead th {
            @apply bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-[10px] font-bold uppercase tracking-tighter px-2 py-2 border-b border-slate-100 dark:border-slate-800 !important;
        }
        table.dataTable tbody td {
            @apply px-2 py-1 text-[12px] border-b border-slate-100 dark:border-slate-800 !important;
            line-height: 1.2;
        }
        /* Width Overrides */
        .col-id { width: 40px !important; }
        .col-report { width: 140px !important; }
        .col-grade { width: 120px !important; }
        .col-status { width: 80px !important; }
        .col-mech { width: 100px !important; }
        .col-actions { width: 80px !important; }

        #mechanicalTable {
            table-layout: fixed;
            width: 100% !important;
        }
        
        .dt-info, .dt-paging {
            @apply text-[11px] mt-2 !important;
        }

        /* CLOAK FIX */
        #mechanicalTable:not(.dataTable) {
            opacity: 0;
            visibility: hidden;
        }
    </style>
@endpush

@section('content')
<div class="mb-4 flex flex-col md:flex-row md:items-end justify-between gap-2">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Mechanical Testing</h1>
        <p class="text-slate-500 mt-1">Daftar sampel yang menunggu atau sudah memiliki hasil uji mekanis.</p>
    </div>
</div>

<div class="rounded-xl bg-white dark:bg-slate-900 shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table id="mechanicalTable" class="w-full text-left transition-opacity duration-300">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/50">
                    <th class="col-id">ID</th>
                    <th class="col-report">Report / Heat</th>
                    <th class="col-grade">Grade / Type</th>
                    <th class="col-status text-center">Status</th>
                    <th class="col-mech">Results</th>
                    <th class="col-actions text-center">Aksi</th>
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
<link href="{{ asset('vendor/datatables/dataTables.dataTables.min.css') }}" rel="stylesheet">
<script>
$(function() {
    $('#mechanicalTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: "{{ route('mechanical.data') }}",
        columns: [
            { data: 'id', className: 'col-id' },
            { data: 'report_no', className: 'col-report whitespace-nowrap overflow-hidden text-ellipsis' },
            { data: 'grade', className: 'col-grade whitespace-nowrap overflow-hidden text-ellipsis' },
            { data: 'status', className: 'col-status text-center' },
            { data: 'mech_data', className: 'col-mech', orderable: false },
            { data: 'actions', className: 'col-actions text-center', orderable: false }
        ],
        order: [[0,'desc']],
        language: {
            processing: '<div class="absolute inset-0 bg-white/50 z-10 flex items-center justify-center"><span class="material-symbols-outlined animate-spin text-primary">sync</span></div>'
        }
    });
});
</script>
@endpush
