@extends('layouts/layoutMaster')

@section('title', 'Sinkron Pelanggan')

@section("vendor-style")
<style>
  .sync-stats .badge {
    font-size: 0.9rem;
    padding: 8px 16px;
  }
  .sync-table th {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .progress-card {
    display: none;
  }
  .progress-card.show {
    display: block;
  }
  .sync-progress .progress {
    height: 24px;
    border-radius: 12px;
  }
  .sync-progress .progress-bar {
    transition: width 0.3s ease;
    font-size: 0.8rem;
    font-weight: 600;
  }
  .status-new {
    background-color: #e3f2fd;
    color: #1565c0;
  }
  .status-existing {
    background-color: #e8f5e9;
    color: #2e7d32;
  }
  .filter-pills .btn {
    border-radius: 20px;
    padding: 4px 16px;
    font-size: 0.8rem;
  }
  .filter-pills .btn.active {
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  }
  .check-all-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
  }
</style>
@endsection

@section("vendor-script")
@endsection

@section('page-script')
<script>
let currentMikrotikId = null;
let secretsData = [];
let currentFilter = 'all';

$(document).ready(function() {
  $('#selectMikrotik').on('change', function() {
    currentMikrotikId = $(this).val();
    if (!currentMikrotikId) {
      resetUI();
      return;
    }
    $('#btn-fetch').removeClass('d-none');
    resetUI();
  });

  $('#btn-fetch').on('click', function() {
    if (!currentMikrotikId) return;
    fetchSecrets(currentMikrotikId);
  });

  $('#btn-sync').on('click', function() {
    executeSync();
  });

  // Filter pills
  $(document).on('click', '.filter-pill', function() {
    $('.filter-pill').removeClass('active');
    $(this).addClass('active');
    currentFilter = $(this).data('filter');
    renderTable(secretsData);
  });

  // Check all
  $(document).on('change', '#checkAll', function() {
    const checked = $(this).is(':checked');
    $('.sync-check:visible').prop('checked', checked);
    updateSyncButton();
  });

  $(document).on('change', '.sync-check', function() {
    updateSyncButton();
  });
});

function resetUI() {
  secretsData = [];
  $('#sync-container').html(`
    <div class="alert alert-info mb-0">
      <i class="mdi mdi-information-outline me-1"></i>
      Pilih server Mikrotik lalu klik <strong>Fetch</strong> untuk mengambil data PPPoE secrets.
    </div>
  `);
  $('#stats-row').addClass('d-none');
  $('#btn-sync').addClass('d-none');
  $('.progress-card').removeClass('show');
}

function fetchSecrets(idMikrotik) {
  $('#sync-container').html(`
    <div class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2 text-muted">Mengambil data PPPoE secrets dari Mikrotik...</p>
    </div>
  `);
  $('#stats-row').addClass('d-none');
  $('#btn-sync').addClass('d-none');
  $('#btn-fetch').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Fetching...');

  $.ajax({
    url: `/mikrotik/sync-customers/fetch/${idMikrotik}`,
    method: 'GET',
    success: function(response) {
      $('#btn-fetch').prop('disabled', false).html('<i class="mdi mdi-cloud-download me-1"></i>Fetch');
      if (!response.error) {
        secretsData = response.data;
        showStats(secretsData);
        renderTable(secretsData);
        $('#btn-sync').removeClass('d-none');
      } else {
        $('#sync-container').html(`<div class="alert alert-danger"><i class="mdi mdi-alert-circle me-1"></i>${response.message}</div>`);
      }
    },
    error: function(xhr) {
      $('#btn-fetch').prop('disabled', false).html('<i class="mdi mdi-cloud-download me-1"></i>Fetch');
      const msg = xhr.responseJSON?.message || 'Gagal mengambil data dari Mikrotik.';
      $('#sync-container').html(`<div class="alert alert-danger"><i class="mdi mdi-alert-circle me-1"></i>${msg}</div>`);
    }
  });
}

function showStats(data) {
  const total = data.length;
  const newCount = data.filter(d => d.status === 'new').length;
  const existingCount = data.filter(d => d.status === 'existing').length;
  const disabledCount = data.filter(d => d.disabled).length;

  $('#stat-total').text(total);
  $('#stat-new').text(newCount);
  $('#stat-existing').text(existingCount);
  $('#stat-disabled').text(disabledCount);
  $('#stats-row').removeClass('d-none');
}

function renderTable(data) {
  let filtered = data;
  if (currentFilter === 'new') filtered = data.filter(d => d.status === 'new');
  else if (currentFilter === 'existing') filtered = data.filter(d => d.status === 'existing');

  if (!filtered.length) {
    $('#sync-container').html(`
      <div class="alert alert-warning mb-0">
        <i class="mdi mdi-alert me-1"></i>Tidak ada data untuk ditampilkan.
      </div>
    `);
    return;
  }

  let rows = '';
  filtered.forEach(function(s, i) {
    const statusBadge = s.status === 'new'
      ? '<span class="badge status-new">Baru</span>'
      : '<span class="badge status-existing">Sudah Ada</span>';
    const disabledBadge = s.disabled
      ? '<span class="badge bg-danger">Disabled</span>'
      : '<span class="badge bg-success">Active</span>';
    const dbInfo = s.status === 'existing'
      ? `<small class="text-muted d-block">${s.db_user_name || '-'}</small>`
      : '';

    rows += `<tr>
      <td><input type="checkbox" class="form-check-input sync-check" data-index="${data.indexOf(s)}" checked></td>
      <td>${i + 1}</td>
      <td><strong>${s.name}</strong>${dbInfo}</td>
      <td>${s.profile || '-'}</td>
      <td><code>${s.remote_address || '-'}</code></td>
      <td>${statusBadge}</td>
      <td>${disabledBadge}</td>
      <td><small class="text-muted">${s.comment || '-'}</small></td>
    </tr>`;
  });

  const html = `
  <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <div class="filter-pills d-flex gap-1">
      <button class="btn btn-sm ${currentFilter === 'all' ? 'btn-primary active' : 'btn-outline-primary'} filter-pill" data-filter="all">Semua</button>
      <button class="btn btn-sm ${currentFilter === 'new' ? 'btn-info active' : 'btn-outline-info'} filter-pill" data-filter="new">Baru</button>
      <button class="btn btn-sm ${currentFilter === 'existing' ? 'btn-success active' : 'btn-outline-success'} filter-pill" data-filter="existing">Sudah Ada</button>
    </div>
    <div class="ms-auto check-all-wrap">
      <input type="checkbox" class="form-check-input" id="checkAll" checked>
      <label class="form-check-label small" for="checkAll">Pilih Semua</label>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-bordered table-hover sync-table" id="syncTable">
      <thead class="table-light">
        <tr>
          <th width="40"></th>
          <th width="50">NO</th>
          <th>SECRET NAME</th>
          <th>PROFILE</th>
          <th>IP ADDRESS</th>
          <th width="100">STATUS</th>
          <th width="90">KONDISI</th>
          <th>KOMENTAR</th>
        </tr>
      </thead>
      <tbody>${rows}</tbody>
    </table>
  </div>`;

  $('#sync-container').html(html);
  updateSyncButton();
}

function updateSyncButton() {
  const checked = $('.sync-check:checked').length;
  if (checked > 0) {
    $('#btn-sync').prop('disabled', false).html(`<i class="mdi mdi-sync me-1"></i>Sinkron (${checked})`);
  } else {
    $('#btn-sync').prop('disabled', true).html('<i class="mdi mdi-sync me-1"></i>Sinkron (0)');
  }
}

