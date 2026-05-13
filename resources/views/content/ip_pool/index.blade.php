@extends('layouts/layoutMaster')

@section('title', 'Manajemen IP Pool')

@section('page-script')
<script>
  $(document).ready(function() {
    var table = $('#table-pools').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('ip-pool.json') }}",
        columns: [
            { data: null, render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; } },
            { data: 'name' },
            { data: 'ranges' },
            { data: 'next_pool' },
            { 
                data: 'id',
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}">
                            <i class="mdi mdi-trash-can-outline"></i>
                        </button>
                    `;
                }
            }
        ],
        displayLength: 10,
        dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        buttons: []
    });

    $("div.head-label").html('<h5 class="card-title mb-0">Daftar IP Pool MikroTik</h5>');

    $('#btn-pull').on('click', function() {
        const idMikrotik = $('#idMikrotik').val();
        if (!idMikrotik) {
            toastr.error('Pilih MikroTik terlebih dahulu!');
            return;
        }

        Swal.fire({
            title: 'Tarik Data?',
            text: "Data IP Pool akan diperbarui dari MikroTik terpilih.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tarik!',
            customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(function(result) {
            if (result.isConfirmed) {
                GlobalLoading.show();
                $.ajax({
                    url: "{{ route('ip-pool.pull') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        idMikrotik: idMikrotik
                    },
                    success: function(res) {
                        GlobalLoading.hide();
                        toastr.success(res.message);
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        GlobalLoading.hide();
                        toastr.error(xhr.responseJSON.message || 'Gagal menarik data.');
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Hapus?',
            text: "Data ini akan dihapus dari database lokal.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/ip-pool/" + id,
                    type: 'DELETE',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        toastr.success(res.message);
                        table.ajax.reload();
                    }
                });
            }
        });
    });
  });
</script>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card h-100">
            <div class="card-body">
                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <h5 class="card-title mb-0">
                            Daftar IP Pool MikroTik 
                            <a href="javascript:void(0);" class="ms-1 text-primary" data-bs-toggle="modal" data-bs-target="#modalInfoIp">
                                <i class="mdi mdi-information-outline mdi-24px"></i>
                            </a>
                        </h5>
                    </div>
                </div>
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-primary">Pilih MikroTik untuk Tarik Data</label>
                        <select id="idMikrotik" class="form-select border-primary">
                            <option value="">-- Pilih MikroTik --</option>
                            @foreach($mikrotiks as $m)
                                <option value="{{ $m->id }}">{{ $m->nama }} ({{ $m->ip }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button id="btn-pull" class="btn btn-primary w-100 shadow">
                            <i class="mdi mdi-download me-1"></i> Tarik Data
                        </button>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="alert alert-outline-primary d-inline-block mb-0 p-2">
                            <small><i class="mdi mdi-sync me-1"></i>Sistem akan otomatis update jika nama pool sudah ada, dan menambah jika belum ada.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-datatable table-responsive">
        <table id="table-pools" class="table table-bordered">
            <thead>
                <tr>
                    <th>NO</th>
                    <th>NAMA POOL</th>
                    <th>RANGES IP</th>
                    <th>NEXT POOL</th>
                    <th>AKSI</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Modal Informasi -->
<div class="modal fade animate__animated animate__fadeIn" id="modalInfoIp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="mdi mdi-help-circle-outline me-2"></i>Penjelasan Teknis</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="avatar avatar-xl bg-label-primary mx-auto mb-3">
                        <span class="avatar-initial rounded-circle"><i class="mdi mdi-network-outline mdi-48px"></i></span>
                    </div>
                    <h5 class="fw-bold">Mengapa ada Address List padahal sudah ada IP Pool?</h5>
                </div>
                
                <div class="d-flex mb-3">
                    <div class="me-3">
                        <span class="badge bg-label-success p-2"><i class="mdi mdi-ip-network mdi-24px"></i></span>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-bold">1. IP POOL (Pemberi Alamat)</h6>
                        <p class="mb-0 small text-muted">Berfungsi menentukan alamat IP apa yang didapat pelanggan. <br><strong>Analogi:</strong> Seperti memberikan nomor kursi di bioskop.</p>
                    </div>
                </div>

                <div class="d-flex mb-3">
                    <div class="me-3">
                        <span class="badge bg-label-info p-2"><i class="mdi mdi-list-status mdi-24px"></i></span>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-bold">2. ADDRESS LIST (Pengelompokan)</h6>
                        <p class="mb-0 small text-muted">Berfungsi memasukkan IP tersebut ke daftar khusus di Firewall MikroTik. <br><strong>Analogi:</strong> Seperti menandai kursi mana yang VIP atau yang harus diawasi petugas.</p>
                    </div>
                </div>

                <div class="bg-label-warning p-3 rounded mt-3">
                    <small class="d-block text-dark fw-bold mb-1">Kesimpulan:</small>
                    <small class="text-dark">IP Pool untuk <strong>"Koneksi"</strong>, sedangkan Address List untuk <strong>"Aturan Khusus"</strong> di firewall router Anda.</small>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Saya Mengerti</button>
            </div>
        </div>
    </div>
</div>
@endsection
