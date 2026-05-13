@extends('layouts/layoutMaster')

@section('title', 'Sinkronisasi MikroTik ke RADIUS')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
@endsection

@section('page-script')
<script>
  $(document).ready(function() {
    // Initialize select2
    $('.select2').select2();

    // Sync from MikroTik (PPP/Hotspot)
    $('#form-sync').on('submit', function(e) {
      e.preventDefault();
      const btn = $('#btn-start-sync');
      const form = $(this);
      
      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses Sinkronisasi...');

      $.ajax({
        url: "{{ route('radius.sync.process') }}",
        method: 'POST',
        data: form.serialize(),
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
        error: function(xhr) {
          toastr.error('Gagal melakukan sinkronisasi: ' + (xhr.responseJSON?.message || 'Error Server'));
        },
        complete: function() {
          btn.prop('disabled', false).text('Mulai Sinkronisasi Sekarang');
        }
      });
    });

    // Sync from Billing
    $('#form-sync-billing').on('submit', function(e) {
      e.preventDefault();
      const btn = $('#btn-start-sync-billing');
      
      Swal.fire({
        title: 'Konfirmasi Sinkronisasi',
        text: "Seluruh data pelanggan di database Billing akan disinkronkan ke RADIUS. Lanjutkan?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Sinkronkan!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');

          $.ajax({
            url: "{{ route('radius.sync.billing') }}",
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
              if (response.status) {
                Swal.fire('Berhasil!', response.message, 'success');
              } else {
                Swal.fire('Gagal', response.message, 'error');
              }
            },
            error: function(xhr) {
              Swal.fire('Error', 'Gagal melakukan sinkronisasi: ' + (xhr.responseJSON?.message || 'Error Server'), 'error');
            },
            complete: function() {
              btn.prop('disabled', false).html('<i class="mdi mdi-database-sync me-1"></i> Sinkron dari Database Billing');
            }
          });
        }
      });
    });

    // Sync NAS
    $('#btn-sync-nas').on('click', function() {
      const btn = $(this);
      Swal.fire({
        title: 'Sinkronisasi NAS?',
        text: "Semua MikroTik aktif akan disinkronkan ke tabel nas di FreeRADIUS.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Sinkronkan!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span>');

          $.ajax({
            url: "{{ route('radius.sync.nas') }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
              if (response.status) {
                Swal.fire('Berhasil!', response.message, 'success');
              } else {
                Swal.fire('Gagal', response.message, 'error');
              }
            },
            error: function() { Swal.fire('Error', 'Gagal sinkronisasi NAS.', 'error'); },
            complete: function() { btn.prop('disabled', false).html('<i class="mdi mdi-router me-1"></i> Sinkron NAS'); }
          });
        }
      });
    });

    // Sync Groups from Billing
    $('#btn-sync-groups').on('click', function() {
      const btn = $(this);
      Swal.fire({
        title: 'Sinkronisasi Paket?',
        text: "Semua paket billing akan disinkronkan ke radgroupreply di FreeRADIUS.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Sinkronkan!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span>');

          $.ajax({
            url: "{{ route('radius.sync.groups') }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
              if (response.status) {
                Swal.fire('Berhasil!', response.message, 'success');
              } else {
                Swal.fire('Gagal', response.message, 'error');
              }
            },
            error: function() { Swal.fire('Error', 'Gagal sinkronisasi Groups.', 'error'); },
            complete: function() { btn.prop('disabled', false).html('<i class="mdi mdi-package-variant-closed me-1"></i> Sinkron Paket'); }
          });
        }
      });
    });

    // Import from MikroTik
    $('#btn-import-mikrotik').on('click', function() {
      const idMikrotik = $('#import_mikrotik_id').val();
      if (!idMikrotik) {
        toastr.error('Pilih router sumber terlebih dahulu.');
        return;
      }
      const btn = $(this);
      Swal.fire({
        title: 'Import dari MikroTik?',
        text: "Data PPP Secret dari router akan ditarik ke Billing dan RADIUS. Lanjutkan?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Import!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Memproses...');

          $.ajax({
            url: "{{ route('radius.sync.import') }}",
            method: 'POST',
            data: { id_mikrotik: idMikrotik, _token: '{{ csrf_token() }}' },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
              if (response.status) {
                Swal.fire('Berhasil!', response.message, 'success');
              } else {
                Swal.fire('Gagal', response.message, 'error');
              }
            },
            error: function() { Swal.fire('Error', 'Gagal import dari MikroTik.', 'error'); },
            complete: function() { btn.prop('disabled', false).html('<i class="mdi mdi-download me-1"></i> Import dari MikroTik'); }
          });
        }
      });
    });
  });
</script>
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">FreeRADIUS /</span> Sinkronisasi Pelanggan
</h4>