function executeSync() {
  const selectedIndices = [];
  $('.sync-check:checked').each(function() {
    selectedIndices.push(parseInt($(this).data('index')));
  });

  if (!selectedIndices.length) {
    Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Pilih minimal satu secret untuk disinkron.', buttonsStyling: false, customClass: { confirmButton: 'btn btn-primary' } });
    return;
  }

  const selectedSecrets = selectedIndices.map(i => secretsData[i]);
  const newCount = selectedSecrets.filter(s => s.status === 'new').length;
  const existCount = selectedSecrets.filter(s => s.status === 'existing').length;

  Swal.fire({
    title: 'Konfirmasi Sinkronisasi',
    html: `
      <div class="text-start">
        <p>Anda akan menyinkronkan <strong>${selectedSecrets.length}</strong> secret:</p>
        <ul class="mb-0">
          ${newCount > 0 ? `<li><span class="badge status-new">Baru</span> ${newCount} pelanggan akan ditambahkan</li>` : ''}
          ${existCount > 0 ? `<li><span class="badge status-existing">Sudah Ada</span> ${existCount} pelanggan akan diupdate</li>` : ''}
        </ul>
      </div>
    `,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'YA, SINKRON!',
    cancelButtonText: 'BATAL',
    buttonsStyling: false,
    customClass: {
      confirmButton: 'btn btn-primary me-2',
      cancelButton: 'btn btn-outline-secondary'
    }
  }).then((result) => {
    if (result.isConfirmed) {
      doSync(selectedSecrets);
    }
  });
}

function doSync(secrets) {
  // Show progress
  const $progress = $('.progress-card');
  $progress.addClass('show');
  $('#btn-sync').prop('disabled', true);
  $('#btn-fetch').prop('disabled', true);
  $('#selectMikrotik').prop('disabled', true);

  const total = secrets.length;
  const batchSize = 10;
  let processed = 0;
  let totalCreated = 0;
  let totalUpdated = 0;
  let totalFailed = 0;
  let allErrors = [];

  // Split into batches
  const batches = [];
  for (let i = 0; i < secrets.length; i += batchSize) {
    batches.push(secrets.slice(i, i + batchSize));
  }

  function updateProgress(count) {
    processed += count;
    const pct = Math.round((processed / total) * 100);
    $('#syncProgressBar').css('width', pct + '%').text(pct + '%');
    $('#syncProgressText').text(`Diproses: ${processed} / ${total}`);
  }

  function processBatch(index) {
    if (index >= batches.length) {
      // All done
      finishSync(totalCreated, totalUpdated, totalFailed, allErrors);
      return;
    }

    $.ajax({
      url: "{{ route('mikrotik.sync-customers.execute') }}",
      method: 'POST',
      data: JSON.stringify({
        idMikrotik: currentMikrotikId,
        secrets: batches[index]
      }),
      contentType: 'application/json',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      success: function(response) {
        totalCreated += response.created || 0;
        totalUpdated += response.updated || 0;
        totalFailed += response.failed || 0;
        if (response.errors && response.errors.length) {
          allErrors = allErrors.concat(response.errors);
        }
        updateProgress(batches[index].length);
        processBatch(index + 1);
      },
      error: function(xhr) {
        totalFailed += batches[index].length;
        allErrors.push('Batch error: ' + (xhr.responseJSON?.message || 'Unknown error'));
        updateProgress(batches[index].length);
        processBatch(index + 1);
      }
    });
  }

  // Start processing
  updateProgress(0);
  processBatch(0);
}

