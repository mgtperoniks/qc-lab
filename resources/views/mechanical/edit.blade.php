@extends('layouts.modern', ['title' => 'Input Mechanical Results - #' . $sample->id])

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Mechanical Testing Input</h1>
        <p class="text-slate-500 mt-1">Record Tensile and Hardness testing results for Sample #{{ $sample->id }}.</p>
    </div>
    <div>
        <a href="{{ route('mechanical.index') }}" class="flex items-center gap-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm font-bold text-slate-700 dark:text-white shadow-sm hover:bg-slate-50 transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Back to List
        </a>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
    {{-- Info Sampel (Mini Dashboard) --}}
    <div class="xl:col-span-1 space-y-6">
        <div class="rounded-xl bg-white dark:bg-slate-900 shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden sticky top-8">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                <h2 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">info</span>
                    Sample Summary
                </h2>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Grade / Material</label>
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-black text-slate-900 dark:text-white">{{ $sample->grade }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Heat Number</label>
                        <p class="text-sm font-mono text-primary font-bold bg-primary/5 px-2 py-1 rounded inline-block">{{ $sample->heat_no ?: '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Standard Reference</label>
                        <p class="text-sm text-slate-600 dark:text-slate-300 font-medium">{{ $sample->standard }}</p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Test Date</label>
                        <p class="text-sm text-slate-600 dark:text-slate-300">{{ optional($sample->test_date)->format('d M Y') ?? '-' }}</p>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between p-3 rounded-lg bg-green-50 dark:bg-green-900/10 border border-green-100 dark:border-green-800/50">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-green-600 dark:text-green-400 text-[20px]">check_circle</span>
                            <span class="text-xs font-bold text-green-800 dark:text-green-400 uppercase tracking-tighter">Chemical Status</span>
                        </div>
                        <span class="text-[10px] font-black text-green-600 dark:text-green-400">PASSED</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Input Mekanik --}}
    <div class="xl:col-span-3">
        <form method="post" action="{{ route('mechanical.update', $sample) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Uji Tarik --}}
                <div class="rounded-xl bg-white dark:bg-slate-900 shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden flex flex-col">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">fitness_center</span>
                        <h2 class="font-bold text-slate-900 dark:text-white">Uji Tarik (Tensile)</h2>
                    </div>
                    <div class="p-6 space-y-5 flex-1">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2">Yield Strength <span class="text-[10px] opacity-70 italic">(MPa)</span></label>
                            <input type="number" step="0.01" name="ys_mpa" value="{{ old('ys_mpa', optional($sample->tensileTest)->ys_mpa) }}" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-semibold focus:ring-2 focus:ring-primary transition-all" placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2">UTS <span class="text-[10px] opacity-70 italic">(MPa)</span></label>
                            <input type="number" step="0.01" name="uts_mpa" value="{{ old('uts_mpa', optional($sample->tensileTest)->uts_mpa) }}" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-semibold focus:ring-2 focus:ring-primary transition-all" placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2">Elongation (%)</label>
                            <div class="relative">
                                <input type="number" step="0.01" name="elong_pct" value="{{ old('elong_pct', optional($sample->tensileTest)->elong_pct) }}" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-semibold focus:ring-2 focus:ring-primary transition-all pr-8" placeholder="0.00">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Uji Kekerasan --}}
                <div class="rounded-xl bg-white dark:bg-slate-900 shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden flex flex-col">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">square_foot</span>
                        <h2 class="font-bold text-slate-900 dark:text-white">Uji Kekerasan</h2>
                    </div>
                    <div class="p-6 space-y-5 flex-1">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2">Brinell Hardness (HB)</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">adjust</span>
                                <input type="number" step="0.01" name="hb" value="{{ old('hb', optional($sample->hardnessTest)->avg_value) }}" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-semibold focus:ring-2 focus:ring-primary transition-all pl-10" placeholder="0.00">
                            </div>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/10 rounded-lg p-4 border border-blue-100 dark:border-blue-800/50">
                            <p class="text-[10px] font-bold text-blue-800 dark:text-blue-400 uppercase tracking-widest mb-1">Standard Reference</p>
                            <p class="text-xs text-blue-700 dark:text-blue-300">Harap pastikan nilai kekerasan sesuai dengan material grade <span class="font-bold">{{ $sample->grade }}</span>.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row items-center justify-end gap-4 pt-4">
                <a href="{{ route('mechanical.index') }}" class="text-sm font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
                    Discard Changes
                </a>
                <button type="submit" class="w-full md:w-auto flex items-center justify-center gap-3 rounded-xl bg-primary px-10 py-4 text-sm font-bold text-white shadow-xl shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-[0.98] transition-all">
                    <span class="material-symbols-outlined">save</span>
                    Simpan Hasil Mekanik
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
