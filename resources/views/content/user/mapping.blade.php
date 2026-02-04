@extends('layouts/layoutMaster')

@section('title', 'Data Pelanggan Mapping')

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
        "ajax": "{{ route('user.mapping.json') }}",
        displayLength: 20,
        lengthMenu: [10, 20, 35, 40, 50, 80, 100],
        language: {
          paginate: {
            next: '<i class="ri-arrow-right-s-line"></i>',
            previous: '<i class="ri-arrow-left-s-line"></i>'
          }
        },
        columns: [
          {
            data: null,
          },

          { data: 'name'},
          {
            data: 'id',
            name: 'action',
            orderable: false,
            searchable: false,
            render: function (data) {
              return `
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="mdi mdi-dots-vertical"></i>
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item fw-bold btn-edit" href="/user/edit/mapping/${data}">
                      <i class="mdi mdi-pencil-outline me-1"></i> Mapping
                    </a>
                    <a class="dropdown-item fw-bold btn-upload-foto" href="javascript:void(0);" data-id="${data}">
                      <i class="mdi mdi-camera-outline me-1"></i> Upload Foto
                    </a>
                  </div>
                </div>
              `;
            }
          },
          { data: 'wa'},
          { data: 'user_detail.tglDafatar'},
          { data: 'created_at'},
        ],
        order: [[4, 'desc']],
        columnDefs: [{
          className: 'control',
          //orderable: !1,
          targets: 0,
          searchable: !1,
          render: function() {
            return ''
          }
        }],

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
          <th>NAMA</th>
          <th>AKSI</th>
          <th>WA</th>
          <th>TGL DAFTAR</th>
          <th>TGL DIBUAT</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modal Upload Foto -->
<div class="modal fade" id="uploadFotoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
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