<?php

namespace App\Http\Controllers;

use App\Models\Sample;
use App\Models\TensileTest;
use App\Models\HardnessTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MechanicalController extends Controller
{
    /** Daftar sample untuk pengujian mekanik */
    public function index()
    {
        // View utama sekarang kosong, data akan dimuat via AJAX
        return view('mechanical.index');
    }

    /** AJAX data for Mechanical DataTables */
    public function data(Request $request)
    {
        // Hanya tampilkan status DRAFT/REJECTED sesuai logika lama
        $query = Sample::whereIn('status', ['DRAFT', 'REJECTED'])
            ->with(['tensileTest', 'hardnessTest']);

        // 1. Search (Global)
        if ($request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('report_no', 'like', "%{$search}%")
                  ->orWhere('heat_no', 'like', "%{$search}%")
                  ->orWhere('grade', 'like', "%{$search}%")
                  ->orWhere('product_type', 'like', "%{$search}%");
            });
        }

        $totalRecords = Sample::whereIn('status', ['DRAFT', 'REJECTED'])->count();
        $filteredRecords = $query->count();

        // 2. Sorting
        $columns = ['id', 'report_no', 'grade', 'status', 'id', 'id']; // Mapping kolom
        $orderColumnIndex = $request->order[0]['column'] ?? 0;
        $orderDir = $request->order[0]['dir'] ?? 'desc';
        $orderField = $columns[$orderColumnIndex] ?? 'id';
        $query->orderBy($orderField, $orderDir);

        // 3. Pagination
        $start = $request->start ?? 0;
        $length = $request->length ?? 25;
        $samples = $query->skip($start)->take($length)->get();

        // 4. Formatting
        $data = $samples->map(function($s) {
            // Mechanical Status Logic
            $hasMech = $s->tensileTest && ($s->tensileTest->ys_mpa || $s->tensileTest->uts_mpa);
            $hasHard = $s->hardnessTest && $s->hardnessTest->avg_value;

            $mechDataHtml = '<div class="flex flex-col">
                                <span class="inline-flex items-center gap-1 text-[10px] '.($hasMech ? 'text-green-600' : 'text-slate-400').'">
                                    <span class="material-symbols-outlined text-[11px]">'.($hasMech ? 'check_circle' : 'pending').'</span>
                                    Tensile
                                </span>
                                <span class="inline-flex items-center gap-1 text-[10px] '.($hasHard ? 'text-green-600' : 'text-slate-400').'">
                                    <span class="material-symbols-outlined text-[11px]">'.($hasHard ? 'check_circle' : 'pending').'</span>
                                    Hardness
                                </span>
                             </div>';

            return [
                'id' => $s->id,
                'report_no' => '<div>
                                    <p class="font-bold text-primary leading-tight">'.($s->report_no ?? 'DRAFT').'</p>
                                    <p class="text-[10px] text-slate-400">H: '.($s->heat_no ?? '-').'</p>
                                 </div>',
                'grade' => '<div>
                                <p class="font-bold text-slate-700 dark:text-white leading-tight">'.$s->grade.'</p>
                                <p class="text-[10px] text-slate-400">'.$s->product_type.'</p>
                            </div>',
                'status' => '<span class="inline-block rounded bg-blue-50 text-blue-700 px-1.5 py-0 text-[10px] font-bold uppercase">
                                '.$s->status.'
                             </span>',
                'mech_data' => $mechDataHtml,
                'actions' => '<div class="flex items-center justify-center gap-1">
                                <a href="'.route('mechanical.edit', $s).'" class="p-0.5 rounded border border-slate-200 text-primary hover:bg-primary/10 transition-all" title="Edit">
                                    <span class="material-symbols-outlined text-[16px]">edit_note</span>
                                </a>
                                <form method="post" action="'.route('samples.submit',$s).'" class="m-0">'.csrf_field().'<button class="p-0.5 rounded border border-slate-200 text-primary hover:bg-primary/10 transition-colors" title="Submit"><span class="material-symbols-outlined text-[16px]">send</span></button></form>
                             </div>'
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    /** Form input data mekanik */
    public function edit(Sample $sample)
    {
        abort_unless(auth()->user()->hasRole(['Operator', 'Approver']), 403);

        if (!in_array($sample->status, ['DRAFT', 'REJECTED'], true)) {
            return redirect()->route('mechanical.index')
                ->with('err', 'Data mekanik hanya bisa diisi untuk status DRAFT atau REVISI.');
        }

        $sample->load(['tensileTest', 'hardnessTest']);

        return view('mechanical.edit', compact('sample'));
    }

    /** Simpan/Update data mekanik */
    public function update(Request $r, Sample $sample)
    {
        abort_unless(auth()->user()->hasRole(['Operator', 'Approver']), 403);

        if (!in_array($sample->status, ['DRAFT', 'REJECTED'], true)) {
            return redirect()->route('mechanical.index')
                ->with('err', 'Hanya DRAFT/REVISI yang bisa diupdate.');
        }

        $rules = [
            'ys_mpa'    => 'nullable|numeric',
            'uts_mpa'   => 'nullable|numeric',
            'elong_pct' => 'nullable|numeric',
            'hb'        => 'nullable|numeric',
        ];

        $data = $r->validate($rules);

        DB::transaction(function () use ($sample, $data) {
            $sample->tensileTest()->updateOrCreate(['sample_id' => $sample->id], [
                'ys_mpa'    => $data['ys_mpa']    ?? null,
                'uts_mpa'   => $data['uts_mpa']   ?? null,
                'elong_pct' => $data['elong_pct'] ?? null,
            ]);

            $sample->hardnessTest()->updateOrCreate(['sample_id' => $sample->id], [
                'method'    => 'HB',
                'avg_value' => $data['hb'] ?? null,
            ]);

            // Opsional: catat aktivitas update di sini jika perlu
        });

        return redirect()->route('mechanical.index')->with('ok', 'Data Mekanik berhasil disimpan.');
    }
}
