@extends('layouts.modern', ['title' => 'Mill Certificate Generator'])

@push('head')
<style>
    .copy-btn.pasted {
        @apply bg-green-500 text-white border-green-500;
    }
    .copy-btn.pasted span {
        @apply text-white;
    }
</style>
@endpush

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Mill Certificate Generator</h1>
    <p class="text-slate-500 mt-1">Generate report data for legacy software input. Search using single or multiple Heat Numbers.</p>
</div>

<div class="grid grid-cols-1 gap-6">
    <!-- Search Card -->
    <div class="rounded-xl bg-white dark:bg-slate-900 shadow-sm border border-slate-100 dark:border-slate-800 p-6">
        <label for="heatsInput" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
            Heat Numbers (Comma or Newline separated)
        </label>
        <div class="flex flex-col md:flex-row gap-4">
            <textarea id="heatsInput" rows="3" 
                class="flex-1 rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-primary focus:border-primary"
                placeholder="A404032606, A427022609..."></textarea>
            <div class="flex items-end">
                <button id="btnGenerate" class="w-full md:w-auto px-6 py-2.5 bg-primary text-white rounded-lg font-semibold hover:bg-primary-dark transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">sync</span>
                    Generate
                </button>
            </div>
        </div>
    </div>

    <!-- Results Card -->
    <div id="resultsCard" class="hidden rounded-xl bg-white dark:bg-slate-900 shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-[10px] font-bold uppercase tracking-wider">
                        <th rowspan="2" class="px-4 py-3 border-b border-r border-slate-100 dark:border-slate-800 text-center">Heat No.</th>
                        <th colspan="9" class="px-4 py-2 border-b border-r border-slate-100 dark:border-slate-800 text-center">Chemical Composition (%)</th>
                        <th colspan="4" class="px-4 py-2 border-b border-r border-slate-100 dark:border-slate-800 text-center">Mechanical Property</th>
                        <th rowspan="2" class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 text-center w-[100px]">Action</th>
                    </tr>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-[9px] font-bold uppercase">
                        <th class="px-2 py-2 border-b border-r border-slate-100 dark:border-slate-800 text-center">C</th>
                        <th class="px-2 py-2 border-b border-r border-slate-100 dark:border-slate-800 text-center">Si</th>
                        <th class="px-2 py-2 border-b border-r border-slate-100 dark:border-slate-800 text-center">Mn</th>
                        <th class="px-2 py-2 border-b border-r border-slate-100 dark:border-slate-800 text-center">P</th>
                        <th class="px-2 py-2 border-b border-r border-slate-100 dark:border-slate-800 text-center">S</th>
                        <th class="px-2 py-2 border-b border-r border-slate-100 dark:border-slate-800 text-center">Cr</th>
                        <th class="px-2 py-2 border-b border-r border-slate-100 dark:border-slate-800 text-center">Ni</th>
                        <th class="px-2 py-2 border-b border-r border-slate-100 dark:border-slate-800 text-center">Mo</th>
                        <th class="px-2 py-2 border-b border-r border-slate-100 dark:border-slate-800 text-center">Fe</th>
                        
                        <th class="px-2 py-2 border-b border-r border-slate-100 dark:border-slate-800 text-center">T.S (MPa)</th>
                        <th class="px-2 py-2 border-b border-r border-slate-100 dark:border-slate-800 text-center">Y.S (MPa)</th>
                        <th class="px-2 py-2 border-b border-r border-slate-100 dark:border-slate-800 text-center">EL (%)</th>
                        <th class="px-2 py-2 border-b border-r border-slate-100 dark:border-slate-800 text-center">HB</th>
                    </tr>
                </thead>
                <tbody id="resultsBody" class="divide-y divide-slate-100 dark:divide-slate-800 text-[12px]">
                    <!-- Rows injected here -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnGenerate = document.getElementById('btnGenerate');
    const heatsInput = document.getElementById('heatsInput');
    const resultsCard = document.getElementById('resultsCard');
    const resultsBody = document.getElementById('resultsBody');

    btnGenerate.addEventListener('click', function() {
        const heats = heatsInput.value.trim();
        if (!heats) return;

        btnGenerate.disabled = true;
        btnGenerate.innerHTML = '<span class="material-symbols-outlined animate-spin text-[20px]">sync</span> Generating...';

        fetch("{{ route('mill-certificate.generate') }}?heats=" + encodeURIComponent(heats))
            .then(res => res.json())
            .then(json => {
                renderResults(json.data);
                resultsCard.classList.remove('hidden');
            })
            .catch(err => {
                alert('Error generating report: ' + err.message);
            })
            .finally(() => {
                btnGenerate.disabled = false;
                btnGenerate.innerHTML = '<span class="material-symbols-outlined text-[20px]">sync</span> Generate';
            });
    });

    function renderResults(data) {
        resultsBody.innerHTML = '';
        if (data.length === 0) {
            resultsBody.innerHTML = '<tr><td colspan="15" class="px-4 py-8 text-center text-slate-500 italic">No samples found for the given Heat Numbers.</td></tr>';
            return;
        }

        data.forEach(item => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors';
            if (!item.found) {
                row.className += ' bg-amber-50/30 dark:bg-amber-900/10';
            }
            
            row.innerHTML = `
                <td class="px-4 py-2 border-r border-slate-100 dark:border-slate-800 font-medium ${item.found ? 'text-slate-900 dark:text-white' : 'text-amber-600 dark:text-amber-500'}">
                    <div class="flex items-center gap-1.5">
                        ${!item.found ? '<span class="material-symbols-outlined text-[14px]">warning</span>' : ''}
                        ${item.heat_no}
                    </div>
                </td>
                <td class="px-2 py-2 border-r border-slate-100 dark:border-slate-800 text-center">${item.chem.c}</td>
                <td class="px-2 py-2 border-r border-slate-100 dark:border-slate-800 text-center">${item.chem.si}</td>
                <td class="px-2 py-2 border-r border-slate-100 dark:border-slate-800 text-center">${item.chem.mn}</td>
                <td class="px-2 py-2 border-r border-slate-100 dark:border-slate-800 text-center">${item.chem.p}</td>
                <td class="px-2 py-2 border-r border-slate-100 dark:border-slate-800 text-center">${item.chem.s}</td>
                <td class="px-2 py-2 border-r border-slate-100 dark:border-slate-800 text-center">${item.chem.cr}</td>
                <td class="px-2 py-2 border-r border-slate-100 dark:border-slate-800 text-center">${item.chem.ni}</td>
                <td class="px-2 py-2 border-r border-slate-100 dark:border-slate-800 text-center">${item.chem.mo}</td>
                <td class="px-2 py-2 border-r border-slate-100 dark:border-slate-800 text-center font-semibold ${item.found ? 'text-primary' : 'text-slate-400'}">${item.chem.fe}</td>
                
                <td class="px-2 py-2 border-r border-slate-100 dark:border-slate-800 text-center">${item.mech.ts}</td>
                <td class="px-2 py-2 border-r border-slate-100 dark:border-slate-800 text-center">${item.mech.ys}</td>
                <td class="px-2 py-2 border-r border-slate-100 dark:border-slate-800 text-center">${item.mech.el}</td>
                <td class="px-2 py-2 border-r border-slate-100 dark:border-slate-800 text-center">${item.mech.hb}</td>
                
                <td class="px-4 py-2 text-center">
                    <button class="copy-btn group flex items-center justify-center gap-1.5 w-full py-1.5 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-[11px] font-semibold text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary transition-all"
                        data-copy="${item.copy_string}">
                        <span class="material-symbols-outlined text-[16px]">content_copy</span>
                        <span class="label">Copy</span>
                    </button>
                    ${!item.found ? '<p class="text-[9px] text-amber-600 mt-1">Not Found</p>' : ''}
                </td>
            `;
            resultsBody.appendChild(row);
        });
    }

    // Event delegation for copy buttons
    resultsBody.addEventListener('click', function(e) {
        const btn = e.target.closest('.copy-btn');
        if (!btn) return;

        const text = btn.getAttribute('data-copy');
        copyToClipboard(text).then(() => {
            const label = btn.querySelector('.label');
            const icon = btn.querySelector('.material-symbols-outlined');
            
            btn.classList.add('pasted');
            label.textContent = 'Pasted';
            icon.textContent = 'check';
            
            setTimeout(() => {
                btn.classList.remove('pasted');
                label.textContent = 'Copy';
                icon.textContent = 'content_copy';
            }, 2000);
        });
    });

    async function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        } else {
            // Fallback for non-secure contexts or older browsers
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-9999px";
            textArea.style.top = "0";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
            } catch (err) {
                console.error('Fallback copy failed', err);
            }
            document.body.removeChild(textArea);
            return Promise.resolve();
        }
    }
});
</script>
@endpush
