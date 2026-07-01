@extends('layouts/layoutMaster')

@section('title', 'Data Pelanggan')

@section("vendor-style")
@endsection


@section("vendor-script")

@endsection

@section('page-script')
<script>
  const baseUrlRoute = "/user"; // Variabel global
  let table;
    $(document).ready(function() {
     table = $('#table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('user.json') }}",
       columns: [
          {
            data: null,
          },
          {
            "data": null,
            "render": function(data, type, row, meta) {
              return meta.row + meta.settings._iDisplayStart + 1;
            }
          },
          {
            data: 'id',
            name: 'action',
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
              let actions = '';
              @if(auth()->user()->hasRole(1,2,4))
              actions += `<a class="dropdown-item fw-bold" href="/user/${data}/edit">
                    <i class="mdi mdi-pencil-outline me-1"></i> Edit
                  </a>`;
              @endif
              @if(auth()->user()->hasRole(1,2))
              actions += `<a class="dropdown-item btn-delete text-danger fw-bold" href="javascript:void(0);" data-id="${data}">
                    <i class="mdi mdi-trash-can-outline me-1"></i> Delete
                  </a>`;
              @endif
              if (!actions) return '-';
              return `
              <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="mdi mdi-dots-vertical"></i>
                </button>
                <div class="dropdown-menu">
                  ${actions}
                </div>
              </div>
            `;
            }
          },
          { 
            data: 'name',
            render: function(data, type, row) {
              return data || '<span class="text-muted">-</span>';
            }
          },
          { 
            data: 'wa',
            render: function(data, type, row) {
              return data || '<span class="text-muted">-</span>';
            }
          },
          { 
            data: 'tglDafatar',
            render: function(data, type, row) {
              return data || '<span class="text-muted">-</span>';
            }
          },
          { 
            data: 'mikrotik',
            render: function(data, type, row) {
              if (type === 'display') {
                return data || '<span class="text-muted">-</span>';
              }
              return data;
            }
          },
          { 
            data: 'paket',
            render: function(data, type, row) {
              if (type === 'display') {
                return data || '<span class="text-muted">-</span>';
              }
              return data;
            }
          },
          { 
            data: 'googleMap',
            render: function(data, type, row) {
              if (type === 'display') {
                return data || '<span class="text-muted">-</span>';
              }
              return data;
            }
          },
          { 
            data: 'foto',
            render: function(data, type, row) {
              if (type === 'display') {
                return data || '<span class="text-muted">-</span>';
              }
              return data;
            }
          }

        ],

        columnDefs: [{
          className: 'control',
          //orderable: !1,
          targets: 0,
          searchable: !1,
          render: function() {
            return ''
          }
        }],

        destroy: !0,
        dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>>' +
         '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>' +
         't' +
         '<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        displayLength: 20,
        lengthMenu: [10, 20, 35, 40, 50, 80, 100],
        language: {
          paginate: {
            next: '<i class="ri-arrow-right-s-line"></i>',
            previous: '<i class="ri-arrow-left-s-line"></i>'
          }
        },
        buttons: [],
        initComplete: function() {
          // If dom still contains 'B' this will remove the (empty) buttons container so no Copy/HTML/PDF tampil
          $('.dt-buttons').remove();
        },
        responsive: {
          details: {
            display: $.fn.dataTable.Responsive.display.modal({
              header: function (e) {
                return "Details Data";
              }
            }),
            type: "column",
            renderer: function (api, rowIdx, columns) {
              var data = $.map(columns, function (col) {
                return col.title ? '<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '"><td>' + col.title + ':</td> <td>' + col.data + '</td></tr>' : '';
              }).join('');
              return data ? $('<table class="table"/><tbody />').append(data) : false;
            }
          }
        }
      });
      $("div.head-label").html('<h5 class="card-title mb-0">Data Pelanggan</h5>');
      $("div.dt-action-buttons").html('<a href="{{ route('user.export') }}" class="btn btn-success"><i class="mdi mdi-file-excel-outline me-1"></i> Download Excel</a>');

      // Delete functionality
      $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();

        const userId = $(this).data('id');
        const userName = $(this).closest('tr').find('td:nth-child(4)').text(); // Get user name from table

         Swal.fire({
        title: "Yakin ingin menghapus?",
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "YA, HAPUS!",
        cancelButtonText: "BATAL",
        buttonsStyling: false,
        customClass: {
          confirmButton: "btn btn-primary me-2",
          cancelButton: "btn btn-outline-danger"
        }
      }).then((result) => {
          if (result.isConfirmed) {
            // Send delete request
            $.ajax({
              url: `${baseUrlRoute}/${userId}`,
              type: 'DELETE',
              data: {
                _token: '{{ csrf_token() }}'
              },

              success: function(response) {
                if (response.error === 'false' || response.error === false) {
                  // Reload table
                  table.ajax.reload();

                  // Show success message
                  Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message || 'Data berhasil dihapus',
                    showConfirmButton: false,
                    timer: 2000,
                    customClass: {
                      popup: 'swal2-show',
                      backdrop: 'swal2-backdrop-show',
                      icon: 'swal2-icon-show'
                    }
                  });
                } else {
                  // Show error message
                  Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: response.message || 'Gagal menghapus data',
                    customClass: {
                      confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                  });
                }
              },
              error: function(xhr, status, error) {
                let errorMessage = 'Terjadi kesalahan saat menghapus data';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                  errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                  errorMessage = 'Data tidak ditemukan';
                } else if (xhr.status === 500) {
                  errorMessage = 'Kesalahan server internal';
                }

                // Show error message
                Swal.fire({
                  icon: 'error',
                  title: 'Error!',
                  text: errorMessage,
                  customClass: {
                    confirmButton: 'btn btn-primary'
                  },
                  buttonsStyling: false
                });

                console.error('Delete error:', error);
                console.error('Response:', xhr.responseText);
              }
            });
          }
        });
      });

      // Upload Foto
      let currentUserId = null;
      
      $(document).on('click', '.btn-upload-foto', function() {
        currentUserId = $(this).data('id');
        $('#uploadFotoModal').modal('show');
        $('#fotos').val('');
      });

      $('#formUploadFoto').on('submit', function(e) {
        e.preventDefault();
        
        if (!$('#fotos').val()) {
          toastr.error('Pilih foto terlebih dahulu!');
          return;
        }

        const formData = new FormData();
        formData.append('idUser', currentUserId);
        
        const files = $('#fotos')[0].files;
        for (let i = 0; i < files.length; i++) {
          formData.append('fotos[]', files[i]);
        }

        $.ajax({
          url: "{{ route('foto.store') }}",
          method: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function(response) {
            toastr.success(response.message || 'Foto berhasil diupload!');
            $('#fotos').val('');
            $('#uploadFotoModal').modal('hide');
            table.ajax.reload();
          },
          error: function(xhr) {
            let message = 'Terjadi kesalahan saat upload foto.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
              message = xhr.responseJSON.message;
            }
            toastr.error(message);
          }
        });
      });
    });

</script>
@endsection

@section('content')


<!-- DataTable with Buttons -->
<div class="card">
  <div class="card-datatable table-responsive pt-0">
    <table id="table" class="datatables-basic table table-bordered">
      <thead>
        <tr>
          <th>+</th>
          <th>NO</th>
          <th>AKSI</th>
          <th>NAMA</th>
          <th>WA</th>
          <th>TGL DAFTAR</th>
          <th>MIKROTIK</th>
          <th>PAKET</th>
          <th>GOOGLE MAP</th>
          <th>FOTO</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modal Upload Foto -->
<div class="modal fade" id="uploadFotoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Upload Foto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formUploadFoto">
          <div class="mb-3">
            <label for="fotos" class="form-label">Pilih Foto (Bisa lebih dari satu)</label>
            <input type="file" class="form-control" id="fotos" name="fotos[]" multiple accept="image/*">
            <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 5MB per file.</small>
          </div>
          <button type="submit" class="btn btn-primary">
            <i class="mdi mdi-upload"></i> Upload Foto
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection
