@extends('layouts/layoutMaster')

@section('title', 'Data Tagihan')

@section('vendor-style')
@endsection

@section('vendor-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
@endsection

@section('page-script')
<script>
  // Define routes for JavaScript
  const routes = {
    show: "{{ route('tagihan.show', ':id') }}",
    edit: "{{ route('tagihan.edit', ':id') }}"
  };

  let table;
  $(document).ready(function() {
    table = $('#table').DataTable({
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "{{ route('tagihan.json') }}",
        data: function(d) {
          d.bulan = $('#filter-bulan').val();
        }
      },
      columns: [{
          data: null
        }, // untuk responsive
        {
          data: null,
          render: function(data, type, row, meta) {
            return meta.row + meta.settings._iDisplayStart + 1;
          }
        },
        {
          data: 'noTagihan',
          title: 'No. Tagihan'
        },
        {
          data: 'paket_nama',
          title: 'Paket'
        },
        {
          data: 'bulan',
          title: 'Bulan'
        },
        {
          data: 'tahun',
          title: 'Tahun'
        },
        {
          data: 'status_bayar',
          render: function(data) {
            if (data === 'PAID' || data === 'Paid') {
              return '<span class="badge bg-success">Lunas</span>';
            } else if (data === 'UNPAID' || data === 'Unpaid') {
              return '<span class="badge bg-warning">Belum Lunas</span>';
            } else {
              return '<span class="badge bg-danger">Gagal</span>';
            }
          }
        },
        {
          data: 'paket_harga',
          render: function(data) {
            return new Intl.NumberFormat('id-ID', {
              style: 'currency',
              currency: 'IDR'
            }).format(data);
          }
        },
        {
          data: 'total',
          render: function(data) {
            return new Intl.NumberFormat('id-ID', {
              style: 'currency',
              currency: 'IDR'
            }).format(data);
          }
        },
        {
          data: 'tglJatuhTempo',
          render: function(data) {
            return moment(data).format('DD-MM-YYYY');
          }
        },
        {
          data: 'created_at',
          title: 'Dibuat'
        },
        {
          data: 'id',
          name: 'action',
          orderable: false,
          searchable: false,
          render: function(data, type, row) {
            // Replace :id with actual ID in the route URLs
            const showUrl = routes.show.replace(':id', data);
            const editUrl = routes.edit.replace(':id', data);

            return `
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                <i class="mdi mdi-dots-vertical"></i>
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item fw-bold" href="${showUrl}">
                  <i class="mdi mdi-eye-outline me-1"></i> Detail
                </a>
                ${(row.status === 'UNPAID' || row.status === 'Unpaid') ? `
                <a class="dropdown-item fw-bold btn-edit" href="${editUrl}">
                  <i class="mdi mdi-pencil-outline me-1"></i> Edit
                </a>
                <a class="dropdown-item btn-pay text-success fw-bold" href="javascript:void(0);" data-id="${data}">
                  <i class="mdi mdi-cash me-1"></i> Bayar
                </a>
                <a class="dropdown-item btn-delete text-danger fw-bold" href="javascript:void(0);" data-id="${data}">
                  <i class="mdi mdi-trash-can-outline me-1"></i> Delete
                </a>
                ` : ''}
              </div>
            </div>
            `;
          }
        }
      ],

      columnDefs: [{
        className: 'control',
        targets: 0,
        searchable: false,
        render: function() {
          return ''
        }
      }],

      destroy: true,
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
      buttons: [{
        text: '<span class="d-none d-sm-inline-block">Download</span>',
        className: "create-new btn btn-primary waves-effect waves-light",
        action: function() {
          const offCanvasEl = document.getElementById("add-new-record");
          const bsOffcanvas = new bootstrap.Offcanvas(offCanvasEl);

          document.getElementById("form-add-new-record").reset();
          bsOffcanvas.show();
        }
      }],
    });

    $("div.head-label").html('<h5 class="card-title mb-0">Data Tagihan</h5>');

    // Filter bulan
    $('#filter-bulan').on('change', function() {
      table.ajax.reload();
    });

    // Delete handler
    $(document).on('click', '.btn-delete', function() {
      const id = $(this).data('id');

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
            url: `/tagihan/${id}`,
            type: 'DELETE',
            data: {
              _token: '{{ csrf_token() }}'
            },
            success: function(response) {
              toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-top-right",
                timeOut: 3000
              };
              toastr.success(response.message || 'Data berhasil dihapus.', 'Berhasil!');

              table.ajax.reload(null, false);
            },
            error: function(xhr) {
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

    // Payment handler
    $(document).on('click', '.btn-pay', function() {
      const id = $(this).data('id');

      Swal.fire({
        title: "Konfirmasi Pembayaran",
        text: "Tandai tagihan ini sebagai lunas?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "YA, BAYAR!",
        cancelButtonText: "BATAL",
        buttonsStyling: false,
        customClass: {
          confirmButton: "btn btn-success me-2",
          cancelButton: "btn btn-outline-secondary"
        }
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: `/tagihan/${id}/pay`,
            type: 'POST',
            data: {
              _token: '{{ csrf_token() }}'
            },
            success: function(response) {
              toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-top-right",
                timeOut: 3000
              };
              toastr.success(response.message || 'Pembayaran berhasil.', 'Berhasil!');

              table.ajax.reload(null, false);
            },
            error: function(xhr) {
              toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-top-right",
                timeOut: 3000
              };
              const errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan saat memproses pembayaran.';
              toastr.error(errorMsg, 'Gagal!');
            }
          });
        }
      });
    });
  });
</script>
@endsection

@section('content')
<!-- Filter Bulan -->
<div class="row mb-3">
  <div class="col-md-3">
    <label for="filter-bulan" class="form-label fw-bold">Filter Bulan</label>
    <select id="filter-bulan" class="form-select">
      <option value="">Semua Bulan</option>
      <option value="1">Januari</option>
      <option value="2">Februari</option>
      <option value="3">Maret</option>
      <option value="4">April</option>
      <option value="5">Mei</option>
      <option value="6">Juni</option>
      <option value="7">Juli</option>
      <option value="8">Agustus</option>
      <option value="9">September</option>
      <option value="10">Oktober</option>
      <option value="11">November</option>
      <option value="12">Desember</option>
    </select>
  </div>
</div>

<!-- DataTable with Buttons -->
<div class="card">
  <div class="card-datatable table-responsive pt-0">
    <table id="table" class="datatables-basic table table-bordered">
      <thead>
        <tr>
          <th>+</th>
          <th>NO</th>
          <th>NO TAGIHAN</th>
          <th>PAKET</th>
          <th>BULAN</th>
          <th>TAHUN</th>
          <th>STATUS</th>
          <th>HARGA</th>
          <th>TOTAL</th>
          <th>JATUH TEMPO</th>
          <th>DIBUAT</th>
          <th>AKSI</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection