@extends('layouts/layoutMaster')

@section('title', 'Paket RADIUS')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')
<script>
  $(document).ready(function() {
    var dt_profile = $('#table-radius-profiles').DataTable({
      processing: true,
      serverSide: true,
      ajax: "{{ route('radius.profile.json') }}",
      columns: [
        { 
            data: 'groupname',
            render: function(data) {
                let color = 'success';
                if (data === 'ISOLIR') color = 'danger';
                return '<div class="d-flex align-items-center"><i class="mdi mdi-package-variant-closed mdi-24px text-' + color + ' me-2"></i><span class="fw-bold">' + data + '</span></div>';
            }
        },
        { 
            data: 'limit',
            render: function(data) {
                if (!data || data === '-') return '<span class="text-muted">-</span>';
                return '<span class="badge bg-label-info fs-6">' + data + '</span>';
            }
        },
        { 
            data: 'pool',
            render: function(data) {
                if (!data || data === '-') return '<span class="text-muted">-</span>';
                return '<span class="badge bg-label-primary">' + data + '</span>';
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
      language: {
        search: "",
        searchPlaceholder: "Cari paket...",
        lengthMenu: "_MENU_ data",
        info: "Menampilkan _START_ - _END_ dari _TOTAL_ paket",
        paginate: {
          previous: "&laquo;",
          next: "&raquo;"
        }
      }
    });

    // Handle Delete Profile
    $(document).on('click', '.btn-delete-profile', function() {
      var groupname = $(this).data('group');
      Swal.fire({
        title: 'Hapus Paket?',
        text: "Group " + groupname + " akan dihapus permanen dari RADIUS.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: '{{ route("radius.profile.delete", ":groupname") }}'.replace(':groupname', encodeURIComponent(groupname)),
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
              if (response.status) {
                Swal.fire('Berhasil!', response.message, 'success');
                dt_profile.ajax.reload();
              } else {
                Swal.fire('Gagal', response.message, 'error');
              }
            },
            error: function() {
              Swal.fire('Error', 'Gagal menghapus paket.', 'error');
            }
          });
        }
      });
    });
  });
</script>
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">FreeRADIUS /</span> Paket
</h4>

{{-- Info Alert --}}
<div class="alert alert-success alert-dismissible d-flex align-items-center mb-4" role="alert">
    <i class="mdi mdi-information-outline me-2 mdi-24px"></i>
    <div>
        Halaman ini menampilkan daftar <b>Group/Paket</b> yang terdaftar di database FreeRADIUS beserta atribut kecepatan dan IP Pool-nya.
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="mdi mdi-package-variant me-1"></i> Daftar Paket RADIUS</h5>
                <small class="text-muted">Data group/profile yang terdaftar di database FreeRADIUS</small>
            </div>
            <div class="card-datatable table-responsive">
                <table id="table-radius-profiles" class="table table-hover border-top">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Paket (Group)</th>
                            <th>Rate Limit</th>
                            <th>IP Pool</th>
                            <th style="width: 80px">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
