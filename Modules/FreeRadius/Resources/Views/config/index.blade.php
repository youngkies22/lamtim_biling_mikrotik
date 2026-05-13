@extends('layouts/layoutMaster')

@section('title', 'Status FreeRADIUS')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')
<script>
  $(document).ready(function() {
    $('#btn-check-server').on('click', function() {
      const btn = $(this);
      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Mengecek...');

      $.ajax({
        url: "{{ route('radius.config.check-server') }}",
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
          if (response.exec_available === false) {
            $('#radiusd-status').html('<span class="badge bg-label-warning fs-6 px-3 py-2">Tidak Dapat Dicek</span>');
            $('#radclient-status').html('<span class="badge bg-label-warning fs-6 px-3 py-2">Tidak Dapat Dicek</span>');
            $('#service-status').html('<span class="badge bg-label-warning fs-6 px-3 py-2">Tidak Dapat Dicek</span>');
            $('#radius-version').text('-');
            toastr.warning('Fungsi shell_exec tidak tersedia di server ini.');
            return;
          }
          $('#radiusd-status').html(response.binary_radiusd
            ? '<span class="badge bg-label-success">Terinstall</span>'
            : '<span class="badge bg-label-danger">Tidak Terinstall</span>');
          $('#radclient-status').html(response.binary_radclient
            ? '<span class="badge bg-label-success">Terinstall</span>'
            : '<span class="badge bg-label-danger">Tidak Terinstall</span>');
          $('#service-status').html(response.service_active
            ? '<span class="badge bg-label-success">Berjalan</span>'
            : '<span class="badge bg-label-danger">Tidak Berjalan</span>');
          $('#radius-version').text(response.version || '-');
          toastr.success('Pengecekan server selesai.');
        },
        error: function() { toastr.error('Gagal mengecek server.'); },
        complete: function() { btn.prop('disabled', false).html('<i class="mdi mdi-refresh me-1"></i> Refresh Status'); }
      });
    });
  });
</script>
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">FreeRADIUS /</span> Status Server
</h4>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="mdi mdi-server me-1"></i> Status FreeRADIUS</h5>
        <button type="button" id="btn-check-server" class="btn btn-outline-secondary btn-sm">
          <i class="mdi mdi-refresh me-1"></i> Refresh Status
        </button>
      </div>
      <div class="card-body">
        @if(!$serverCheck['exec_available'])
        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
          <i class="mdi mdi-alert-circle-outline me-2 mdi-24px"></i>
          <div>Fungsi <code>shell_exec</code> tidak tersedia di server ini. Status tidak dapat dicek secara otomatis.</div>
        </div>
        @endif
        <ul class="list-unstyled mb-0">
          <li class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <span><i class="mdi mdi-console me-2 text-muted"></i> Binary radiusd</span>
            <span id="radiusd-status">
              @if(!$serverCheck['exec_available'])
                <span class="badge bg-label-warning fs-6 px-3 py-2">Tidak Dapat Dicek</span>
              @elseif($serverCheck['binary_radiusd'])
                <span class="badge bg-label-success fs-6 px-3 py-2">Terinstall</span>
              @else
                <span class="badge bg-label-danger fs-6 px-3 py-2">Tidak Terinstall</span>
              @endif
            </span>
          </li>
          <li class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <span><i class="mdi mdi-console me-2 text-muted"></i> Binary radclient</span>
            <span id="radclient-status">
              @if(!$serverCheck['exec_available'])
                <span class="badge bg-label-warning fs-6 px-3 py-2">Tidak Dapat Dicek</span>
              @elseif($serverCheck['binary_radclient'])
                <span class="badge bg-label-success fs-6 px-3 py-2">Terinstall</span>
              @else
                <span class="badge bg-label-danger fs-6 px-3 py-2">Tidak Terinstall</span>
              @endif
            </span>
          </li>
          <li class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <span><i class="mdi mdi-run me-2 text-muted"></i> Service</span>
            <span id="service-status">
              @if(!$serverCheck['exec_available'])
                <span class="badge bg-label-warning fs-6 px-3 py-2">Tidak Dapat Dicek</span>
              @elseif($serverCheck['service_active'])
                <span class="badge bg-label-success fs-6 px-3 py-2">Berjalan</span>
              @else
                <span class="badge bg-label-danger fs-6 px-3 py-2">Tidak Berjalan</span>
              @endif
            </span>
          </li>
          <li class="d-flex justify-content-between align-items-center">
            <span><i class="mdi mdi-tag-text-outline me-2 text-muted"></i> Versi</span>
            <span id="radius-version" class="fw-bold fs-5">{{ $serverCheck['exec_available'] ? ($serverCheck['version'] ?? '-') : '-' }}</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection
