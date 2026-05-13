@extends('layouts/layoutMaster')

@section('title', 'Tambah Pelanggan RADIUS')

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
    $('#form-add-radius-user').on('submit', function(e) {
      e.preventDefault();
      const btn = $('#btn-save');
      const form = $(this);
      
      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...');

      $.ajax({
        url: "{{ route('radius.user.store') }}",
        method: 'POST',
        data: form.serialize(),
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
          if (response.status) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: response.message,
                customClass: { confirmButton: 'btn btn-success' }
            }).then(() => {
                form[0].reset();
                window.location.href = "{{ route('radius.user.index') }}";
            });
          } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: response.message,
                customClass: { confirmButton: 'btn btn-primary' }
            });
          }
        },
        error: function(xhr) {
          Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Terjadi kesalahan sistem.',
                customClass: { confirmButton: 'btn btn-primary' }
            });
        },
        complete: function() {
          btn.prop('disabled', false).text('Simpan Pelanggan');
        }
      });
    });
  });
</script>
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">FreeRADIUS /</span> Tambah Pelanggan Baru
</h4>

<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card mb-4 border-top border-primary border-3">
      <div class="card-header d-flex align-items-center">
        <h5 class="mb-0 fw-bold"><i class="mdi mdi-account-plus-outline me-2"></i>Form Input Pelanggan RADIUS</h5>
      </div>
      <form id="form-add-radius-user" class="card-body">
        @csrf
        <div class="row g-4">
          <div class="col-md-6">
            <div class="form-floating form-floating-outline">
              <input type="text" name="username" class="form-control border-primary" autocomplete="off" placeholder="Username" required />
              <label>Username (Secret Name)</label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-floating form-floating-outline">
              <input type="password" name="password" class="form-control border-primary" autocomplete="new-password" placeholder="Password" required />
              <label>Password</label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-floating form-floating-outline">
              <select name="profile" class="form-select border-primary" required>
                <option value="" disabled selected>Pilih Profile / Paket</option>
                @foreach($pakets as $p)
                  <option value="{{ $p->kode }}">{{ $p->nama }}</option>
                @endforeach
              </select>
              <label>PPPoE / Hotspot Profile</label>
            </div>
            <small class="text-muted ps-2">Profile akan dipetakan ke Group RADIUS.</small>
          </div>
          <div class="col-md-6">
            <div class="form-floating form-floating-outline">
              <input type="text" name="limit" class="form-control" placeholder="10M/10M" />
              <label>Custom Rate Limit (Opsional)</label>
            </div>
            <small class="text-muted ps-2">Format: Upload/Download (contoh: 5M/5M).</small>
          </div>
        </div>

        <div class="pt-4 mt-3 border-top d-flex justify-content-between">
          <a href="{{ route('radius.user.index') }}" class="btn btn-label-secondary">
            <i class="mdi mdi-arrow-left me-1"></i> Kembali
          </a>
          <button type="submit" id="btn-save" class="btn btn-primary px-5 shadow">
            <i class="mdi mdi-content-save-outline me-1"></i> Simpan Pelanggan
          </button>
        </div>
      </form>
    </div>
    
    <div class="alert alert-outline-info d-flex align-items-start mt-3" role="alert">
        <i class="mdi mdi-information-outline me-2 mdi-24px"></i>
        <div>
            <h6 class="alert-heading fw-bold mb-1">Informasi RADIUS</h6>
            <span>Data yang Anda masukkan di sini akan disimpan langsung ke tabel <code>radcheck</code> dan <code>radusergroup</code> di database lokal. Pastikan Router MikroTik Anda sudah diarahkan ke server RADIUS ini.</span>
        </div>
    </div>
  </div>
</div>
@endsection
