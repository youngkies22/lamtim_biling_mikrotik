@extends('layouts/layoutMaster')

@section('title', 'Sinkronisasi Sistem')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4 overflow-hidden">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white"><i class="mdi mdi-sync-circle me-2"></i>Pusat Sinkronisasi Multi-MikroTik</h5>
                <span class="badge bg-white text-primary">v2.0</span>
            </div>
            <div class="card-body pt-4">
                <div class="row mb-4">
                    <div class="col-md-12 mb-3">
                        <div class="alert alert-outline-primary mb-0 w-100 py-2">
                            <i class="mdi mdi-information-outline me-2"></i>
                            Pastikan koneksi API MikroTik aktif sebelum memulai.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Pilih MikroTik Utama</label>
                        <select id="mikrotik-select" class="form-select form-select-lg border-primary">
                            @foreach($mikrotiks as $m)
                                <option value="{{ $m->id }}">{{ $m->nama }} ({{ $m->ip }})</option>
                            @endforeach
                        </select>
                        <div class="form-text mt-2">Pilih server MikroTik yang akan menjadi sumber sinkronisasi data.</div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Step 1: Kategori -->
                    <div class="col-md-3">
                        <div class="card border shadow-none h-100 sync-card" id="card-kategori">
                            <div class="card-body text-center p-4">
                                <div class="avatar avatar-lg bg-label-info mx-auto mb-3">
                                    <span class="avatar-initial rounded-circle"><i class="mdi mdi-shape-outline mdi-24px"></i></span>
                                </div>
                                <h5 class="mb-2">1. Verifikasi Kategori</h5>
                                <p class="text-muted small">Memastikan data kategori paket tersedia di sistem untuk pengelompokan.</p>
                                <button class="btn btn-info w-100 btn-sync" data-type="kategori">
                                    <i class="mdi mdi-check-circle-outline me-1"></i> Verifikasi Data
                                </button>
                                <div class="sync-status mt-3 d-none">
                                    <div class="spinner-border spinner-border-sm text-info me-2" role="status"></div>
                                    <span class="small fw-bold">Memeriksa...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Jaringan -->
                    <div class="col-md-3">
                        <div class="card border shadow-none h-100 sync-card" id="card-jaringan">
                            <div class="card-body text-center p-4">
                                <div class="avatar avatar-lg bg-label-primary mx-auto mb-3">
                                    <span class="avatar-initial rounded-circle"><i class="mdi mdi-lan mdi-24px"></i></span>
                                </div>
                                <h5 class="mb-2">2. Sinkron Jaringan</h5>
                                <p class="text-muted small">Mengambil data IP Pool dan Address List dari MikroTik.</p>
                                <button class="btn btn-primary w-100 btn-sync" data-type="jaringan" disabled>
                                    <i class="mdi mdi-sync me-1"></i> Mulai Sinkron
                                </button>
                                <div class="sync-status mt-3 d-none">
                                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                    <span class="small fw-bold">Memproses...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Paket -->
                    <div class="col-md-3">
                        <div class="card border shadow-none h-100 sync-card" id="card-paket">
                            <div class="card-body text-center p-4">
                                <div class="avatar avatar-lg bg-label-warning mx-auto mb-3">
                                    <span class="avatar-initial rounded-circle"><i class="mdi mdi-package-variant mdi-24px"></i></span>
                                </div>
                                <h5 class="mb-2">3. Sinkron Paket</h5>
                                <p class="text-muted small">Simpan Profil MikroTik ke kategori yang dipilih:</p>
                                
                                <select id="category-select" class="form-select form-select-sm mb-3 border-warning" disabled>
                                    @foreach($kategoris as $k)
                                        <option value="{{ $k->id }}">{{ $k->nama }}</option>
                                    @endforeach
                                </select>

                                <button class="btn btn-warning w-100 btn-sync" data-type="paket" disabled>
                                    <i class="mdi mdi-sync me-1"></i> Mulai Sinkron
                                </button>
                                <div class="sync-status mt-3 d-none">
                                    <div class="spinner-border spinner-border-sm text-warning me-2" role="status"></div>
                                    <span class="small fw-bold">Memproses...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Pelanggan -->
                    <div class="col-md-3">
                        <div class="card border shadow-none h-100 sync-card" id="card-pelanggan">
                            <div class="card-body text-center p-4">
                                <div class="avatar avatar-lg bg-label-success mx-auto mb-3">
                                    <span class="avatar-initial rounded-circle"><i class="mdi mdi-account-group mdi-24px"></i></span>
                                </div>
                                <h5 class="mb-2">4. Sinkron Pelanggan</h5>
                                <p class="text-muted small">Sinkronisasi data Secret ke Billing dan RADIUS dengan progress.</p>
                                
                                <div id="progress-container" class="mb-3 d-none">
                                    <div class="progress" style="height: 20px;">
                                        <div id="sync-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                    </div>
                                    <small id="progress-text" class="text-muted mt-1 d-block">Memproses 0 dari 0...</small>
                                </div>

                                <button class="btn btn-success w-100 btn-sync" data-type="pelanggan" id="btn-sync-pelanggan" disabled>
                                    <i class="mdi mdi-sync me-1"></i> Mulai Sinkron
                                </button>
                                <div class="sync-status mt-3 d-none" id="status-pelanggan">
                                    <div class="spinner-border spinner-border-sm text-success me-2" role="status"></div>
                                    <span class="small fw-bold">Menyiapkan data...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 p-3 bg-light rounded border border-dashed">
                    <h6 class="fw-bold mb-3"><i class="mdi mdi-history me-2"></i>Log Aktivitas Terakhir</h6>
                    <div id="sync-logs" class="small text-muted" style="max-height: 150px; overflow-y: auto;">
                        <div class="mb-1 text-primary">[INFO] Sistem siap untuk sinkronisasi.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')
