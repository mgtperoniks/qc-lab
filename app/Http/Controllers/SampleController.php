<?php

namespace App\Http\Controllers;

use App\Models\Sample;
use App\Models\SpectroResult;
use App\Models\TensileTest;
use App\Models\HardnessTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SampleController extends Controller
{
    /** Daftar sample */
    public function index()
    {
        // View utama sekarang kosong, data akan dimuat via AJAX
        return view('samples.index');
    }

    /** AJAX data for DataTables */
    public function data(Request $request)
    {
        $query = Sample::query();

        // --- FILTERING ---
        // 1. Grade
        if ($request->grade) {
            $query->where('grade', $request->grade);
        }

        // 2. Date Range
        if ($request->from) {
            $query->whereDate('test_date', '>=', $request->from);
        }
        if ($request->to) {
            $query->whereDate('test_date', '<=', $request->to);
        }

        // 3. Search (Global)
        if ($request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('report_no', 'like', "%{$search}%")
                  ->orWhere('heat_no', 'like', "%{$search}%")
                  ->orWhere('customer', 'like', "%{$search}%")
                  ->orWhere('grade', 'like', "%{$search}%")
                  ->orWhere('standard', 'like', "%{$search}%");
            });
        }

        $totalRecords = Sample::count();
        $filteredRecords = $query->count();

        // --- SORTING ---
        $columns = ['id', 'report_no', 'grade', 'test_date', 'status'];
        $orderColumnIndex = $request->order[0]['column'] ?? 0;
        $orderDir = $request->order[0]['dir'] ?? 'desc';
        $orderField = $columns[$orderColumnIndex] ?? 'id';
        
        $query->orderBy($orderField, $orderDir);

        // --- PAGINATION ---
        $start = $request->start ?? 0;
        $length = $request->length ?? 25;
        $samples = $query->skip($start)->take($length)->get();

        // --- FORMATTING ---
        $data = $samples->map(function($s) {
            $status = strtoupper($s->status ?? 'DRAFT');
            
            // Generate Status Badges (Copied logic from blade for consistency)
            $statusClasses = match($status) {
                'APPROVED'  => 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400',
                'REJECTED'  => 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400',
                'SUBMITTED' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
                default     => 'bg-slate-50 text-slate-700 dark:bg-slate-800 dark:text-slate-400',
            };
            $dotClasses = match($status) {
                'APPROVED'  => 'bg-green-600',
                'REJECTED'  => 'bg-red-600',
                'SUBMITTED' => 'bg-blue-600',
                default     => 'bg-slate-400',
            };

            $statusHtml = "<span class='inline-flex items-center gap-1 rounded-full {$statusClasses} px-2.5 py-0.5 text-xs font-bold'>
                            <span class='h-1.5 w-1.5 rounded-full {$dotClasses}'></span>
                            {$status}
                          </span>";

            // Generate Action Buttons
            $actions = '<div class="flex items-center justify-center gap-2">';
            
            $statusStr = strtoupper($s->status ?? '');
            if (in_array($statusStr, ['DRAFT', 'REJECTED'])) {
                $actions .= '<a href="'.route('reports.pdf',$s).'?inline=1" target="_blank" class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 transition-colors" title="Preview"><span class="material-symbols-outlined !text-[18px]">visibility</span></a>';
                $actions .= '<a href="'.route('samples.edit',$s).'" class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-amber-600 hover:bg-amber-50 transition-colors" title="Edit"><span class="material-symbols-outlined !text-[18px]">edit</span></a>';
                $actions .= '<form method="post" action="'.route('samples.submit',$s).'" class="m-0">'.csrf_field().'<button class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-primary hover:bg-primary/5 transition-colors" title="Submit"><span class="material-symbols-outlined !text-[18px]">send</span></button></form>';
                $actions .= '<form method="post" action="'.route('samples.destroy',$s).'" class="m-0" onsubmit="return confirm(\'Pindahkan ke Recycle Bin?\');">'.csrf_field().method_field('DELETE').'<button class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-red-600 hover:bg-red-50 transition-colors" title="Hapus"><span class="material-symbols-outlined !text-[18px]">delete</span></button></form>';
            }

            if ($statusStr === 'APPROVED') {
                $actions .= '<a href="'.route('reports.pdf',$s).'?inline=1" target="_blank" class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 transition-colors" title="Preview"><span class="material-symbols-outlined !text-[18px]">visibility</span></a>';
                $actions .= '<a href="'.route('reports.pdf',$s).'" class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-primary hover:bg-primary/5 transition-colors" title="Download PDF"><span class="material-symbols-outlined !text-[18px]">download_for_offline</span></a>';
                
                // Button Revoke (hanya untuk Approver)
                if (auth()->user()->hasRole('Approver')) {
                    $actions .= '<form method="post" action="'.route('samples.revoke',$s).'" class="m-0" onsubmit="return confirm(\'Batalkan persetujuan ini agar bisa diedit ulang?\');">'.csrf_field().'<button class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-amber-600 hover:bg-amber-50 transition-colors" title="Revoke Approval (Undo)"><span class="material-symbols-outlined !text-[18px]">undo</span></button></form>';
                }
            }
            
            $actions .= '</div>';

            return [
                'id' => $s->id,
                'report_no' => '<div>
                                    <p class="text-sm font-semibold text-primary">'.($s->report_no ?? 'DRAFT').'</p>
                                    <p class="text-xs text-slate-400">Heat: '.($s->heat_no ?? '-').'</p>
                                 </div>',
                'grade' => '<div>
                                <p class="text-sm font-medium">'.$s->grade.'</p>
                                <p class="text-xs text-slate-400">'.$s->standard.'</p>
                            </div>',
                'test_date' => '<span class="text-sm">'.(optional($s->test_date)->format('d M Y') ?? '-').'</span>',
                'status' => $statusHtml,
                'actions' => $actions
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    /** Form input sample baru (Operator/Approver) */
    public function create()
    {
        // Approver (Kabag QC) dilarang membuat data baru langsung, hanya Operator.
        if (auth()->user()->hasRole('Approver') || auth()->user()->email === 'kabagqc@peroniks.com') {
            abort(403, 'Approver hanya boleh menyetujui, tidak diperkenankan input data baru.');
        }
        abort_unless(auth()->user()->hasRole('Operator'), 403);

        // Opsi dropdown
        $grades = ['CF8', 'CF8M', 'SCS13A', 'SCS14A', '1.4308', '1.4408'];
        $productTypes = ['Flange', 'Fitting'];

        return view('samples.create', compact('grades', 'productTypes'));
    }

    /** Simpan sample baru sebagai DRAFT */
    public function store(Request $r)
    {
        abort_unless(auth()->user()->hasRole(['Operator', 'Approver']), 403);

        $rules = [
            // Identitas
            'grade'        => 'required|in:CF8,CF8M,SCS13A,SCS14A,1.4308,1.4408',
            'standard'     => 'nullable|string',
            'product_type' => 'nullable|string',
            'heat_no'      => 'nullable|string',
            'batch_no'     => 'nullable|string',
            'po_customer'  => 'nullable|string|max:100',
            'test_date'    => 'nullable|date',

            // Spektro
            'c'  => 'nullable|numeric',
            'si' => 'nullable|numeric',
            'mn' => 'nullable|numeric',
            'p'  => 'nullable|numeric',
            's'  => 'nullable|numeric',
            'cr' => 'nullable|numeric',
            'ni' => 'nullable|numeric',
            'mo' => 'nullable|numeric',
            'cu' => 'nullable|numeric',
            'co' => 'nullable|numeric',
            'al' => 'nullable|numeric',
            'v'  => 'nullable|numeric',
            'n'  => 'nullable|numeric',

            // Tarik
            'ys_mpa'    => 'nullable|numeric',
            'uts_mpa'   => 'nullable|numeric',
            'elong_pct' => 'nullable|numeric',

            // Kekerasan
            'hb' => 'nullable|numeric',
        ];

        $data = $r->validate($rules);

        // Map grade → standard (hindari dot-notation untuk key 1.4308/1.4408)
        $standard = $this->resolveStandard($data['grade']);

        DB::transaction(function () use ($data, $standard) {
            $s = Sample::create([
                'report_no'    => null,
                'grade'        => $data['grade'],
                'standard'     => $standard,
                'product_type' => $data['product_type'] ?? 'Flange',
                'heat_no'      => $data['heat_no'] ?? null,
                'batch_no'     => $data['batch_no'] ?? null,
                'po_customer'  => $data['po_customer'] ?? null,
                'test_date'    => $data['test_date'] ?? now(),
                'status'       => 'DRAFT',
            ]);

            SpectroResult::create([
                'sample_id' => $s->id,
                'c'  => $data['c']  ?? null,
                'si' => $data['si'] ?? null,
                'mn' => $data['mn'] ?? null,
                'p'  => $data['p']  ?? null,
                's'  => $data['s']  ?? null,
                'cr' => $data['cr'] ?? null,
                'ni' => $data['ni'] ?? null,
                'mo' => $data['mo'] ?? null,
                'cu' => $data['cu'] ?? null,
                'co' => $data['co'] ?? null,
                'al' => $data['al'] ?? null,
                'v'  => $data['v']  ?? null,
                'n'  => $data['n']  ?? null,
            ]);
        });

        return redirect()->route('samples.index')->with('ok', 'Draft Chemical Testing tersimpan.');
    }

    /** Edit DRAFT/REJECTED */
    public function edit(Sample $sample)
    {
        abort_unless(auth()->user()->hasRole(['Operator','Approver']), 403);

        if (!in_array($sample->status, ['DRAFT', 'REJECTED'], true)) {
            return redirect()->route('samples.index')
                ->with('err', 'Hanya DRAFT/REVISI yang bisa diedit.');
        }

        $sample->load(['spectroResult']);

        // Opsi dropdown untuk halaman Edit
        $grades = ['CF8', 'CF8M', 'SCS13A', 'SCS14A', '1.4308', '1.4408'];
        $productTypes = ['Flange', 'Fitting'];

        return view('samples.edit', compact('sample', 'grades', 'productTypes'));
    }

    /** Update DRAFT/REJECTED */
    public function update(Request $r, Sample $sample)
    {
        abort_unless(auth()->user()->hasRole(['Operator','Approver']), 403);

        if (!in_array($sample->status, ['DRAFT', 'REJECTED'], true)) {
            return redirect()->route('samples.index')
                ->with('err', 'Hanya DRAFT/REVISI yang bisa diupdate.');
        }

        $rules = [
            // Identitas
            'grade'        => 'required|in:CF8,CF8M,SCS13A,SCS14A,1.4308,1.4408',
            'standard'     => 'nullable|string',
            'product_type' => 'nullable|string',
            'heat_no'      => 'nullable|string',
            'batch_no'     => 'nullable|string',
            'po_customer'  => 'nullable|string|max:100',
            'test_date'    => 'nullable|date',

            // Spektro
            'c'  => 'nullable|numeric',
            'si' => 'nullable|numeric',
            'mn' => 'nullable|numeric',
            'p'  => 'nullable|numeric',
            's'  => 'nullable|numeric',
            'cr' => 'nullable|numeric',
            'ni' => 'nullable|numeric',
            'mo' => 'nullable|numeric',
            'cu' => 'nullable|numeric',
            'co' => 'nullable|numeric',
            'al' => 'nullable|numeric',
            'v'  => 'nullable|numeric',
            'n'  => 'nullable|numeric',
        ];

        $data = $r->validate($rules);

        // Map grade → standard (hindari dot-notation untuk key 1.4308/1.4408)
        $standard = $this->resolveStandard($data['grade']);

        DB::transaction(function () use ($sample, $data, $standard) {
            $sample->update([
                'grade'        => $data['grade'],
                'standard'     => $standard,
                'product_type' => $data['product_type'] ?? $sample->product_type,
                'heat_no'      => $data['heat_no'] ?? null,
                'batch_no'     => $data['batch_no'] ?? null,
                'po_customer'  => $data['po_customer'] ?? $sample->po_customer,
                'test_date'    => $data['test_date'] ?? $sample->test_date,
            ]);

            $sample->spectroResult()->updateOrCreate(['sample_id' => $sample->id], [
                'c'  => $data['c']  ?? null,
                'si' => $data['si'] ?? null,
                'mn' => $data['mn'] ?? null,
                'p'  => $data['p']  ?? null,
                's'  => $data['s']  ?? null,
                'cr' => $data['cr'] ?? null,
                'ni' => $data['ni'] ?? null,
                'mo' => $data['mo'] ?? null,
                'cu' => $data['cu'] ?? null,
                'co' => $data['co'] ?? null,
                'al' => $data['al'] ?? null,
                'v'  => $data['v']  ?? null,
                'n'  => $data['n']  ?? null,
            ]);
        });

        return redirect()->route('samples.index')->with('ok', 'Perubahan disimpan.');
    }

    /** Soft delete sample (pindah ke Recycle Bin) */
    public function destroy(Sample $sample)
    {
        abort_unless(auth()->user()->hasRole('Approver'), 403);

        DB::transaction(function () use ($sample) {
            $sample->spectroResult()?->delete();
            $sample->tensileTest()?->delete();
            $sample->hardnessTest()?->delete();
            $sample->delete();
        });

        return back()->with('ok','Sample dipindah ke Recycle Bin (soft delete).');
    }

    /** Lihat isi Recycle Bin */
    public function recycleBin()
    {
        abort_unless(auth()->user()->hasRole('Approver'), 403);

        $samples = Sample::onlyTrashed()->orderByDesc('id')->get();
        return view('samples.recycle-bin', compact('samples'));
    }

    /** Pulihkan dari Recycle Bin */
    public function restore($id)
    {
        abort_unless(auth()->user()->hasRole('Approver'), 403);

        DB::transaction(function () use ($id) {
            $s = Sample::onlyTrashed()->findOrFail($id);
            $s->restore();
            $s->spectroResult()->withTrashed()->restore();
            $s->tensileTest()->withTrashed()->restore();
            $s->hardnessTest()->withTrashed()->restore();
        });

        return back()->with('ok','Sample berhasil dipulihkan.');
    }

    /** Hapus permanen (beserta PDF jika ada) */
    public function forceDelete($id)
    {
        abort_unless(auth()->user()->hasRole('Approver'), 403);

        DB::transaction(function () use ($id) {
            $s = Sample::onlyTrashed()->findOrFail($id);

            // Hapus file PDF terkait (jika ada pola penamaan report_no)
            if ($s->report_no) {
                $safeReport = preg_replace('/[\/\\\\]+/', '-', (string) $s->report_no);
                $yearMonth  = $s->approved_at ? $s->approved_at->format('Y/m') : null;

                if ($yearMonth) {
                    $dir = 'qc-pdf/'.$yearMonth;
                    foreach (Storage::files($dir) as $f) {
                        if (str_contains($f, 'QC-'.$safeReport)) {
                            Storage::delete($f);
                        }
                    }
                } else {
                    foreach (Storage::allFiles('qc-pdf') as $f) {
                        if (str_contains($f, 'QC-'.$safeReport)) {
                            Storage::delete($f);
                        }
                    }
                }
            }

            // Hapus permanen data terkait
            $s->spectroResult()->withTrashed()->forceDelete();
            $s->tensileTest()->withTrashed()->forceDelete();
            $s->hardnessTest()->withTrashed()->forceDelete();

            $s->forceDelete();
        });

        return back()->with('ok','Sample & PDF terkait dihapus permanen.');
    }

    /**
     * Resolve standard berdasarkan grade tanpa terjebak "dot notation"
     */
    private function resolveStandard(string $grade): string
    {
        $materials = config('qc.materials', []);           // ambil seluruh array
        $mat       = $materials[$grade] ?? null;           // index manual (aman untuk key bertitik)

        return $mat['standard'] ?? match ($grade) {
            '1.4308', '1.4408' => 'BS EN 10213',
            default            => 'ASTM A351',
        };
    }
}
