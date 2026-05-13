@extends('layouts/layoutMaster')

@section('title', 'System Settings - Hapus Bersih Data')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')
<script>
$(function () {
    const formHapus = $('#form-hapus-bersih');

    formHapus.on('submit', function (e) {
        e.preventDefault();

        const selectedGroups = $('input[name="groups[]"]:checked').length;
        if (selectedGroups === 0) {
            Swal.fire({
                title: 'Pilih Data!',
                text: 'Silakan pilih minimal satu kelompok data yang ingin dihapus.',
                icon: 'warning',
                showClass: {
                    popup: 'animate__animated animate__shakeX'
                },
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            });
            return;
        }

        Swal.fire({
            title: '<span class="text-danger">Apakah Anda Yakin?</span>',
            html: `Data yang dihapus <b>tidak dapat dikembalikan</b>!<br><br>Ketik <b>HAPUS</b> di bawah untuk mengonfirmasi:`,
            icon: 'warning',
            input: 'text',
            inputAttributes: {
                autocapitalize: 'on',
                placeholder: 'Ketik HAPUS di sini'
            },
            showCancelButton: true,
            showDenyButton: false,
            showCloseButton: false,
            confirmButtonText: 'Ya, Hapus Sekarang!',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-label-secondary ms-2',
                input: 'form-control mx-auto w-75'
            },
            buttonsStyling: false,
            allowOutsideClick: () => !Swal.isLoading(),
            preConfirm: (inputValue) => {
                if (inputValue !== 'HAPUS') {
                    Swal.showValidationMessage('Anda harus mengetik "HAPUS" dengan benar!');
                    return false;
                }
                return inputValue;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Gunakan GlobalLoading standar proyek jika tersedia
                if (window.GlobalLoading) {
                    GlobalLoading.show('Sedang menghapus data terpilih, mohon tunggu...');
                } else {
                    Swal.fire({
                        title: 'Sedang Memproses...',
                        html: '<div class="mb-3">Mohon tunggu sebentar, sistem sedang membersihkan database.</div>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        showCancelButton: false,
                        showDenyButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                }

                // Submit via AJAX
                $.ajax({
                    url: "{{ route('setting.hapus-bersih') }}",
                    method: 'POST',
                    data: formHapus.serialize() + '&konfirmasi=' + result.value,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (window.GlobalLoading) GlobalLoading.hide();

                        if (response.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message,
                                icon: 'success',
                                showClass: {
                                    popup: 'animate__animated animate__bounceIn'
                                },
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                },
                                buttonsStyling: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal Menghapus!',
                                text: response.message,
                                icon: 'error',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            });
                        }
                    },
                    error: function (xhr) {
                        if (window.GlobalLoading) GlobalLoading.hide();

                        let msg = 'Terjadi kesalahan sistem yang tidak terduga.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            title: '<span class="text-danger">Sistem Error!</span>',
                            html: `<div class="text-start bg-light p-3 rounded small" style="max-height: 200px; overflow-y: auto;">
                                    <b>Pesan Kesalahan:</b><br>${msg}
                                   </div>`,
                            icon: 'error',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false
                        });
                    }
                });
            }
        });
    });

    // Check all functionality
    $('#check-all').on('change', function() {
        $('input[name="groups[]"]').prop('checked', this.checked);
    });
});
</script>
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Pengaturan /</span> Hapus Bersih Data
</h4>

<div class="row">
  <div class="col-md-12">
    <div class="card mb-4 border-top border-danger border-3">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0"><i class="mdi mdi-alert-decagram text-danger me-2"></i>Wipe Out Data (Reset System)</h5>
        <span class="badge bg-label-danger">Danger Zone</span>
      </div>
      <div class="card-body">
        <div class="alert alert-warning border-warning d-flex align-items-start" role="alert">
          <i class="mdi mdi-alert-circle-outline me-3 mdi-24px"></i>
          <div>
            <h6 class="alert-heading fw-bold mb-1">Peringatan Keras!</h6>
            <span>Aksi ini akan menghapus data secara permanen dari database. Pastikan Anda telah melakukan backup data sebelum melanjutkan. Data yang sudah dihapus <b>tidak dapat dikembalikan lagi</b>.</span>
          </div>
        </div>

        <form id="form-hapus-bersih" class="mt-4">
          @csrf
          <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <p class="fw-semibold mb-0">Pilih kelompok data yang akan dikosongkan:</p>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="check-all">
                <label class="form-check-label fw-bold text-primary" for="check-all">
                  Pilih Semua
                </label>
              </div>
            </div>
            
            <div class="row g-4">
              <div class="col-md-6">
                <div class="card bg-lighter border-0 shadow-none h-100">
                  <div class="card-body">
                    <div class="form-check mb-3">
                      <input class="form-check-input" type="checkbox" name="groups[]" value="pelanggan" id="group-pelanggan">
                      <label class="form-check-label w-100" for="group-pelanggan">
                        <span class="fw-bold d-block text-dark h6">Data Pelanggan</span>
                        <small class="text-muted">Menghapus data pelanggan (User Role 5), detail mikrotik pelanggan, dan profil pelanggan.</small>
                      </label>
                    </div>

                    <div class="form-check mb-3">
                      <input class="form-check-input" type="checkbox" name="groups[]" value="tagihan" id="group-tagihan">
                      <label class="form-check-label w-100" for="group-tagihan">
                        <span class="fw-bold d-block text-dark h6">Data Tagihan & Transaksi</span>
                        <small class="text-muted">Menghapus seluruh invoice tagihan, riwayat transaksi pembayaran, dan data diskon.</small>
                      </label>
                    </div>

                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="groups[]" value="layanan" id="group-layanan">
                      <label class="form-check-label w-100" for="group-layanan">
                        <span class="fw-bold d-block text-dark h6">Data Layanan</span>
                        <small class="text-muted">Menghapus master data Paket Internet dan Kategori Layanan.</small>
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="card bg-lighter border-0 shadow-none h-100">
                  <div class="card-body">
                    <div class="form-check mb-3">
                      <input class="form-check-input" type="checkbox" name="groups[]" value="infrastruktur" id="group-infra">
                      <label class="form-check-label w-100" for="group-infra">
                        <span class="fw-bold d-block text-dark h6">Data Infrastruktur & Jaringan</span>
                        <small class="text-muted">Menghapus data Mikrotik, OLT, ODC, ODP, IP Pool, dan Address List.</small>
                      </label>
                    </div>

                    <div class="form-check mb-3">
                      <input class="form-check-input" type="checkbox" name="groups[]" value="pemetaan" id="group-map">
                      <label class="form-check-label w-100" for="group-map">
                        <span class="fw-bold d-block text-dark h6">Data Pemetaan & Media</span>
                        <small class="text-muted">Menghapus data Area, Route Map, dan Foto-foto yang tersimpan di database.</small>
                      </label>
                    </div>

                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="groups[]" value="logs" id="group-logs">
                      <label class="form-check-label w-100" for="group-logs">
                        <span class="fw-bold d-block text-dark h6">Log Sistem</span>
                        <small class="text-muted">Mengosongkan seluruh catatan log aktifitas sistem.</small>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="pt-3 border-top">
            <button type="submit" class="btn btn-danger btn-lg me-sm-3 me-1">
              <i class="mdi mdi-trash-can-outline me-1"></i>Hapus Data Terpilih
            </button>
            <button type="reset" class="btn btn-label-secondary btn-lg">Batal</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
