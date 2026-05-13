@extends('layouts/layoutMaster')

@section('title', 'Manajemen Address List')

@section('page-script')
<script>
  $(document).ready(function() {
    var table = $('#table-lists').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('address-list.json') }}",
        columns: [
            { data: null, render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; } },
            { 
                data: 'name',
                render: function(data) {
                    return `<span class="badge bg-label-info fw-bold">${data}</span>`;
                }
            },
            { data: 'description' },
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

    $("div.head-label").html('<h5 class="card-title mb-0">Daftar Address List MikroTik</h5>');

    $('#btn-pull').on('click', function() {
        const idMikrotik = $('#idMikrotik').val();
        if (!idMikrotik) {
            toastr.error('Pilih MikroTik terlebih dahulu!');
            return;
        }

        Swal.fire({
            title: 'Tarik Data?',
            text: "Sistem akan mengambil kategori nama list dari Firewall MikroTik.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Sinkronkan!',
            customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(function(result) {
            if (result.isConfirmed) {
                GlobalLoading.show();
                $.ajax({
                    url: "{{ route('address-list.pull') }}",
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
                    url: "/address-list/" + id,
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
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <h5 class="card-title mb-0">
                            Sinkronisasi Address List
                            <a href="javascript:void(0);" class="ms-1 text-primary" data-bs-toggle="modal" data-bs-target="#modalInfoIp">
                                <i class="mdi mdi-information-outline mdi-24px"></i>
                            </a>
                        </h5>
                        <p class="text-muted small mb-0">Gunakan fitur ini untuk menarik nama-nama list dari Firewall MikroTik Anda.</p>
                    </div>
                </div>
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-primary">Pilih Sumber MikroTik</label>
                        <select id="idMikrotik" class="form-select border-primary select2">
                            <option value="">-- Pilih MikroTik --</option>
                            @foreach($mikrotiks as $m)
                                <option value="{{ $m->id }}">{{ $m->nama }} ({{ $m->ip }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button id="btn-pull" class="btn btn-primary w-100 shadow">
                            <i class="mdi mdi-sync me-1"></i> Mulai Sinkronisasi
                        </button>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <div class="alert alert-outline-info d-inline-block mb-0 p-2">
                            <small><i class="mdi mdi-check-decagram me-1"></i>Data yang ditarik adalah nama kategori list yang unik.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-datatable table-responsive">
        <table id="table-lists" class="table table-hover border-top">
            <thead class="table-light">
                <tr>
                    <th width="50">NO</th>
                    <th>NAMA LIST</th>
                    <th>KETERANGAN</th>
                    <th width="100">AKSI</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Modal Informasi (Reuse from IP Pool or similar) -->
<div class="modal fade animate__animated animate__fadeIn" id="modalInfoIp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="mdi mdi-help-circle-outline me-2"></i>Penjelasan Address List</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="avatar avatar-xl bg-label-primary mx-auto mb-3">
                        <span class="avatar-initial rounded-circle"><i class="mdi mdi-list-status mdi-48px"></i></span>
                    </div>
                    <h5 class="fw-bold">Apa itu Address List di MikroTik?</h5>
                </div>
                
                <p class="text-muted">Address List adalah fitur MikroTik untuk menandai alamat IP tertentu agar bisa diberikan aturan khusus pada Firewall.</p>

                <div class="bg-label-info p-3 rounded mb-3">
                    <h6 class="fw-bold mb-1"><i class="mdi mdi-check-circle-outline me-1"></i>Manfaat Utama:</h6>
                    <ul class="list-unstyled mb-0 small">
                        <li><i class="mdi mdi-chevron-right me-1"></i><strong>Isolir:</strong> Memblokir internet bagi pelanggan menunggak.</li>
                        <li><i class="mdi mdi-chevron-right me-1"></i><strong>Prioritas:</strong> Memberikan jalur khusus untuk trafik Game/Bisnis.</li>
                        <li><i class="mdi mdi-chevron-right me-1"></i><strong>Routing:</strong> Mengalihkan trafik ke Gateway (ISP) tertentu.</li>
                    </ul>
                </div>

                <div class="bg-label-warning p-3 rounded">
                    <small class="d-block text-dark fw-bold mb-1">Catatan Penting:</small>
                    <small class="text-dark">Dengan menarik data ke sini, Anda bisa langsung memilih nama list tersebut saat membuat <strong>Paket Internet</strong> di menu Paket.</small>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Saya Mengerti</button>
            </div>
        </div>
    </div>
</div>
@endsection