function finishSync(created, updated, failed, errors) {
  $('#btn-fetch').prop('disabled', false);
  $('#selectMikrotik').prop('disabled', false);

  let errorHtml = '';
  if (errors.length > 0) {
    errorHtml = `<div class="text-start mt-3" style="max-height:150px;overflow-y:auto"><small class="text-danger">`;
    errors.forEach(e => { errorHtml += `${e}<br>`; });
    errorHtml += `</small></div>`;
  }

  Swal.fire({
    title: 'Sinkronisasi Selesai!',
    html: `
      <div class="row g-2 mb-2">
        <div class="col-4">
          <div class="p-2 rounded bg-label-success text-center">
            <div class="fs-4 fw-bold">${created}</div>
            <small>Ditambahkan</small>
          </div>
        </div>
        <div class="col-4">
          <div class="p-2 rounded bg-label-info text-center">
            <div class="fs-4 fw-bold">${updated}</div>
            <small>Diupdate</small>
          </div>
        </div>
        <div class="col-4">
          <div class="p-2 rounded bg-label-danger text-center">
            <div class="fs-4 fw-bold">${failed}</div>
            <small>Gagal</small>
          </div>
        </div>
      </div>
      ${errorHtml}
    `,
    icon: failed > 0 ? 'warning' : 'success',
    buttonsStyling: false,
    customClass: { confirmButton: 'btn btn-primary' }
  });

  // Re-fetch to update status
  if (currentMikrotikId) {
    fetchSecrets(currentMikrotikId);
  }
}
</script>
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    {{-- Header --}}
    <div class="card mb-4">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="card-title mb-0">
          <i class="mdi mdi-sync me-2"></i>Sinkron Pelanggan
        </h5>
        <div class="d-flex align-items-center gap-2">
          <div style="min-width: 250px;">
            <select id="selectMikrotik" class="form-select">
              <option value="">-- Pilih Server Mikrotik --</option>
              @foreach(\App\Helpers\Helpers::getMikrotik() as $mk)
                <option value="{{ $mk->id }}">{{ $mk->nama }} ({{ $mk->ip }})</option>
              @endforeach
            </select>
          </div>
          <button class="btn btn-primary d-none" id="btn-fetch">
            <i class="mdi mdi-cloud-download me-1"></i>Fetch
          </button>
          <button class="btn btn-success d-none" id="btn-sync" disabled>
            <i class="mdi mdi-sync me-1"></i>Sinkron (0)
          </button>
        </div>
      </div>
    </div>

    {{-- Stats --}}
    <div class="row mb-4 d-none" id="stats-row">
      <div class="col-6 col-md-3 mb-2">
        <div class="card shadow-none border">
          <div class="card-body py-3 text-center">
            <div class="d-flex align-items-center justify-content-center gap-2">
              <span class="rounded-circle bg-label-primary p-2"><i class="mdi mdi-account-group mdi-24px"></i></span>
              <div>
                <h4 class="mb-0" id="stat-total">0</h4>
                <small class="text-muted">Total Secrets</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-2">
        <div class="card shadow-none border">
          <div class="card-body py-3 text-center">
            <div class="d-flex align-items-center justify-content-center gap-2">
              <span class="rounded-circle bg-label-info p-2"><i class="mdi mdi-account-plus mdi-24px"></i></span>
              <div>
                <h4 class="mb-0" id="stat-new">0</h4>
                <small class="text-muted">Baru</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-2">
        <div class="card shadow-none border">
          <div class="card-body py-3 text-center">
            <div class="d-flex align-items-center justify-content-center gap-2">
              <span class="rounded-circle bg-label-success p-2"><i class="mdi mdi-account-check mdi-24px"></i></span>
              <div>
                <h4 class="mb-0" id="stat-existing">0</h4>
                <small class="text-muted">Sudah Ada</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-2">
        <div class="card shadow-none border">
          <div class="card-body py-3 text-center">
            <div class="d-flex align-items-center justify-content-center gap-2">
              <span class="rounded-circle bg-label-danger p-2"><i class="mdi mdi-account-off mdi-24px"></i></span>
              <div>
                <h4 class="mb-0" id="stat-disabled">0</h4>
                <small class="text-muted">Disabled</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Progress --}}
    <div class="card mb-4 progress-card">
      <div class="card-body sync-progress">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="fw-semibold"><i class="mdi mdi-loading mdi-spin me-1"></i>Sedang Menyinkronkan...</span>
          <span class="text-muted" id="syncProgressText">Diproses: 0 / 0</span>
        </div>
        <div class="progress">
          <div class="progress-bar bg-primary" id="syncProgressBar" role="progressbar" style="width: 0%">0%</div>
        </div>
      </div>
    </div>

    {{-- Table --}}
    <div class="card">
      <div class="card-body" id="sync-container">
        <div class="alert alert-info mb-0">
          <i class="mdi mdi-information-outline me-1"></i>
          Pilih server Mikrotik lalu klik <strong>Fetch</strong> untuk mengambil data PPPoE secrets.
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
