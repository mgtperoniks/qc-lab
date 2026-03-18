@extends('layouts.modern', ['title' => 'Heat Numbers Checkers'])

@push('head')
<!-- Handsontable CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css">
<style>
    .htRowOk { background-color: #dcfce7 !important; } /* green-100 */
    .htRowFail { background-color: #fee2e2 !important; } /* red-100 */
    .handsontable td { font-size: 12px; }
    .handsontable th { font-size: 11px; font-weight: bold; background: #f8fafc; }
</style>
@endpush

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Heat Numbers Checkers</h1>
        <p class="text-slate-500 mt-1">Verify ERP data consistency by pasting Excel output below.</p>
    </div>
    <div class="flex gap-3">
        <button id="btnVerify" class="flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:bg-primary/90 transition-all">
            <span class="material-symbols-outlined text-[18px]">verified_user</span>
            Verify Data
        </button>
        <button id="btnClear" class="flex items-center gap-2 rounded-lg bg-white border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all">
            <span class="material-symbols-outlined text-[18px]">delete_sweep</span>
            Clear
        </button>
    </div>
</div>

<div class="grid grid-cols-1 gap-6">
    <!-- Summary Alert -->
    <div id="summaryAlert" class="hidden rounded-xl border p-4 flex items-center gap-3">
        <div id="summaryIcon" class="material-symbols-outlined"></div>
        <div>
            <p id="summaryText" class="font-bold"></p>
            <p id="summarySubtext" class="text-sm opacity-90"></p>
        </div>
    </div>

    <!-- Spreadsheet Card -->
    <div class="rounded-xl bg-white dark:bg-slate-900 shadow-sm border border-slate-100 dark:border-slate-800 p-4">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Paste from Excel (Heat No, Chem 9 Cols, Mech 4 Cols)</p>
        <div id="hotContainer" class="overflow-hidden rounded-lg border border-slate-200"></div>
    </div>
</div>

@endsection

@push('scripts')
<!-- Handsontable JS -->
<script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('hotContainer');
    const btnVerify = document.getElementById('btnVerify');
    const btnClear  = document.getElementById('btnClear');
    const summaryAlert = document.getElementById('summaryAlert');
    const summaryText = document.getElementById('summaryText');
    const summarySubtext = document.getElementById('summarySubtext');
    const summaryIcon = document.getElementById('summaryIcon');

    // Column mapping: Heat No + 9 Chem + 4 Mech = 14 cols
    const colHeaders = [
        'Heat No.', 'C', 'Si', 'Mn', 'P', 'S', 'Cr', 'Ni', 'Mo', 'Fe', 'T.S', 'Y.S', 'EL', 'HB'
    ];

    const hot = new Handsontable(container, {
        data: Array(20).fill().map(() => Array(14).fill('')),
        rowHeaders: true,
        colHeaders: colHeaders,
        height: '450px',
        width: '100%',
        licenseKey: 'non-commercial-and-evaluation',
        stretchH: 'all',
        columns: [
            { width: 120 }, // Heat No
            { type: 'numeric', numericFormat: { pattern: '0.0000' } }, // C
            { type: 'numeric', numericFormat: { pattern: '0.0000' } }, // Si
            { type: 'numeric', numericFormat: { pattern: '0.0000' } }, // Mn
            { type: 'numeric', numericFormat: { pattern: '0.0000' } }, // P
            { type: 'numeric', numericFormat: { pattern: '0.0000' } }, // S
            { type: 'numeric', numericFormat: { pattern: '0.0000' } }, // Cr
            { type: 'numeric', numericFormat: { pattern: '0.0000' } }, // Ni
            { type: 'numeric', numericFormat: { pattern: '0.0000' } }, // Mo
            { type: 'numeric', numericFormat: { pattern: '0.0000' } }, // Fe
            { type: 'numeric' }, // TS
            { type: 'numeric' }, // YS
            { type: 'numeric' }, // EL
            { type: 'numeric' }, // HB
        ],
        cells(row, col) {
            const cellProp = {};
            const rowData = this.instance.getSourceDataAtRow(row);
            if (rowData._status === 'OK') cellProp.className = 'htRowOk';
            if (rowData._status === 'FAIL') cellProp.className = 'htRowFail';
            return cellProp;
        }
    });

    btnClear.addEventListener('click', () => {
        hot.loadData(Array(20).fill().map(() => Array(14).fill('')));
        summaryAlert.classList.add('hidden');
    });

    btnVerify.addEventListener('click', async () => {
        const data = hot.getData();
        // Filter rows that have a Heat Number
        const rowsToCheck = data.filter(row => row[0] && row[0].toString().trim() !== '');
        
        if (rowsToCheck.length === 0) {
            alert('Please paste some data first.');
            return;
        }

        const heatList = rowsToCheck.map(r => r[0].toString().trim());

        btnVerify.disabled = true;
        btnVerify.innerHTML = '<span class="material-symbols-outlined animate-spin text-[18px]">sync</span> Verifying...';

        try {
            const response = await fetch("{{ route('checker.verify') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ heats: heatList })
            });

            const result = await response.json();
            const dbData = result.data; // Array of results indexed by input heat_no

            let okCount = 0;
            let failCount = 0;
            let failedRows = [];

            // Map results back to Handsontable rows
            const sourceData = hot.getSourceData();
            
            // Note: DB data returned in same order as heatList
            let dbIdx = 0;
            for (let i = 0; i < sourceData.length; i++) {
                const heatNo = sourceData[i][0]?.toString().trim();
                if (!heatNo) {
                    sourceData[i]._status = null;
                    continue;
                }

                const dbItem = dbData[dbIdx++];
                if (!dbItem || !dbItem.found) {
                    sourceData[i]._status = 'FAIL';
                    failCount++;
                    failedRows.push({ row: i + 1, heat: heatNo });
                    continue;
                }

                // Comparison logic
                const matchChem = 
                    compare(sourceData[i][1], dbItem.chem.c) &&
                    compare(sourceData[i][2], dbItem.chem.si) &&
                    compare(sourceData[i][3], dbItem.chem.mn) &&
                    compare(sourceData[i][4], dbItem.chem.p) &&
                    compare(sourceData[i][5], dbItem.chem.s) &&
                    compare(sourceData[i][6], dbItem.chem.cr) &&
                    compare(sourceData[i][7], dbItem.chem.ni) &&
                    compare(sourceData[i][8], dbItem.chem.mo) &&
                    compare(sourceData[i][9], dbItem.chem.fe);

                const matchMech = 
                    compare(sourceData[i][10], dbItem.mech.ts, 0) &&
                    compare(sourceData[i][11], dbItem.mech.ys, 0) &&
                    compare(sourceData[i][12], dbItem.mech.el, 0) &&
                    compare(sourceData[i][13], dbItem.mech.hb, 0);

                if (matchChem && matchMech) {
                    sourceData[i]._status = 'OK';
                    okCount++;
                } else {
                    sourceData[i]._status = 'FAIL';
                    failCount++;
                    failedRows.push({ row: i + 1, heat: heatNo });
                }
            }

            hot.render();
            showSummary(okCount, failCount, failedRows);

        } catch (err) {
            alert('Error during verification: ' + err.message);
        } finally {
            btnVerify.disabled = false;
            btnVerify.innerHTML = '<span class="material-symbols-outlined text-[18px]">verified_user</span> Verify Data';
        }
    });

    function compare(val1, val2, precision = 4) {
        if (val1 === null || val1 === undefined || val1 === '') val1 = 0;
        if (val2 === null || val2 === undefined || val2 === '') val2 = 0;
        
        // Convert both to float and compare with a small epsilon or strictly after formatting
        const f1 = parseFloat(val1).toFixed(precision);
        const f2 = parseFloat(val2).toFixed(precision);
        return f1 === f2;
    }

    function showSummary(ok, fail, failedRows = []) {
        summaryAlert.classList.remove('hidden', 'bg-green-50', 'border-green-200', 'text-green-800', 'bg-red-50', 'border-red-200', 'text-red-800');
        
        if (fail === 0 && ok > 0) {
            summaryAlert.classList.add('bg-green-50', 'border-green-200', 'text-green-800');
            summaryIcon.textContent = 'check_circle';
            summaryText.textContent = 'QC PASS';
            summarySubtext.textContent = `All ${ok} data matched perfectly. Database consistency verified.`;
        } else {
            summaryAlert.classList.add('bg-red-50', 'border-red-200', 'text-red-800');
            summaryIcon.textContent = 'error';
            summaryText.textContent = ok + ' OK, ' + fail + ' FAILED';
            
            let details = `Errors found in ${fail} rows. Please double check ERP input.`;
            if (failedRows.length > 0) {
                const list = failedRows.map(f => `Row ${f.row} (${f.heat})`).join(', ');
                details += ` <br><span class="font-semibold text-xs mt-1 block">Failed Items: ${list}</span>`;
            }
            summarySubtext.innerHTML = details;
        }
    }
});
</script>
@endpush
