<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>QC Report</title>
<style>
  /* Margin halaman DomPDF */
  @page { margin: 110px 40px 110px 40px; }

  /* Font & text */
  body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; }
  .right { text-align: right; }
  .center { text-align: center; }
  .fail { color: #b00020; font-weight: 700; } /* merah untuk FAIL */
  .meta-2row { line-height: 1.25; font-size: 12px; }

  /* Header & Footer fixed */
  header {
    position: fixed; top: -85px; left: 0; right: 0; height: 75px;
    border-bottom: 1px solid #ccc;
  }
  footer {
    position: fixed; bottom: -70px; left: 0; right: 0; height: 60px;
    font-size: 10px; color: #555; border-top: 1px solid #ccc; padding-top: 6px;
  }

  /* Watermark untuk non-approved */
  .watermark {
    position: fixed; top: 35%; left: 10%; width: 80%;
    text-align: center; opacity: 0.12; transform: rotate(-25deg); font-size: 48px;
  }

  /* Tabel */
  table { width: 100%; border-collapse: collapse; }
  th, td { border: 1px solid #ccc; padding: 6px; }
  th { background: #f2f2f2; }

  /* Blok approval (muncul hanya saat approved) */
  .approval-wrapper { margin-top: 16px; }
  .approval-box {
    position: relative; width: 260px; height: 140px;
    border: 1px solid #ddd; padding: 8px; border-radius: 6px;
  }
  .approval-title { font-weight: 700; margin-bottom: 6px; }
  .sig-layer { position: absolute; left: 28px; top: 24px; width: 150px; opacity: 0.95; }
  .stamp-layer { position: absolute; left: 70px; top: 10px; width: 120px; opacity: 0.65; }
  .approval-role { position: absolute; left: 0; right: 0; bottom: 28px; text-align: center; font-size: 11px; }
  .approval-date { position: absolute; left: 0; right: 0; bottom: 8px; text-align: center; font-size: 11px; }
</style>
</head>
<body>

@php
use Illuminate\Support\Carbon;

/** 1) Status approved? */
$approved = strtoupper((string)($sample->status ?? '')) === 'APPROVED';

/** 2) Ambil batas dari config grade (FIX: hindari dot-notation utk key 1.4308/1.4408) */
$paramsAll = config('qc.params', []);                           // <-- ambil seluruh array
$cfg       = $paramsAll[$sample->grade ?? ''] ?? ['chem'=>[], 'mech'=>[]]; // <-- index manual aman
$chem      = $cfg['chem'] ?? [];
$mech      = $cfg['mech'] ?? [];

/** (Opsional) fallback standard dari config materials bila kolom DB kosong */
$materialsAll = config('qc.materials', []);
$stdFromCfg   = $materialsAll[$sample->grade]['standard'] ?? null;

/** 3) Helpers lokal */
$fmt = function($v, $dec = 3) { return is_null($v) ? '—' : number_format((float)$v, $dec, '.', ''); };
$range = function(string $key) use ($chem, $mech) {
    if (array_key_exists($key, $chem)) return $chem[$key];   // [min,max]
    if (array_key_exists($key, $mech)) return $mech[$key];   // [min,max]
    return [null, null];
};
$judge = function($val, $min, $max) {
    if (is_null($val) || (is_null($min) && is_null($max))) return '—';
    if (!is_null($min) && $val < $min) return 'FAIL';
    if (!is_null($max) && $val > $max) return 'FAIL';
    return 'PASS';
};
$statusTxt = function($val, $min, $max) use ($judge) {
    $st = $judge($val, $min, $max);
    return $st === 'FAIL' ? 'FAIL' : '';
};
$actual = fn($obj, $field) => $obj ? ($obj->{$field} ?? null) : null;
$decByUnit = fn($unit) => str_contains($unit, 'wt') ? 4 : 2;

/** 4) Relasi (aman null) */
$s = $sample->spectroResult ?? null;
$t = $sample->tensileTest ?? null;
$h = $sample->hardnessTest ?? null;

/** 5) Header meta */
$reportNo   = !empty($sample->report_no) ? ('QC-'.$sample->report_no) : '—';
$reportDate = !empty($sample->test_date)
    ? Carbon::parse($sample->test_date)->timezone('Asia/Jakarta')->format('Y-m-d')
    : now('Asia/Jakarta')->format('Y-m-d');
$approvedDate = !empty($sample->approved_at)
    ? Carbon::parse($sample->approved_at)->timezone('Asia/Jakarta')->format('Y-m-d')
    : '—';
@endphp

<header>
  <div style="display:flex; align-items:center; gap:10px; padding-top:6px;">
    @if(!empty($logoData))
      <img src="{{ $logoData }}" alt="Logo" style="height:50px;">
    @endif
    <div style="flex:1;">
      <div style="font-size:15px; font-weight:700;">REPORT OF ANALYSIS</div>
      <div class="meta-2row">
        <div>Report No : <strong>{{ $reportNo }}</strong></div>
        <div>Date      : <strong>{{ $reportDate }}</strong></div>
      </div>
    </div>
  </div>
</header>

{{-- Watermark hanya bila belum approved --}}
@if(!$approved)
  <div class="watermark">PREVIEW COPY</div>
@endif

{{-- ===== Identitas Sampel ===== --}}
<table style="margin-top:54px;">
  <tr>
    <th style="width:25%;">Sample Identification</th>
    <td style="width:25%;">{{ $sample->product_type ?? '—' }}</td>
    <th style="width:25%;">Standard</th>
    <td style="width:25%;">{{ $sample->standard ?? ($stdFromCfg ?? '—') }}</td>
  </tr>
  <tr>
    <th>Grade</th>
    <td>{{ $sample->grade ?? '—' }}</td>
    <th>PO/Customer</th>
    <td>{{ $sample->po_customer ?? '—' }}</td>
  </tr>
  <tr>
    <th>Heat/Batch</th>
    <td colspan="3">{{ trim(($sample->heat_no ?? '') . ' / ' . ($sample->batch_no ?? '')) ?: '—' }}</td>
  </tr>
</table>

{{-- ===== Tabel Hasil (Status hanya tampil bila FAIL) ===== --}}
<table style="margin-top:12px;">
  <thead>
    <tr>
      <th style="width:6%;">No</th>
      <th style="width:34%;">Parameter</th>
      <th style="width:10%;">Unit</th>
      <th style="width:15%;">Test Result</th>
      <th style="width:15%;">Min</th>
      <th style="width:15%;">Max</th>
      <th style="width:5%;">Status</th>
    </tr>
  </thead>
  <tbody>
    @php $no=1; @endphp

    {{-- Kimia --}}
    @php [$min,$max]=$range('c');  $val=$actual($s,'c');  $unit='% wt'; @endphp
    <tr>
      <td class="center">{{ $no++ }}</td><td>Carbon (C)</td><td class="center">{{ $unit }}</td>
      <td class="right">{{ $fmt($val, $decByUnit($unit)) }}</td><td class="right">{{ $fmt($min) }}</td><td class="right">{{ $fmt($max) }}</td>
      <td class="center {{ $judge($val,$min,$max)==='FAIL'?'fail':'' }}">{{ $statusTxt($val,$min,$max) }}</td>
    </tr>

    @php [$min,$max]=$range('si'); $val=$actual($s,'si'); $unit='% wt'; @endphp
    <tr>
      <td class="center">{{ $no++ }}</td><td>Silicon (Si)</td><td class="center">{{ $unit }}</td>
      <td class="right">{{ $fmt($val, $decByUnit($unit)) }}</td><td class="right">{{ $fmt($min) }}</td><td class="right">{{ $fmt($max) }}</td>
      <td class="center {{ $judge($val,$min,$max)==='FAIL'?'fail':'' }}">{{ $statusTxt($val,$min,$max) }}</td>
    </tr>

    @php [$min,$max]=$range('mn'); $val=$actual($s,'mn'); $unit='% wt'; @endphp
    <tr>
      <td class="center">{{ $no++ }}</td><td>Manganese (Mn)</td><td class="center">{{ $unit }}</td>
      <td class="right">{{ $fmt($val, $decByUnit($unit)) }}</td><td class="right">{{ $fmt($min) }}</td><td class="right">{{ $fmt($max) }}</td>
      <td class="center {{ $judge($val,$min,$max)==='FAIL'?'fail':'' }}">{{ $statusTxt($val,$min,$max) }}</td>
    </tr>

    @php [$min,$max]=$range('p');  $val=$actual($s,'p');  $unit='% wt'; @endphp
    <tr>
      <td class="center">{{ $no++ }}</td><td>Phosphorus (P)</td><td class="center">{{ $unit }}</td>
      <td class="right">{{ $fmt($val, $decByUnit($unit)) }}</td><td class="right">{{ $fmt($min) }}</td><td class="right">{{ $fmt($max) }}</td>
      <td class="center {{ $judge($val,$min,$max)==='FAIL'?'fail':'' }}">{{ $statusTxt($val,$min,$max) }}</td>
    </tr>

    @php [$min,$max]=$range('s');  $val=$actual($s,'s');  $unit='% wt'; @endphp
    <tr>
      <td class="center">{{ $no++ }}</td><td>Sulphur (S)</td><td class="center">{{ $unit }}</td>
      <td class="right">{{ $fmt($val, $decByUnit($unit)) }}</td><td class="right">{{ $fmt($min) }}</td><td class="right">{{ $fmt($max) }}</td>
      <td class="center {{ $judge($val,$min,$max)==='FAIL'?'fail':'' }}">{{ $statusTxt($val,$min,$max) }}</td>
    </tr>

    @php [$min,$max]=$range('cr'); $val=$actual($s,'cr'); $unit='% wt'; @endphp
    <tr>
      <td class="center">{{ $no++ }}</td><td>Chromium (Cr)</td><td class="center">{{ $unit }}</td>
      <td class="right">{{ $fmt($val, 2) }}</td><td class="right">{{ $fmt($min, 2) }}</td><td class="right">{{ $fmt($max, 2) }}</td>
      <td class="center {{ $judge($val,$min,$max)==='FAIL'?'fail':'' }}">{{ $statusTxt($val,$min,$max) }}</td>
    </tr>

    @php [$min,$max]=$range('ni'); $val=$actual($s,'ni'); $unit='% wt'; @endphp
    <tr>
      <td class="center">{{ $no++ }}</td><td>Nickel (Ni)</td><td class="center">{{ $unit }}</td>
      <td class="right">{{ $fmt($val, 2) }}</td><td class="right">{{ $fmt($min, 2) }}</td><td class="right">{{ $fmt($max, 2) }}</td>
      <td class="center {{ $judge($val,$min,$max)==='FAIL'?'fail':'' }}">{{ $statusTxt($val,$min,$max) }}</td>
    </tr>

    @php [$min,$max]=$range('mo'); $val=$actual($s,'mo'); $unit='% wt'; @endphp
    <tr>
      <td class="center">{{ $no++ }}</td><td>Molybdenum (Mo)</td><td class="center">{{ $unit }}</td>
      <td class="right">{{ $fmt($val, 3) }}</td><td class="right">{{ $fmt($min, 3) }}</td><td class="right">{{ $fmt($max, 3) }}</td>
      <td class="center {{ $judge($val,$min,$max)==='FAIL'?'fail':'' }}">{{ $statusTxt($val,$min,$max) }}</td>
    </tr>

    @php [$min,$max]=$range('cu'); $val=$actual($s,'cu'); $unit='% wt'; @endphp
    <tr>
      <td class="center">{{ $no++ }}</td><td>Copper (Cu)</td><td class="center">{{ $unit }}</td>
      <td class="right">{{ $fmt($val, 3) }}</td><td class="right">{{ $fmt($min, 3) }}</td><td class="right">{{ $fmt($max, 3) }}</td>
      <td class="center {{ $judge($val,$min,$max)==='FAIL'?'fail':'' }}">{{ $statusTxt($val,$min,$max) }}</td>
    </tr>

    @php [$min,$max]=$range('co'); $val=$actual($s,'co'); $unit='% wt'; @endphp
    <tr>
      <td class="center">{{ $no++ }}</td><td>Cobalt (Co)</td><td class="center">{{ $unit }}</td>
      <td class="right">{{ $fmt($val, 3) }}</td><td class="right">{{ $fmt($min, 3) }}</td><td class="right">{{ $fmt($max, 3) }}</td>
      <td class="center {{ $judge($val,$min,$max)==='FAIL'?'fail':'' }}">{{ $statusTxt($val,$min,$max) }}</td>
    </tr>

    @php [$min,$max]=$range('al'); $val=$actual($s,'al'); $unit='% wt'; @endphp
    <tr>
      <td class="center">{{ $no++ }}</td><td>Aluminium (Al)</td><td class="center">{{ $unit }}</td>
      <td class="right">{{ $fmt($val, 3) }}</td><td class="right">{{ $fmt($min, 3) }}</td><td class="right">{{ $fmt($max, 3) }}</td>
      <td class="center {{ $judge($val,$min,$max)==='FAIL'?'fail':'' }}">{{ $statusTxt($val,$min,$max) }}</td>
    </tr>

    @php [$min,$max]=$range('v');  $val=$actual($s,'v');  $unit='% wt'; @endphp
    <tr>
      <td class="center">{{ $no++ }}</td><td>Vanadium (V)</td><td class="center">{{ $unit }}</td>
      <td class="right">{{ $fmt($val, 3) }}</td><td class="right">{{ $fmt($min, 3) }}</td><td class="right">{{ $fmt($max, 3) }}</td>
      <td class="center {{ $judge($val,$min,$max)==='FAIL'?'fail':'' }}">{{ $statusTxt($val,$min,$max) }}</td>
    </tr>

    @php
      $showMech = ($t && ($t->ys_mpa || $t->uts_mpa || $t->elong_pct)) || ($h && $h->avg_value);
    @endphp

    @if($showMech)
        {{-- Mekanik --}}
        @php [$min,$max]=$range('ys_mpa');    $val=$actual($t,'ys_mpa');    $unit='MPa'; @endphp
        <tr>
          <td class="center">{{ $no++ }}</td><td>Yield Strength</td><td class="center">{{ $unit }}</td>
          <td class="right">{{ $fmt($val, 2) }}</td><td class="right">{{ $fmt($min, 2) }}</td><td class="right">{{ $fmt($max, 2) }}</td>
          <td class="center {{ $judge($val,$min,$max)==='FAIL'?'fail':'' }}">{{ $statusTxt($val,$min,$max) }}</td>
        </tr>

        @php [$min,$max]=$range('uts_mpa');   $val=$actual($t,'uts_mpa');   $unit='MPa'; @endphp
        <tr>
          <td class="center">{{ $no++ }}</td><td>Ultimate Tensile Strength</td><td class="center">{{ $unit }}</td>
          <td class="right">{{ $fmt($val, 2) }}</td><td class="right">{{ $fmt($min, 2) }}</td><td class="right">{{ $fmt($max, 2) }}</td>
          <td class="center {{ $judge($val,$min,$max)==='FAIL'?'fail':'' }}">{{ $statusTxt($val,$min,$max) }}</td>
        </tr>

        @php [$min,$max]=$range('elong_pct'); $val=$actual($t,'elong_pct'); $unit='%'; @endphp
        <tr>
          <td class="center">{{ $no++ }}</td><td>Elongation</td><td class="center">{{ $unit }}</td>
          <td class="right">{{ $fmt($val, 2) }}</td><td class="right">{{ $fmt($min, 2) }}</td><td class="right">{{ $fmt($max, 2) }}</td>
          <td class="center {{ $judge($val,$min,$max)==='FAIL'?'fail':'' }}">{{ $statusTxt($val,$min,$max) }}</td>
        </tr>

        @php [$min,$max]=$range('hb');        $val=$actual($h,'avg_value'); $unit='HB'; @endphp
        <tr>
          <td class="center">{{ $no++ }}</td><td>Brinell Hardness</td><td class="center">{{ $unit }}</td>
          <td class="right">{{ $fmt($val, 2) }}</td><td class="right">{{ $fmt($min, 2) }}</td><td class="right">{{ $fmt($max, 2) }}</td>
          <td class="center {{ $judge($val,$min,$max)==='FAIL'?'fail':'' }}">{{ $statusTxt($val,$min,$max) }}</td>
        </tr>
    @endif
  </tbody>
</table>

{{-- ===== Blok Approval (muncul hanya saat APPROVED) ===== --}}
@if($approved && (!empty($signatureData) || !empty($stampImgData)))
  <div class="approval-wrapper">
    <div class="approval-box">
      <div class="approval-title">Approved By</div>
      @if(!empty($signatureData))
        <img class="sig-layer" src="{{ $signatureData }}" alt="Signature">
      @endif
      @if(!empty($stampImgData))
        <img class="stamp-layer" src="{{ $stampImgData }}" alt="Stamp">
      @endif
      <div class="approval-role">Kabag QC</div>
      <div class="approval-date">Approved on: <strong>{{ $approvedDate }}</strong></div>
    </div>
  </div>
@endif

<footer>
  <div style="display:flex; justify-content:space-between;">
    <div>
      Printed by: <strong>{{ $stampUser ?? '-' }}</strong><br>
      Access time (WIB): <strong>{{ $stampTime ?? '-' }}</strong><br>
      Stamp ID: <strong>{{ $stampId ?? '-' }}</strong>
    </div>
    <div style="text-align:right;">
      Document: QC-Report #{{ $sample->id ?? '-' }}<br>
      Page <span class="pageNumber"></span> / <span class="totalPages"></span>
    </div>
  </div>
</footer>

{{-- Counter halaman untuk DomPDF --}}
<script type="text/php">
if (isset($pdf)) {
    // X, Y disesuaikan margin bawah (110px) -> kira2 810 cocok untuk A4 72dpi
    $pdf->page_text(520, 810, "Page {PAGE_NUM} / {PAGE_COUNT}", "DejaVu Sans", 8, [0,0,0]);
}
</script>

</body>
</html>
