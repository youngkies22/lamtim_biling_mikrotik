@extends('layouts/layoutMaster')

@section('title', 'Data ODC')

@section("vendor-style")
@endsection


@section("vendor-script")

@endsection

@section('page-script')
<script>
  let table;
    $(document).ready(function() {
     table = $('#table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('odc.json') }}",
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
          data: 'kode',
          render: function(data, type, row) {
            if (row.portSisa === 0) {
              return '<span class="text-danger">' + data + '</span>';
            }
            return data;
          }
        },
        {
          data: 'nama',
          render: function(data, type, row) {
            if (row.portSisa === 0) {
              return '<span class="text-danger">' + data + '</span>';
            }
            return data;
          }
        },
        { data: 'olt'},
        { data: 'portOlt'},
        { data: 'port'},
        { data: 'portSisa'},
        { data: 'created_at'},
        {
          data: 'id',
          name: 'action',
          orderable: false,
          searchable: false,
          render: function (data, type, row) {
            return `
              <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="mdi mdi-dots-vertical"></i>
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item fw-bold btn-edit" href="javascript:void(0);"
                    data-id="${data}" data-nama="${row.nama}" data-kode="${row.kode}"
                    data-olt="${row.idOlt}" data-portolt="${row.portOlt}"
                    data-port="${row.port}" data-sisa="${row.portSisa}">
                    <i class="mdi mdi-pencil-outline me-1"></i> Edit
                  </a>
                   <a class="dropdown-item btn-delete text-danger fw-bold" href="javascript:void(0);" data-id="${data}">
                    <i class="mdi mdi-trash-can-outline me-1"></i> Delete
                  </a>
                </div>
              </div>
            `;
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
         buttons: [
          {
              text: '<i class="mdi mdi-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Add</span>',
              className: "create-new btn btn-primary waves-effect waves-light",
              action: function () {
                const offCanvasEl = document.getElementById("add-new-record");
                const bsOffcanvas = new bootstrap.Offcanvas(offCanvasEl);

                document.getElementById("form-add-new-record").reset(); // cukup ini
                bsOffcanvas.show();
              }
            }
          ],
        responsive: {
          details: {
            display: $.fn.dataTable.Responsive.display.modal({
              header: function (e) {
                return "Details of " + e.data().nama;
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
    $("div.head-label").html('<h5 class="card-title mb-0">Data ODC</h5>');
    $('#form-add-new-record').on('submit', function (e) {
          e.preventDefault();
          const form = this;
          const recordId = form.querySelector('[name="recordId"]')?.value;
          const url = recordId
            ? `/odc/${recordId}`   // Untuk edit (PUT)
            : `/odc`;              // Untuk tambah baru (POST)
          const method = 'POST';
            const formData = new FormData(form);
          if (recordId) {
            formData.append('_method', 'PUT');
          }

          $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
              // Reset form & tutup offcanvas
              form.reset();
              if (form.querySelector('[name="recordId"]')) {
                form.querySelector('[name="recordId"]').remove(); // bersihkan ID agar jadi Add lagi
              }
              bootstrap.Offcanvas.getInstance(document.getElementById("add-new-record")).hide();

              // Reload datatable
              $('#table').DataTable().ajax.reload(null, false);

              // Opsional: tampilkan notifikasi
              toastr.success('Data berhasil disimpan');
            },
            error: function (xhr) {
              let message = 'Terjadi kesalahan.';
              if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
              }
              toastr.error(message);
            }
          });
        });
    });

    $(document).on('click', '.btn-delete', function () {
      const id = $(this).data('id'); // ID terenkripsi

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
            url: `/odc/${id}`,
            type: 'DELETE',
            data: {
              _token: '{{ csrf_token() }}'
            },
            success: function (response) {
              // TOASTR SUCCESS
              toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-top-right",
                timeOut: 3000
              };
              toastr.success(response.message || 'Data berhasil dihapus.', 'Berhasil!');

              table.ajax.reload(null, false); // false untuk tidak reset ke halaman pertama
            },
            error: function () {
              // TOASTR ERROR
              toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-top-right",
                timeOut: 3000
              };
              const errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus data.';
              toastr.error(errorMsg, 'Gagal!');
            }
          });
        }
      });
    });

    $(document).on("click", ".btn-edit", function () {
      $('.dtr-bs-modal').remove();
      $('.modal-backdrop').remove();
      const offCanvasEl = document.getElementById("add-new-record");
      const bsOffcanvas = new bootstrap.Offcanvas(offCanvasEl);
      const form = document.getElementById("form-add-new-record");

      // Ambil data dari atribut
      const id = $(this).data("id");
      const nama = $(this).data("nama");
      const kode = $(this).data("kode");
      const port = $(this).data("port");
      const sisa = $(this).data("sisa");
      const olt = $(this).data("olt");
      const portolt = $(this).data("portolt");

      // Isi form
      form.reset(); // Reset dulu
      form.querySelector('[name="nama"]').value = nama;
      form.querySelector('[name="kode"]').value = kode;
      form.querySelector('[name="port"]').value = port;
      form.querySelector('[name="sisa"]').value = sisa;
      form.querySelector('[name="olt"]').value = olt;
      form.querySelector('[name="portolt"]').value = portolt;


      // Tambahkan hidden input untuk ID (jika belum ada)
      if (!form.querySelector('[name="recordId"]')) {
        const hidden = document.createElement("input");
        hidden.type = "hidden";
        hidden.name = "recordId";
        form.appendChild(hidden);
      }
      form.querySelector('[name="recordId"]').value = id;

      // Ganti title modal
      offCanvasEl.querySelector(".offcanvas-title").innerText = "Edit Record";

      // Tampilkan offcanvas
      bsOffcanvas.show();
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
          <th>KODE</th>
          <th>NAMA</th>
          <th>OLT</th>
          <th>PORT SFP OLT</th>
          <th>PORT TOTAL</th>
          <th>PORT TERSEDIA</th>
          <th>CREAD</th>
          <th>AKSI</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
<!-- Modal to add new record -->
<div class="offcanvas offcanvas-end" id="add-new-record">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title" id="exampleModalLabel">New Data</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body flex-grow-1">
    <form class="add-new-record pt-0 row g-3" id="form-add-new-record" onsubmit="return false">
      <div class="col-sm-12">
        <div class="input-group input-group-merge">
          <span id="basicFullname2" class="input-group-text"><i class="mdi mdi-account-outline"></i></span>
          <div class="form-floating form-floating-outline">
            <input type="text" id="nama" class="form-control dt-full-name" name="nama" autocomplete="off" />
            <label for="basicFullname">NAMA</label>
          </div>
        </div>
      </div>
      <div class="col-sm-12">
        <div class="input-group input-group-merge">
          <span id="basicPost2" class="input-group-text"><i class='mdi mdi-stack-overflow'></i></span>
          <div class="form-floating form-floating-outline">
            <input type="text" id="kode" name="kode" class="form-control dt-post" autocomplete="off" />
            <label for="basicPost">KODE</label>
          </div>
        </div>
      </div>

      <div class="col-sm-12">
        <div class="input-group input-group-merge">
          <span id="basicSalary2" class="input-group-text"><i class='mdi mdi-usb-port'></i></span>
          <div class="form-floating form-floating-outline">
            <input type="number" id="port" name="port" class="form-control " autocomplete="off" />
            <label for="basicSalary">TOTAL PORT</label>
          </div>
        </div>
      </div>
      <div class="col-sm-12">
        <div class="input-group input-group-merge">
          <span id="basicSalary2" class="input-group-text"><i class='mdi mdi-cable-data'></i></span>
          <div class="form-floating form-floating-outline">
            <input type="number" id="sisa" name="sisa" class="form-control " autocomplete="off" />
            <label for="basicSalary">TERSEDIA PORT</label>
          </div>
        </div>
      </div>
      <div class="col-sm-12">
        <div class="input-group input-group-merge">
          <span id="basicSalary2" class="input-group-text"><i class='mdi mdi-cable-data'></i></span>
          <div class="form-floating form-floating-outline">
            <input type="number" id="portolt" name="portolt" class="form-control " autocomplete="off" />
            <label for="basicSalary">DI PORT OLT KE</label>
          </div>
        </div>
      </div>

      <div class="col-sm-12">
        <div class="input-group input-group-merge">
          <span id="basicSalary2" class="input-group-text"><i class='mdi mdi-server-network'></i></span>
          <div class="form-floating form-floating-outline">
            <select name="olt" class="form-select">
              @foreach (Helper::getOlt() as $val)
              <option value="{{ $val->id }}">{{ $val->nama }}</option>
              @endforeach
            </select>
            <label for="basicSalary">OLT</label>
          </div>
        </div>
      </div>
      <div class="col-sm-12">
        <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">Submit</button>
        <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
      </div>
    </form>

  </div>
</div>
@endsection