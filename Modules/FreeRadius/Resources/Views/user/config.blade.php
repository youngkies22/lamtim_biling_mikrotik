@extends('layouts/layoutMaster')

@section('title', 'Konfigurasi User - ' . $username)

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
    $('#form-config').on('submit', function(e) {
      e.preventDefault();
      var btn = $('#btn-save');
      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menyimpan...');

      $.ajax({
        url: '{{ route("radius.user.config.update", $username) }}',
        type: 'POST',
        data: $(this).serialize(),
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
          if (response.status) {
            Swal.fire('Berhasil!', response.message, 'success');
          } else {
            Swal.fire('Gagal', response.message, 'error');
          }
        },
        error: function() {
          Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
        },
        complete: function() {
          btn.prop('disabled', false).html('<i class="mdi mdi-content-save-outline me-1"></i> Simpan');
        }
      });
    });
  });
</script>
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">FreeRADIUS / Pelanggan /</span> Konfigurasi: {{ $username }}
</h4>

<div class="row">
  <div class="col-md-6">
    <div class="card mb-4 border-top border-primary border-3">
      <div class="card-header">
        <h5 class="mb-0 fw-bold"><i class="mdi mdi-cog-outline me-1"></i> Konfigurasi RADIUS</h5>
      </div>
      <form id="form-config" class="card-body">
        @csrf

        <div class="mb-4">
          <label class="form-label fw-bold">Static IP Address</label>
          <input type="text" name="static_ip" class="form-control" value="{{ $replies->get('Framed-IP-Address') ? $replies->get('Framed-IP-Address')->value : '' }}" placeholder="Ex: 10.10.10.50">
          <small class="text-muted">Kosongkan jika ingin menggunakan IP Dinamis (Pool).</small>
        </div>

        <div class="mb-4">
          <label class="form-label fw-bold">VLAN ID (Steering)</label>
          <input type="number" name="vlan_id" class="form-control" value="{{ $replies->get('Tunnel-Private-Group-Id') ? $replies->get('Tunnel-Private-Group-Id')->value : '' }}" placeholder="Contoh: 10">
          <small class="text-muted">User akan otomatis diarahkan ke VLAN ID ini saat berhasil login.</small>
        </div>

        <hr>
        <div class="d-flex justify-content-between">
          <a href="{{ route('radius.user.index') }}" class="btn btn-label-secondary">
            <i class="mdi mdi-arrow-left me-1"></i> Kembali
          </a>
          <button type="submit" id="btn-save" class="btn btn-primary">
            <i class="mdi mdi-content-save-outline me-1"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="col-md-6">
    {{-- Statistics Card --}}
    <div class="card mb-4 border-start border-info border-3">
      <div class="card-header">
        <h5 class="mb-0"><i class="mdi mdi-chart-bar me-1"></i> Statistik Pemakaian</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-6 mb-3">
            <small class="text-muted text-uppercase d-block">Total Upload</small>
            <span class="fw-bold text-success fs-5">{{ $stats['upload'] }}</span>
          </div>
          <div class="col-6 mb-3">
            <small class="text-muted text-uppercase d-block">Total Download</small>
            <span class="fw-bold text-primary fs-5">{{ $stats['download'] }}</span>
          </div>
          <div class="col-12">
            <small class="text-muted text-uppercase d-block">Total Waktu Sesi</small>
            <span class="fw-bold text-info fs-5">{{ $stats['total_time'] }}</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Info Card --}}
    <div class="card border-start border-warning border-3">
      <div class="card-header">
        <h5 class="mb-0"><i class="mdi mdi-information-outline me-1"></i> Informasi</h5>
      </div>
      <div class="card-body">
        <div class="alert alert-info d-flex mb-3" role="alert">
          <i class="mdi mdi-information-outline me-2 mdi-24px"></i>
          <div>Perubahan pada IP Statis dan VLAN akan berlaku pada sesi login berikutnya. Jika user sedang online, lakukan <b>Disconnect</b> terlebih dahulu.</div>
        </div>
        <h6 class="fw-bold"><i class="mdi mdi-lan me-1"></i> Static IP Address</h6>
        <p class="text-muted small">RADIUS mengirimkan atribut <code>Framed-IP-Address</code> ke MikroTik. Memaksa router untuk memberikan IP yang sama setiap kali pelanggan melakukan koneksi.</p>
        <h6 class="fw-bold"><i class="mdi mdi-git-branch me-1"></i> VLAN ID (Steering)</h6>
        <p class="text-muted small mb-0">Menggunakan atribut <code>Tunnel-Private-Group-Id</code>, RADIUS menginstruksikan MikroTik untuk memasukkan sesi user ke VLAN ID tertentu (Dynamic VLAN Assignment).</p>
      </div>
    </div>
  </div>
</div>
@endsection
