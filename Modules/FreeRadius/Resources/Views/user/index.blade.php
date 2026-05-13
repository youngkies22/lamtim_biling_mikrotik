@extends('layouts/layoutMaster')

@section('title', 'Pelanggan RADIUS')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')
<script>
  $(document).ready(function() {
    // Initialize Select2
    $('#filter-profile').select2({
        placeholder: "Semua Paket",
        allowClear: true
    });

    var dt_user = $('#table-radius-users').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('radius.user.json') }}",
        data: function(d) {
          d.profile = $('#filter-profile').val();
          d.status = $('#filter-status').val();
        }
      },
      columns: [
        { 
            data: 'username',
            render: function(data) {
                return '<div class="d-flex align-items-center"><i class="mdi mdi-account-circle mdi-24px text-primary me-2"></i><span class="fw-bold">' + data + '</span></div>';
            }
        },
        { 
            data: 'nama',
            render: function(data) {
                if (!data || data === '-') return '<span class="text-muted fst-italic">-</span>';
                return '<span>' + data + '</span>';
            }
        },
        { 
            data: 'password',
            render: function(data) {
                if (!data || data === '-') return '<span class="text-muted">-</span>';
                return '<code class="bg-label-secondary p-1 rounded">' + data + '</code>';
            }
        },
        { 
            data: 'group',
            render: function(data) {
                if (!data || data === '-') return '<span class="text-muted">-</span>';
                let color = 'primary';
                if (data === 'ISOLIR') color = 'danger';
                return '<span class="badge rounded-pill bg-label-' + color + ' fw-bold">' + data + '</span>';
            }
        },
        { 
            data: 'limit',
            render: function(data) {
                if (!data || data === '-') return '<span class="text-muted">-</span>';
                return '<span class="badge bg-label-info">' + data + '</span>';
            }
        },
        { 
            data: 'ip',
            render: function(data) {
                if (!data || data === '-') return '<span class="text-muted">-</span>';
                return '<code class="text-primary">' + data + '</code>';
            }
        },
        { 
            data: 'status',
            render: function(data) {
                let color = 'secondary';
                let icon = 'mdi mdi-close-circle';
                if (data === 'Online') { color = 'success'; icon = 'mdi mdi-check-circle'; }
                if (data === 'Isolir') { color = 'danger'; icon = 'mdi mdi-alert-circle'; }
                return '<span class="badge bg-label-' + color + '"><i class="' + icon + ' me-1"></i>' + data + '</span>';
            }
        },
        { 
            data: 'action',
            orderable: false,
            searchable: false
        }
      ],
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      displayLength: 25,
      lengthMenu: [10, 25, 50, 100],
      language: {
        search: "",
        searchPlaceholder: "Cari username...",
        lengthMenu: "_MENU_ data per halaman",
        info: "Menampilkan _START_ - _END_ dari _TOTAL_ pelanggan",
        paginate: { previous: "&laquo;", next: "&raquo;" }
      }
    });

    // Filter events
    $('#filter-profile, #filter-status').on('change', function() {
        dt_user.draw();
    });

    $('#btn-reset-filter').on('click', function() {
        $('#filter-profile').val('').trigger('change.select2');
        $('#filter-status').val('Online');
        dt_user.draw();
    });

    // Sync All from Database
    $('#btn-sync-all').on('click', function() {
        const btn = $(this);
        Swal.fire({
            title: 'Sinkron Semua Pelanggan?',
            text: 'Semua data pelanggan dari database billing akan disinkronkan ke FreeRADIUS.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Sinkronkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Sinkronisasi...');
                $.ajax({
                    url: "{{ route('radius.user.sync-from-db') }}",
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire('Berhasil!', response.message, 'success');
                            dt_user.ajax.reload();
                        } else {
                            Swal.fire('Gagal', response.message, 'error');
                        }
                    },
                    error: function() { Swal.fire('Error', 'Gagal sinkronisasi.', 'error'); },
                    complete: function() { btn.prop('disabled', false).html('<i class="mdi mdi-database-sync me-1"></i> Sinkron Semua dari Database'); }
                });
            }
        });
    });

    // Sync Single User
    $(document).on('click', '.btn-sync-single', function() {
        const username = $(this).data('username');
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span>');
        $.ajax({
            url: '{{ route("radius.user.sync-single", ":username") }}'.replace(':username', encodeURIComponent(username)),
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                if (response.status) {
                    toastr.success(response.message);
                    dt_user.ajax.reload(null, false);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() { toastr.error('Gagal sinkronisasi user.'); },
            complete: function() { btn.prop('disabled', false).html('<i class="mdi mdi-sync"></i>'); }
        });
    });

    // Delete User
    $(document).on('click', '.btn-delete-user', function() {
        const username = $(this).data('username');
        Swal.fire({
            title: 'Hapus User?',
            text: 'User ' + username + ' akan dihapus dari RADIUS. Data billing tidak terpengaruh.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("radius.user.delete", ":username") }}'.replace(':username', encodeURIComponent(username)),
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire('Berhasil!', response.message, 'success');
                            dt_user.ajax.reload();
                        } else {
                            Swal.fire('Gagal', response.message, 'error');
                        }
                    },
                    error: function() { Swal.fire('Error', 'Gagal menghapus user.', 'error'); }
                });
            }
        });
    });
  });
