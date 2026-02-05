<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Google Map - Network Topology</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; overflow: hidden; background: #1a1a2e; }
    #google-map { width: 100vw; height: 100vh; }

    .panel {
      position: absolute;
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(10px);
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
      z-index: 1000;
    }

    .panel-header {
      padding: 14px 18px;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .panel-header h5 { font-size: 13px; font-weight: 600; color: #333; }
    .panel-body { padding: 14px 18px; }

    .control-panel { top: 20px; right: 20px; width: 320px; max-height: calc(100vh - 40px); overflow-y: auto; transition: transform 0.3s ease, opacity 0.3s ease; }
    .control-panel.hidden { transform: translateX(350px); opacity: 0; pointer-events: none; }

    .stats-panel { top: 20px; left: 20px; transition: transform 0.3s ease, opacity 0.3s ease; }
    .stats-panel.hidden { transform: translateX(-250px); opacity: 0; pointer-events: none; }

    /* Toggle Buttons */
    .panel-toggle {
      position: absolute;
      top: 80px;
      width: 44px;
      height: 44px;
      background: rgba(255, 255, 255, 0.95);
      border: none;
      border-radius: 12px;
      cursor: pointer;
      z-index: 999;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
      transition: all 0.3s ease;
      opacity: 0;
      pointer-events: none;
    }
    .panel-toggle:hover { transform: scale(1.05); box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2); }
    .panel-toggle.visible { opacity: 1; pointer-events: auto; }
    .panel-toggle svg { width: 20px; height: 20px; color: #333; }
    .panel-toggle-left { left: 20px; }
    .panel-toggle-right { right: 20px; }

    /* Close button in panel header */
    .panel-close {
      width: 28px;
      height: 28px;
      background: #f5f5f5;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }
    .panel-close:hover { background: #eee; }
    .panel-close svg { width: 16px; height: 16px; color: #666; }

    .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .stat-item { text-align: center; padding: 10px; background: #f8f9fa; border-radius: 8px; }
    .stat-value { font-size: 22px; font-weight: 700; color: #333; }
    .stat-label { font-size: 10px; color: #666; margin-top: 2px; }
    .stat-item.server { border-left: 3px solid #e91e63; }
    .stat-item.odc { border-left: 3px solid #ff9800; }
    .stat-item.odp { border-left: 3px solid #4caf50; }
    .stat-item.user { border-left: 3px solid #2196f3; }
    .stat-item.polyline { border-left: 3px solid #9c27b0; }

    .mode-banner {
      position: absolute;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      padding: 10px 24px;
      border-radius: 25px;
      font-weight: 600;
      font-size: 13px;
      z-index: 1001;
      display: none;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    .mode-banner.drawing { background: linear-gradient(135deg, #ff9800, #f57c00); color: white; }
    .mode-banner.editing { background: linear-gradient(135deg, #2196f3, #1976d2); color: white; }

    .form-group { margin-bottom: 14px; }
    .form-label { display: block; font-size: 11px; font-weight: 500; color: #555; margin-bottom: 5px; }
    .form-select, .form-input {
      width: 100%;
      padding: 9px 11px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 12px;
      background: #fff;
    }
    .form-select:focus, .form-input:focus { outline: none; border-color: #696cff; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

    .btn {
      padding: 9px 14px;
      border: none;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
    }
    .btn-primary { background: #696cff; color: #fff; }
    .btn-success { background: #71dd37; color: #fff; }
    .btn-danger { background: #ff3e1d; color: #fff; }
    .btn-warning { background: #ffab00; color: #fff; }
    .btn-secondary { background: #6c757d; color: #fff; }
    .btn-outline { background: transparent; border: 1px solid #ddd; color: #666; }
    .btn:hover { opacity: 0.9; transform: translateY(-1px); }
    .btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
    .btn-group { display: flex; gap: 8px; margin-top: 10px; }
    .btn-group .btn { flex: 1; }

    .section-divider { height: 1px; background: #eee; margin: 14px 0; }
    .section-title { font-size: 10px; font-weight: 600; color: #999; text-transform: uppercase; margin-bottom: 10px; }

    .toggle-group { display: flex; align-items: center; justify-content: space-between; padding: 6px 0; }
    .toggle-label { font-size: 12px; color: #333; }
    .toggle-switch { position: relative; width: 40px; height: 22px; background: #ddd; border-radius: 11px; cursor: pointer; }
    .toggle-switch.active { background: #696cff; }
    .toggle-switch::after { content: ''; position: absolute; top: 2px; left: 2px; width: 18px; height: 18px; background: #fff; border-radius: 50%; transition: 0.3s; }
    .toggle-switch.active::after { left: 20px; }

    .item-list { max-height: 180px; overflow-y: auto; }
    .list-item {
      display: flex;
      align-items: center;
      padding: 8px 10px;
      background: #f8f9fa;
      border-radius: 6px;
      margin-bottom: 6px;
      font-size: 11px;
    }
    .list-item:last-child { margin-bottom: 0; }
    .list-color { width: 10px; height: 10px; border-radius: 50%; margin-right: 8px; }
    .list-info { flex: 1; overflow: hidden; }
    .list-name { font-weight: 500; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .list-meta { font-size: 9px; color: #999; }
    .btn-delete-sm { width: 22px; height: 22px; padding: 0; border-radius: 4px; font-size: 11px; background: #ff3e1d; color: #fff; border: none; cursor: pointer; }

    .drawing-info {
      background: #fff3e0;
      border: 1px solid #ffcc80;
      border-radius: 8px;
      padding: 10px;
      font-size: 11px;
      color: #e65100;
    }
    .drawing-info strong { display: block; margin-bottom: 4px; }
    .waypoint-badge { background: #ff9800; color: white; padding: 3px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; margin-top: 6px; display: inline-block; }

    .loading-overlay {
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(255, 255, 255, 0.9);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      z-index: 2000;
    }
    .spinner { width: 36px; height: 36px; border: 3px solid #f3f3f3; border-top: 3px solid #696cff; border-radius: 50%; animation: spin 1s linear infinite; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    .loading-text { margin-top: 12px; color: #666; font-size: 13px; }

    .info-window { padding: 6px; }
    .info-window h6 { font-size: 13px; font-weight: 600; color: #333; margin-bottom: 6px; padding-bottom: 6px; border-bottom: 1px solid #eee; }
    .info-window p { font-size: 11px; color: #666; margin: 3px 0; }
    .info-window .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 600; color: #fff; }
    .badge-odc { background: #ff9800; }
    .badge-odp { background: #4caf50; }
    .badge-user { background: #2196f3; }

    .empty-state { text-align: center; color: #999; font-size: 11px; padding: 20px; }

    .tabs { display: flex; border-bottom: 1px solid #eee; margin-bottom: 14px; }
    .tab { flex: 1; padding: 10px; text-align: center; font-size: 11px; font-weight: 500; color: #666; cursor: pointer; border-bottom: 2px solid transparent; }
    .tab.active { color: #696cff; border-bottom-color: #696cff; }

    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: #f1f1f1; }
    ::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }

    @media (max-width: 768px) {
      .control-panel { width: calc(100vw - 40px); max-width: 320px; }
      .stats-panel { width: calc(100vw - 40px); max-width: 260px; }
    }

    /* Modal */
    .modal-overlay {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.5);
      z-index: 3000;
      display: none;
      align-items: center;
      justify-content: center;
    }
    .modal-overlay.show { display: flex; }
    .modal {
      background: #fff;
      border-radius: 16px;
      width: 90%;
      max-width: 500px;
      max-height: 80vh;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    .modal-header {
      padding: 16px 20px;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .modal-header h4 { font-size: 15px; font-weight: 600; color: #333; }
    .modal-close { background: none; border: none; font-size: 20px; cursor: pointer; color: #999; }
    .modal-body { padding: 20px; max-height: 50vh; overflow-y: auto; }
    .modal-footer { padding: 16px 20px; border-top: 1px solid #eee; display: flex; gap: 10px; justify-content: flex-end; }

    .import-section { margin-bottom: 16px; }
    .import-section-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 12px;
      background: #f8f9fa;
      border-radius: 8px;
      cursor: pointer;
      margin-bottom: 8px;
    }
    .import-section-header:hover { background: #f0f0f0; }
    .import-section-title { font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
    .import-section-title .badge { padding: 2px 8px; border-radius: 10px; font-size: 10px; color: #fff; }
    .import-section-count { font-size: 11px; color: #666; }
    .import-list { max-height: 150px; overflow-y: auto; }
    .import-item {
      display: flex;
      align-items: center;
      padding: 8px 12px;
      border-radius: 6px;
      margin-bottom: 4px;
      cursor: pointer;
      transition: background 0.2s;
    }
    .import-item:hover { background: #f5f5f5; }
    .import-item.selected { background: #e3f2fd; }
    .import-item input[type="checkbox"] { margin-right: 10px; }
    .import-item-info { flex: 1; }
    .import-item-name { font-size: 12px; font-weight: 500; color: #333; }
    .import-item-meta { font-size: 10px; color: #888; }
    .select-all-btn { font-size: 10px; color: #696cff; cursor: pointer; }
  </style>
</head>
<body>
  <div id="google-map"></div>

  <div class="loading-overlay" id="loading">
    <div class="spinner"></div>
    <div class="loading-text">Memuat peta...</div>
  </div>

  <!-- Import Modal -->
  <div class="modal-overlay" id="import-modal">
    <div class="modal">
      <div class="modal-header">
        <h4>Pilih Data untuk Import</h4>
        <button class="modal-close" id="close-import-modal">&times;</button>
      </div>
      <div class="modal-body" id="import-modal-body">
        <div class="loading-text" style="text-align: center;">Memuat data...</div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" id="cancel-import">Batal</button>
        <button class="btn btn-primary" id="confirm-import">Import Terpilih</button>
      </div>
    </div>
  </div>

  <div class="mode-banner drawing" id="drawing-banner">Mode Gambar - Klik untuk tambah titik</div>
  <div class="mode-banner editing" id="editing-banner">Mode Edit - Drag marker/jalur untuk pindah</div>

  <!-- Toggle Buttons (appear when panels hidden) -->
  <button class="panel-toggle panel-toggle-left" id="toggle-stats" title="Tampilkan Statistik">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
  </button>
  <button class="panel-toggle panel-toggle-right" id="toggle-control" title="Tampilkan Panel Kontrol">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v18M6 9l6-6 6 6M6 15l6 6 6-6"/></svg>
  </button>

  <div class="panel stats-panel" id="stats-panel">
    <div class="panel-header">
      <h5>Statistik</h5>
      <button class="panel-close" id="close-stats" title="Sembunyikan">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="panel-body">
      <div class="stats-grid">
        <div class="stat-item" style="border-left: 3px solid #e91e63;"><div class="stat-value">🖥️</div><div class="stat-label">Server</div></div>
        <div class="stat-item odc"><div class="stat-value" id="stat-odc">0</div><div class="stat-label">ODC</div></div>
        <div class="stat-item odp"><div class="stat-value" id="stat-odp">0</div><div class="stat-label">ODP</div></div>
        <div class="stat-item user"><div class="stat-value" id="stat-user">0</div><div class="stat-label">Pelanggan</div></div>
      </div>
      <div style="margin-top: 10px;">
        <div class="stat-item polyline"><div class="stat-value" id="stat-polyline">0</div><div class="stat-label">Jalur</div></div>
      </div>
    </div>
  </div>

  <div class="panel control-panel" id="control-panel">
    <div class="panel-header">
      <h5>Network Mapping</h5>
      <button class="panel-close" id="close-control" title="Sembunyikan">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="panel-body">
      <div class="tabs">
        <div class="tab active" data-tab="markers">Marker</div>
        <div class="tab" data-tab="polylines">Jalur</div>
        <div class="tab" data-tab="settings">Pengaturan</div>
      </div>

      <!-- MARKERS TAB -->
      <div id="tab-markers">
        <div class="section-title">Import Data</div>
        <button class="btn btn-primary" id="btn-import" style="width: 100%;">+ Pilih ODC/ODP/Pelanggan</button>

        <div class="section-divider"></div>

        <div class="section-title">Tambah Marker Manual</div>
        <div class="form-group">
          <label class="form-label">Tipe</label>
          <select class="form-select" id="marker-type">
            <option value="">-- Pilih Tipe --</option>
            <option value="odc">ODC</option>
            <option value="odp">ODP</option>
            <option value="user">Pelanggan</option>
            <option value="custom">Custom</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Nama</label>
          <input type="text" class="form-input" id="marker-name" placeholder="Nama marker">
        </div>
        <button class="btn btn-success" id="btn-add-marker" style="width: 100%;" disabled>Klik Peta untuk Posisi</button>

        <div class="section-divider"></div>

        <div class="section-title">Daftar Marker</div>
        <div class="item-list" id="marker-list">
          <p class="empty-state">Belum ada marker</p>
        </div>

        <button class="btn btn-danger" id="btn-clear-markers" style="width: 100%; margin-top: 10px;">Hapus Semua Marker</button>
      </div>

      <!-- POLYLINES TAB -->
      <div id="tab-polylines" style="display: none;">
        <div class="section-title">Buat Jalur</div>

        <div id="draw-setup">
          <div class="form-group">
            <label class="form-label">Warna Cepat</label>
            <div style="display: flex; gap: 6px; margin-bottom: 10px;">
              <button class="color-preset" data-color="#ff9800" title="ODC (Orange)" style="width:32px; height:32px; border-radius:6px; border:2px solid #fff; background:#ff9800; cursor:pointer; box-shadow:0 2px 4px rgba(0,0,0,0.2);"></button>
              <button class="color-preset" data-color="#4caf50" title="ODP (Green)" style="width:32px; height:32px; border-radius:6px; border:2px solid #fff; background:#4caf50; cursor:pointer; box-shadow:0 2px 4px rgba(0,0,0,0.2);"></button>
              <button class="color-preset" data-color="#2196f3" title="Pelanggan (Blue)" style="width:32px; height:32px; border-radius:6px; border:2px solid #fff; background:#2196f3; cursor:pointer; box-shadow:0 2px 4px rgba(0,0,0,0.2);"></button>
              <button class="color-preset" data-color="#9c27b0" title="Custom (Purple)" style="width:32px; height:32px; border-radius:6px; border:2px solid #fff; background:#9c27b0; cursor:pointer; box-shadow:0 2px 4px rgba(0,0,0,0.2);"></button>
              <button class="color-preset" data-color="#e91e63" title="Server (Pink)" style="width:32px; height:32px; border-radius:6px; border:2px solid #fff; background:#e91e63; cursor:pointer; box-shadow:0 2px 4px rgba(0,0,0,0.2);"></button>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Warna Custom</label>
              <input type="color" class="form-input" id="polyline-color" value="#ff9800" style="height: 36px; padding: 3px;">
            </div>
            <div class="form-group">
              <label class="form-label">Tebal</label>
              <input type="number" class="form-input" id="polyline-weight" value="3" min="1" max="10">
            </div>
          </div>
          <button class="btn btn-warning" id="btn-start-draw" style="width: 100%;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Mulai Gambar Jalur
          </button>
          <p style="font-size: 10px; color: #888; margin-top: 8px; text-align: center;">Klik marker atau peta untuk membuat jalur</p>
        </div>

        <div id="drawing-mode" style="display: none;">
          <div class="drawing-info">
            <strong>🎯 Mode Gambar Aktif</strong>
            <span id="draw-instruction">Klik marker awal atau langsung klik peta</span>
            <div class="waypoint-badge" id="waypoint-count">0 titik</div>
          </div>
          <div class="btn-group">
            <button class="btn btn-outline" id="btn-undo">↩ Undo</button>
            <button class="btn btn-danger" id="btn-cancel-draw">✕ Batal</button>
          </div>
          <button class="btn btn-success" id="btn-save-polyline" style="width: 100%; margin-top: 8px;" disabled>✓ Simpan Jalur</button>
        </div>

        <div class="section-divider"></div>

        <div class="section-title">Daftar Jalur</div>
        <div class="item-list" id="polyline-list">
          <p class="empty-state">Belum ada jalur</p>
        </div>

        <button class="btn btn-danger" id="btn-clear-polylines" style="width: 100%; margin-top: 10px;">Hapus Semua Jalur</button>
      </div>

      <!-- SETTINGS TAB -->
      <div id="tab-settings" style="display: none;">
        <div class="section-title">Opsi Tampilan</div>
        <div class="toggle-group">
          <span class="toggle-label">Tampilkan Jalur</span>
          <div class="toggle-switch active" id="toggle-polylines"></div>
        </div>
        <div class="toggle-group">
          <span class="toggle-label">Animasi Jalur</span>
          <div class="toggle-switch active" id="toggle-animation"></div>
        </div>
        <div class="toggle-group">
          <span class="toggle-label">Marker Draggable</span>
          <div class="toggle-switch active" id="toggle-draggable"></div>
        </div>

        <div class="section-divider"></div>

        <div class="section-title">Filter Marker</div>
        <div class="toggle-group">
          <span class="toggle-label">ODC</span>
          <div class="toggle-switch active" data-filter="odc"></div>
        </div>
        <div class="toggle-group">
          <span class="toggle-label">ODP</span>
          <div class="toggle-switch active" data-filter="odp"></div>
        </div>
        <div class="toggle-group">
          <span class="toggle-label">Pelanggan</span>
          <div class="toggle-switch active" data-filter="user"></div>
        </div>

        <div class="section-divider"></div>

        <div class="section-title">Info Server</div>
        <p style="font-size: 11px; color: #666; margin-bottom: 10px;">
          Lat: {{ $mapCenter['latitude'] }}<br>
          Lng: {{ $mapCenter['longitude'] }}
        </p>
        <button class="btn btn-primary" id="btn-center-server" style="width: 100%;">🖥️ Kembali ke Server</button>
      </div>
    </div>
  </div>

  <script>
    let map, infoWindow;
    let markers = [];
    let polylines = [];
    let serverMarker = null;
    let mapData = null;

    let showPolylines = true;
    let animatePolylines = true;
    let markersAreDraggable = true;
    let visibleTypes = { odc: true, odp: true, user: true };

    let isDrawing = false;
    let isAddingMarker = false;
    let drawingState = { type: 'custom', path: [], polyline: null, tempMarkers: [], fromMarker: null, toMarker: null };

    const mapCenter = { lat: parseFloat("{{ $mapCenter['latitude'] }}"), lng: parseFloat("{{ $mapCenter['longitude'] }}") };
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let selectOptions = @json($selectOptions ?? ['odcs' => [], 'odps' => [], 'users' => []]);

    function initMap() {
      try {
        map = new google.maps.Map(document.getElementById('google-map'), {
          center: mapCenter,
          zoom: 14,
          mapTypeControl: true,
          fullscreenControl: true,
          streetViewControl: false,
          styles: [{ featureType: 'poi', elementType: 'labels', stylers: [{ visibility: 'off' }] }]
        });

        infoWindow = new google.maps.InfoWindow();

        map.addListener('click', (e) => {
          if (isDrawing) {
            // Try to detect if click is near a marker first
            const nearestMarker = findNearestMarker(e.latLng);
            if (nearestMarker) {
              // Track marker connection
              if (drawingState.path.length === 0) {
                drawingState.fromMarker = nearestMarker;
              } else {
                drawingState.toMarker = nearestMarker;
              }
            }
            addDrawingPoint(e.latLng);
          } else if (isAddingMarker) {
            createMarkerAtPosition(e.latLng);
          }
        });

        loadMapData();
        document.getElementById('loading').style.display = 'none';
      } catch (error) {
        console.error('Map init error:', error);
      }
    }

    async function loadMapData() {
      try {
        const response = await fetch('{{ route("google-map.data") }}');
        const result = await response.json();
        if (result.status && result.data) {
          mapData = result.data;
          renderMap();
          updateStats();
          renderLists();
        }
      } catch (error) {
        console.error('Error loading data:', error);
      }
    }

    function renderMap() {
      clearAllMapObjects();

      // Render server marker first (always visible)
      if (mapData.server) {
        renderServerMarker(mapData.server);
      }

      // Render markers
      (mapData.markers || []).forEach(m => {
        if (visibleTypes[m.tipe] !== false) {
          const marker = createMarker(m);
          markers.push(marker);
        }
      });

      // Render polylines
      if (showPolylines) {
        (mapData.polylines || []).forEach(p => {
          if (p.koordinat && p.koordinat.length >= 2) {
            const pl = createPolyline(p);
            polylines.push(pl);
          }
        });
      }

      fitBounds();
    }

    function renderServerMarker(data) {
      if (serverMarker) {
        serverMarker.setMap(null);
      }

      serverMarker = new google.maps.Marker({
        position: { lat: data.latitude, lng: data.longitude },
        map: map,
        icon: {
          path: google.maps.SymbolPath.CIRCLE,
          scale: 14,
          fillColor: '#e91e63',
          fillOpacity: 1,
          strokeColor: '#fff',
          strokeWeight: 3,
        },
        title: data.nama,
        zIndex: 9999,
        draggable: false
      });

      // Add pulsing effect
      const pulseMarker = new google.maps.Marker({
        position: { lat: data.latitude, lng: data.longitude },
        map: map,
        icon: {
          path: google.maps.SymbolPath.CIRCLE,
          scale: 20,
          fillColor: '#e91e63',
          fillOpacity: 0.3,
          strokeColor: '#e91e63',
          strokeWeight: 1,
        },
        zIndex: 9998
      });

      serverMarker.addListener('click', () => {
        infoWindow.setContent(`
          <div class="info-window">
            <h6>🖥️ ${data.nama}</h6>
            <p><strong>Titik Pusat Server</strong></p>
            <p style="color:#999; font-size:9px;">Lat: ${data.latitude.toFixed(6)}, Lng: ${data.longitude.toFixed(6)}</p>
          </div>
        `);
        infoWindow.open(map, serverMarker);
      });
    }

    function createMarker(data) {
      const icons = {
        odc: { url: 'http://maps.google.com/mapfiles/ms/icons/orange-dot.png', scaledSize: new google.maps.Size(38, 38) },
        odp: { url: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png', scaledSize: new google.maps.Size(34, 34) },
        user: { url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png', scaledSize: new google.maps.Size(30, 30) },
        custom: { url: 'http://maps.google.com/mapfiles/ms/icons/purple-dot.png', scaledSize: new google.maps.Size(32, 32) }
      };

      const marker = new google.maps.Marker({
        position: { lat: data.latitude, lng: data.longitude },
        map: map,
        icon: icons[data.tipe] || icons.custom,
        title: data.nama,
        draggable: markersAreDraggable,
        markerId: data.id,
        markerType: data.tipe,
        markerData: data
      });

      marker.addListener('click', () => {
        if (isDrawing) {
          // Track which marker is being connected
          if (drawingState.path.length === 0) {
            // First point - this is the "from" marker
            drawingState.fromMarker = { id: data.id, type: data.tipe, ref_id: data.ref_id || null };
          } else {
            // Last point - this is the "to" marker
            drawingState.toMarker = { id: data.id, type: data.tipe, ref_id: data.ref_id || null };
          }
          // Add marker position to drawing path
          addDrawingPoint(marker.getPosition());
        } else {
          showMarkerInfo(marker, data);
        }
      });

      marker.addListener('dragend', async () => {
        const pos = marker.getPosition();
        await updateMarkerPosition(data.id, pos.lat(), pos.lng());
      });

      return marker;
    }

    function showMarkerInfo(marker, data) {
      const badges = { odc: 'badge-odc', odp: 'badge-odp', user: 'badge-user', custom: 'badge-odc' };
      const labels = { odc: 'ODC', odp: 'ODP', user: 'Pelanggan', custom: 'Custom' };

      let content = `<div class="info-window">
        <h6><span class="badge ${badges[data.tipe]}">${labels[data.tipe]}</span></h6>`;

      const ed = data.extra_data || {};

      if (data.tipe === 'odc') {
        // ODC Info Window
        content += `<p><strong>Nama:</strong> ${data.nama}</p>`;
        content += `<p><strong>Port:</strong> ${ed.port ?? '-'}</p>`;
        content += `<p><strong>Port Sisa:</strong> ${ed.portSisa ?? '-'}</p>`;
        content += `<p><strong>Total ODP:</strong> ${ed.totalOdp ?? 0}</p>`;
        content += `<p><strong>Total User:</strong> ${ed.totalUser ?? 0}</p>`;
      } else if (data.tipe === 'odp') {
        // ODP Info Window
        content += `<p><strong>Nama:</strong> ${data.nama}</p>`;
        content += `<p><strong>Port:</strong> ${ed.port ?? '-'}</p>`;
        content += `<p><strong>Port Sisa:</strong> ${ed.portSisa ?? '-'}</p>`;
        content += `<p><strong>Total User:</strong> ${ed.totalUser ?? 0}</p>`;
      } else if (data.tipe === 'user') {
        // User Info Window
        content += `<p><strong>ID User:</strong> ${ed.idUser ?? data.ref_id ?? '-'}</p>`;
        content += `<p><strong>Nama:</strong> ${data.nama}</p>`;
        content += `<p><strong>Kategori:</strong> ${ed.kategori ?? '-'}</p>`;
        content += `<p><strong>Paket:</strong> ${ed.paket ?? '-'}</p>`;
      } else {
        // Custom/other marker
        content += `<p><strong>Nama:</strong> ${data.nama}</p>`;
      }

      content += `<p style="color:#999; font-size:9px; margin-top:6px;">Lat: ${data.latitude.toFixed(6)}, Lng: ${data.longitude.toFixed(6)}</p></div>`;

      infoWindow.setContent(content);
      infoWindow.open(map, marker);
    }

    async function updateMarkerPosition(id, lat, lng) {
      try {
        await fetch(`/google-map/marker/${id}/position`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
          body: JSON.stringify({ latitude: lat, longitude: lng })
        });
      } catch (error) {
        console.error('Error updating marker position:', error);
      }
    }

    function createPolyline(data) {
      const path = data.koordinat.map(c => ({ lat: parseFloat(c.lat), lng: parseFloat(c.lng) }));

      const polyline = new google.maps.Polyline({
        path: path,
        geodesic: true,
        strokeColor: data.warna || '#3388ff',
        strokeOpacity: animatePolylines ? 0 : 0.9,
        strokeWeight: data.ketebalan || 3,
        editable: false, // No visible edit points
        map: map,
        polylineId: data.id
      });

      if (animatePolylines) {
        const lineSymbol = { path: 'M 0,-1 0,1', strokeOpacity: 1, strokeColor: data.warna || '#3388ff', scale: data.ketebalan || 3 };
        polyline.setOptions({ icons: [{ icon: lineSymbol, offset: '0', repeat: '20px' }] });

        let count = 0;
        polyline.animateInterval = setInterval(() => {
          if (!animatePolylines || !polyline.getMap()) { clearInterval(polyline.animateInterval); return; }
          count = (count + 1) % 200;
          const icons = polyline.get('icons');
          icons[0].offset = (count / 2) + '%';
          polyline.set('icons', icons);
        }, 50);
      }

      return polyline;
    }

    function clearAllMapObjects() {
      markers.forEach(m => m.setMap(null));
      markers = [];
      polylines.forEach(p => { if (p.animateInterval) clearInterval(p.animateInterval); p.setMap(null); });
      polylines = [];
    }

    function fitBounds() {
      const bounds = new google.maps.LatLngBounds();

      // Always include server marker
      if (serverMarker) {
        bounds.extend(serverMarker.getPosition());
      }

      // Include all other markers
      markers.forEach(m => bounds.extend(m.getPosition()));

      if (markers.length === 0 && !serverMarker) return;

      if (markers.length === 0 && serverMarker) {
        // Only server marker - just center on it
        map.setCenter(serverMarker.getPosition());
        map.setZoom(14);
      } else {
        map.fitBounds(bounds);
        // Limit max zoom out - keep it reasonable
        google.maps.event.addListenerOnce(map, 'bounds_changed', function() {
          if (map.getZoom() < 12) {
            map.setZoom(12);
          }
        });
      }
    }

    function centerOnServer() {
      if (serverMarker) {
        map.setCenter(serverMarker.getPosition());
        map.setZoom(14);
      }
    }

    function updateStats() {
      const stats = mapData.statistics || {};
      document.getElementById('stat-odc').textContent = stats.odc || 0;
      document.getElementById('stat-odp').textContent = stats.odp || 0;
      document.getElementById('stat-user').textContent = stats.user || 0;
      document.getElementById('stat-polyline').textContent = (mapData.polylines || []).length;
    }

    function renderLists() {
      // Marker list
      const markerList = document.getElementById('marker-list');
      const markerData = mapData.markers || [];
      if (markerData.length === 0) {
        markerList.innerHTML = '<p class="empty-state">Belum ada marker. Klik Import untuk memulai.</p>';
      } else {
        const colors = { odc: '#ff9800', odp: '#4caf50', user: '#2196f3', custom: '#9c27b0' };
        markerList.innerHTML = markerData.map(m => `
          <div class="list-item">
            <span class="list-color" style="background: ${colors[m.tipe]}"></span>
            <div class="list-info">
              <div class="list-name">${m.nama}</div>
              <div class="list-meta">${m.tipe.toUpperCase()}</div>
            </div>
            <button class="btn-delete-sm" onclick="deleteMarker(${m.id})">×</button>
          </div>
        `).join('');
      }

      // Polyline list
      const polylineList = document.getElementById('polyline-list');
      const polylineData = mapData.polylines || [];
      if (polylineData.length === 0) {
        polylineList.innerHTML = '<p class="empty-state">Belum ada jalur</p>';
      } else {
        const typeLabels = { odc_to_odp: 'ODC→ODP', odp_to_odp: 'ODP→ODP', odp_to_user: 'ODP→User', custom: 'Custom' };
        polylineList.innerHTML = polylineData.map(p => `
          <div class="list-item">
            <span class="list-color" style="background: ${p.warna || '#3388ff'}"></span>
            <div class="list-info">
              <div class="list-name">${p.nama || 'Jalur #' + p.id}</div>
              <div class="list-meta">${typeLabels[p.tipe] || p.tipe} (${p.koordinat?.length || 0} titik)</div>
            </div>
            <button class="btn-delete-sm" onclick="deletePolyline(${p.id})">×</button>
          </div>
        `).join('');
      }

      // Update select options from markers
      updateSelectOptions();
    }

    function updateSelectOptions() {
      const markerData = mapData.markers || [];
      selectOptions.odcs = markerData.filter(m => m.tipe === 'odc');
      selectOptions.odps = markerData.filter(m => m.tipe === 'odp');
      selectOptions.users = markerData.filter(m => m.tipe === 'user');
    }

    // === MARKER ACTIONS ===

    let availableItems = { odcs: [], odps: [], users: [] };
    let selectedItems = { odc_ids: [], odp_ids: [], user_ids: [] };

    async function openImportModal() {
      document.getElementById('import-modal').classList.add('show');
      document.getElementById('import-modal-body').innerHTML = '<div class="loading-text" style="text-align: center;">Memuat data...</div>';

      try {
        const response = await fetch('{{ route("google-map.marker.available") }}');
        const result = await response.json();
        if (result.status) {
          availableItems = result.data;
          selectedItems = { odc_ids: [], odp_ids: [], user_ids: [] };
          renderImportModal();
        }
      } catch (error) {
        document.getElementById('import-modal-body').innerHTML = '<div class="empty-state">Gagal memuat data</div>';
      }
    }

    function renderImportModal() {
      const body = document.getElementById('import-modal-body');
      let html = '';

      // ODC Section
      html += `<div class="import-section">
        <div class="import-section-header">
          <div class="import-section-title">
            <span class="badge" style="background:#ff9800">ODC</span>
            ODC (${availableItems.odcs.length} tersedia)
          </div>
          <span class="select-all-btn" onclick="toggleSelectAll('odc')">Pilih Semua</span>
        </div>
        <div class="import-list">
          ${availableItems.odcs.length === 0 ? '<div class="empty-state">Semua ODC sudah di-import</div>' :
            availableItems.odcs.map(o => `
              <label class="import-item" onclick="toggleItem('odc', ${o.id})">
                <input type="checkbox" id="odc-${o.id}" ${selectedItems.odc_ids.includes(o.id) ? 'checked' : ''}>
                <div class="import-item-info">
                  <div class="import-item-name">${o.nama}</div>
                  <div class="import-item-meta">${o.info}</div>
                </div>
              </label>
            `).join('')}
        </div>
      </div>`;

      // ODP Section
      html += `<div class="import-section">
        <div class="import-section-header">
          <div class="import-section-title">
            <span class="badge" style="background:#4caf50">ODP</span>
            ODP (${availableItems.odps.length} tersedia)
          </div>
          <span class="select-all-btn" onclick="toggleSelectAll('odp')">Pilih Semua</span>
        </div>
        <div class="import-list">
          ${availableItems.odps.length === 0 ? '<div class="empty-state">Semua ODP sudah di-import</div>' :
            availableItems.odps.map(o => `
              <label class="import-item" onclick="toggleItem('odp', ${o.id})">
                <input type="checkbox" id="odp-${o.id}" ${selectedItems.odp_ids.includes(o.id) ? 'checked' : ''}>
                <div class="import-item-info">
                  <div class="import-item-name">${o.nama}</div>
                  <div class="import-item-meta">${o.info}</div>
                </div>
              </label>
            `).join('')}
        </div>
      </div>`;

      // User Section
      html += `<div class="import-section">
        <div class="import-section-header">
          <div class="import-section-title">
            <span class="badge" style="background:#2196f3">Pelanggan</span>
            Pelanggan (${availableItems.users.length} tersedia)
          </div>
          <span class="select-all-btn" onclick="toggleSelectAll('user')">Pilih Semua</span>
        </div>
        <div class="import-list">
          ${availableItems.users.length === 0 ? '<div class="empty-state">Semua Pelanggan sudah di-import</div>' :
            availableItems.users.map(u => `
              <label class="import-item" onclick="toggleItem('user', ${u.id})">
                <input type="checkbox" id="user-${u.id}" ${selectedItems.user_ids.includes(u.id) ? 'checked' : ''}>
                <div class="import-item-info">
                  <div class="import-item-name">${u.nama}</div>
                  <div class="import-item-meta">${u.info}</div>
                </div>
              </label>
            `).join('')}
        </div>
      </div>`;

      body.innerHTML = html;
    }

    function toggleItem(type, id) {
      const key = type + '_ids';
      const idx = selectedItems[key].indexOf(id);
      if (idx > -1) {
        selectedItems[key].splice(idx, 1);
      } else {
        selectedItems[key].push(id);
      }
      // Update checkbox
      const cb = document.getElementById(`${type}-${id}`);
      if (cb) cb.checked = selectedItems[key].includes(id);
    }

    function toggleSelectAll(type) {
      const key = type + '_ids';
      const items = type === 'odc' ? availableItems.odcs : (type === 'odp' ? availableItems.odps : availableItems.users);
      const allSelected = items.length > 0 && items.every(i => selectedItems[key].includes(i.id));

      if (allSelected) {
        selectedItems[key] = [];
      } else {
        selectedItems[key] = items.map(i => i.id);
      }
      renderImportModal();
    }

    function closeImportModal() {
      document.getElementById('import-modal').classList.remove('show');
    }

    async function confirmImport() {
      const total = selectedItems.odc_ids.length + selectedItems.odp_ids.length + selectedItems.user_ids.length;
      if (total === 0) {
        alert('Pilih minimal 1 item untuk di-import');
        return;
      }

      try {
        const response = await fetch('{{ route("google-map.marker.import-selected") }}', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
          body: JSON.stringify(selectedItems)
        });
        const result = await response.json();
        if (result.status) {
          alert(`Berhasil import: ODC=${result.data.odc}, ODP=${result.data.odp}, Pelanggan=${result.data.user}`);
          closeImportModal();
          await loadMapData();
          // Auto-center to server after import
          setTimeout(() => centerOnServer(), 500);
        } else {
          alert('Gagal: ' + result.message);
        }
      } catch (error) {
        alert('Error: ' + error.message);
      }
    }

    function startAddMarker() {
      const type = document.getElementById('marker-type').value;
      const name = document.getElementById('marker-name').value;
      if (!type || !name) { alert('Pilih tipe dan isi nama!'); return; }

      isAddingMarker = true;
      document.getElementById('btn-add-marker').textContent = 'Klik pada peta...';
      document.getElementById('btn-add-marker').disabled = true;
    }

    async function createMarkerAtPosition(latLng) {
      const type = document.getElementById('marker-type').value;
      const name = document.getElementById('marker-name').value;

      try {
        const response = await fetch('{{ route("google-map.marker.create") }}', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
          body: JSON.stringify({
            tipe: type,
            nama: name,
            latitude: latLng.lat(),
            longitude: latLng.lng()
          })
        });
        const result = await response.json();
        if (result.status) {
          loadMapData();
          document.getElementById('marker-type').value = '';
          document.getElementById('marker-name').value = '';
        } else {
          alert('Gagal: ' + result.message);
        }
      } catch (error) {
        alert('Error: ' + error.message);
      }

      isAddingMarker = false;
      document.getElementById('btn-add-marker').textContent = 'Klik Peta untuk Posisi';
      document.getElementById('btn-add-marker').disabled = true;
    }

    async function deleteMarker(id) {
      if (!confirm('Hapus marker ini?')) return;
      try {
        await fetch(`/google-map/marker/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } });
        loadMapData();
      } catch (error) {
        alert('Error: ' + error.message);
      }
    }

    async function clearAllMarkers() {
      if (!confirm('Hapus SEMUA marker?')) return;
      try {
        await fetch('{{ route("google-map.marker.clear") }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } });
        loadMapData();
      } catch (error) {
        alert('Error: ' + error.message);
      }
    }

    // === POLYLINE ACTIONS ===

    function startDrawing() {
      isDrawing = true;
      drawingState = { type: 'custom', path: [], polyline: null, tempMarkers: [] };

      document.getElementById('draw-setup').style.display = 'none';
      document.getElementById('drawing-mode').style.display = 'block';
      document.getElementById('drawing-banner').style.display = 'block';
      document.getElementById('draw-instruction').textContent = 'Klik marker atau peta untuk titik awal';

      updateWaypointCount();
    }

    function addDrawingPoint(latLng) {
      drawingState.path.push({ lat: latLng.lat(), lng: latLng.lng() });

      // Try to detect if this point is near a marker (if not already set)
      if (drawingState.path.length === 1 && !drawingState.fromMarker) {
        // Check if first point is near any marker
        const nearestMarker = findNearestMarker(latLng);
        if (nearestMarker) {
          drawingState.fromMarker = nearestMarker;
        }
      } else if (drawingState.path.length > 1 && !drawingState.toMarker) {
        // Check if last point is near any marker
        const nearestMarker = findNearestMarker(latLng);
        if (nearestMarker) {
          drawingState.toMarker = nearestMarker;
        }
      }

      // Update polyline preview (no temp markers - cleaner look)
      if (drawingState.polyline) drawingState.polyline.setMap(null);
      drawingState.polyline = new google.maps.Polyline({
        path: drawingState.path,
        strokeColor: document.getElementById('polyline-color').value,
        strokeWeight: parseInt(document.getElementById('polyline-weight').value),
        strokeOpacity: 0.8,
        map: map
      });

      // Update instruction
      if (drawingState.path.length === 1) {
        document.getElementById('draw-instruction').textContent = 'Klik untuk tambah titik, atau simpan jalur';
      } else {
        document.getElementById('draw-instruction').textContent = 'Klik untuk tambah titik belok, atau simpan';
      }

      updateWaypointCount();
    }

    function findNearestMarker(latLng) {
      const threshold = 0.0001; // ~11 meters in degrees
      let nearest = null;
      let minDistance = Infinity;

      markers.forEach(marker => {
        const markerPos = marker.getPosition();
        
        // Calculate distance in degrees (simple approximation)
        const latDiff = Math.abs(latLng.lat() - markerPos.lat());
        const lngDiff = Math.abs(latLng.lng() - markerPos.lng());
        const distanceInDegrees = Math.sqrt(latDiff * latDiff + lngDiff * lngDiff);
        
        if (distanceInDegrees < threshold && distanceInDegrees < minDistance) {
          minDistance = distanceInDegrees;
          const data = marker.markerData;
          nearest = { id: data.id, type: data.tipe, ref_id: data.ref_id || null };
        }
      });

      return nearest;
    }

    function undoDrawingPoint() {
      if (drawingState.path.length > 0) {
        drawingState.path.pop();
        if (drawingState.polyline) drawingState.polyline.setPath(drawingState.path);

        // Update instruction
        if (drawingState.path.length === 0) {
          document.getElementById('draw-instruction').textContent = 'Klik marker atau peta untuk titik awal';
        } else if (drawingState.path.length === 1) {
          document.getElementById('draw-instruction').textContent = 'Klik untuk tambah titik, atau simpan jalur';
        }

        updateWaypointCount();
      }
    }

    function updateWaypointCount() {
      document.getElementById('waypoint-count').textContent = drawingState.path.length + ' titik';
      document.getElementById('btn-save-polyline').disabled = drawingState.path.length < 2;
    }

    function cancelDrawing() {
      isDrawing = false;
      clearTempDrawing();

      document.getElementById('draw-setup').style.display = 'block';
      document.getElementById('drawing-mode').style.display = 'none';
      document.getElementById('drawing-banner').style.display = 'none';

      drawingState = { type: 'custom', path: [], polyline: null, tempMarkers: [], fromMarker: null, toMarker: null };
    }

    function clearTempDrawing() {
      if (drawingState.polyline) { drawingState.polyline.setMap(null); drawingState.polyline = null; }
    }

    async function savePolyline() {
      if (drawingState.path.length < 2) { alert('Minimal 2 titik!'); return; }

      // Auto-detect connection type based on markers
      let detectedType = 'custom';
      let markerFromId = null;
      let markerToId = null;

      if (drawingState.fromMarker && drawingState.toMarker) {
        const fromType = drawingState.fromMarker.type;
        const toType = drawingState.toMarker.type;

        if ((fromType === 'odc' && toType === 'odp') || (fromType === 'odp' && toType === 'odc')) {
          // Handle both directions: ODC to ODP or ODP to ODC
          detectedType = 'odc_to_odp';
          // Always set ODC as from and ODP as to
          if (fromType === 'odc') {
            markerFromId = drawingState.fromMarker.id;
            markerToId = drawingState.toMarker.id;
          } else {
            // Reverse: ODP to ODC, swap them
            markerFromId = drawingState.toMarker.id;
            markerToId = drawingState.fromMarker.id;
          }
        } else if (fromType === 'odp' && toType === 'odp') {
          detectedType = 'odp_to_odp';
          markerFromId = drawingState.fromMarker.id;
          markerToId = drawingState.toMarker.id;
        } else if (fromType === 'odp' && toType === 'user') {
          detectedType = 'odp_to_user';
          markerFromId = drawingState.fromMarker.id;
          markerToId = drawingState.toMarker.id;
        } else if (fromType === 'user' && toType === 'odp') {
          // Reverse: User to ODP, swap them
          detectedType = 'odp_to_user';
          markerFromId = drawingState.toMarker.id;
          markerToId = drawingState.fromMarker.id;
        }
      }

      const data = {
        tipe: detectedType,
        koordinat: drawingState.path,
        warna: document.getElementById('polyline-color').value,
        ketebalan: parseInt(document.getElementById('polyline-weight').value),
        animasi: true,
        marker_from_id: markerFromId,
        marker_to_id: markerToId
      };

      try {
        const response = await fetch('{{ route("google-map.polyline.save") }}', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
          body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.status) {
          cancelDrawing();
          loadMapData();
        } else {
          alert('Gagal: ' + result.message);
        }
      } catch (error) {
        alert('Error: ' + error.message);
      }
    }

    async function deletePolyline(id) {
      if (!confirm('Hapus jalur ini?')) return;
      try {
        await fetch(`/google-map/polyline/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } });
        loadMapData();
      } catch (error) {
        alert('Error: ' + error.message);
      }
    }

    async function clearAllPolylines() {
      if (!confirm('Hapus SEMUA jalur?')) return;
      try {
        await fetch('{{ route("google-map.polyline.clear") }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } });
        loadMapData();
      } catch (error) {
        alert('Error: ' + error.message);
      }
    }

    // === EVENT LISTENERS ===

    document.addEventListener('DOMContentLoaded', () => {
      // Tabs
      document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
          document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
          tab.classList.add('active');
          document.getElementById('tab-markers').style.display = 'none';
          document.getElementById('tab-polylines').style.display = 'none';
          document.getElementById('tab-settings').style.display = 'none';
          document.getElementById('tab-' + tab.dataset.tab).style.display = 'block';
        });
      });

      // Marker actions
      document.getElementById('btn-import').addEventListener('click', openImportModal);
      document.getElementById('btn-clear-markers').addEventListener('click', clearAllMarkers);

      // Import modal
      document.getElementById('close-import-modal').addEventListener('click', closeImportModal);
      document.getElementById('cancel-import').addEventListener('click', closeImportModal);
      document.getElementById('confirm-import').addEventListener('click', confirmImport);

      document.getElementById('marker-type').addEventListener('change', () => {
        const hasType = document.getElementById('marker-type').value;
        const hasName = document.getElementById('marker-name').value;
        document.getElementById('btn-add-marker').disabled = !hasType || !hasName;
      });
      document.getElementById('marker-name').addEventListener('input', () => {
        const hasType = document.getElementById('marker-type').value;
        const hasName = document.getElementById('marker-name').value;
        document.getElementById('btn-add-marker').disabled = !hasType || !hasName;
      });
      document.getElementById('btn-add-marker').addEventListener('click', startAddMarker);

      // Polyline actions
      document.getElementById('btn-start-draw').addEventListener('click', startDrawing);
      document.getElementById('btn-undo').addEventListener('click', undoDrawingPoint);
      document.getElementById('btn-cancel-draw').addEventListener('click', cancelDrawing);
      document.getElementById('btn-save-polyline').addEventListener('click', savePolyline);
      document.getElementById('btn-clear-polylines').addEventListener('click', clearAllPolylines);

      // Color preset buttons
      document.querySelectorAll('.color-preset').forEach(btn => {
        btn.addEventListener('click', () => {
          document.getElementById('polyline-color').value = btn.dataset.color;
          // Visual feedback - highlight selected
          document.querySelectorAll('.color-preset').forEach(b => b.style.borderColor = '#fff');
          btn.style.borderColor = '#333';
        });
      });

      // Settings toggles
      document.getElementById('toggle-polylines').addEventListener('click', function() {
        this.classList.toggle('active');
        showPolylines = this.classList.contains('active');
        renderMap();
      });
      document.getElementById('toggle-animation').addEventListener('click', function() {
        this.classList.toggle('active');
        animatePolylines = this.classList.contains('active');
        renderMap();
      });
      document.getElementById('toggle-draggable').addEventListener('click', function() {
        this.classList.toggle('active');
        markersAreDraggable = this.classList.contains('active');
        markers.forEach(m => m.setDraggable(markersAreDraggable));
      });

      // Center on server button
      document.getElementById('btn-center-server').addEventListener('click', centerOnServer);

      // Filter toggles
      document.querySelectorAll('[data-filter]').forEach(toggle => {
        toggle.addEventListener('click', function() {
          this.classList.toggle('active');
          visibleTypes[this.dataset.filter] = this.classList.contains('active');
          renderMap();
        });
      });

      // Panel show/hide
      document.getElementById('close-stats').addEventListener('click', () => {
        document.getElementById('stats-panel').classList.add('hidden');
        document.getElementById('toggle-stats').classList.add('visible');
      });
      document.getElementById('close-control').addEventListener('click', () => {
        document.getElementById('control-panel').classList.add('hidden');
        document.getElementById('toggle-control').classList.add('visible');
      });
      document.getElementById('toggle-stats').addEventListener('click', () => {
        document.getElementById('stats-panel').classList.remove('hidden');
        document.getElementById('toggle-stats').classList.remove('visible');
      });
      document.getElementById('toggle-control').addEventListener('click', () => {
        document.getElementById('control-panel').classList.remove('hidden');
        document.getElementById('toggle-control').classList.remove('visible');
      });
    });

    function loadGoogleMaps() {
      const script = document.createElement('script');
      script.src = 'https://maps.googleapis.com/maps/api/js?key={{ $apiKey }}&libraries=geometry&callback=initMap';
      script.async = true;
      script.defer = true;
      document.head.appendChild(script);
    }

    loadGoogleMaps();
  </script>
</body>
</html>
