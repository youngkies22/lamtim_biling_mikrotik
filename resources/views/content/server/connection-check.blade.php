@extends('layouts/layoutMaster')

@section('title', 'Cek Koneksi Mikrotik')

@section("vendor-style")
<style>
  .server-card {
    transition: all 0.3s ease;
    border-left: 4px solid #d9dee3;
  }
  .server-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  }
  .server-card.border-connected {
    border-left-color: #28a745;
  }
  .server-card.border-disconnected {
    border-left-color: #dc3545;
  }
  .status-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
  }
  .pulse-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
  }
  .pulse-dot.online {
    background: #28a745;
    box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4);
    animation: pulse-green 2s infinite;
  }
  .pulse-dot.offline {
    background: #dc3545;
  }
  @keyframes pulse-green {
    0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4); }
    70% { box-shadow: 0 0 0 8px rgba(40, 167, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
  }
</style>
@endsection

@section("vendor-script")
@endsection

@section('page-script')
<script>
$(document).ready(function() {
  loadServers();
});

function loadServers() {
  $('#btn-test-all').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Mengecek...');
  $('#server-grid').html(`
    <div class="col-12 text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2 text-muted">Mengecek koneksi semua server...</p>
    </div>
  `);

  $.ajax({
    url: "{{ route('mikrotik.connection-check.test-all') }}",
    method: 'GET',
    success: function(response) {
      if (!response.error) {
        renderCards(response.data);
        updateSummary(response.data);
      }
    },
    error: function() {
      $('#server-grid').html(`
        <div class="col-12">
          <div class="alert alert-danger">
            <i class="mdi mdi-alert-circle me-1"></i>
            Gagal memuat data server.
          </div>
        </div>
      `);
    },
    complete: function() {
      $('#btn-test-all').prop('disabled', false).html('<i class="mdi mdi-refresh me-1"></i>Test Semua');
    }
  });
}

function updateSummary(servers) {
  const total = servers.length;
  const online = servers.filter(s => s.connected).length;
  const offline = total - online;

  $('#summary-total').text(total);
  $('#summary-online').text(online);
  $('#summary-offline').text(offline);
}

function renderCards(servers) {
  let html = '';
  servers.forEach(function(s) {
    const connected = s.connected;
    const borderClass = connected ? 'border-connected' : 'border-disconnected';
    const statusIcon = connected ? 'mdi-check-network' : 'mdi-close-network';
    const iconBg = connected ? 'bg-success' : 'bg-danger';
    const statusText = connected ? 'Terhubung' : 'Terputus';
    const badgeClass = connected ? 'bg-success' : 'bg-danger';
    const dotClass = connected ? 'online' : 'offline';
    const identityHtml = s.identity ? `<div class="text-muted small mt-1"><i class="mdi mdi-router-wireless me-1"></i>${s.identity}</div>` : '';
    const messageHtml = !connected ? `<div class="text-danger small mt-2" style="word-break:break-all;"><i class="mdi mdi-information-outline me-1"></i>${s.message}</div>` : '';

    html += `
    <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
      <div class="card server-card h-100 ${borderClass}" data-server-id="${s.id}">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between mb-3">
            <div class="d-flex align-items-center">
              <div class="status-icon ${iconBg} bg-opacity-10 text-${connected ? 'success' : 'danger'} me-3">
                <i class="mdi ${statusIcon} mdi-24px"></i>
              </div>
              <div>
                <h6 class="mb-0">${s.nama}</h6>
                <small class="text-muted">${s.ip}:${s.port}</small>
              </div>
            </div>
            <span class="pulse-dot ${dotClass}"></span>
          </div>
          ${identityHtml}
          <div class="mt-2">
            <span class="badge ${badgeClass}">${statusText}</span>
          </div>
          ${messageHtml}
        </div>
        <div class="card-footer bg-transparent pt-0">
          <button class="btn btn-sm btn-outline-primary w-100 btn-test-single" data-id="${s.id}">
            <i class="mdi mdi-refresh me-1"></i>Test Ulang
          </button>
        </div>
      </div>
    </div>`;
  });

  if (html === '') {
    html = '<div class="col-12"><div class="alert alert-info"><i class="mdi mdi-information-outline me-1"></i>Tidak ada server Mikrotik aktif.</div></div>';
  }
  $('#server-grid').html(html);
}

// Test single server
$(document).on('click', '.btn-test-single', function() {
  const btn = $(this);
  const id = btn.data('id');
  const card = btn.closest('.card');

  btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Testing...');

  $.ajax({
    url: `/mikrotik/connection-check/test/${id}`,
    method: 'GET',
    success: function(response) {
      const s = response.data;
      const connected = s.connected;

      card.removeClass('border-connected border-disconnected')
          .addClass(connected ? 'border-connected' : 'border-disconnected');
      card.find('.badge').removeClass('bg-success bg-danger')
          .addClass(connected ? 'bg-success' : 'bg-danger')
          .text(connected ? 'Terhubung' : 'Terputus');
      card.find('.status-icon')
          .removeClass('bg-success bg-danger text-success text-danger bg-opacity-10')
          .addClass(`bg-${connected ? 'success' : 'danger'} bg-opacity-10 text-${connected ? 'success' : 'danger'}`);
      card.find('.status-icon i').attr('class', `mdi ${connected ? 'mdi-check-network' : 'mdi-close-network'} mdi-24px`);
      card.find('.pulse-dot').removeClass('online offline').addClass(connected ? 'online' : 'offline');

      toastr[connected ? 'success' : 'error'](s.message);
    },
    error: function() {
      toastr.error('Gagal menguji koneksi.');
    },
    complete: function() {
      btn.prop('disabled', false).html('<i class="mdi mdi-refresh me-1"></i>Test Ulang');
    }
  });
});

// Test all
$('#btn-test-all').on('click', function() {
  loadServers();
});
</script>
@endsection

@section('content')
{{-- Summary Cards --}}
<div class="row mb-4">
  <div class="col-md-4 mb-3">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center">
        <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center me-3" style="width:50px;height:50px;">
          <i class="mdi mdi-server-network mdi-24px"></i>
        </div>
        <div>
          <h4 class="mb-0 fw-bold" id="summary-total">-</h4>
          <small class="text-muted">Total Server</small>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-3">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center">
        <div class="bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center me-3" style="width:50px;height:50px;">
          <i class="mdi mdi-check-network mdi-24px"></i>
        </div>
        <div>
          <h4 class="mb-0 fw-bold text-success" id="summary-online">-</h4>
          <small class="text-muted">Terhubung</small>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-3">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center">
        <div class="bg-danger bg-opacity-10 text-danger rounded-3 d-flex align-items-center justify-content-center me-3" style="width:50px;height:50px;">
          <i class="mdi mdi-close-network mdi-24px"></i>
        </div>
        <div>
          <h4 class="mb-0 fw-bold text-danger" id="summary-offline">-</h4>
          <small class="text-muted">Terputus</small>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Header --}}
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="mdi mdi-server-network me-2"></i>Status Koneksi Mikrotik
    </h5>
    <button class="btn btn-primary" id="btn-test-all">
      <i class="mdi mdi-refresh me-1"></i>Test Semua
    </button>
  </div>
</div>

{{-- Server Grid --}}
<div class="row" id="server-grid">
  {{-- Cards rendered by JS --}}
</div>
@endsection
