@extends('layouts.modern', ['title' => 'Antrian Persetujuan'])

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Antrian Persetujuan</h1>
        <p class="text-slate-500 mt-1">Daftar laporan QC yang diajukan dan menunggu verifikasi Anda.</p>
    </div>
    <div>
        <a href="{{ route('samples.index') }}" class="flex items-center gap-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm font-bold text-slate-700 dark:text-white shadow-sm hover:bg-slate-50 transition-colors">
            <span class="material-symbols-outlined text-[18px]">biotech</span>
            Daftar Chemical
        </a>
    </div>
</div>

<div class="rounded-xl bg-white dark:bg-slate-900 shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table id="approvalTable" class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/50">
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">ID</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Report No</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Grade</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Heat No</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($submitted as $s)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4 text-sm font-medium text-slate-400">#{{ $s->id }}</td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-bold text-primary">QC-{{ $s->report_no }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $s->grade }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-mono text-slate-600 dark:text-slate-400">{{ $s->heat_no ?: '-' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400 px-2.5 py-0.5 text-xs font-bold uppercase">
                            SUBMITTED
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2">
                            {{-- PREVIEW --}}
                            <a href="{{ route('reports.pdf',$s) }}?inline=1" 
                               target="_blank" rel="noopener"
                               class="js-preview flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all"
                               data-sample-id="{{ $s->id }}">
                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                Preview PDF
                            </a>

                            {{-- APPROVE --}}
                            <form method="post" action="{{ route('approvals.approve',$s) }}" class="js-approve-form" data-sample-id="{{ $s->id }}">
                                @csrf
                                <button type="submit" 
                                        class="js-approve-btn flex items-center gap-1.5 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-bold text-white shadow-lg shadow-green-600/20 hover:bg-green-700 transition-all disabled:opacity-30 disabled:grayscale disabled:cursor-not-allowed"
                                        data-sample-id="{{ $s->id }}"
                                        disabled>
                                    <span class="material-symbols-outlined text-[18px]">verified</span>
                                    Approve
                                </button>
                            </form>

                            {{-- REJECT --}}
                            <form method="post" action="{{ route('approvals.reject',$s) }}" onsubmit="return confirm('Kembalikan ke operator untuk revisi?');">
                                @csrf
                                <button type="submit" class="flex items-center gap-1.5 rounded-lg border border-amber-200 dark:border-amber-900/50 px-3 py-1.5 text-xs font-bold text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-all">
                                    <span class="material-symbols-outlined text-[18px]">history_edu</span>
                                    Revisi
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <span class="material-symbols-outlined text-slate-300 text-5xl">task_alt</span>
                            <p class="text-slate-500 font-medium">Tidak ada yang menunggu persetujuan.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Custom Alert Modal (Hidden by default) --}}
<div id="reviewRequiredModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-2xl border border-slate-100 dark:border-slate-800">
        <div class="flex items-center gap-3 text-amber-500 mb-4">
            <span class="material-symbols-outlined text-3xl">warning</span>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Review Diperlukan</h3>
        </div>
        <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed mb-6">
            Anda harus <strong>membuka Preview PDF</strong> terlebih dahulu sebelum menyetujui dan mengarsipkan dokumen ini. 
            Hal ini penting untuk memastikan seluruh data sudah benar.
        </p>
        <button onclick="document.getElementById('reviewRequiredModal').classList.add('hidden')" class="w-full rounded-xl bg-primary py-3 text-sm font-bold text-white shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all">
            Saya Mengerti
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.min.js') }}"></script>
<link href="{{ asset('vendor/datatables/dataTables.dataTables.min.css') }}" rel="stylesheet">

<script>
$(function() {
    $('#approvalTable').DataTable({
        pageLength: 25,
        order: [[0,'desc']],
        language: { search: "", searchPlaceholder: "Cari antrian..." }
    });

    const sessionKey = (id) => 'reviewed-' + id;

    const enableApprove = (id) => {
        try { sessionStorage.setItem(sessionKey(id), '1'); } catch(e) {}
        $(`.js-approve-btn[data-sample-id="${id}"]`).prop('disabled', false);
    };

    // Restore buttons
    $('.js-approve-btn').each(function() {
        const id = $(this).data('sample-id');
        try {
            if (sessionStorage.getItem(sessionKey(id)) === '1') {
                $(this).prop('disabled', false);
            }
        } catch(e) {}
    });

    // Preview click handler
    $('.js-preview').on('click', function() {
        const id = $(this).data('sample-id');
        enableApprove(id);
    });

    // Form submit guard
    $('.js-approve-form').on('submit', function(e) {
        const id = $(this).data('sample-id');
        try {
            if (sessionStorage.getItem(sessionKey(id)) !== '1') {
                e.preventDefault();
                $('#reviewRequiredModal').removeClass('hidden');
            }
        } catch(e) {}
    });
});
</script>
@endpush
