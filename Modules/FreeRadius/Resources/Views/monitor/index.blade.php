@extends('layouts/layoutMaster')

@section('title', 'User Online')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')
<script>
  $(document).ready(function() {
    // Search
    $('#searchOnline').on('keyup', function() {
      var value = $(this).val().toLowerCase();
      $('.user-card-item').each(function() {
        var username = $(this).data('username');
        var ip = $(this).data('ip');
        if (username.indexOf(value) > -1 || ip.indexOf(value) > -1) {
          $(this).show();
        } else {
          $(this).hide();
        }
      });
    });

    // Disconnect
    $(document).on('click', '.btn-disconnect', function() {
      var username = $(this).data('username');
      var card = $(this).closest('.user-card-item');

      Swal.fire({
        title: 'Putuskan Koneksi?',
        text: "User " + username + " akan diputus paksa dari router.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Kick!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: '{{ route("radius.monitor.disconnect", ":username") }}'.replace(':username', encodeURIComponent(username)),
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
              if (response.status) {
                Swal.fire('Berhasil!', response.message, 'success');
                card.fadeOut(300, function() { $(this).remove(); });
              } else {
                Swal.fire('Gagal', response.message, 'error');
              }
            },
            error: function() {
              Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
            }
          });
        }
      });
    });

    // Auto refresh every 30 seconds
    setInterval(function() {
      location.reload();
    }, 30000);
  });
</script>
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">FreeRADIUS /</span> Live Monitoring: Online Users
</h4>

<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
    <h5 class="mb-0"><i class="mdi mdi-wifi me-1"></i> User Sedang Online</h5>
    <div class="d-flex gap-2">
      <div class="input-group" style="max-width: 250px;">
        <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
        <input type="text" id="searchOnline" class="form-control" placeholder="Cari user...">
      </div>
      <button type="button" class="btn btn-outline-primary" onclick="location.reload()" title="Refresh">
        <i class="mdi mdi-refresh"></i>
      </button>
    </div>
  </div>
  <div class="card-body">
    <div id="onlineUsersContainer" class="row">
      @forelse($sessions as $session)
      <div class="col-md-6 col-lg-4 mb-3 user-card-item"
           data-username="{{ strtolower($session->username) }}"
           data-ip="{{ $session->framedipaddress }}">
        <div class="card border-start border-start-3 border-start-primary h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <h6 class="fw-bold mb-0">
                <i class="mdi mdi-account-circle text-primary me-1"></i>
                {{ $session->username }}
              </h6>
              <button class="btn btn-sm btn-outline-danger btn-disconnect" data-username="{{ $session->username }}" title="Putuskan Koneksi">
                <i class="mdi mdi-flash"></i>
              </button>
            </div>
            <small class="text-muted d-block mb-2">
              <i class="mdi mdi-clock-outline me-1"></i>{{ $session->acctstarttime }}
            </small>

            <div class="row mb-2">
              <div class="col-6">
                <small class="text-muted d-block">IP Address</small>
                <span class="badge bg-label-info">{{ $session->framedipaddress }}</span>
              </div>
              <div class="col-6">
                <small class="text-muted d-block">Durasi</small>
                <span class="fw-bold">{{ gmdate('H:i:s', $session->acctsessiontime ?? 0) }}</span>
              </div>
            </div>

            <div class="row mb-2">
              <div class="col-6">
                <small class="text-muted d-block"><i class="mdi mdi-upload text-success me-1"></i>Upload</small>
                <span class="text-success fw-bold">
                  @php
                    $upload = $session->acctinputoctets ?? 0;
                    echo $upload > 0 ? round($upload / 1024 / 1024, 2) . ' MB' : '0 B';
                  @endphp
                </span>
              </div>
              <div class="col-6">
                <small class="text-muted d-block"><i class="mdi mdi-download text-primary me-1"></i>Download</small>
                <span class="text-primary fw-bold">
                  @php
                    $download = $session->acctoutputoctets ?? 0;
                    echo $download > 0 ? round($download / 1024 / 1024, 2) . ' MB' : '0 B';
                  @endphp
                </span>
              </div>
            </div>

            @if($session->callingstationid)
            <div class="border-top pt-2 mt-2">
              <small class="text-muted d-block">MAC Address</small>
              <code>{{ $session->callingstationid }}</code>
            </div>
            @endif

            <div class="border-top pt-2 mt-2">
              <small class="text-muted d-block">NAS IP</small>
              <span><i class="mdi mdi-router me-1"></i>{{ $session->nasipaddress }}</span>
            </div>
          </div>
        </div>
      </div>
      @empty
      <div class="col-12 text-center py-5">
        <i class="mdi mdi-wifi-off mdi-48px text-muted mb-3"></i>
        <h5 class="text-muted">Tidak ada user online saat ini</h5>
      </div>
      @endforelse
    </div>
  </div>
</div>

<div class="alert alert-info d-flex align-items-center" role="alert">
    <i class="mdi mdi-information-outline me-2 mdi-24px"></i>
    <div>
        Halaman ini otomatis <b>merefresh</b> setiap 30 detik. Gunakan tombol <b>Disconnect</b> untuk memutuskan koneksi user secara paksa melalui Packet of Disconnect (PoD).
    </div>
</div>
@endsection
