@extends('layouts/layoutMaster')

@section('title', 'Area')

@section('page-script')
<script>
  let table;
let currentId = null;

$(document).ready(function() {
  // === INIT DATATABLE ===
  table = $('#table').DataTable({
    processing: true,
    serverSide: true,
    ajax: "{{ route('area.json') }}",
    columns: [
      {
        data: null,
        render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1,
        className: "text-center",
      },
      {
        data: 'id',
        orderable: false,
        searchable: false,
        render: (data, type, row) => `
          <div class="dropdown text-center">
            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
              <i class="mdi mdi-dots-vertical"></i>
            </button>
            <div class="dropdown-menu">
              <a class="dropdown-item fw-bold" href="javascript:void(0);" onclick="openEditModal('${row.id}')">
                <i class="mdi mdi-pencil-outline me-1"></i> Edit
              </a>
              <a class="dropdown-item text-danger fw-bold" href="javascript:void(0);" onclick="deleteArea('${row.id}')">
                <i class="mdi mdi-trash-can-outline me-1"></i> Delete
              </a>
            </div>
          </div>
        `
      },
      { data: 'name', className: "fw-semibold" },
      { data: 'address' },
      { data: 'code_area', className: "text-center" },
    ]
  });
});

// === TAMBAH AREA ===
function saveAdd() {
  const data = {
    name: $('#add_name').val(),
    address: $('#add_address').val(),
    code_area: $('#add_code_area').val(),
    _token: '{{ csrf_token() }}'
  };

  $.ajax({
    url: "{{ route('area.store') }}",
    method: 'POST',
    data: data,
    success: function(response) {
      $('#addModal').modal('hide');
      $('#addForm')[0].reset(); // Reset form
      table.ajax.reload(null, false);
      toastr.success(response.message || 'Data berhasil disimpan.');
    },
    error: function(xhr) {
      let message = 'Terjadi kesalahan.';
      if (xhr.responseJSON && xhr.responseJSON.message) {
        message = xhr.responseJSON.message;
      } else if (xhr.responseJSON && xhr.responseJSON.errors) {
        const errors = xhr.responseJSON.errors;
        message = Object.values(errors).flat().join(', ');
      }
      toastr.error(message);
    }
  });
}

// === EDIT AREA (langsung dari DataTables) ===
function openEditModal(id) {
  // Ambil data baris dari DataTables berdasarkan ID
  const rowData = table.rows().data().toArray().find(r => r.id === id);

  if (!rowData) {
    toastr.error("Data tidak ditemukan di tabel.");
    return;
  }

  // Simpan ID untuk proses update
  currentId = id;

  // Isi form modal
  $('#edit_name').val(rowData.name);
  $('#edit_address').val(rowData.address);
  $('#edit_code_area').val(rowData.code_area);

  // Tampilkan modal edit
  $('#editModal').modal('show');
}

function saveEdit() {
  const data = {
    name: $('#edit_name').val(),
    address: $('#edit_address').val(),
    code_area: $('#edit_code_area').val(),
    _token: '{{ csrf_token() }}'
  };

  $.ajax({
    url: `{{ url('/area') }}/${currentId}/update`,
    method: 'POST',
    data: data,
    success: function(response) {
      $('#editModal').modal('hide');
      table.ajax.reload(null, false);
      toastr.success(response.message || 'Data berhasil diperbarui.');
    },
    error: function(xhr) {
      let message = 'Terjadi kesalahan.';
      if (xhr.responseJSON && xhr.responseJSON.message) {
        message = xhr.responseJSON.message;
      } else if (xhr.responseJSON && xhr.responseJSON.errors) {
        const errors = xhr.responseJSON.errors;
        message = Object.values(errors).flat().join(', ');
      }
      toastr.error(message);
    }
  });
}

// === DELETE AREA ===
function deleteArea(id) {
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
      $.ajax({
        url: `{{ url('/area') }}/${id}/delete`,
        method: 'DELETE',
        data: {
          _token: '{{ csrf_token() }}'
        },
        success: function(response) {
          table.ajax.reload(null, false);
          toastr.success(response.message || 'Data berhasil dihapus.');
        },
        error: function(xhr) {
          let message = 'Terjadi kesalahan saat menghapus data.';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
          }
          toastr.error(message);
        }
      });
    }
  });
}
</script>
@endsection

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Daftar Area</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
      <i class="mdi mdi-plus"></i> Tambah Area
    </button>
  </div>

  <div class="card-datatable table-responsive pt-0">
    <table id="table" class="datatables-basic table table-bordered">
      <thead>
        <tr>
          <th>NO</th>
          <th>AKSI</th>
          <th>NAMA</th>
          <th>ALAMAT</th>
          <th>KODE AREA</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title text-white" id="addModalLabel">Tambah Area Baru</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="addForm">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nama Area</label>
            <input type="text" class="form-control" id="add_name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Alamat</label>
            <input type="text" class="form-control" id="add_address" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Kode Area</label>
            <input type="text" class="form-control" id="add_code_area" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-success" onclick="saveAdd()">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDIT -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title text-white" id="editModalLabel">Edit Area</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="editForm">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nama Area</label>
            <input type="text" class="form-control" id="edit_name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Alamat</label>
            <input type="text" class="form-control" id="edit_address" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Kode Area</label>
            <input type="text" class="form-control" id="edit_code_area" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" onclick="saveEdit()">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
