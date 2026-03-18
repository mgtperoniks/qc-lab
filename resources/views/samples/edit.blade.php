@extends('layouts.modern', ['title' => 'Edit QC Sample'])

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit QC Sample (ID #{{ $sample->id }})</h1>
        <p class="text-slate-500 mt-1">Perbarui identitas dan hasil uji kimia untuk sampel ini.</p>
    </div>
    <div>
        <a href="{{ route('samples.index') }}" class="flex items-center gap-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm font-bold text-slate-700 dark:text-white shadow-sm hover:bg-slate-50">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Kembali ke Daftar
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="mb-6 rounded-xl bg-red-50 dark:bg-red-900/20 p-4 border border-red-100 dark:border-red-800">
        <div class="flex items-center gap-2 text-red-800 dark:text-red-400 font-bold mb-2">
            <span class="material-symbols-outlined">error</span>
            Periksa kembali isian berikut:
        </div>
        <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $sr = $sample->spectroResult;
    
    // Hindari error format() bila test_date berupa string/null
    $testDate = old('test_date');
    if (!$testDate && !empty($sample->test_date)) {
        try {
            $testDate = \Illuminate\Support\Carbon::parse($sample->test_date)->format('Y-m-d');
        } catch (\Throwable $e) {
            $testDate = '';
        }
    }
@endphp

<form method="post" action="{{ route('samples.update', $sample) }}" class="space-y-8">
    @csrf
    @method('PUT')

    {{-- Identitas Sampel --}}
    <div class="rounded-xl bg-white dark:bg-slate-900 shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
            <h2 class="font-bold text-slate-900 dark:text-white">Identitas Sampel</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Grade / Material <span class="text-red-500">*</span></label>
                    <select name="grade" id="grade" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm focus:ring-primary" required>
                        @foreach(['CF8','CF8M','SCS13A','SCS14A','1.4308','1.4408'] as $g)
                            <option value="{{ $g }}" {{ old('grade', $sample->grade) === $g ? 'selected' : '' }}>
                                {{ $g }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Standard</label>
                    <input type="text" id="standard" name="standard" value="{{ old('standard', $sample->standard) }}" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800/50 text-sm text-slate-500 cursor-not-allowed" readonly>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Product Type <span class="text-red-500">*</span></label>
                    <select name="product_type" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm focus:ring-primary" required>
                        @foreach(['Flange','Fitting'] as $pt)
                            <option value="{{ $pt }}" {{ old('product_type', $sample->product_type) === $pt ? 'selected' : '' }}>
                                {{ $pt }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Test Date</label>
                    <input type="date" name="test_date" value="{{ $testDate }}" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm focus:ring-primary">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Heat No</label>
                    <input type="text" name="heat_no" value="{{ old('heat_no', $sample->heat_no) }}" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm focus:ring-primary" placeholder="Masukkan Heat No">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Batch No</label>
                    <input type="text" name="batch_no" value="{{ old('batch_no', $sample->batch_no) }}" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm focus:ring-primary" placeholder="Masukkan Batch No">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">P.O / Customer</label>
                    <input type="text" name="po_customer" value="{{ old('po_customer', $sample->po_customer) }}" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm focus:ring-primary" placeholder="Masukkan PO atau Nama Customer">
                </div>
            </div>
        </div>
    </div>

    {{-- Uji Komposisi (Spektro) --}}
    <div class="rounded-xl bg-white dark:bg-slate-900 shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
            <h2 class="font-bold text-slate-900 dark:text-white">Uji Komposisi Kimia (Spektro) — %wt</h2>
        </div>
        <div class="p-6">
            @php
              $order  = ['c','si','mn','p','s','cr','ni','mo','cu','co','al','v','n'];
              $labels = ['c'=>'Carbon (C)','si'=>'Silicon (Si)','mn'=>'Manganese (Mn)','p'=>'Phosphorus (P)',
                         's'=>'Sulfur (S)','cr'=>'Chromium (Cr)','ni'=>'Nickel (Ni)','mo'=>'Molybdenum (Mo)',
                         'cu'=>'Copper (Cu)','co'=>'Cobalt (Co)','al'=>'Aluminium (Al)','v'=>'Vanadium (V)',
                         'n'=>'Nitrogen (N)'];
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                @foreach($order as $name)
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">{{ $labels[$name] }}</label>
                    <div class="relative">
                        <input type="number" step="0.0001" min="0" max="100" name="{{ $name }}" 
                               value="{{ old($name, optional($sr)->$name) }}"
                               class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm focus:ring-primary pr-8" placeholder="0.0000">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400">%</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="flex justify-end items-center gap-4">
        <a href="{{ route('samples.index') }}" class="text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors">Batal</a>
        <button type="submit" class="flex items-center gap-2 rounded-lg bg-primary px-6 py-3 text-sm font-bold text-white shadow-lg hover:bg-primary/90 transition-all transform hover:-translate-y-0.5">
            <span class="material-symbols-outlined text-[18px]">save</span>
            Simpan Perubahan
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
const MAP_STD = {
  "CF8":"ASTM A351","CF8M":"ASTM A351",
  "SCS13A":"JIS G 5121","SCS14A":"JIS G 5121",
  "1.4308":"BS EN 10213","1.4408":"BS EN 10213",
};
function applyStandard(){ 
    const grade = document.getElementById('grade').value;
    document.getElementById('standard').value = MAP_STD[grade] || ''; 
}
document.getElementById('grade').addEventListener('change', applyStandard);
window.addEventListener('DOMContentLoaded', applyStandard);
</script>
@endpush
