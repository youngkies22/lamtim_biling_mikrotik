@extends('layouts/layoutMaster')

@section('title', 'Generate Tagihan')

@section('page-style')
<style>
  .stat-card {
    border: none;
    border-radius: 12px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    overflow: hidden;
  }
  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
  }
  .stat-card .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
  }
  .stat-card .stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1;
  }
  .stat-card .stat-label {
    font-size: 0.8rem;
    color: #697a8d;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .generate-card {
    border: none;
    border-radius: 16px;
    overflow: hidden;
  }
  .generate-card .card-header {
    background: linear-gradient(135deg, #696cff 0%, #8592ff 100%);
    padding: 1.5rem 1.75rem;
    border: none;
  }
  .generate-card .card-header h5 {
    color: #fff;
    margin: 0;
    font-weight: 600;
  }
  .generate-card .card-header p {
    color: rgba(255,255,255,0.8);
    margin: 0.25rem 0 0;
    font-size: 0.875rem;
  }

  .period-selector {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
  }
  .period-selector .form-group {
    flex: 1;
  }
  .period-selector .form-select {
    border-radius: 10px;
    padding: 0.625rem 1rem;
    font-size: 0.95rem;
    border: 2px solid #e7e7ff;
    transition: all 0.2s ease;
  }
  .period-selector .form-select:focus {
    border-color: #696cff;
    box-shadow: 0 0 0 0.2rem rgba(105,108,255,0.15);
  }
  .period-selector .form-label {
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #566a7f;
    margin-bottom: 0.5rem;
  }

  #btn-generate {
    border-radius: 12px;
    padding: 0.875rem 2rem;
    font-size: 1rem;
    font-weight: 600;
    letter-spacing: 0.3px;
    transition: all 0.25s ease;
    border: none;
    background: linear-gradient(135deg, #696cff, #8592ff);
    box-shadow: 0 4px 15px rgba(105,108,255,0.35);
  }
  #btn-generate:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(105,108,255,0.45);
  }
  #btn-generate:active:not(:disabled) {
    transform: translateY(0);
  }
  #btn-generate:disabled {
    opacity: 0.65;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
  }

  .info-panel {
    border: none;
    border-radius: 16px;
    background: #f8f7ff;
    border: 2px solid #e7e7ff;
  }
  .info-panel .info-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #eeeeff;
  }
  .info-panel .info-item:last-child {
    border-bottom: none;
  }
  .info-panel .info-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.875rem;
  }
  .info-panel .info-text {
    font-size: 0.875rem;
    color: #566a7f;
    line-height: 1.5;
  }

  .result-card {
    border-radius: 12px;
    border: none;
    animation: slideUp 0.3s ease;
  }
  @keyframes slideUp {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .result-card .result-stat {
    text-align: center;
    padding: 1rem;
  }
  .result-card .result-stat .result-value {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
  }
  .result-card .result-stat .result-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0.25rem;
    font-weight: 500;
  }

  @media (max-width: 767.98px) {
    .period-selector {
      flex-direction: column;
      gap: 0;
    }
    .period-selector .form-group {
      margin-bottom: 1rem;
    }
    .stat-card .stat-value {
      font-size: 1.5rem;
    }
  }
</style>
@endsection

