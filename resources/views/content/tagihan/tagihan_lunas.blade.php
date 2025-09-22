@extends('layouts/layoutMaster')

@section('title', 'Data Tagihan Lunas')

@section('vendor-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
@endsection

@section('page-script')
<script>
  const baseUrlRoute = "/user";
  let table;

  $(document).ready(function() {
    // ✅ Enable dan show loading dengan pengecekan ketersediaan
    if (window.GlobalLoading) {
      GlobalLoading.enableAutoLoading();
      GlobalLoading.show('Memuat data tagihan lunas...');
    }

    table = $('#table').DataTable({
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "{{ route('tagihan.json') }}",
        data: function(d) {
          d.status_bayar = 1; // Untuk data table ambil yang lunas
          d.bulan = $('#filter-bulan').val();
        },
        // ✅ Process summary data dari response DataTables
        dataSrc: function(json) {
          // Update summary cards dari response DataTable
          if (json.summary) {
            updateSummaryCards(json.summary);
          }

          // ✅ Update data tagihan belum bayar dari unpaid_summary
          if (json.unpaid_summary) {
            updateUnpaidSummary(json.unpaid_summary);
          }

          // Return data untuk DataTable
          return json.data;
        }
      },
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
            return `
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                <i class="mdi mdi-dots-vertical"></i>
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item btn-cancel-payment text-warning fw-bold" href="javascript:void(0);" data-id="${data}">
                  <i class="mdi mdi-undo me-1"></i> Batal Pembayaran
                </a>
                <a class="dropdown-item btn-delete text-danger fw-bold" href="javascript:void(0);" data-id="${data}">
                  <i class="mdi mdi-trash-can-outline me-1"></i> Delete
                </a>
              </div>
            </div>
          `;
          }
        },
        { data: 'noTagihan'},
        { data: 'paket_nama'},
        { data: 'bulan'},
        { data: 'tahun'},
        {
          data: 'status_bayar',
          render: function(data, type, row) {
            if (data === 'unpaid') {
              return `<span class="badge bg-danger">${data}</span>`;
            } else {
              return `<span class="badge bg-success">${data}</span>`;
            }
          }
        },
        {
          data: 'harga',
          render: function(data, type, row) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(data);
          }
        },
        {
          data: 'ppn',
          render: function(data, type, row) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(data);
          }
        },
        {
          data: 'diskon',
          render: function(data, type, row) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(data);
          }
        },
        {
          data: 'total',
          render: function(data, type, row) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(data);
          }
        },
        { data: 'tglBayar'},
        { data: 'prosesNama'},
        { data: 'metode'},
      ],

      columnDefs: [{
        className: 'control',
        targets: 0,
        searchable: !1,
        render: function() {
          return ''
        }
      }],

      destroy: !0,
      dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0">>'+
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
      },

      "initComplete": function() {
        if (window.GlobalLoading) {
          GlobalLoading.hide();
        }
      },
      "drawCallback": function() {
        if (window.GlobalLoading && window.GlobalLoading.isLoading) {
          GlobalLoading.hide();
        }
      }
    });

    // Tambahkan label di dalam header card
    $("div.head-label").html('<h5 class="card-title mb-0">Data Tagihan Lunas</h5>');

    // ✅ Event handler untuk tombol delete dengan GlobalLoading
    $('#table').on('click', '.btn-delete', function() {
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
          const deleteSteps = [
            { progress: 25, text: 'Memvalidasi data...', delay: 300 },
            { progress: 60, text: 'Menghapus dari database...', delay: 500 },
            { progress: 85, text: 'Membersihkan cache...', delay: 200 },
            { progress: 100, text: 'Data berhasil dihapus!', delay: 200 }
          ];

          if (window.GlobalLoading) {
            GlobalLoading.showWithSteps(deleteSteps);
          }

          setTimeout(() => {
            $.ajax({
              url: `{{ url('tagihan/destroy') }}/${id}`,
              type: 'DELETE',
              data: {
                _token: '{{ csrf_token() }}'
              },
              success: function(response) {
                if (window.GlobalLoading) {
                  GlobalLoading.hide();
                }

                if (response.success) {
                  Swal.fire({
                    title: 'Data Berhasil Dihapus!',
                    text: 'Tagihan telah dihapus dari sistem.',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    buttonsStyling: false,
                    customClass: {
                      confirmButton: "btn btn-success"
                    }
                  });

                  if (window.GlobalLoading) {
                    GlobalLoading.show('Memuat ulang data...');
                  }

                  table.ajax.reload(() => {
                    if (window.GlobalLoading) {
                      GlobalLoading.hide();
                    }
                  });

                } else {
                  Swal.fire({
                    title: 'Gagal Menghapus Data',
                    text: response.message || 'Terjadi kesalahan saat menghapus data.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    buttonsStyling: false,
                    customClass: {
                      confirmButton: "btn btn-danger"
                    }
                  });
                }
              },
              error: function(xhr) {
                if (window.GlobalLoading) {
                  GlobalLoading.hide();
                }

                let message = 'Terjadi kesalahan pada server.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                  message = xhr.responseJSON.message;
                }

                Swal.fire({
                  title: 'Error Sistem',
                  text: message,
                  icon: 'error',
                  confirmButtonText: 'OK',
                  buttonsStyling: false,
                  customClass: {
                    confirmButton: "btn btn-danger"
                  }
                });
              }
            });
          }, 1200);
        }
      });
    });

    // ✅ Event handler untuk tombol batal pembayaran dengan GlobalLoading
    $('#table').on('click', '.btn-cancel-payment', function() {
      const id = $(this).data('id');

      Swal.fire({
        title: "Yakin ingin membatalkan pembayaran?",
        html: `
          <div class="text-start">
            <p class="mb-2"><strong>Peringatan:</strong></p>
            <ul class="text-muted small">
              <li>Status tagihan akan kembali menjadi BELUM LUNAS</li>
              <li>Data transaksi akan dihapus</li>
              <li>Tanggal bayar akan dihapus</li>
              <li>Data metode pembayaran akan dihapus</li>
            </ul>
            <p class="text-danger small mt-2"><i class="mdi mdi-alert-circle"></i> <strong>Aksi ini tidak dapat dibatalkan!</strong></p>
          </div>
        `,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "YA, BATAL PEMBAYARAN!",
        cancelButtonText: "TIDAK",
        buttonsStyling: false,
        customClass: {
          confirmButton: "btn btn-warning me-2",
          cancelButton: "btn btn-outline-secondary"
        }
      }).then((result) => {
        if (result.isConfirmed) {
          const cancelSteps = [
            { progress: 15, text: 'Memvalidasi data pembayaran...', delay: 300 },
            { progress: 35, text: 'Membatalkan pembayaran...', delay: 500 },
            { progress: 60, text: 'Menghapus data transaksi...', delay: 600 },
            { progress: 85, text: 'Mengupdate status tagihan...', delay: 400 },
            { progress: 100, text: 'Pembayaran berhasil dibatalkan!', delay: 300 }
          ];

          if (window.GlobalLoading) {
            GlobalLoading.showWithSteps(cancelSteps);
          }

          setTimeout(() => {
            $.ajax({
              url: `{{ url('tagihan/batal-pembayaran') }}/${id}`,
              type: 'POST',
              data: {
                _token: '{{ csrf_token() }}'
              },
              success: function(response) {
                if (window.GlobalLoading) {
                  GlobalLoading.hide();
                }

                if (response.success) {
                  Swal.fire({
                    title: 'Pembayaran Berhasil Dibatalkan!',
                    html: `
                      <div class="text-start">
                        <p class="text-success mt-2"><i class="mdi mdi-check-circle"></i> Status tagihan telah dikembalikan menjadi BELUM LUNAS</p>
                        <p class="text-info"><i class="mdi mdi-information"></i> Data transaksi telah dihapus</p>
                        <p class="text-warning"><i class="mdi mdi-clock"></i> Tanggal bayar telah direset</p>
                      </div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'OK',
                    buttonsStyling: false,
                    customClass: {
                      confirmButton: "btn btn-success"
                    }
                  });

                  if (window.GlobalLoading) {
                    GlobalLoading.show('Memuat ulang data tagihan...');
                  }

                  table.ajax.reload(() => {
                    if (window.GlobalLoading) {
                      GlobalLoading.hide();
                    }
                  });

                } else {
                  Swal.fire({
                    title: 'Gagal Membatalkan Pembayaran',
                    text: response.message || 'Terjadi kesalahan saat membatalkan pembayaran.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    buttonsStyling: false,
                    customClass: {
                      confirmButton: "btn btn-danger"
                    }
                  });
                }
              },
              error: function(xhr) {
                if (window.GlobalLoading) {
                  GlobalLoading.hide();
                }

                let message = 'Terjadi kesalahan pada server.';
                let details = '';

                if (xhr.responseJSON) {
                  message = xhr.responseJSON.message || message;
                  if (xhr.responseJSON.errors && Array.isArray(xhr.responseJSON.errors)) {
                    details = xhr.responseJSON.errors.join('<br>');
                  }
                }

                Swal.fire({
                  title: 'Error Sistem',
                  html: `
                    <div class="text-start">
                      <p><strong>Pesan:</strong> ${message}</p>
                      ${details ? `<p><strong>Detail:</strong><br>${details}</p>` : ''}
                      <p class="text-muted small mt-2">Silakan coba lagi atau hubungi administrator jika masalah berlanjut.</p>
                    </div>
                  `,
                  icon: 'error',
                  confirmButtonText: 'OK',
                  buttonsStyling: false,
                  customClass: {
                    confirmButton: "btn btn-danger"
                  }
                });
              }
            });
          }, 2100);
        }
      });
    });

    // ✅ Filter bulan event handler dengan GlobalLoading - akan otomatis update unpaid summary juga
    $('#filter-bulan').on('change', function() {
      if (window.GlobalLoading) {
        GlobalLoading.show('Memfilter data berdasarkan bulan...');
      }

      // DataTable reload otomatis akan update summary + unpaid summary bersamaan
      table.ajax.reload(() => {
        if (window.GlobalLoading) {
          GlobalLoading.hide();
        }
      });
    });
  });

  // ✅ Function untuk update summary cards - tagihan lunas
  function updateSummaryCards(summaryData) {
    $('#total-count').text(summaryData.total_count || 0);
    $('#total-paid').text('Rp ' + formatNumber(summaryData.total_unpaid || 0)); // total yang sudah dibayar
    $('#total-diskon-lunas').text('Rp ' + formatNumber(summaryData.total_diskon || 0)); // total diskon tagihan lunas
  }

  // ✅ Function untuk update summary tagihan belum bayar - DATA BARU
  function updateUnpaidSummary(unpaidSummary) {
    if (unpaidSummary && unpaidSummary.unpaid_total !== undefined) {
      $('#total-unpaid').text('Rp ' + formatNumber(unpaidSummary.unpaid_total || 0));
    } else {
      $('#total-unpaid').text('Rp 0');
    }
  }

  // ✅ Helper function untuk format number
  function formatNumber(number) {
    return new Intl.NumberFormat('id-ID').format(number);
  }
</script>
@endsection

@section('content')
<!-- Bootstrap Summary Section untuk Tagihan Lunas -->
<div class="row mb-4">
  <!-- Filter Section -->
  <div class="col-lg-3 col-md-4 mb-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body p-3">
        <label for="filter-bulan" class="form-label fw-semibold text-muted mb-2">
          <i class="mdi mdi-filter-variant me-1"></i>Filter Periode
        </label>
        <select id="filter-bulan" class="form-select form-select-sm">
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
  </div>

  <!-- Summary Cards -->
  <div class="col-lg-9 col-md-8">
    <div class="row g-2">
      <!-- Total Tagihan Lunas -->
      <div class="col-lg-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body p-3 d-flex align-items-center">
            <div
              class="d-flex align-items-center justify-content-center me-3 bg-success bg-opacity-10 text-success rounded-2 flex-shrink-0"
              style="width: 40px; height: 40px;">
              <i class="mdi mdi-check-circle fs-5"></i>
            </div>
            <div class="flex-grow-1">
              <small class="text-muted fw-medium">Tagihan Lunas</small>
              <div class="fs-6 fw-bold text-dark" id="total-count">0</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Total Terbayar -->
      <div class="col-lg-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body p-3 d-flex align-items-center">
            <div
              class="d-flex align-items-center justify-content-center me-3 bg-primary bg-opacity-10 text-primary rounded-2 flex-shrink-0"
              style="width: 40px; height: 40px;">
              <i class="text-white mdi mdi-cash fs-5"></i>
            </div>
            <div class="flex-grow-1">
              <small class="text-muted fw-medium">Total Terbayar</small>
              <div class="fs-6 fw-bold text-dark" id="total-paid">Rp 0</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Total Diskon Lunas -->
      <div class="col-lg-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body p-3 d-flex align-items-center">
            <div
              class="d-flex align-items-center justify-content-center me-3 bg-info bg-opacity-10 text-info rounded-2 flex-shrink-0"
              style="width: 40px; height: 40px;">
              <i class="mdi mdi-percent fs-5"></i>
            </div>
            <div class="flex-grow-1">
              <small class="text-muted fw-medium">Total Diskon</small>
              <div class="fs-6 fw-bold text-dark" id="total-diskon-lunas">Rp 0</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tagihan Belum Bayar -->
      <div class="col-lg-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body p-3 d-flex align-items-center">
            <div
              class="d-flex align-items-center justify-content-center me-3 bg-warning bg-opacity-10 text-warning rounded-2 flex-shrink-0"
              style="width: 40px; height: 40px;">
              <i class="mdi mdi-file-document-multiple fs-5"></i>
            </div>
            <div class="flex-grow-1">
              <small class="text-muted fw-medium">Belum Bayar</small>
              <div class="fs-6 fw-bold text-dark" id="total-unpaid">Rp 0</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- DataTable Card -->
<div class="card">
  <div class="card-datatable table-responsive pt-0">
    <table id="table" class="datatables-basic table table-bordered">
      <thead>
        <tr>
          <th>+</th>
          <th>NO</th>
          <th>AKSI</th>
          <th>NO TAGIHAN</th>
          <th>PAKET</th>
          <th>BULAN</th>
          <th>TAHUN</th>
          <th>STATUS</th>
          <th>HARGA</th>
          <th>PPN</th>
          <th>DISKON</th>
          <th>TOTAL</th>
          <th>TGL BAYAR</th>
          <th>ADMIN</th>
          <th>METODE BAYAR</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection