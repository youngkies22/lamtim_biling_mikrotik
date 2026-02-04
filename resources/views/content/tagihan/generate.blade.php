@extends('layouts/layoutMaster')

@section('title', 'Generate Tagihan')

@section('vendor-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
@endsection

@section('page-style')
<style>
  /* Styling untuk tombol Generate Tagihan */
  #btn-generate {
    transition: all 0.3s ease;
    font-weight: 500;
    padding: 0.75rem 2rem;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    font-size: 1rem;
  }

  #btn-generate:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
  }

  #btn-generate:active:not(:disabled) {
    transform: translateY(0);
  }

  #btn-generate:disabled {
    opacity: 0.7;
    cursor: not-allowed;
  }

  #btn-generate i {
    font-size: 1.25rem;
  }

  .form-select, .form-select-sm {
    border-radius: 0.5rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
  }

  .form-select:focus, .form-select-sm:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
  }

  .card {
    border-radius: 0.75rem;
  }

  .info-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }

  .info-card .card-body {
    padding: 1.5rem;
  }
</style>
@endsection

@section('page-script')
<script>
  document.getElementById('btn-generate').addEventListener('click', async function() {
    const btn = this;
    const bulan = document.getElementById('filter-bulan').value;
    const tahun = document.getElementById('filter-tahun').value;
    const msgBox = document.getElementById('status-msg');

    // Validasi
    if (!bulan || !tahun) {
      Swal.fire({
        title: 'Validasi Error',
        text: 'Pilih tahun dan bulan terlebih dahulu!',
        icon: 'warning',
        confirmButtonText: 'OK',
        buttonsStyling: false,
        customClass: {
          confirmButton: "btn btn-warning"
        }
      });
      return;
    }

    // Konfirmasi
    Swal.fire({
      title: 'Generate Tagihan?',
      html: `
        <div class="text-start">
          <p><strong>Periode:</strong> ${document.getElementById('filter-bulan').options[document.getElementById('filter-bulan').selectedIndex].text} ${tahun}</p>
          <p class="text-muted small">Tagihan akan dibuat untuk semua pelanggan aktif pada periode tersebut.</p>
        </div>
      `,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Generate',
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

          if (msgBox) {
            if (data.success) {
              const { inserted = 0, skipped = 0, errors = [] } = data.data || {};

              let html = `
                <div class="alert alert-success border-0 shadow-sm py-3 px-4 mb-0">
                  <div class="d-flex align-items-center mb-2">
                    <i class="mdi mdi-check-circle me-2 fs-4"></i>
                    <div class="fw-semibold fs-5">${data.message || '✅ Tagihan berhasil digenerate.'}</div>
                  </div>
                  <ul class="mb-0 mt-2">
                    <li class="mb-1">Tagihan baru dibuat: <strong class="text-success">${inserted}</strong></li>
                    <li class="mb-1">Dilewati (sudah ada): <strong>${skipped}</strong></li>
                    ${errors.length > 0
                      ? `<li class="text-danger">Error: ${errors.join(', ')}</li>`
                      : ''
                    }
                  </ul>
                </div>
              `;
              msgBox.innerHTML = html;

              // Show success alert
              Swal.fire({
                title: 'Berhasil!',
                html: `
                  <div class="text-start">
                    <p>Tagihan berhasil digenerate untuk periode <strong>${document.getElementById('filter-bulan').options[document.getElementById('filter-bulan').selectedIndex].text} ${tahun}</strong></p>
                    <ul class="text-start mt-2">
                      <li>Tagihan baru: <strong>${inserted}</strong></li>
                      <li>Dilewati: <strong>${skipped}</strong></li>
                    </ul>
                  </div>
                `,
                icon: 'success',
                confirmButtonText: 'OK',
                buttonsStyling: false,
                customClass: {
                  confirmButton: "btn btn-success"
                }
              });
            } else {
              msgBox.innerHTML = `
                <div class="alert alert-danger border-0 shadow-sm py-3 px-4 mb-0">
                  <div class="d-flex align-items-center">
                    <i class="mdi mdi-alert-circle me-2 fs-4"></i>
                    <div class="fw-semibold">❌ ${data.message || 'Gagal membuat tagihan bulanan.'}</div>
                  </div>
                </div>
              `;

              Swal.fire({
                title: 'Gagal!',
                text: data.message || 'Gagal membuat tagihan bulanan.',
                icon: 'error',
                confirmButtonText: 'OK',
                buttonsStyling: false,
                customClass: {
                  confirmButton: "btn btn-danger"
                }
              });
            }
          }
        } catch (err) {
          console.error(err);
          if (msgBox)
            msgBox.innerHTML = `
              <div class="alert alert-warning border-0 shadow-sm py-3 px-4 mb-0">
                <div class="d-flex align-items-center">
                  <i class="mdi mdi-alert me-2 fs-4"></i>
                  <div class="fw-semibold">⚠️ Terjadi kesalahan koneksi ke server.</div>
                </div>
              </div>
            `;

          Swal.fire({
            title: 'Error!',
            text: 'Terjadi kesalahan koneksi ke server.',
            icon: 'error',
            confirmButtonText: 'OK',
            buttonsStyling: false,
            customClass: {
              confirmButton: "btn btn-danger"
            }
          });
        } finally {
          btn.disabled = false;
          btn.innerHTML = '<i class="mdi mdi-cash-multiple me-2"></i><span>Generate Tagihan</span>';
        }
      }
    });
  });
</script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Header -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <h4 class="card-title mb-1">
                <i class="mdi mdi-cash-multiple me-2"></i>Generate Tagihan
              </h4>
              <p class="text-muted mb-0">Buat tagihan bulanan untuk semua pelanggan aktif</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="row">
    <!-- Form Generate -->
    <div class="col-lg-8 col-md-12 mb-4">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom">
          <h5 class="card-title mb-0">
            <i class="mdi mdi-calendar-month me-2"></i>Pilih Periode
          </h5>
        </div>
        <div class="card-body p-4">
          <!-- Filter Tahun -->
          <div class="mb-4">
            <label for="filter-tahun" class="form-label fw-semibold mb-2">
              <i class="mdi mdi-calendar-year me-1"></i>Tahun
            </label>
            <select id="filter-tahun" class="form-select form-select-lg">
              <option value="">-- Pilih Tahun --</option>
              @php
                $currentYear = date('Y');
                for ($year = $currentYear; $year >= $currentYear - 5; $year--) {
                  echo "<option value=\"{$year}\"" . ($year == $currentYear ? ' selected' : '') . ">{$year}</option>";
                }
              @endphp
            </select>
          </div>

          <!-- Filter Bulan -->
          <div class="mb-4">
            <label for="filter-bulan" class="form-label fw-semibold mb-2">
              <i class="mdi mdi-calendar-month me-1"></i>Bulan
            </label>
            <select id="filter-bulan" class="form-select form-select-lg">
              <option value="">-- Pilih Bulan --</option>
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

          <!-- Tombol Generate -->
          <div class="d-grid">
            <button id="btn-generate" class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2">
              <i class="mdi mdi-cash-multiple"></i>
              <span>Generate Tagihan</span>
            </button>
          </div>

          <!-- Status Message -->
          <div id="status-msg" class="mt-4"></div>
        </div>
      </div>
    </div>

    <!-- Info Card -->
    <div class="col-lg-4 col-md-12 mb-4">
      <div class="card info-card border-0 shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title text-white mb-3">
            <i class="mdi mdi-information-outline me-2"></i>Informasi
          </h5>
          <ul class="list-unstyled mb-0">
            <li class="mb-3">
              <i class="mdi mdi-check-circle me-2"></i>
              <span>Tagihan akan dibuat untuk semua pelanggan aktif</span>
            </li>
            <li class="mb-3">
              <i class="mdi mdi-check-circle me-2"></i>
              <span>Tagihan yang sudah ada akan dilewati</span>
            </li>
            <li class="mb-3">
              <i class="mdi mdi-check-circle me-2"></i>
              <span>PPN dan diskon akan dihitung otomatis</span>
            </li>
            <li class="mb-0">
              <i class="mdi mdi-check-circle me-2"></i>
              <span>Jatuh tempo: 30 hari dari tanggal generate</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
