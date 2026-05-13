@extends('layouts/layoutMaster')

@section('title', 'Konfigurasi FreeRADIUS')

@section('page-script')
<script>
  $(document).ready(function() {
    $('#btn-test-connection').on('click', function() {
      const btn = $(this);
      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menghubungkan...');

      $.ajax({
        url: "{{ route('radius.config.test') }}",
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
          if (response.status) {
            toastr.success(response.message);
          } else {
            toastr.error(response.message);
          }
        },
        error: function() {
          toastr.error('Terjadi kesalahan sistem.');
        },
        complete: function() {
          btn.prop('disabled', false).text('Test Koneksi');
        }
      });
    });
  });
</script>
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">FreeRADIUS /</span> Pengaturan Koneksi
</h4>

<div class="row">
  <div class="col-md-8">
    <div class="card mb-4">
      <h5 class="card-header">Detail Database FreeRADIUS</h5>
      <form action="{{ route('radius.config.store') }}" method="POST" class="card-body">
        @csrf
        <div class="row g-4">
          <div class="col-md-8">
            <div class="form-floating form-floating-outline">
              <input type="text" name="host" class="form-control" value="{{ $config->host ?? '' }}" placeholder="172.93.186.7" required />
              <label>Host / IP Server (SQL)</label>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-floating form-floating-outline">
              <input type="number" name="port" class="form-control" value="{{ $config->port ?? 3306 }}" placeholder="3306" required />
              <label>Port</label>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-floating form-floating-outline">
              <input type="text" name="database" class="form-control" value="{{ $config->database ?? '' }}" placeholder="radius" required />
              <label>Nama Database</label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-floating form-floating-outline">
              <input type="text" name="username" class="form-control" value="{{ $config->username ?? '' }}" placeholder="root" required />
              <label>Username SQL</label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-floating form-floating-outline">
              <input type="password" name="password" class="form-control" value="{{ $config->password ?? '' }}" placeholder="············" />
              <label>Password SQL</label>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-floating form-floating-outline">
              <input type="text" name="secret" class="form-control" value="{{ $config->secret ?? '' }}" placeholder="Shared Secret Radius" />
              <label>Shared Secret (MikroTik)</label>
            </div>
          </div>
        </div>
        <div class="pt-4 d-flex justify-content-between">
          <button type="submit" class="btn btn-primary">Simpan Konfigurasi</button>
          <button type="button" id="btn-test-connection" class="btn btn-outline-info">Test Koneksi</button>
        </div>
      </form>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card bg-lighter border-0">
      <div class="card-body">
        <h5 class="mb-3">Informasi Penting</h5>
        <div class="d-flex align-items-start mb-3">
          <div class="badge rounded bg-label-primary me-3 p-2">
            <i class="mdi mdi-database mdi-24px"></i>
          </div>
          <div>
            <h6 class="mb-0">Akses SQL</h6>
            <small class="text-muted">Aplikasi memerlukan akses port 3306 ke server target untuk mengelola user secara langsung.</small>
          </div>
        </div>
        <div class="d-flex align-items-start mb-3">
          <div class="badge rounded bg-label-info me-3 p-2">
            <i class="mdi mdi-security mdi-24px"></i>
          </div>
          <div>
            <h6 class="mb-0">Shared Secret</h6>
            <small class="text-muted">Gunakan secret yang sama pada pengaturan Radius Client di sisi MikroTik Anda.</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