<script>
$(function() {
    const logs = $('#sync-logs');
    
    function addLog(msg, type = 'info') {
        const time = new Date().toLocaleTimeString();
        const color = type === 'success' ? 'text-success' : (type === 'error' ? 'text-danger' : 'text-primary');
        logs.prepend(`<div class="mb-1 ${color}">[${time}] ${msg}</div>`);
    }

    $('.btn-sync:not(#btn-sync-pelanggan)').on('click', function() {
        const btn = $(this);
        const type = btn.data('type');
        const idMikrotik = $('#mikrotik-select').val();
        const idKategori = $('#category-select').val(); // Ambil kategori yang dipilih
        const card = btn.closest('.sync-card');
        const status = card.find('.sync-status');

        btn.prop('disabled', true);
        status.removeClass('d-none');
        addLog(`Memulai sinkronisasi ${type}...`, 'info');

        $.ajax({
            url: `/settings/sync/${type}`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id_mikrotik: idMikrotik,
                id_kategori: idKategori // Kirim ke server
            },
            success: function(res) {
                status.addClass('d-none');
                if (res.status) {
                    addLog(res.message, 'success');
                    card.addClass('bg-label-success border-success');
                    btn.html('<i class="mdi mdi-check-circle me-1"></i> Selesai');
                    
                    // Enable next step & populate dropdown if needed
                    if (type === 'kategori') {
                        const select = $('#category-select');
                        select.empty().prop('disabled', false);
                        if (res.data && res.data.length > 0) {
                            res.data.forEach(item => {
                                select.append(`<option value="${item.id}">${item.nama}</option>`);
                            });
                        }
                        $('[data-type="jaringan"]').prop('disabled', false);
                    } else if (type === 'jaringan') {
                        $('[data-type="paket"]').prop('disabled', false);
                    } else if (type === 'paket') {
                        $('[data-type="pelanggan"]').prop('disabled', false);
                    }
                } else {
                    handleSyncError(res.message, btn, status, null);
                }
            },
            error: function(xhr) {
                handleSyncError(xhr.responseJSON?.message || 'Gagal', btn, status, null);
            }
        });
    });

    // Logic khusus untuk tombol Pelanggan (Batching)
    $('#btn-sync-pelanggan').on('click', function() {
        const btn = $(this);
        const idMikrotik = $('#mikrotik-select').val();
        const card = btn.closest('.sync-card');
        const status = $('#status-pelanggan');
        const progressContainer = $('#progress-container');
        const progressBar = $('#sync-progress-bar');
        const progressText = $('#progress-text');

        btn.prop('disabled', true);
        status.removeClass('d-none');
        progressContainer.removeClass('d-none');
        progressBar.css('width', '0%').text('0%');
        progressText.text('Mengambil data dari MikroTik...');
        addLog(`Memulai sinkronisasi pelanggan...`, 'info');

        // Step 1: Ambil daftar list
        $.ajax({
            url: `/settings/sync/pelanggan-list`,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', id_mikrotik: idMikrotik },
            success: function(res) {
                if (res.status) {
                    const totalData = res.total;
                    const secrets = res.data;
                    
                    if (totalData === 0) {
                        status.addClass('d-none');
                        progressBar.css('width', '100%').text('100%');
                        progressText.text('Selesai! Tidak ada data.');
                        addLog('Tidak ada pelanggan yang perlu disinkronisasi.', 'info');
                        btn.html('<i class="mdi mdi-check-circle me-1"></i> Selesai');
                        return;
                    }

                    addLog(`Ditemukan ${totalData} pelanggan. Memulai proses batch...`, 'info');
                    processBatch(secrets, totalData, 0, idMikrotik, progressBar, progressText, status, btn);
                } else {
                    handleSyncError(res.message, btn, status, progressContainer);
                }
            },
            error: function(xhr) {
                handleSyncError(xhr.responseJSON?.message || 'Gagal mengambil data', btn, status, progressContainer);
            }
        });
    });

    function processBatch(allSecrets, totalData, currentIndex, idMikrotik, progressBar, progressText, status, btn) {
        const batchSize = 10;
        const currentBatch = allSecrets.slice(currentIndex, currentIndex + batchSize);
        
        if (currentBatch.length === 0) {
            // Selesai
            status.addClass('d-none');
            btn.html('<i class="mdi mdi-check-circle me-1"></i> Selesai').prop('disabled', true);
            progressText.text(`Selesai memproses ${totalData} data.`);
            addLog(`Sinkronisasi pelanggan selesai.`, 'success');
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Sinkronisasi pelanggan selesai.', timer: 2000, showConfirmButton: false });
            return;
        }

        const processedSoFar = Math.min(currentIndex + batchSize, totalData);
        const percent = Math.round((processedSoFar / totalData) * 100);

        $.ajax({
            url: `/settings/sync/pelanggan-batch`,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', id_mikrotik: idMikrotik, secrets: currentBatch },
            success: function(res) {
                if (res.status) {
                    // Update UI
                    progressBar.css('width', `${percent}%`).text(`${percent}%`);
                    progressText.text(`Memproses ${processedSoFar} dari ${totalData}...`);
                    
                    // Lanjut batch berikutnya
                    processBatch(allSecrets, totalData, currentIndex + batchSize, idMikrotik, progressBar, progressText, status, btn);
                } else {
                    handleSyncError(res.message, btn, status, $('#progress-container'));
                }
            },
            error: function(xhr) {
                handleSyncError(xhr.responseJSON?.message || 'Gagal memproses batch', btn, status, $('#progress-container'));
            }
        });
    }

    function handleSyncError(msg, btn, status, progressContainer) {
        status.addClass('d-none');
        if (progressContainer) progressContainer.addClass('d-none');
        btn.prop('disabled', false);
        addLog(`ERROR: ${msg}`, 'error');
        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
    }
});
</script>

<style>
.sync-card {
    transition: all 0.3s ease;
}
.sync-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
}
</style>
@endsection