{{-- Row 1: Sync Utama --}}
<div class="row">
  <div class="col-md-6">
    <div class="card mb-4">
      <h5 class="card-header">Transfer Data dari MikroTik</h5>
      <form id="form-sync" class="card-body">
        @csrf
        <p class="mb-4">Fitur ini akan menyalin data pelanggan (Username, Password, Profile) dari MikroTik yang Anda pilih ke database FreeRADIUS.</p>
        
        <div class="row g-4">
          <div class="col-md-12">
            <div class="form-floating form-floating-outline">
              <select name="id_mikrotik" class="form-select" required>
                <option value="" disabled selected>Pilih Router Sumber</option>
                @foreach($mikrotiks as $m)
                  <option value="{{ $m->id }}">{{ $m->nama }} ({{ $m->ip }})</option>
                @endforeach
              </select>
              <label>Router Sumber</label>
            </div>
          </div>
          
          <div class="col-md-12">
            <div class="form-floating form-floating-outline">
              <select name="type" class="form-select" required>
                <option value="ppp">PPPoE (PPP Secrets)</option>
                <option value="hotspot">Hotspot Users</option>
              </select>
              <label>Tipe Layanan</label>
            </div>
          </div>
        </div>

        <div class="pt-4 mt-2">
          <button type="submit" id="btn-start-sync" class="btn btn-primary w-100">
            <i class="mdi mdi-sync me-1"></i> Mulai Sinkronisasi Sekarang
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card mb-4 border-success border-opacity-25">
      <h5 class="card-header text-success">Transfer Data dari Billing</h5>
      <div class="card-body">
        <p class="mb-4">Fitur ini akan menyelaraskan <b>Database Billing</b> ke <b>FreeRADIUS</b> secara massal. Berguna untuk sinkronisasi awal atau perbaikan data.</p>
        
        <div class="alert alert-soft-success d-flex align-items-center mb-4" role="alert">
          <i class="mdi mdi-check-circle-outline me-2"></i>
          <div>
            Data akan disinkronkan berdasarkan status (Aktif/Isolir/Off) yang ada di billing.
          </div>
        </div>

        <form id="form-sync-billing">
          @csrf
          <button type="submit" id="btn-start-sync-billing" class="btn btn-success w-100">
            <i class="mdi mdi-database-sync me-1"></i> Sinkron dari Database Billing
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- Row 2: NAS Sync + Groups Sync + Import --}}
<div class="row">
  <div class="col-md-4">
    <div class="card mb-4 border-info border-opacity-25">
      <div class="card-body text-center">
        <i class="mdi mdi-router mdi-48px text-info mb-2"></i>
        <h5 class="card-title">Sinkronisasi NAS</h5>
        <p class="small text-muted mb-3">Salin IP dan Secret dari MikroTik aktif ke tabel <code>nas</code> di FreeRADIUS.</p>
        <button type="button" id="btn-sync-nas" class="btn btn-info w-100">
          <i class="mdi mdi-router me-1"></i> Sinkron NAS
        </button>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card mb-4 border-warning border-opacity-25">
      <div class="card-body text-center">
        <i class="mdi mdi-package-variant-closed mdi-48px text-warning mb-2"></i>
        <h5 class="card-title">Sinkronisasi Paket</h5>
        <p class="small text-muted mb-3">Sinkronkan paket billing ke <code>radgroupreply</code> termasuk burst dan IP Pool.</p>
        <button type="button" id="btn-sync-groups" class="btn btn-warning w-100">
          <i class="mdi mdi-package-variant-closed me-1"></i> Sinkron Paket
        </button>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card mb-4 border-primary border-opacity-25">
      <div class="card-body text-center">
        <i class="mdi mdi-download mdi-48px text-primary mb-2"></i>
        <h5 class="card-title">Import dari MikroTik</h5>
        <p class="small text-muted mb-3">Tarik data PPP Secret dari router ke dalam Billing dan RADIUS sekaligus.</p>
        <div class="form-floating form-floating-outline mb-3">
          <select id="import_mikrotik_id" class="form-select select2">
            <option value="">Pilih Router Sumber</option>
            @foreach($mikrotiks as $m)
              <option value="{{ $m->id }}">{{ $m->nama }} ({{ $m->ip }})</option>
            @endforeach
          </select>
          <label>Router Sumber</label>
        </div>
        <button type="button" id="btn-import-mikrotik" class="btn btn-primary w-100">
          <i class="mdi mdi-download me-1"></i> Import dari MikroTik
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Row 3: Aturan --}}
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h5 class=""><i class="mdi mdi-information-outline me-1"></i> Aturan Sinkronisasi</h5>
        <div class="row">
          <div class="col-md-6">
            <ul class="list-unstyled mb-0">
              <li class="mb-2 d-flex">
                <i class="mdi mdi-check-circle-outline text-success me-2"></i>
                <span><b>MikroTik ke RADIUS</b>: Berguna jika Anda ingin migrasi dari router ke RADIUS.</span>
              </li>
              <li class="mb-2 d-flex">
                <i class="mdi mdi-check-circle-outline text-success me-2"></i>
                <span><b>Billing ke RADIUS</b>: Memastikan data di RADIUS sama persis dengan status di billing.</span>
              </li>
              <li class="mb-2 d-flex">
                <i class="mdi mdi-check-circle-outline text-success me-2"></i>
                <span><b>NAS Sync</b>: Mendaftarkan router MikroTik agar bisa berkomunikasi dengan server RADIUS.</span>
              </li>
            </ul>
          </div>
          <div class="col-md-6">
            <ul class="list-unstyled mb-0">
              <li class="mb-2 d-flex">
                <i class="mdi mdi-check-circle-outline text-success me-2"></i>
                <span><b>Import MikroTik</b>: Menarik data PPP Secret ke Billing dan RADIUS sekaligus.</span>
              </li>
              <li class="mb-2 d-flex">
                <i class="mdi mdi-alert-circle-outline text-warning me-2"></i>
                <span>User dengan status <b>OFF</b> di billing akan otomatis dihapus dari RADIUS.</span>
              </li>
              <li class="mb-2 d-flex">
                <i class="mdi mdi-alert-circle-outline text-warning me-2"></i>
                <span>User dengan status <b>ISOLIR</b> akan diarahkan ke profile <b>ISOLIR</b> di RADIUS.</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
