@extends('layouts/layoutMaster')

@section('title', 'Kelola Isolir')

@section("vendor-style")
<style>
  /* Modern Card Styling */
  .isolir-stats-card {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  }
  .isolir-stats-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
  }
  .stats-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
  }
  .stats-icon.total { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
  .stats-icon.aktif { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
  .stats-icon.isolir { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); }

  .stats-value {
    font-size: 32px;
    font-weight: 700;
    line-height: 1.2;
  }
  .stats-label {
    font-size: 13px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* Filter Card */
  .filter-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  }

  /* User Card Styling */
  .user-card {
    border: none;
    border-radius: 14px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    overflow: hidden;
  }
  .user-card:hover {
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
  }
  .user-card.selected {
    border: 2px solid #696cff;
    box-shadow: 0 6px 24px rgba(105, 108, 255, 0.2);
  }
  .user-card.isolir-status {
    border-left: 4px solid #ff3e1d;
  }
  .user-card.aktif-status {
    border-left: 4px solid #71dd37;
  }

  .user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 600;
    color: #fff;
  }
  .user-avatar.isolir { background: linear-gradient(135deg, #ff6b6b, #ee5a52); }
  .user-avatar.aktif { background: linear-gradient(135deg, #51cf66, #40c057); }

  .user-name {
    font-size: 15px;
    font-weight: 600;
    color: #333;
    margin-bottom: 2px;
  }
  .user-meta {
    font-size: 12px;
    color: #888;
  }

  .status-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .status-badge.isolir {
    background: rgba(255, 62, 29, 0.12);
    color: #ff3e1d;
  }
  .status-badge.aktif {
    background: rgba(113, 221, 55, 0.12);
    color: #71dd37;
  }

  /* Action Buttons */
  .btn-action {
    border-radius: 10px;
    padding: 8px 16px;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.2s ease;
  }
  .btn-isolir {
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
    border: none;
    color: #fff;
  }
  .btn-isolir:hover {
    background: linear-gradient(135deg, #ff5252, #e53935);
    color: #fff;
    transform: scale(1.02);
  }
  .btn-aktifkan {
    background: linear-gradient(135deg, #51cf66, #40c057);
    border: none;
    color: #fff;
  }
  .btn-aktifkan:hover {
    background: linear-gradient(135deg, #40c057, #37b24d);
    color: #fff;
    transform: scale(1.02);
  }

  /* Checkbox styling */
  .user-checkbox {
    width: 20px;
    height: 20px;
    border-radius: 6px;
    cursor: pointer;
  }

  /* Bulk Action Bar */
  .bulk-action-bar {
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    background: #fff;
    border-radius: 16px;
    padding: 16px 24px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    display: none;
    z-index: 1000;
    min-width: 400px;
  }
  .bulk-action-bar.show {
    display: flex;
    animation: slideUp 0.3s ease;
  }
  @keyframes slideUp {
    from { transform: translateX(-50%) translateY(100px); opacity: 0; }
    to { transform: translateX(-50%) translateY(0); opacity: 1; }
  }

  /* Loading State */
  .loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    border-radius: 16px;
  }

  /* Empty State */
  .empty-state {
    text-align: center;
    padding: 60px 20px;
  }
  .empty-state-icon {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: #f5f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
  }
  .empty-state-icon i {
    font-size: 48px;
    color: #a5a3ae;
  }

  /* Search Input */
  .search-input {
    border-radius: 12px;
    border: 2px solid #e9ecef;
    padding: 12px 16px;
    font-size: 14px;
    transition: all 0.2s;
  }
  .search-input:focus {
    border-color: #696cff;
    box-shadow: 0 0 0 3px rgba(105, 108, 255, 0.1);
  }

  /* Filter Buttons */
  .filter-btn {
    border-radius: 10px;
    padding: 10px 20px;
    font-weight: 500;
    transition: all 0.2s;
  }
  .filter-btn.active {
    background: #696cff;
    color: #fff;
  }

  /* Grid Layout */
  .users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
    gap: 16px;
  }

  /* Pagination */
  .pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    margin-top: 20px;
    flex-wrap: wrap;
    gap: 16px;
  }
  .pagination-info {
    color: #6c757d;
    font-size: 14px;
  }
  .pagination-controls {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .pagination-btn {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: 2px solid #e9ecef;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
  }
  .pagination-btn:hover:not(:disabled) {
    border-color: #696cff;
    color: #696cff;
  }
  .pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
  .pagination-btn.active {
    background: #696cff;
    border-color: #696cff;
    color: #fff;
  }
  .page-size-select {
    border-radius: 10px;
    border: 2px solid #e9ecef;
    padding: 8px 12px;
    font-size: 14px;
  }

  @media (max-width: 576px) {
    .users-grid {
      grid-template-columns: 1fr;
    }
    .bulk-action-bar {
      min-width: calc(100% - 40px);
      bottom: 20px;
    }
    .pagination-container {
      flex-direction: column;
      text-align: center;
    }
  }
</style>
@endsection

@section("vendor-script")
@endsection

@section('page-script')
<script>
let allUsers = [];
let selectedUsers = [];
let currentFilter = 'all';
let currentMikrotik = '';
let currentSearch = '';
let currentPage = 1;
let perPage = 20;
let paginationData = null;

$(document).ready(function() {
  // Tampilkan pesan untuk pilih server terlebih dahulu
  showSelectServerMessage();

  // Filter buttons
  $('.filter-btn').on('click', function() {
    $('.filter-btn').removeClass('active');
    $(this).addClass('active');
    currentFilter = $(this).data('filter');
    currentPage = 1;
    if (currentMikrotik) {
      loadData();
    }
  });

  // Mikrotik filter
  $('#filterMikrotik').on('change', function() {
    currentMikrotik = $(this).val();
    currentPage = 1;
    if (currentMikrotik) {
      loadData();
    } else {
      showSelectServerMessage();
    }
  });

  // Search
  let searchTimeout;
  $('#searchInput').on('input', function() {
    clearTimeout(searchTimeout);
    currentSearch = $(this).val();
    searchTimeout = setTimeout(() => {
      currentPage = 1;
      if (currentMikrotik) {
        loadData();
      }
    }, 300);
  });

  // Per page change
  $(document).on('change', '#perPageSelect', function() {
    perPage = parseInt($(this).val());
    currentPage = 1;
    loadData();
  });

  // Select all
  $('#selectAll').on('change', function() {
    const isChecked = $(this).is(':checked');

    if (isChecked) {
      selectedUsers = allUsers.map(u => u.id);
    } else {
      selectedUsers = [];
    }
    renderUsers();
    updateBulkActionBar();
  });

  // Bulk isolir
  $('#bulkIsolir').on('click', function() {
    if (selectedUsers.length === 0) return;

    Swal.fire({
      title: `Isolir ${selectedUsers.length} User?`,
      text: "User akan di-disconnect dan tidak bisa akses internet.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "YA, ISOLIR!",
      cancelButtonText: "BATAL",
      buttonsStyling: false,
      customClass: { confirmButton: "btn btn-danger me-2", cancelButton: "btn btn-outline-secondary" }
    }).then((result) => {
      if (result.isConfirmed) {
        bulkAction('isolir', selectedUsers);
      }
    });
  });

  // Bulk aktifkan
  $('#bulkAktifkan').on('click', function() {
    if (selectedUsers.length === 0) return;

    Swal.fire({
      title: `Aktifkan ${selectedUsers.length} User?`,
      text: "User akan bisa mengakses internet kembali.",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "YA, AKTIFKAN!",
      cancelButtonText: "BATAL",
      buttonsStyling: false,
      customClass: { confirmButton: "btn btn-success me-2", cancelButton: "btn btn-outline-secondary" }
    }).then((result) => {
      if (result.isConfirmed) {
        bulkAction('aktifkan', selectedUsers);
      }
    });
  });

  // Clear selection
  $('#clearSelection').on('click', function() {
    selectedUsers = [];
    $('#selectAll').prop('checked', false);
    renderUsers();
    updateBulkActionBar();
  });
});

function loadData() {
  $('#usersContainer').html(`
    <div class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3 text-muted">Memuat data pelanggan...</p>
    </div>
  `);
  $('#paginationContainer').hide();

  $.ajax({
    url: "{{ route('mikrotik.kelola-isolir.data') }}",
    method: 'GET',
    data: {
      mikrotik: currentMikrotik,
      search: currentSearch,
      status: currentFilter === 'all' ? '' : (currentFilter === 'isolir' ? 1 : 0),
      page: currentPage,
      per_page: perPage
    },
    success: function(response) {
      if (!response.error) {
        allUsers = response.data;
        paginationData = response.pagination;
        updateStats(response.stats);
        renderUsers();
        renderPagination();
      } else {
        showError(response.message);
      }
    },
    error: function(xhr) {
      showError(xhr.responseJSON?.message || 'Gagal memuat data');
    }
  });
}

function updateStats(stats) {
  $('#statTotal').text(stats.total);
  $('#statAktif').text(stats.aktif);
  $('#statIsolir').text(stats.isolir);
}

function renderUsers() {
  const users = allUsers;

  if (users.length === 0) {
    $('#usersContainer').html(`
      <div class="empty-state">
        <div class="empty-state-icon">
          <i class="mdi mdi-account-search"></i>
        </div>
        <h5 class="text-muted">Tidak ada data</h5>
        <p class="text-muted mb-0">Tidak ada pelanggan yang sesuai dengan filter.</p>
      </div>
    `);
    $('#paginationContainer').hide();
    return;
  }

  let html = '<div class="users-grid">';
  users.forEach(user => {
    const isIsolir = user.status_isolir == 1;
    const isSelected = selectedUsers.includes(user.id);
    const initial = user.name.charAt(0).toUpperCase();

    html += `
      <div class="user-card card ${isIsolir ? 'isolir-status' : 'aktif-status'} ${isSelected ? 'selected' : ''}" data-id="${user.id}">
        <div class="card-body p-3">
          <div class="d-flex align-items-start gap-3">
            <input type="checkbox" class="user-checkbox" data-id="${user.id}" ${isSelected ? 'checked' : ''}>
            <div class="user-avatar ${isIsolir ? 'isolir' : 'aktif'}">${initial}</div>
            <div class="flex-grow-1 min-width-0">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <div class="user-name">${user.name}</div>
                  <div class="user-meta">
                    <i class="mdi mdi-phone me-1"></i>${user.wa || '-'}
                  </div>
                </div>
                <span class="status-badge ${isIsolir ? 'isolir' : 'aktif'}">
                  ${isIsolir ? 'Isolir' : 'Aktif'}
                </span>
              </div>
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <small class="text-muted d-block">PPPoE</small>
                  <strong class="text-dark" style="font-size: 12px;">${user.pppoe_username}</strong>
                </div>
                <div class="col-6">
                  <small class="text-muted d-block">Mikrotik</small>
                  <strong class="text-dark" style="font-size: 12px;">${user.mikrotik_nama}</strong>
                </div>
                <div class="col-6">
                  <small class="text-muted d-block">Paket</small>
                  <strong class="text-dark" style="font-size: 12px;">${user.paket}</strong>
                </div>
                <div class="col-6">
                  <small class="text-muted d-block">Kategori</small>
                  <strong class="text-dark" style="font-size: 12px;">${user.kategori}</strong>
                </div>
              </div>
              <div class="d-flex gap-2">
                ${isIsolir ?
                  `<button class="btn btn-aktifkan btn-action flex-grow-1" onclick="aktifkanUser(${user.id}, '${user.name}')">
                    <i class="mdi mdi-check-circle me-1"></i>Aktifkan
                  </button>` :
                  `<button class="btn btn-isolir btn-action flex-grow-1" onclick="isolirUser(${user.id}, '${user.name}')">
                    <i class="mdi mdi-close-circle me-1"></i>Isolir
                  </button>`
                }
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
  });
  html += '</div>';

  $('#usersContainer').html(html);

  // Checkbox event
  $('.user-checkbox').on('change', function() {
    const userId = $(this).data('id');
    if ($(this).is(':checked')) {
      if (!selectedUsers.includes(userId)) {
        selectedUsers.push(userId);
      }
    } else {
      selectedUsers = selectedUsers.filter(id => id !== userId);
    }
    $(this).closest('.user-card').toggleClass('selected', $(this).is(':checked'));
    updateBulkActionBar();
  });
}

function updateBulkActionBar() {
  if (selectedUsers.length > 0) {
    $('#bulkActionBar').addClass('show');
    $('#selectedCount').text(selectedUsers.length);
  } else {
    $('#bulkActionBar').removeClass('show');
  }
}

function renderPagination() {
  if (!paginationData || paginationData.total === 0) {
    $('#paginationContainer').hide();
    return;
  }

  const { current_page, last_page, per_page, total, from, to } = paginationData;

  let pagesHtml = '';

  // Previous button
  pagesHtml += `<button class="pagination-btn" onclick="goToPage(${current_page - 1})" ${current_page === 1 ? 'disabled' : ''}>
    <i class="mdi mdi-chevron-left"></i>
  </button>`;

  // Page numbers
  let startPage = Math.max(1, current_page - 2);
  let endPage = Math.min(last_page, current_page + 2);

  if (startPage > 1) {
    pagesHtml += `<button class="pagination-btn" onclick="goToPage(1)">1</button>`;
    if (startPage > 2) {
      pagesHtml += `<span class="px-2">...</span>`;
    }
  }

  for (let i = startPage; i <= endPage; i++) {
    pagesHtml += `<button class="pagination-btn ${i === current_page ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
  }

  if (endPage < last_page) {
    if (endPage < last_page - 1) {
      pagesHtml += `<span class="px-2">...</span>`;
    }
    pagesHtml += `<button class="pagination-btn" onclick="goToPage(${last_page})">${last_page}</button>`;
  }

  // Next button
  pagesHtml += `<button class="pagination-btn" onclick="goToPage(${current_page + 1})" ${current_page === last_page ? 'disabled' : ''}>
    <i class="mdi mdi-chevron-right"></i>
  </button>`;

  $('#paginationContainer').html(`
    <div class="pagination-info">
      Menampilkan ${from || 0} - ${to || 0} dari ${total} pelanggan
    </div>
    <div class="d-flex align-items-center gap-3">
      <div class="d-flex align-items-center gap-2">
        <span class="text-muted">Per halaman:</span>
        <select class="page-size-select" id="perPageSelect">
          <option value="10" ${per_page == 10 ? 'selected' : ''}>10</option>
          <option value="20" ${per_page == 20 ? 'selected' : ''}>20</option>
          <option value="50" ${per_page == 50 ? 'selected' : ''}>50</option>
          <option value="100" ${per_page == 100 ? 'selected' : ''}>100</option>
        </select>
      </div>
      <div class="pagination-controls">
        ${pagesHtml}
      </div>
    </div>
  `).show();
}

function goToPage(page) {
  if (page < 1 || (paginationData && page > paginationData.last_page)) return;
  currentPage = page;
  selectedUsers = [];
  $('#selectAll').prop('checked', false);
  updateBulkActionBar();
  loadData();
}

function isolirUser(userId, userName) {
  Swal.fire({
    title: `Isolir ${userName}?`,
    text: "User akan di-disconnect dan tidak bisa akses internet.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "YA, ISOLIR!",
    cancelButtonText: "BATAL",
    buttonsStyling: false,
    customClass: { confirmButton: "btn btn-danger me-2", cancelButton: "btn btn-outline-secondary" }
  }).then((result) => {
    if (result.isConfirmed) {
      doIsolir(userId);
    }
  });
}

function aktifkanUser(userId, userName) {
  Swal.fire({
    title: `Aktifkan ${userName}?`,
    text: "User akan bisa mengakses internet kembali.",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "YA, AKTIFKAN!",
    cancelButtonText: "BATAL",
    buttonsStyling: false,
    customClass: { confirmButton: "btn btn-success me-2", cancelButton: "btn btn-outline-secondary" }
  }).then((result) => {
    if (result.isConfirmed) {
      doAktifkan(userId);
    }
  });
}

function doIsolir(userId) {
  showLoading();
  $.ajax({
    url: "{{ route('mikrotik.kelola-isolir.isolir') }}",
    method: 'POST',
    data: { user_id: userId, _token: '{{ csrf_token() }}' },
    success: function(response) {
      hideLoading();
      if (!response.error) {
        toastr.success(response.message);
        loadData();
      } else {
        toastr.error(response.message);
      }
    },
    error: function(xhr) {
      hideLoading();
      toastr.error(xhr.responseJSON?.message || 'Gagal isolir user');
    }
  });
}

function doAktifkan(userId) {
  showLoading();
  $.ajax({
    url: "{{ route('mikrotik.kelola-isolir.aktifkan') }}",
    method: 'POST',
    data: { user_id: userId, _token: '{{ csrf_token() }}' },
    success: function(response) {
      hideLoading();
      if (!response.error) {
        toastr.success(response.message);
        loadData();
      } else {
        toastr.error(response.message);
      }
    },
    error: function(xhr) {
      hideLoading();
      toastr.error(xhr.responseJSON?.message || 'Gagal aktifkan user');
    }
  });
}

function bulkAction(action, userIds) {
  showLoading();
  const url = action === 'isolir'
    ? "{{ route('mikrotik.kelola-isolir.bulk-isolir') }}"
    : "{{ route('mikrotik.kelola-isolir.bulk-aktifkan') }}";

  $.ajax({
    url: url,
    method: 'POST',
    data: { user_ids: userIds, _token: '{{ csrf_token() }}' },
    success: function(response) {
      hideLoading();
      if (!response.error) {
        toastr.success(response.message);
        selectedUsers = [];
        $('#selectAll').prop('checked', false);
        updateBulkActionBar();
        loadData();
      } else {
        toastr.error(response.message);
      }
    },
    error: function(xhr) {
      hideLoading();
      toastr.error(xhr.responseJSON?.message || 'Gagal melakukan aksi');
    }
  });
}

function showError(message) {
  $('#usersContainer').html(`
    <div class="alert alert-danger">
      <i class="mdi mdi-alert-circle me-2"></i>${message}
    </div>
  `);
}

function showSelectServerMessage() {
  allUsers = [];
  updateStats({ total: 0, aktif: 0, isolir: 0 });
  $('#paginationContainer').hide();
  $('#usersContainer').html(`
    <div class="empty-state">
      <div class="empty-state-icon">
        <i class="mdi mdi-server-network"></i>
      </div>
      <h5 class="text-muted">Pilih Server Mikrotik</h5>
      <p class="text-muted mb-0">Silakan pilih server Mikrotik terlebih dahulu untuk menampilkan data pelanggan.</p>
    </div>
  `);
}

function showLoading() {
  Swal.fire({
    title: 'Mohon Tunggu...',
    allowOutsideClick: false,
    didOpen: () => { Swal.showLoading(); }
  });
}

function hideLoading() {
  Swal.close();
}
</script>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="row g-4 mb-4">
  <div class="col-lg-4 col-md-4 col-sm-6">
    <div class="card isolir-stats-card h-100">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3">
          <div class="stats-icon total text-white">
            <i class="mdi mdi-account-group"></i>
          </div>
          <div>
            <div class="stats-value" id="statTotal">0</div>
            <div class="stats-label">Total Pelanggan</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4 col-md-4 col-sm-6">
    <div class="card isolir-stats-card h-100">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3">
          <div class="stats-icon aktif text-white">
            <i class="mdi mdi-check-circle"></i>
          </div>
          <div>
            <div class="stats-value text-success" id="statAktif">0</div>
            <div class="stats-label">Aktif</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4 col-md-4 col-sm-6">
    <div class="card isolir-stats-card h-100">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3">
          <div class="stats-icon isolir text-white">
            <i class="mdi mdi-close-circle"></i>
          </div>
          <div>
            <div class="stats-value text-danger" id="statIsolir">0</div>
            <div class="stats-label">Terisolir</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Filter Card -->
<div class="card filter-card mb-4">
  <div class="card-body">
    <div class="row g-3 align-items-center">
      <div class="col-lg-4 col-md-6">
        <div class="input-group">
          <span class="input-group-text bg-transparent border-end-0">
            <i class="mdi mdi-magnify"></i>
          </span>
          <input type="text" class="form-control search-input border-start-0" id="searchInput" placeholder="Cari nama, WA, atau PPPoE...">
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <select class="form-select" id="filterMikrotik" style="border-radius: 12px; padding: 12px 16px;">
          <option value="">Semua Mikrotik</option>
          @foreach(\App\Helpers\Helpers::getMikrotik() as $mk)
            <option value="{{ $mk->id }}">{{ $mk->nama }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-lg-5 col-md-12">
        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-outline-secondary filter-btn active" data-filter="all">
            <i class="mdi mdi-view-grid me-1"></i>Semua
          </button>
          <button class="btn btn-outline-success filter-btn" data-filter="aktif">
            <i class="mdi mdi-check-circle me-1"></i>Aktif
          </button>
          <button class="btn btn-outline-danger filter-btn" data-filter="isolir">
            <i class="mdi mdi-close-circle me-1"></i>Isolir
          </button>
          <div class="ms-auto">
            <input type="checkbox" class="form-check-input" id="selectAll" style="width: 20px; height: 20px;">
            <label class="form-check-label ms-1" for="selectAll">Pilih Semua</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Users Container -->
<div id="usersContainer">
  <div class="empty-state">
    <div class="empty-state-icon">
      <i class="mdi mdi-server-network"></i>
    </div>
    <h5 class="text-muted">Pilih Server Mikrotik</h5>
    <p class="text-muted mb-0">Silakan pilih server Mikrotik terlebih dahulu untuk menampilkan data pelanggan.</p>
  </div>
</div>

<!-- Pagination Container -->
<div class="pagination-container" id="paginationContainer" style="display: none;">
</div>

<!-- Bulk Action Bar -->
<div class="bulk-action-bar" id="bulkActionBar">
  <div class="d-flex align-items-center justify-content-between w-100 gap-3">
    <div class="d-flex align-items-center gap-2">
      <span class="badge bg-primary rounded-pill px-3 py-2" style="font-size: 14px;">
        <span id="selectedCount">0</span> dipilih
      </span>
      <button class="btn btn-sm btn-outline-secondary" id="clearSelection">
        <i class="mdi mdi-close"></i> Batal
      </button>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-aktifkan btn-action" id="bulkAktifkan">
        <i class="mdi mdi-check-circle me-1"></i>Aktifkan Semua
      </button>
      <button class="btn btn-isolir btn-action" id="bulkIsolir">
        <i class="mdi mdi-close-circle me-1"></i>Isolir Semua
      </button>
    </div>
  </div>
</div>
@endsection