</script>
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">FreeRADIUS /</span> Pelanggan
</h4>

{{-- Info --}}
<div class="alert alert-primary alert-dismissible d-flex align-items-center mb-4" role="alert">
    <i class="mdi mdi-information-outline me-2 mdi-24px"></i>
    <div>
        Data pelanggan diambil langsung dari <b>database FreeRADIUS</b>. Gunakan tombol <b>Sinkron</b> untuk menyelaraskan data dari database billing ke RADIUS.
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

{{-- Filter --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold"><i class="mdi mdi-filter-outline me-1"></i>Filter Paket</label>
                <select id="filter-profile" class="form-select select2">
                    <option value="">Semua Paket</option>
                    @foreach($profiles as $p)
                        <option value="{{ $p->groupname }}">{{ $p->groupname }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="mdi mdi-wifi me-1"></i>Status</label>
                <select id="filter-status" class="form-select">
                    <option value="" {{ request('status') == 'Semua' ? 'selected' : '' }}>Semua Status</option>
                    <option value="Online" {{ request('status') == 'Online' || !request()->has('status') ? 'selected' : '' }}>Online</option>
                    <option value="Offline" {{ request('status') == 'Offline' ? 'selected' : '' }}>Offline</option>
                    <option value="Isolir" {{ request('status') == 'Isolir' ? 'selected' : '' }}>Isolir</option>
                </select>
            </div>
            <div class="col-md-2">
                <button id="btn-reset-filter" class="btn btn-outline-secondary w-100">
                    <i class="mdi mdi-refresh me-1"></i> Reset
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h5 class="mb-0"><i class="mdi mdi-account-group me-1"></i> Daftar Pelanggan RADIUS</h5>
            <small class="text-muted">Data dari tabel radcheck, radusergroup, radreply, dan radacct</small>
        </div>
        <button type="button" id="btn-sync-all" class="btn btn-success">
            <i class="mdi mdi-database-sync me-1"></i> Sinkron Semua dari Database
        </button>
    </div>
    <div class="card-datatable table-responsive">
        <table id="table-radius-users" class="table table-hover border-top">
            <thead class="table-light">
                <tr>
                    <th>Username</th>
                    <th>Nama Pelanggan</th>
                    <th>Password</th>
                    <th>Profile / Group</th>
                    <th>Rate Limit</th>
                    <th>Alamat IP</th>
                    <th>Status</th>
                    <th style="width: 120px">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection
