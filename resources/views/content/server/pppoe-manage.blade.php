@extends('layouts/layoutMaster')

@section('title', 'Kelola PPPoE')

@section("vendor-style")
<style>
  .status-enabled {
    color: #28a745;
  }
  .status-disabled {
    color: #dc3545;
  }
</style>
@endsection

@section("vendor-script")
@endsection

@section('page-script')
<script>
let currentMikrotikId = null;

$(document).ready(function() {
  $('#selectMikrotik').on('change', function() {
    currentMikrotikId = $(this).val();
    if (!currentMikrotikId) {
      $('#pppoe-container').html(`
        <div class="alert alert-info mb-0">
          <i class="mdi mdi-information-outline me-1"></i>
          Pilih server Mikrotik terlebih dahulu untuk menampilkan data PPPoE.
        </div>
      `);
      $('#btn-refresh').addClass('d-none');
      return;
    }
    $('#btn-refresh').removeClass('d-none');
    loadPppoeUsers(currentMikrotikId);
  });
});

function loadPppoeUsers(idMikrotik) {
  $('#pppoe-container').html(`
    <div class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2 text-muted">Memuat data PPPoE dari Mikrotik...</p>
    </div>
  `);

  $.ajax({
    url: `/mikrotik/pppoe-manage/users/${idMikrotik}`,
    method: 'GET',
    success: function(response) {
      if (!response.error) {
        renderPppoeTable(response.data, idMikrotik);
      } else {
        $('#pppoe-container').html(`<div class="alert alert-danger"><i class="mdi mdi-alert-circle me-1"></i>${response.message}</div>`);
      }
    },
    error: function(xhr) {
      const msg = xhr.responseJSON?.message || 'Gagal memuat data PPPoE.';
      $('#pppoe-container').html(`<div class="alert alert-danger"><i class="mdi mdi-alert-circle me-1"></i>${msg}</div>`);
    }
  });
}

function renderPppoeTable(users, idMikrotik) {
  if (!users || users.length === 0) {
    $('#pppoe-container').html('<div class="alert alert-warning"><i class="mdi mdi-alert me-1"></i>Tidak ada PPPoE user pada server ini.</div>');
    return;
  }

  // Hitung statistik
  const total = users.length;
  const enabled = users.filter(u => u.disabled !== 'true' && u.disabled !== true).length;
  const disabled = total - enabled;

  let rows = '';
  users.forEach(function(user, index) {
    const isDisabled = user.disabled === 'true' || user.disabled === true;
    const statusBadge = isDisabled
      ? '<span class="badge bg-danger">Disabled</span>'
      : '<span class="badge bg-success">Enabled</span>';
    const actionBtn = isDisabled
      ? `<button class="btn btn-sm btn-success btn-enable" data-mikrotik="${idMikrotik}" data-username="${user.name}">
           <i class="mdi mdi-play-circle-outline me-1"></i>Enable
         </button>`
      : `<button class="btn btn-sm btn-danger btn-disable" data-mikrotik="${idMikrotik}" data-username="${user.name}">
           <i class="mdi mdi-stop-circle-outline me-1"></i>Disable
         </button>`;

    rows += `<tr>
      <td>${index + 1}</td>
      <td><strong>${user.name || '-'}</strong></td>
      <td>${user.profile || '-'}</td>
      <td>${statusBadge}</td>
      <td><small class="text-muted">${user.comment || '-'}</small></td>
      <td>${actionBtn}</td>
    </tr>`;
  });

  const html = `
  <div class="row mb-3">
    <div class="col-auto">
      <span class="badge bg-primary fs-6">Total: ${total}</span>
    </div>
    <div class="col-auto">
      <span class="badge bg-success fs-6">Enabled: ${enabled}</span>
    </div>
    <div class="col-auto">
      <span class="badge bg-danger fs-6">Disabled: ${disabled}</span>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-bordered table-hover" id="pppoeTable">
      <thead class="table-light">
        <tr>
          <th width="50">NO</th>
          <th>USERNAME</th>
          <th>PROFILE</th>
          <th>STATUS</th>
          <th>COMMENT</th>
          <th width="120">AKSI</th>
        </tr>
      </thead>
      <tbody>${rows}</tbody>
    </table>
  </div>`;

  $('#pppoe-container').html(html);

  if ($.fn.DataTable.isDataTable('#pppoeTable')) {
    $('#pppoeTable').DataTable().destroy();
  }
  $('#pppoeTable').DataTable({
    paging: true,
    searching: true,
    ordering: true,
    info: true,
    lengthMenu: [10, 25, 50, 100],
    pageLength: 25,
  });
}

// Disable PPPoE
$(document).on('click', '.btn-disable', function() {
  const btn = $(this);
  const username = btn.data('username');
  const idMikrotik = btn.data('mikrotik');

  Swal.fire({
    title: `Disable ${username}?`,
    text: "User akan di-disconnect dan tidak bisa login PPPoE.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "YA, DISABLE!",
    cancelButtonText: "BATAL",
    buttonsStyling: false,
    customClass: {
      confirmButton: "btn btn-danger me-2",
      cancelButton: "btn btn-outline-secondary"
    }
  }).then((result) => {
    if (result.isConfirmed) {
      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
      $.ajax({
        url: "{{ route('mikrotik.pppoe-manage.disable') }}",
        method: 'POST',
        data: {
          idMikrotik: idMikrotik,
          username: username,
          _token: '{{ csrf_token() }}'
        },
        success: function(response) {
          toastr.success(response.message);
          loadPppoeUsers(idMikrotik);
        },
        error: function(xhr) {
          const msg = xhr.responseJSON?.message || 'Gagal disable user.';
          toastr.error(msg);
          btn.prop('disabled', false).html('<i class="mdi mdi-stop-circle-outline me-1"></i>Disable');
        }
      });
    }
  });
});

// Enable PPPoE
$(document).on('click', '.btn-enable', function() {
  const btn = $(this);
  const username = btn.data('username');
  const idMikrotik = btn.data('mikrotik');

  btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
  $.ajax({
    url: "{{ route('mikrotik.pppoe-manage.enable') }}",
    method: 'POST',
    data: {
      idMikrotik: idMikrotik,
      username: username,
      _token: '{{ csrf_token() }}'
    },
    success: function(response) {
      toastr.success(response.message);
      loadPppoeUsers(idMikrotik);
    },
    error: function(xhr) {
      const msg = xhr.responseJSON?.message || 'Gagal enable user.';
      toastr.error(msg);
      btn.prop('disabled', false).html('<i class="mdi mdi-play-circle-outline me-1"></i>Enable');
    }
  });
});

// Refresh
$(document).on('click', '#btn-refresh', function() {
  if (currentMikrotikId) {
    loadPppoeUsers(currentMikrotikId);
  }
});
</script>
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    {{-- Header --}}
    <div class="card mb-4">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="card-title mb-0">
          <i class="mdi mdi-account-network me-2"></i>Kelola PPPoE User
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
          <button class="btn btn-outline-primary d-none" id="btn-refresh">
            <i class="mdi mdi-refresh"></i>
          </button>
        </div>
      </div>
    </div>

    {{-- PPPoE Table --}}
    <div class="card">
      <div class="card-body" id="pppoe-container">
        <div class="alert alert-info mb-0">
          <i class="mdi mdi-information-outline me-1"></i>
          Pilih server Mikrotik terlebih dahulu untuk menampilkan data PPPoE.
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