@section('page-script')
<script>
  const namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

  document.getElementById('btn-generate').addEventListener('click', async function() {
    const btn = this;
    const bulan = document.getElementById('filter-bulan').value;
    const tahun = document.getElementById('filter-tahun').value;
    const msgBox = document.getElementById('status-msg');

    if (!bulan || !tahun) {
      Swal.fire({
        title: 'Periode Belum Dipilih',
        text: 'Silakan pilih tahun dan bulan terlebih dahulu.',
        icon: 'warning',
        confirmButtonText: 'OK',
        buttonsStyling: false,
        customClass: { confirmButton: "btn btn-warning" }
      });
      return;
    }

    const periodeText = namaBulan[parseInt(bulan)] + ' ' + tahun;

    Swal.fire({
      title: 'Generate Tagihan?',
      html: `
        <div class="text-start">
          <div class="d-flex align-items-center gap-2 mb-3 p-3 rounded" style="background:#f8f7ff;">
            <i class="mdi mdi-calendar-clock mdi-24px text-primary"></i>
            <div>
              <div class="text-muted small">Periode</div>
              <div class="fw-bold">${periodeText}</div>
            </div>
          </div>
          <p class="text-muted mb-0" style="font-size:0.875rem;">Tagihan akan dibuat untuk semua pelanggan aktif. Tagihan yang sudah ada akan dilewati.</p>
        </div>
      `,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: '<i class="mdi mdi-check me-1"></i> Ya, Generate',
      cancelButtonText: 'Batal',
      buttonsStyling: false,
      customClass: {
        confirmButton: "btn btn-primary me-2",
        cancelButton: "btn btn-outline-secondary"
      }
    }).then(async (result) => {
      if (result.isConfirmed) {
        btn.disabled = true;
        btn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-2"></i>Memproses...';
        if (msgBox) msgBox.innerHTML = '';

        try {
          const response = await fetch(`/tagihan/generate-tagihan-bulanan`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ bulan, tahun })
          });

          const data = await response.json();

          if (data.success) {
            const { inserted = 0, skipped = 0, errors = [] } = data.data || {};

            if (msgBox) {
              msgBox.innerHTML = `
                <div class="result-card shadow-sm">
                  <div class="card">
                    <div class="card-body p-3">
                      <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="stat-icon bg-label-success" style="width:36px;height:36px;border-radius:10px;">
                          <i class="mdi mdi-check-circle"></i>
                        </div>
                        <div>
                          <div class="fw-bold">Generate Berhasil</div>
                          <div class="text-muted" style="font-size:0.8rem;">${periodeText}</div>
                        </div>
                      </div>
                      <div class="row g-2">
                        <div class="col-6">
                          <div class="result-stat rounded" style="background:#e8fadf;">
                            <div class="result-value text-success">${inserted}</div>
                            <div class="result-label text-success">Dibuat</div>
                          </div>
                        </div>
                        <div class="col-6">
                          <div class="result-stat rounded" style="background:#fff2d6;">
                            <div class="result-value text-warning">${skipped}</div>
                            <div class="result-label text-warning">Dilewati</div>
                          </div>
                        </div>
                      </div>
                      ${errors.length > 0 ? `
                        <div class="mt-2 p-2 rounded" style="background:#ffe0db;">
                          <small class="text-danger"><i class="mdi mdi-alert-circle me-1"></i>${errors.join(', ')}</small>
                        </div>
                      ` : ''}
                    </div>
                  </div>
                </div>
              `;
            }

            Swal.fire({
              title: 'Berhasil!',
              html: `<p>Tagihan periode <strong>${periodeText}</strong> berhasil digenerate.</p>
                <div class="d-flex justify-content-center gap-4 mt-2">
                  <div class="text-center"><div class="fs-4 fw-bold text-success">${inserted}</div><small class="text-muted">Dibuat</small></div>
                  <div class="text-center"><div class="fs-4 fw-bold text-warning">${skipped}</div><small class="text-muted">Dilewati</small></div>
                </div>`,
              icon: 'success',
              confirmButtonText: 'OK',
              buttonsStyling: false,
              customClass: { confirmButton: "btn btn-success" }
            });

            // Update stat cards
            const statGen = document.getElementById('stat-generated');
            const statUnpaid = document.getElementById('stat-unpaid');
            if (statGen) statGen.textContent = parseInt(statGen.textContent) + inserted;
            if (statUnpaid) statUnpaid.textContent = parseInt(statUnpaid.textContent) + inserted;
          } else {
            if (msgBox) {
              msgBox.innerHTML = `
                <div class="result-card shadow-sm">
                  <div class="alert alert-danger d-flex align-items-center gap-2 mb-0 rounded-3">
                    <i class="mdi mdi-alert-circle mdi-24px"></i>
                    <div>${data.message || 'Gagal membuat tagihan bulanan.'}</div>
                  </div>
                </div>
              `;
            }
            Swal.fire({
              title: 'Gagal!',
              text: data.message || 'Gagal membuat tagihan bulanan.',
              icon: 'error',
              confirmButtonText: 'OK',
              buttonsStyling: false,
              customClass: { confirmButton: "btn btn-danger" }
            });
          }
        } catch (err) {
          console.error(err);
          if (msgBox) {
            msgBox.innerHTML = `
              <div class="result-card shadow-sm">
                <div class="alert alert-warning d-flex align-items-center gap-2 mb-0 rounded-3">
                  <i class="mdi mdi-wifi-off mdi-24px"></i>
                  <div>Terjadi kesalahan koneksi ke server.</div>
                </div>
              </div>
            `;
          }
          Swal.fire({
            title: 'Error!',
            text: 'Terjadi kesalahan koneksi ke server.',
            icon: 'error',
            confirmButtonText: 'OK',
            buttonsStyling: false,
            customClass: { confirmButton: "btn btn-danger" }
          });
        } finally {
          btn.disabled = false;
          btn.innerHTML = '<i class="mdi mdi-flash me-2"></i>Generate Tagihan';
        }
      }
    });
  });

  // Auto-select current month
  document.addEventListener('DOMContentLoaded', function() {
    const bulanSelect = document.getElementById('filter-bulan');
    const currentMonth = new Date().getMonth() + 1;
    if (bulanSelect) bulanSelect.value = currentMonth;
  });
