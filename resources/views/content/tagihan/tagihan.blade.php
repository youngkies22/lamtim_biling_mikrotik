@extends('layouts/layoutMaster')

@section('title', 'Data Tagihan')

@section('vendor-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
@endsection

@section('page-style')
<style>
  /* Styling untuk filter card */
  .card-body .form-label {
    font-size: 0.875rem;
  }

  .form-select-sm {
    border-radius: 0.375rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
  }

  .form-select-sm:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
  }
</style>
@endsection

@section('page-script')
<script>
  const baseUrlRoute = "/user";
  let table;

  $(document).ready(function() {
    // ✅ Enable GlobalLoading untuk halaman ini
    if (window.GlobalLoading) {
      GlobalLoading.enableAutoLoading();
      GlobalLoading.show('Memuat data tagihan...');
    }

    table = $('#table').DataTable({
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "{{ route('tagihan.json') }}",
        data: function(d) {
          d.status_bayar = 0;
          const tahun = $('#filter-tahun').val();
          const bulan = $('#filter-bulan').val();
          if (tahun) d.tahun = tahun;
          if (bulan) d.bulan = bulan;
        },
        // ✅ Process summary data dari response DataTables
        dataSrc: function(json) {
          // Update summary cards dari response DataTable
          if (json.summary) {
            updateSummaryCards(json.summary);
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
            console.log(data);
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
                <a class="dropdown-item btn-bayar text-success fw-bold" href="javascript:void(0);" data-id="${data}">
                  <i class="mdi mdi-credit-card-outline me-1"></i> Bayar
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
        { data: 'user_nama'},
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
        { data: 'tglJatuhTempo'},
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
        // ✅ Hide loading setelah DataTable selesai load
        if (window.GlobalLoading) {
          GlobalLoading.hide();
        }
      }
    });

    $("div.head-label").html('<h5 class="card-title mb-0">Data Tagihan Belum Lunas</h5>');

    // ✅ Event handler untuk tombol bayar dengan GlobalLoading
    $('#table').on('click', '.btn-bayar', function() {
      const id = $(this).data('id');
      const rowData = table.row($(this).closest('tr')).data();

      // ✅ Show loading saat memuat data modal
      if (window.GlobalLoading) {
        GlobalLoading.show('Memuat data pembayaran...');
      }

      // Simulasi delay untuk loading experience yang lebih baik
      setTimeout(() => {
        // Isi data ke modal
        $('#modal-noTagihan').text(rowData.noTagihan);
        $('#modal-paket').text(rowData.paket_nama);
        $('#modal-bulan').text(rowData.bulan);
        $('#modal-tahun').text(rowData.tahun);
        $('#modal-harga').text('Rp ' + new Intl.NumberFormat('id-ID').format(rowData.harga));
        $('#modal-ppn').text('Rp ' + new Intl.NumberFormat('id-ID').format(rowData.ppn));
        $('#modal-diskon').text('Rp ' + new Intl.NumberFormat('id-ID').format(rowData.diskon));
        $('#modal-total').text('Rp ' + new Intl.NumberFormat('id-ID').format(rowData.total));
        $('#modal-jatuhTempo').text(rowData.tglJatuhTempo);

        // Set ID tagihan untuk proses bayar
        $('#form-bayar').data('tagihan-id', id);

        // Reset form
        $('#metode_bayar').val('');
        $('#keterangan_bayar').val('');

        // ✅ Hide loading dan tampilkan modal
        if (window.GlobalLoading) {
          GlobalLoading.hide();
        }

        // Tampilkan modal setelah loading selesai
        $('#modalBayar').modal('show');
      }, 600);
    });

    // ✅ Event handler untuk form bayar dengan GlobalLoading Progress Steps
    $('#form-bayar').on('submit', function(e) {
      e.preventDefault();

      const tagihanId = $(this).data('tagihan-id');
      const metodeBayar = $('#metode_bayar').val();
      const keterangan = $('#keterangan_bayar').val();

      if (!metodeBayar) {
        Swal.fire({
          title: 'Validasi Error',
          text: 'Pilih metode pembayaran terlebih dahulu!',
          icon: 'warning',
          confirmButtonText: 'OK',
          buttonsStyling: false,
          customClass: {
            confirmButton: "btn btn-warning"
          }
        });
        return;
      }

      // ✅ Tutup modal terlebih dahulu
      $('#modalBayar').modal('hide');

      // ✅ Show loading dengan progress steps
      const paymentSteps = [
        { progress: 10, text: 'Validasi data pembayaran...', delay: 300 },
        { progress: 25, text: 'Memproses ke sistem pembayaran...', delay: 400 },
        { progress: 50, text: 'Menghubungi gateway pembayaran...', delay: 600 },
        { progress: 75, text: 'Menyimpan data transaksi...', delay: 500 },
        { progress: 90, text: 'Mengupdate status tagihan...', delay: 400 },
        { progress: 100, text: 'Pembayaran berhasil diproses!', delay: 300 }
      ];

      if (window.GlobalLoading) {
        GlobalLoading.showWithSteps(paymentSteps);
      }

      // ✅ Delay total sesuai dengan steps
      setTimeout(() => {
        $.ajax({
          url: `{{ url('tagihan/bayar') }}/${tagihanId}`,
          type: 'POST',
          data: {
            _token: '{{ csrf_token() }}',
            metode_bayar: metodeBayar,
            keterangan_bayar: keterangan
          },
          success: function(response) {
            if (window.GlobalLoading) {
              GlobalLoading.hide();
            }

            if (response.success) {
              Swal.fire({
                title: 'Pembayaran Berhasil!',
                html: `
                  <div class="text-start">
                    <p><strong>No. Tagihan:</strong> ${$('#modal-noTagihan').text()}</p>
                    <p><strong>Total Bayar:</strong> <span class="text-success">${$('#modal-total').text()}</span></p>
                    <p><strong>Metode:</strong> ${metodeBayar}</p>
                    <p class="text-success mt-2"><i class="mdi mdi-check-circle"></i> Status tagihan telah diupdate menjadi LUNAS</p>
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
                title: 'Gagal Memproses Pembayaran',
                text: response.message || 'Terjadi kesalahan saat memproses pembayaran.',
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
      }, 2500);
    });

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
            { progress: 20, text: 'Memvalidasi data...', delay: 200 },
            { progress: 60, text: 'Menghapus dari database...', delay: 400 },
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
          }, 800);
        }
      });
    });

    // Filter tahun & bulan event handler
    $('#filter-tahun, #filter-bulan').on('change', function() {
      if (window.GlobalLoading) {
        GlobalLoading.show('Memfilter data berdasarkan periode...');
      }
      table.ajax.reload(() => {
        if (window.GlobalLoading) {
          GlobalLoading.hide();
        }
      });
    });
  });

  // ✅ Function untuk update summary cards
  function updateSummaryCards(summaryData) {
    $('#total-count').text(summaryData.total_count || 0);
    $('#total-unpaid').text('Rp ' + formatNumber(summaryData.total_unpaid || 0));
    $('#total-diskon').text('Rp ' + formatNumber(summaryData.total_diskon || 0));
    $('#potential-income').text('Rp ' + formatNumber(summaryData.potential_income || 0));
  }

  // ✅ Helper function untuk format number
  function formatNumber(number) {
    return new Intl.NumberFormat('id-ID').format(number);
  }
</script>

@endsection

@section('content')
<!-- Bootstrap Only Summary Section -->
<div class="row mb-4 align-items-center">
  <!-- Filter Section - Sejajar dengan Summary Cards -->
  <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body p-3 d-flex align-items-center justify-content-center">
        <div class="d-flex align-items-end gap-2 w-100">
          <!-- Filter Tahun -->
          <div class="flex-grow-1">
            <label for="filter-tahun" class="form-label small text-muted mb-1" style="font-size: 0.75rem;">Tahun</label>
            <select id="filter-tahun" class="form-select form-select-sm">
              <option value="">Semua Tahun</option>
              @if(isset($filterData['tahun']) && !empty($filterData['tahun']))
                @foreach($filterData['tahun'] as $tahun)
                  <option value="{{ $tahun }}" {{ $tahun == $currentYear ? 'selected' : '' }}>{{ $tahun }}</option>
                @endforeach
              @else
                <option value="{{ $currentYear }}" selected>{{ $currentYear }}</option>
              @endif
            </select>
          </div>

          <!-- Filter Bulan -->
          <div class="flex-grow-1" id="bulan-filter-container">
            <label for="filter-bulan" class="form-label small text-muted mb-1" style="font-size: 0.75rem;">Bulan</label>
            <select id="filter-bulan" class="form-select form-select-sm">
              <option value="">Semua Bulan</option>
              @php
                $namaBulan = [
                  1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                  5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                  9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
              @endphp
              @foreach($namaBulan as $key => $nama)
                <option value="{{ $key }}" {{ $key == $currentMonth ? 'selected' : '' }}>{{ $nama }}</option>
              @endforeach
          </select>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="col-lg-9 col-md-6 col-sm-6">
    <div class="row g-2">
      <!-- Total Tagihan -->
      <div class="col-lg-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body p-3 d-flex align-items-center">
            <div
              class="d-flex align-items-center justify-content-center me-3 bg-danger bg-opacity-10 text-danger rounded-2 flex-shrink-0"
              style="width: 40px; height: 40px;">
              <i class="mdi mdi-file-document-multiple fs-5"></i>
            </div>
            <div class="flex-grow-1">
              <small class="text-muted fw-medium">Total Tagihan</small>
              <div class="fs-6 fw-bold text-dark" id="total-count">0</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Total Belum Terbayar -->
      <div class="col-lg-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body p-3 d-flex align-items-center">
            <div
              class="d-flex align-items-center justify-content-center me-3 bg-warning bg-opacity-10 text-warning rounded-2 flex-shrink-0"
              style="width: 40px; height: 40px;">
              <i class="mdi mdi-cash-clock fs-5"></i>
            </div>
            <div class="flex-grow-1">
              <small class="text-muted fw-medium">Belum Terbayar</small>
              <div class="fs-6 fw-bold text-dark" id="total-unpaid">Rp 0</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Total Diskon -->
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
              <div class="fs-6 fw-bold text-dark" id="total-diskon">Rp 0</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Potensi Revenue -->
      <div class="col-lg-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body p-3 d-flex align-items-center">
            <div
              class="d-flex align-items-center justify-content-center me-3 bg-success bg-opacity-10 text-success rounded-2 flex-shrink-0"
              style="width: 40px; height: 40px;">
              <i class="mdi mdi-trending-up fs-5"></i>
            </div>
            <div class="flex-grow-1">
              <small class="text-muted fw-medium">Potensi Revenue</small>
              <div class="fs-6 fw-bold text-dark" id="potential-income">Rp 0</div>
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
          <th>NAMA</th>
          <th>PAKET</th>
          <th>BULAN</th>
          <th>TAHUN</th>
          <th>STATUS</th>
          <th>HARGA</th>
          <th>PPN</th>
          <th>DISKON</th>
          <th>TOTAL</th>
          <th>JATUH TEMPO</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modal Bayar -->
<div class="modal fade" id="modalBayar" tabindex="-1" aria-labelledby="modalBayarLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalBayarLabel">
          <i class="mdi mdi-credit-card-outline me-2"></i>Proses Pembayaran
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Data Tagihan -->
        <div class="card mb-3">
          <div class="card-header">
            <h6 class="card-title mb-0">Detail Tagihan</h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <table class="table table-borderless">
                  <tr>
                    <td class="fw-bold">No. Tagihan:</td>
                    <td id="modal-noTagihan">-</td>
                  </tr>
                  <tr>
                    <td class="fw-bold">Paket:</td>
                    <td id="modal-paket">-</td>
                  </tr>
                  <tr>
                    <td class="fw-bold">Periode:</td>
                    <td><span id="modal-bulan">-</span> / <span id="modal-tahun">-</span></td>
                  </tr>
                  <tr>
                    <td class="fw-bold">Jatuh Tempo:</td>
                    <td id="modal-jatuhTempo">-</td>
                  </tr>
                </table>
              </div>
              <div class="col-md-6">
                <table class="table table-borderless">
                  <tr>
                    <td class="fw-bold">Harga:</td>
                    <td id="modal-harga">-</td>
                  </tr>
                  <tr>
                    <td class="fw-bold">PPN:</td>
                    <td id="modal-ppn">-</td>
                  </tr>
                  <tr>
                    <td class="fw-bold">Diskon:</td>
                    <td id="modal-diskon">-</td>
                  </tr>
                  <tr>
                    <td class="fw-bold text-success">Total:</td>
                    <td class="fw-bold text-success" id="modal-total">-</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Pembayaran -->
        <form id="form-bayar">
          <div class="row">
            <div class="col-12 mb-3">
              <label for="metode_bayar" class="form-label fw-bold">Metode Pembayaran <span
                  class="text-danger">*</span></label>
              <select id="metode_bayar" name="metode_bayar" class="form-select" required>
                <option value="">-- Pilih Metode Pembayaran --</option>
                <option value="transfer">Transfer Bank</option>
                <option value="ewallet">E-Wallet</option>
                <option value="qris">Qris</option>
                <option value="cod">Cash On Delivery (COD)</option>
              </select>
            </div>
            <div class="col-12 mb-3">
              <label for="keterangan" class="form-label fw-bold">Keterangan</label>
              <textarea id="keterangan_bayar" name="keterangan" class="form-control" rows="3"
                placeholder="Masukkan keterangan pembayaran (opsional)"></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" form="form-bayar" class="btn btn-success" id="btn-proses-bayar">
          <i class="mdi mdi-check me-1"></i>Proses Bayar
        </button>
      </div>
    </div>
  </div>
</div>
@endsection