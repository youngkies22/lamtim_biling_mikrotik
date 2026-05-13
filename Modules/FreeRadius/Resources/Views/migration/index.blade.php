@extends('layouts/layoutMaster')

@section('title', 'Migrasi RADIUS ke MikroTik')

@section('page-script')
<script>
  $(document).ready(function() {
    $('#form-migration').on('submit', function(e) {
      e.preventDefault();
      const btn = $('#btn-migrate');
      
      if(!confirm('Apakah Anda yakin ingin melakukan migrasi seluruh user RADIUS ke MikroTik? Proses ini tidak dapat dibatalkan.')) return;

      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses Migrasi...');

      $.ajax({
        url: "{{ route('radius.migration.process') }}",
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
          if (response.status) {
            Swal.fire({
              icon: 'success',
              title: 'Migrasi Selesai',
              text: 'Berhasil memigrasikan ' + response.count + ' pelanggan ke MikroTik.',
              customClass: { confirmButton: 'btn btn-primary' }
            });
          } else {
            toastr.error(response.message);
          }
        },
        error: function(xhr) {
          toastr.error('Terjadi kesalahan sistem.');
        },
        complete: function() {
          btn.prop('disabled', false).html('<i class="mdi mdi-export-variant me-1"></i> Jalankan Migrasi Sekarang');
        }
      });
    });
  });
</script>
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">FreeRADIUS /</span> Alat Migrasi
</h4>

<div class="row">
  <div class="col-md-7">
    <div class="card mb-4">
      <h5 class="card-header">Migrasi Data (RADIUS → MikroTik)</h5>
      <form id="form-migration" class="card-body">
        @csrf
        <p class="mb-4">Gunakan fitur ini jika Anda ingin mengembalikan atau memindahkan data pelanggan dari server RADIUS kembali ke sistem lokal MikroTik (PPP Secret).</p>
        
        <div class="row g-4">
          <div class="col-md-12">
            <div class="form-floating form-floating-outline">
              <select name="id_mikrotik" class="form-select" required>
                <option value="" disabled selected>Pilih Router Tujuan</option>
                @foreach($mikrotiks as $m)
                  <option value="{{ $m->id }}">{{ $m->nama }} ({{ $m->ip }})</option>
                @endforeach
              </select>
              <label>Router Tujuan</label>
            </div>
          </div>
        </div>

        <div class="pt-4 mt-2">
            <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                <i class="mdi mdi-alert-circle-outline me-2"></i>
                <div>
                    Pastikan router tujuan memiliki Profile yang namanya SAMA dengan nama Group di RADIUS. Jika data sudah ada di MikroTik, maka proses untuk user tersebut akan dilewati.
                </div>
            </div>
          <button type="submit" id="btn-migrate" class="btn btn-warning w-100">
            <i class="mdi mdi-export-variant me-1"></i> Jalankan Migrasi Sekarang
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="col-md-5">
    <div class="card bg-lighter border-0">
      <div class="card-body">
        <h5 class="mb-3">Log Penerapan</h5>
        <div class="d-flex align-items-start mb-3">
          <div class="badge rounded bg-label-warning me-3 p-2">
            <i class="mdi mdi-account-switch mdi-24px"></i>
          </div>
          <div>
            <h6 class="mb-0">Format Data</h6>
            <small class="text-muted">Username akan dijadikan Nama Secret, Password diambil dari Cleartext-Password, dan Group menjadi Profile.</small>
          </div>
        </div>
        <div class="d-flex align-items-start">
          <div class="badge rounded bg-label-info me-3 p-2">
            <i class="mdi mdi-security mdi-24px"></i>
          </div>
          <div>
            <h6 class="mb-0">Keamanan</h6>
            <small class="text-muted">Proses ini menggunakan koneksi API MikroTik yang aman. Rekomendasi: Lakukan proses ini saat beban trafik rendah.</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