</script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Stat Cards --}}
  @php
    $bulanNama = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $periodeLabel = $bulanNama[date('n')] . ' ' . date('Y');
  @endphp
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
      <div class="card stat-card shadow-sm">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="stat-icon bg-label-primary">
            <i class="mdi mdi-account-group"></i>
          </div>
          <div>
            <div class="stat-value">{{ $stats['pelangganAktif'] }}</div>
            <div class="stat-label">Pelanggan Aktif</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card stat-card shadow-sm">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="stat-icon bg-label-info">
            <i class="mdi mdi-file-document-check"></i>
          </div>
          <div>
            <div class="stat-value" id="stat-generated">{{ $stats['sudahGenerate'] }}</div>
            <div class="stat-label">Tagihan {{ $periodeLabel }}</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card stat-card shadow-sm">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="stat-icon bg-label-warning">
            <i class="mdi mdi-clock-alert"></i>
          </div>
          <div>
            <div class="stat-value" id="stat-unpaid">{{ $stats['belumBayar'] }}</div>
            <div class="stat-label">Belum Bayar</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card stat-card shadow-sm">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="stat-icon bg-label-success">
            <i class="mdi mdi-check-decagram"></i>
          </div>
          <div>
            <div class="stat-value">{{ $stats['sudahBayar'] }}</div>
            <div class="stat-label">Sudah Bayar</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Main Content --}}
  <div class="row g-4">
    {{-- Generate Form --}}
    <div class="col-lg-8">
      <div class="card generate-card shadow-sm">
        <div class="card-header">
          <h5><i class="mdi mdi-flash me-2"></i>Generate Tagihan Bulanan</h5>
          <p>Buat tagihan untuk semua pelanggan aktif pada periode tertentu</p>
        </div>
        <div class="card-body p-4">
          <div class="period-selector mb-4">
            <div class="form-group">
              <label for="filter-tahun" class="form-label">
                <i class="mdi mdi-calendar me-1"></i>Tahun
              </label>
              <select id="filter-tahun" class="form-select">
                @php
                  $currentYear = (int) date('Y');
                  for ($year = $currentYear + 1; $year >= $currentYear; $year--) {
                    echo "<option value=\"{$year}\"" . ($year == $currentYear ? ' selected' : '') . ">{$year}</option>";
                  }
                @endphp
              </select>
            </div>
            <div class="form-group">
              <label for="filter-bulan" class="form-label">
                <i class="mdi mdi-calendar-month me-1"></i>Bulan
              </label>
              <select id="filter-bulan" class="form-select">
                <option value="1">Januari</option>
                <option value="2">Februari</option>
                <option value="3">Maret</option>
                <option value="4">April</option>
                <option value="5">Mei</option>
                <option value="6">Juni</option>
                <option value="7">Juli</option>
                <option value="8">Agustus</option>
                <option value="9">September</option>
                <option value="10">Oktober</option>
                <option value="11">November</option>
                <option value="12">Desember</option>
              </select>
            </div>
          </div>

          <div class="d-grid">
            <button id="btn-generate" class="btn btn-primary btn-lg">
              <i class="mdi mdi-flash me-2"></i>Generate Tagihan
            </button>
          </div>

          <div id="status-msg" class="mt-4"></div>
        </div>
      </div>
    </div>

    {{-- Info Panel --}}
    <div class="col-lg-4">
      <div class="card info-panel shadow-sm h-100">
        <div class="card-body p-4">
          <h6 class="fw-bold text-primary mb-3">
            <i class="mdi mdi-information me-1"></i>Panduan
          </h6>

          <div class="info-item">
            <div class="info-icon bg-label-success">
              <i class="mdi mdi-account-check"></i>
            </div>
            <div class="info-text">
              Tagihan akan dibuat untuk semua <strong>pelanggan aktif</strong> yang sudah di-mapping paketnya.
            </div>
          </div>

          <div class="info-item">
            <div class="info-icon bg-label-warning">
              <i class="mdi mdi-skip-next"></i>
            </div>
            <div class="info-text">
              Pelanggan yang <strong>sudah punya tagihan</strong> di periode yang sama akan otomatis dilewati.
            </div>
          </div>

          <div class="info-item">
            <div class="info-icon bg-label-info">
              <i class="mdi mdi-calculator"></i>
            </div>
            <div class="info-text">
              <strong>PPN dan diskon</strong> dihitung otomatis berdasarkan pengaturan paket.
            </div>
          </div>

          <div class="info-item">
            <div class="info-icon bg-label-primary">
              <i class="mdi mdi-calendar-clock"></i>
            </div>
            <div class="info-text">
              Jatuh tempo dihitung <strong>30 hari</strong> dari tanggal generate.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
