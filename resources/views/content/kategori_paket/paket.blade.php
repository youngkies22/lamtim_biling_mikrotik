@extends('layouts/layoutMaster')

@section('title', 'Data Paket Internet')

@section("vendor-style")
@endsection


@section("vendor-script")

@endsection

@section('page-script')
<script>
  const baseUrlRoute = "/paket"; // Variabel global
  let table;
    $(document).ready(function() {
     table = $('#table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('paket.json') }}",
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
        { data: 'kode'},
        { data: 'nama'},
        { data: 'price'},
        { data: 'speed_limit'},
        { data: 'kategori'},
        { data: 'description'},
        {
          data: 'isActive',
          render: function(data, type, row) {
            if (data == 1) {
              return '<span class="badge bg-success">Aktif</span>';
            } else {
              return '<span class="badge bg-danger">Tidak Aktif</span>';
            }
          }
        },
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
                    data-id="${data}" data-nama="${row.nama}" data-description="${row.description}"
                    data-kode="${row.kode}" data-price="${row.price}" data-idkategori="${row.idKategori}"
                    data-isactive="${row.isActive}" data-speed_limit="${row.speed_limit || ''}"
                    data-ip_pool="${row.ip_pool || ''}" data-address_list="${row.address_list || ''}"
                    data-is_burst="${row.is_burst || 0}" data-burst_rate="${row.burst_rate || ''}"
                    data-burst_threshold="${row.burst_threshold || ''}" data-burst_time="${row.burst_time || ''}"
                    data-priority="${row.priority || 8}">
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
    $("div.head-label").html(`
      <div class="d-flex flex-column">
        <h5 class="card-title mb-1">Data Paket</h5>
        <small class="text-muted">Pastikan kode paket sama dengan profile yg ada di mikrotik</small>
      </div>
    `);
    $('#form-add-new-record').on('submit', function (e) {
          e.preventDefault();
          const form = this;
          const recordId = form.querySelector('[name="recordId"]')?.value;
          const url = recordId
            ? `${baseUrlRoute}/${recordId}`   // Untuk edit (PUT)
            : `${baseUrlRoute}`;              // Untuk tambah baru (POST)
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
              toastr.success(response.message);
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
            url: `${baseUrlRoute}/${id}`,
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
      const price = $(this).data("price");
      const description = $(this).data("description");
      const speed_limit = $(this).data("speed_limit");
      const is_burst = $(this).data("is_burst");
      const burst_rate = $(this).data("burst_rate");
      const burst_threshold = $(this).data("burst_threshold");
      const burst_time = $(this).data("burst_time");
      const priority = $(this).data("priority");
      const ip_pool = $(this).data("ip_pool");
      const address_list = $(this).data("address_list");
      const isactive = $(this).data("isactive");
      const idkategori = $(this).data("idkategori");

      // Isi form
      form.reset(); // Reset dulu
      form.querySelector('[name="nama"]').value = nama;
      form.querySelector('[name="kode"]').value = kode;
      form.querySelector('[name="price"]').value = price;
      form.querySelector('[name="description"]').value = description;
      form.querySelector('[name="speed_limit"]').value = speed_limit;
      form.querySelector('[name="is_burst"]').value = is_burst;
      form.querySelector('[name="burst_rate"]').value = burst_rate;
      form.querySelector('[name="burst_threshold"]').value = burst_threshold;
      form.querySelector('[name="burst_time"]').value = burst_time;
      form.querySelector('[name="priority"]').value = priority;
      
      // Toggle visibility based on is_burst
      if (is_burst == 1) {
          $('#section-burst').show();
          $('#is_burst').prop('checked', true);
      } else {
          $('#section-burst').hide();
          $('#is_burst').prop('checked', false);
      }

      form.querySelector('[name="ip_pool"]').value = ip_pool;
      form.querySelector('[name="address_list"]').value = address_list;
      form.querySelector('[name="isActive"]').value = isactive;
      form.querySelector('[name="idKategori"]').value = idkategori;


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

    $(document).on('click', '.btn-speed-pick', function() {
        const speed = $(this).data('speed');
        $('#speed_limit').val(speed);
    });

    $(document).on('change', '#is_burst', function() {
        if ($(this).is(':checked')) {
            $('#section-burst').slideDown();
            $('input[name="is_burst"]').val(1);
        } else {
            $('#section-burst').slideUp();
            $('input[name="is_burst"]').val(0);
        }
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
          <th>HARGA</th>
          <th>LIMIT</th>
          <th>KATEGORI</th>
          <th>KETERANGAN</th>
          <th>STATUS</th>
          <th>CREAD</th>
          <th>AKSI</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
<!-- Modal to add new record -->
<div class="offcanvas offcanvas-end" id="add-new-record" style="width: 650px !important;">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title" id="exampleModalLabel">New Data</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body flex-grow-1">
    <form class="add-new-record pt-0 row g-3" id="form-add-new-record" onsubmit="return false">
      <div class="col-sm-12">
        <label class="form-label fw-bold"><i class="mdi mdi-information-outline me-1"></i>Informasi Dasar</label>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="form-floating form-floating-outline">
              <select name="idKategori" class="form-select border-primary">
                @foreach (Helper::getKategori() as $val)
                <option value="{{ $val->id }}">{{ $val->nama }}</option>
                @endforeach
              </select>
              <label>Kategori Paket</label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-floating form-floating-outline">
              <input type="text" id="kode" name="kode" class="form-control border-primary" autocomplete="off" placeholder="KODE" />
              <label>KODE PAKET (Radius Group)</label>
            </div>
          </div>
          <div class="col-md-8">
            <div class="form-floating form-floating-outline">
              <input type="text" id="nama" name="nama" class="form-control" autocomplete="off" placeholder="NAMA" />
              <label>NAMA PAKET</label>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-floating form-floating-outline">
              <input type="number" id="price" name="price" class="form-control" autocomplete="off" placeholder="HARGA" />
              <label>HARGA (Rp)</label>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-12 mt-4">
        <label class="form-label fw-bold text-primary"><i class="mdi mdi-tune-vertical me-1"></i>Konfigurasi Teknis (MikroTik)</label>
        <div class="card bg-label-primary border-0 shadow-none mb-3">
            <div class="card-body p-3">
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="form-floating form-floating-outline">
                          <input type="text" id="speed_limit" name="speed_limit" class="form-control bg-white" placeholder="10M/10M" autocomplete="off" />
                          <label class="fw-bold">RATE LIMIT DASAR (RX/TX)</label>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            <button type="button" class="btn btn-xs btn-primary btn-speed-pick" data-speed="1M/1M">1M</button>
                            <button type="button" class="btn btn-xs btn-primary btn-speed-pick" data-speed="5M/5M">5M</button>
                            <button type="button" class="btn btn-xs btn-primary btn-speed-pick" data-speed="10M/10M">10M</button>
                            <button type="button" class="btn btn-xs btn-primary btn-speed-pick" data-speed="20M/20M">20M</button>
                            <button type="button" class="btn btn-xs btn-primary btn-speed-pick" data-speed="30M/30M">30M</button>
                            <button type="button" class="btn btn-xs btn-primary btn-speed-pick" data-speed="50M/50M">50M</button>
                            <button type="button" class="btn btn-xs btn-primary btn-speed-pick" data-speed="100M/100M">100M</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                    <select id="ip_pool" name="ip_pool" class="form-select border-primary">
                        <option value="">-- TANPA POOL --</option>
                        @foreach($ipPools as $pool)
                            <option value="{{ $pool->name }}">{{ $pool->name }} ({{ $pool->ranges }})</option>
                        @endforeach
                    </select>
                    <label>
                        IP POOL (RADIUS)
                        <a href="javascript:void(0);" class="ms-1 text-primary" data-bs-toggle="modal" data-bs-target="#modalInfoIp">
                            <i class="mdi mdi-information-outline"></i>
                        </a>
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                    <select id="address_list" name="address_list" class="form-select border-primary">
                        <option value="">-- TANPA LIST --</option>
                        @foreach($addressLists as $list)
                            <option value="{{ $list->name }}">{{ $list->name }}</option>
                        @endforeach
                    </select>
                    <label>
                        ADDRESS LIST (RADIUS)
                        <a href="javascript:void(0);" class="ms-1 text-primary" data-bs-toggle="modal" data-bs-target="#modalInfoIp">
                            <i class="mdi mdi-information-outline"></i>
                        </a>
                    </label>
                </div>
            </div>
        </div>
      </div>

      <div class="col-sm-12 mt-4">
        <div class="card border-primary">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h6 class="mb-0 fw-bold text-primary"><i class="mdi mdi-flash-circle me-1"></i>ADVANCED BURST MODE</h6>
                <small>Gunakan ini untuk fitur akselerasi kecepatan sementara</small>
              </div>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input scale-150" type="checkbox" id="is_burst">
                <input type="hidden" name="is_burst" value="0">
              </div>
            </div>

            <div id="section-burst" style="display: none;">
                <hr class="my-3">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted uppercase">Burst Rate (Max)</label>
                    <div class="form-floating form-floating-outline">
                      <input type="text" name="burst_rate" class="form-control" placeholder="40M/40M" />
                      <label>Contoh: 40M/40M</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted uppercase">Burst Threshold</label>
                    <div class="form-floating form-floating-outline">
                      <input type="text" name="burst_threshold" class="form-control" placeholder="10M/10M" />
                      <label>Contoh: 10M/10M</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted uppercase">Burst Time (Detik)</label>
                    <div class="form-floating form-floating-outline">
                      <input type="text" name="burst_time" class="form-control" placeholder="60/60" />
                      <label>Contoh: 60/60</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted uppercase">Antrian Priority</label>
                    <div class="form-floating form-floating-outline">
                      <select name="priority" class="form-select">
                        @for($i=1; $i<=8; $i++)
                          <option value="{{ $i }}" {{ $i == 8 ? 'selected' : '' }}>Priority {{ $i }}</option>
                        @endfor
                      </select>
                      <label>Pilih Prioritas</label>
                    </div>
                  </div>
                </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-12 mt-4">
          <div class="form-floating form-floating-outline mb-3">
            <textarea id="description" name="description" class="form-control" style="height: 80px;" placeholder="KETERANGAN"></textarea>
            <label>KETERANGAN PAKET</label>
          </div>
          <div class="form-floating form-floating-outline">
            <select name="isActive" class="form-select">
              <option value="1">AKTIF</option>
              <option value="0">NON-AKTIF</option>
            </select>
            <label>STATUS PAKET</label>
          </div>
      </div>

      <div class="col-sm-12 mt-4 pt-2 border-top">
        <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1 shadow w-100 mb-2">SIMPAN PERUBAHAN PAKET</button>
        <button type="reset" class="btn btn-outline-secondary w-100" data-bs-dismiss="offcanvas">BATAL</button>
      </div>
    </form>

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