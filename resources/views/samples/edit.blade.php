@extends('layouts.app', ['title' => 'Edit QC Sample'])

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Edit QC Sample (ID #{{ $sample->id }})</h4>
    <a href="{{ route('samples.index') }}" class="btn btn-outline-secondary">← Kembali</a>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger">
      <div class="fw-semibold mb-1">Periksa kembali isian berikut:</div>
      <ul class="mb-0">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @php
    $sr = $sample->spectroResult;
    $tt = $sample->tensileTest;
    $hd = $sample->hardnessTest;

    // Fallback jika controller belum mengirim variabel opsi
    $grades = $grades ?? ['CF8','CF8M','SCS13A','SCS14A','1.4308','1.4408'];
    $productTypes = $productTypes ?? ['Flange','Fitting'];

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

  <form method="post" action="{{ route('samples.update',$sample) }}" class="needs-validation" novalidate>
    @csrf @method('PUT')

    {{-- Identitas --}}
    <div class="card mb-3">
      <div class="card-header fw-semibold">Identitas Sampel</div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Grade <span class="text-danger">*</span></label>
            <select name="grade" id="grade" class="form-select" required>
              <option value="">— Pilih —</option>
              @foreach($grades as $g)
                <option value="{{ $g }}" {{ old('grade',$sample->grade) === $g ? 'selected' : '' }}>
                  {{ $g }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Standard</label>
            <input type="text" name="standard" id="standard" class="form-control"
                   value="{{ old('standard',$sample->standard) }}" placeholder="Otomatis dari grade">
          </div>

          <div class="col-md-3">
            <label class="form-label">Product Type</label>
            <select name="product_type" class="form-select" required>
              <option value="">— Pilih —</option>
              @foreach($productTypes as $pt)
                <option value="{{ $pt }}" {{ old('product_type',$sample->product_type) === $pt ? 'selected' : '' }}>
                  {{ $pt }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Test Date</label>
            <input type="date" name="test_date" class="form-control" value="{{ $testDate }}">
          </div>

      </div>
    </div>
  </form>

  {{-- Auto-isi Standard berdasarkan Grade --}}
  <script>
  document.addEventListener('DOMContentLoaded', function () {
      const gradeEl = document.getElementById('grade');
      const stdEl   = document.getElementById('standard');

      const map = {
          'CF8'   : 'ASTM A351 ',
          'CF8M'  : 'ASTM A351 ',
          'SCS13A': 'JIS G 5121 ',
          'SCS14A': 'JIS G 5121 ',
          '1.4308': 'BS EN 10213',
          '1.4408': 'BS EN 10213',
      };

      function applyStandard(force=false) {
          const g = gradeEl.value;
          if (map[g] && (force || !stdEl.value)) {
              stdEl.value = map[g];
          }
      }

      gradeEl.addEventListener('change', function(){ applyStandard(true); });
      applyStandard(false); // saat halaman dibuka
  });
  </script>
@endsection
