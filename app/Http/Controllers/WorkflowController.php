<?php

namespace App\Http\Controllers;

use App\Models\Sample;
use App\Services\ReportNoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class WorkflowController extends Controller
{
    // Antrian yang butuh persetujuan (APPROVER only)
    public function queue()
    {
        abort_unless(auth()->user()->hasRole('Approver'), 403);

        $submitted = Sample::where('status', 'SUBMITTED')
            ->orderByDesc('id')
            ->get();

        return view('approvals.index', compact('submitted'));
    }

    // Operator/Approver: ubah DRAFT/REJECTED -> SUBMITTED + isi Report No (kalau kosong)
    public function submit(Sample $sample)
    {
        abort_unless(auth()->user()->hasRole(['Operator', 'Approver']), 403);

        if (!in_array($sample->status, ['DRAFT', 'REJECTED'], true)) {
            return back()->with('err', 'Hanya DRAFT/REJECTED yang bisa di-submit.');
        }

        if (!$sample->report_no) {
            $sample->report_no = ReportNoService::next();
        }

        $sample->status = 'SUBMITTED';
        $sample->save();

        return back()->with('ok', 'Submitted. Menunggu approve.');
    }

    // Approver: kembalikan ke REJECTED
    public function reject(Sample $sample)
    {
        abort_unless(auth()->user()->hasRole('Approver'), 403);

        if ($sample->status !== 'SUBMITTED') {
            return back()->with('err', 'Hanya status SUBMITTED yang bisa direvisi.');
        }

        $sample->status       = 'REJECTED';
        $sample->approved_at  = null;
        $sample->approved_by  = null; // jika kolom ada
        $sample->save();

        return back()->with('ok', 'Dikembalikan ke operator untuk revisi.');
    }

    // Approver: set APPROVED + render & arsipkan PDF (fix: kirim isPreview dan aman untuk grade bertitik)
    public function approve(Sample $sample)
    {
        abort_unless(auth()->user()->hasRole('Approver'), 403);

        if (!in_array($sample->status, ['SUBMITTED', 'REJECTED'], true)) {
            return back()->with('err', 'Status tidak valid untuk approve.');
        }

        // Tandai approved
        $sample->status      = 'APPROVED';
        $sample->approved_at = now();
        $sample->approved_by = auth()->id(); // jika kolom ada
        $sample->save();

        // Relasi nilai uji
        $sample->load(['spectroResult', 'tensileTest', 'hardnessTest']);

        // === Ambil standard & limits TANPA dot-notation (aman untuk key "1.4308"/"1.4408") ===
        $materialsAll = config('qc.materials', []);
        $paramsAll    = config('qc.params', []);

        $material = $materialsAll[$sample->grade] ?? [];
        $limits   = $paramsAll[$sample->grade]    ?? ['chem' => [], 'mech' => []];

        // Standard: utamakan nilai yang tersimpan di DB; fallback ke config; fallback terakhir sesuai grade
        $standard = $sample->standard
            ?: ($material['standard'] ?? (in_array($sample->grade, ['1.4308','1.4408'], true) ? 'BS EN 10213' : 'ASTM A351'));

        // === Siapkan aset (base64) agar DomPDF tidak gagal load dari path lokal ===
        $embed = function (string $relPath): ?string {
            $path = public_path($relPath);
            if (!is_file($path)) return null;
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = $ext === 'svg' ? 'image/svg+xml' : 'image/'.$ext;
            return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
        };

        $logoData      = $embed('storage/assets/logo.png');
        $stampImgData  = $embed('storage/assets/stamp.png');
        $signatureData = $embed('storage/assets/signature.png');

        // === Render view PDF (FIX: selalu kirim 'isPreview' agar Blade tidak error) ===
        $pdf = Pdf::loadView('pdf.report', [
            'sample'        => $sample,
            'isPreview'     => false, // << PENTING: hindari "Undefined variable $isPreview"
            'stampUser'     => auth()->user()?->name ?? 'system',
            'stampTime'     => now('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'stampId'       => (string) Str::uuid(),

            // gambar base64
            'logoData'      => $logoData,
            'signatureData' => $signatureData,
            'stampImgData'  => $stampImgData,

            // untuk template yang mengambil standard/limit
            'standard'      => $standard,
            'limits'        => $limits,
        ])->setPaper('A4');

        // === Arsip: storage/app/qc-pdf/{Y}/{m}/QC-{report}-{heat}-{Ymd}-v{n}.pdf ===
        $safeReportNo = preg_replace('/[\/\\\\]+/', '-', (string) $sample->report_no);
        $dt           = $sample->approved_at ?? now();
        $dir          = 'qc-pdf/'.$dt->format('Y/m');
        $name         = 'QC-'.$safeReportNo.'-'.($sample->heat_no ?: 'NOHEAT').'-'.$dt->format('Ymd').'-v'.($sample->version ?? 1).'.pdf';

        Storage::makeDirectory($dir);
        Storage::put($dir.'/'.$name, $pdf->output());

        return back()->with('ok', 'Disetujui. PDF tersimpan: '.$dir.'/'.$name);
    }

    // Approver: batalkan status APPROVED -> REJECTED (Revisi) agar bisa diedit ulang
    public function revoke(Sample $sample)
    {
        abort_unless(auth()->user()->hasRole('Approver'), 403);

        if ($sample->status !== 'APPROVED') {
            return back()->with('err', 'Hanya laporan APPROVED yang bisa dibatalkan (revoke).');
        }

        DB::transaction(function () use ($sample) {
            $sample->status      = 'REJECTED';
            $sample->approved_at = null;
            $sample->approved_by = null;
            $sample->save();
        });

        return back()->with('ok', 'Persetujuan dibatalkan. Status kembali ke REVISI.');
    }
}
