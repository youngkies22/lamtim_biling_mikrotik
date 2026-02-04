@extends('layouts/layoutMaster')

@section('title', 'Data Pelanggan Mapping')

@section("vendor-style")
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<style>
  #map {
    height: 100vh;
    position: relative;
    z-index: 1;
    transition: all 0.3s ease;
  }

  .filter-overlay {
    position: relative;
    z-index: 9991;
  }

  /* Pastikan tombol toggle distance dan name selalu terlihat */
  #toggle-distance,
  #toggle-name {
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
    position: relative !important;
  }

  /* Pastikan map container tidak membuat stacking context yang mengganggu */
  #map.leaflet-container {
    z-index: 1 !important;
    position: relative !important;
  }

  /* Pastikan leaflet container memiliki z-index rendah */
  .leaflet-container {
    z-index: 1 !important;
    position: relative !important;
  }

  .leaflet-pane {
    z-index: 1 !important;
  }

  .leaflet-map-pane {
    z-index: 1 !important;
  }

  .leaflet-tile-pane {
    z-index: 1 !important;
  }

  .leaflet-overlay-pane {
    z-index: 2 !important;
  }

  .leaflet-shadow-pane {
    z-index: 3 !important;
  }

  .leaflet-marker-pane {
    z-index: 4 !important;
  }

  .leaflet-tooltip-pane {
    z-index: 5 !important;
  }

  .leaflet-popup-pane {
    z-index: 6 !important;
  }

  .leaflet-top,
  .leaflet-bottom {
    z-index: 7 !important;
  }

  .leaflet-control {
    z-index: 7 !important;
  }

  /* Perbaiki z-index untuk dropdown menu agar muncul di atas map */
  .dropdown-menu {
    z-index: 9999 !important;
  }

  .dropdown {
    z-index: 9999 !important;
  }

  .dropdown.show {
    z-index: 9999 !important;
  }

  .dropdown-toggle::after {
    z-index: 10000 !important;
  }

  /* Perbaiki z-index untuk menu horizontal (menu-sub) - hanya z-index, jangan ubah position */
  .menu-sub {
    z-index: 9999 !important;
  }

  .menu-dropdown {
    z-index: 9999 !important;
  }

  .menu-item.menu-dropdown {
    z-index: 9999 !important;
  }

  .menu-item.menu-dropdown.open {
    z-index: 9999 !important;
  }

  .menu-horizontal {
    z-index: 9998 !important;
  }

  .layout-menu-horizontal {
    z-index: 9998 !important;
  }

  /* Pastikan semua elemen dropdown di atas map */
  body .dropdown-menu {
    z-index: 9999 !important;
  }

  body .navbar .dropdown-menu {
    z-index: 9999 !important;
  }

  body .menu-sub {
    z-index: 9999 !important;
  }

  /* Pastikan parent element dropdown juga di atas */
  .navbar-nav .dropdown {
    z-index: 9999 !important;
  }

  .navbar-nav .nav-item.dropdown {
    z-index: 9999 !important;
  }

  /* Pastikan semua elemen di navbar di atas map */
  .layout-navbar .dropdown-menu {
    z-index: 9999 !important;
  }

  .layout-navbar .navbar-nav {
    z-index: 9999 !important;
  }

  .layout-navbar .nav-item {
    z-index: 9999 !important;
  }

  /* Pastikan layout wrapper tidak menghalangi */
  .layout-wrapper {
    z-index: 10 !important;
  }

  .layout-navbar {
    z-index: 9998 !important;
  }

  /* Pastikan content wrapper di atas map */
  .content-wrapper {
    z-index: 10 !important;
  }

  .layout-page {
    z-index: 10 !important;
  }

  .offcanvas {
    z-index: 1055 !important;
  }

  .modal {
    z-index: 1060 !important;
  }

  .btn-group {
    z-index: 1050 !important;
    position: relative !important;
  }

  /* Pastikan card dan form di atas map */
  .card {
    position: relative;
    z-index: 10 !important;
  }

  /* Pastikan navbar dan menu di atas map */
  .layout-navbar {
    z-index: 9998 !important;
    position: relative !important;
  }

  .layout-menu {
    z-index: 1020 !important;
  }

  .navbar {
    z-index: 9998 !important;
    position: relative !important;
  }

  .navbar-brand,
  .navbar-nav,
  .navbar-nav .nav-link {
    z-index: 9998 !important;
    position: relative !important;
  }

  /* Pastikan dropdown di navbar juga di atas map */
  .navbar .dropdown-menu {
    z-index: 9999 !important;
    position: absolute !important;
  }

  .navbar .dropdown {
    z-index: 9999 !important;
    position: relative !important;
  }

  .navbar .dropdown.show {
    z-index: 9999 !important;
  }

  /* Pastikan tooltip dan popover di atas map */
  .tooltip {
    z-index: 1070 !important;
  }

  .popover {
    z-index: 1060 !important;
  }

  /* Pastikan select2 dropdown di atas map */
  .select2-container {
    z-index: 1050 !important;
  }

  .select2-dropdown {
    z-index: 1051 !important;
  }

  .custom-marker {
    border-radius: 50%;
    width: 16px;
    height: 16px;
    display: block;
    border: 2px solid white;
    box-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
  }

  .odc-icon {
    background-color: orange;
  }

  .odp-icon {
    background-color: green;
  }

  .distance-label {
    background: rgba(255, 255, 255, 0.8);
    padding: 2px 4px;
    border-radius: 4px;
    font-size: 10px;
    color: #333;
    border: 1px solid #ccc;
  }

  .name-label {
    background: rgba(255, 255, 255, 0.95);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
    color: #333;
    border: 1px solid #007bff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    white-space: nowrap;
  }

  .custom-marker {
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0%, 100% {
      transform: scale(1);
      opacity: 1;
    }
    50% {
      transform: scale(1.1);
      opacity: 0.8;
    }
  }

  .leaflet-control-fullscreen-btn {
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    cursor: pointer;
    background: #fff;
    border: none;
    text-decoration: none;
    color: #333;
  }
  .leaflet-control-fullscreen-btn:hover {
    background: #f4f4f4;
  }

  /* Filter panel inside map (visible in fullscreen) */
  .leaflet-control-filter-panel {
    display: none;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 8px;
    padding: 10px 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
    max-width: 420px;
  }

  /* Show filter panel only when map is fullscreen */
  #map:fullscreen .leaflet-control-filter-panel,
  #map:-webkit-full-screen .leaflet-control-filter-panel {
    display: block !important;
  }

  .filter-panel-title {
    font-size: 12px;
    font-weight: 600;
    color: #333;
    margin-bottom: 6px;
  }

  .filter-panel-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-bottom: 6px;
  }

  .filter-panel-buttons .fp-btn {
    padding: 4px 10px;
    font-size: 11px;
    border: 1px solid #7c7c7c;
    border-radius: 4px;
    cursor: pointer;
    background: #fff;
    color: #333;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s;
  }

  .filter-panel-buttons .fp-btn:hover {
    background: #e9ecef;
  }

  .filter-panel-buttons .fp-btn.fp-active {
    background: #696cff;
    color: #fff;
    border-color: #696cff;
  }

  .filter-panel-toggles {
    display: flex;
    gap: 4px;
    border-top: 1px solid #ddd;
    padding-top: 6px;
  }

  .filter-panel-toggles .fp-toggle {
    padding: 3px 8px;
    font-size: 10px;
    border: 1px solid #7c7c7c;
    border-radius: 4px;
    cursor: pointer;
    background: #fff;
    color: #555;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    transition: all 0.2s;
  }

  .filter-panel-toggles .fp-toggle:hover {
    background: #e9ecef;
  }

  .filter-panel-toggles .fp-toggle.fp-on {
    background: #6c757d;
    color: #fff;
    border-color: #6c757d;
  }
</style>
@endsection


@section("vendor-script")
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
@endsection

@section('page-script')

<script>
  // Perbaiki z-index dropdown saat dibuka - hanya z-index, jangan ubah position
  $(document).on('show.bs.dropdown', '.dropdown', function() {
    $(this).css('z-index', '9999');
    $(this).find('.dropdown-menu').css('z-index', '9999');
    // Pastikan parent juga memiliki z-index tinggi
    $(this).closest('.navbar-nav, .navbar, .layout-navbar').css('z-index', '9998');
  });

  $(document).on('shown.bs.dropdown', '.dropdown', function() {
    $(this).css('z-index', '9999');
    $(this).find('.dropdown-menu').css('z-index', '9999');
    // Pastikan parent juga memiliki z-index tinggi
    $(this).closest('.navbar-nav, .navbar, .layout-navbar').css('z-index', '9998');
  });

  // Pastikan saat dropdown ditutup, z-index tetap tinggi untuk navbar
  $(document).on('hide.bs.dropdown', '.dropdown', function() {
    $(this).closest('.navbar-nav, .navbar, .layout-navbar').css('z-index', '9998');
  });

  // Perbaiki z-index untuk menu horizontal (menu-sub)
  function fixMenuZIndex() {
    // Pastikan menu-sub selalu di atas map - hanya z-index, jangan ubah position
    $('.menu-sub').css('z-index', '9999');
    $('.menu-dropdown').css('z-index', '9999');
    $('.menu-item.menu-dropdown').css('z-index', '9999');
    $('.menu-horizontal, .layout-menu-horizontal').css('z-index', '9998');
    $('.menu-item.menu-dropdown.open').css('z-index', '9999');
  }

  $(document).ready(function() {
    fixMenuZIndex();
    
    // Update z-index saat menu dibuka/ditutup
    $(document).on('click', '.menu-toggle', function() {
      setTimeout(fixMenuZIndex, 50);
    });

    // Observer untuk perubahan class
    const observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
          setTimeout(fixMenuZIndex, 10);
        }
      });
    });

    // Observe semua menu-item
    $('.menu-item.menu-dropdown').each(function() {
      observer.observe(this, {
        attributes: true,
        attributeFilter: ['class']
      });
    });
  });

  const latitude = "{{ config('services.mapping.latitude-server') }}";
  const longitude = "{{ config('services.mapping.longitude-server') }}";
  const centerLatLng = [parseFloat(latitude), parseFloat(longitude)];

  // Inisialisasi peta
  const map = L.map('map').setView(centerLatLng, 13);

  // Tile layer dari OpenStreetMap
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 35,
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  // Custom fullscreen control (native browser API)
  L.Control.FullscreenCustom = L.Control.extend({
    options: { position: 'topleft' },
    onAdd: function(map) {
      const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
      const btn = L.DomUtil.create('a', 'leaflet-control-fullscreen-btn', container);
      btn.href = '#';
      btn.title = 'Fullscreen';
      btn.innerHTML = '&#x26F6;';
      btn.setAttribute('role', 'button');

      L.DomEvent.disableClickPropagation(container);
      L.DomEvent.on(btn, 'click', function(e) {
        L.DomEvent.preventDefault(e);
        const mapEl = map.getContainer();
        if (!document.fullscreenElement) {
          mapEl.requestFullscreen().then(() => {
            btn.innerHTML = '&#x2716;';
            btn.title = 'Keluar Fullscreen';
            setTimeout(() => map.invalidateSize(), 200);
          }).catch(() => {});
        } else {
          document.exitFullscreen().then(() => {
            btn.innerHTML = '&#x26F6;';
            btn.title = 'Fullscreen';
            setTimeout(() => map.invalidateSize(), 200);
          }).catch(() => {});
        }
      });

      document.addEventListener('fullscreenchange', function() {
        if (!document.fullscreenElement) {
          btn.innerHTML = '&#x26F6;';
          btn.title = 'Fullscreen';
          setTimeout(() => map.invalidateSize(), 200);
        }
      });

      return container;
    }
  });
  map.addControl(new L.Control.FullscreenCustom());

  // Filter panel control (visible only in fullscreen)
  L.Control.FilterPanel = L.Control.extend({
    options: { position: 'topleft' },
    onAdd: function(map) {
      const container = L.DomUtil.create('div', 'leaflet-control leaflet-control-filter-panel');
      L.DomEvent.disableClickPropagation(container);
      L.DomEvent.disableScrollPropagation(container);

      container.innerHTML = `
        <div class="filter-panel-title">Filter Tampilan</div>
        <div class="filter-panel-buttons">
          <a href="#" class="fp-btn fp-active" data-fp-filter="all">Semua</a>
          <a href="#" class="fp-btn" data-fp-filter="odc">ODC</a>
          <a href="#" class="fp-btn" data-fp-filter="odp">ODP</a>
          <a href="#" class="fp-btn" data-fp-filter="user">Pelanggan</a>
        </div>
        <div class="filter-panel-toggles">
          <a href="#" class="fp-toggle fp-on" id="fp-toggle-distance">Jarak: ON</a>
          <a href="#" class="fp-toggle" id="fp-toggle-name">Nama: OFF</a>
        </div>
      `;

      // Filter buttons
      container.querySelectorAll('.fp-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const type = this.getAttribute('data-fp-filter');
          // Update active state in this panel
          container.querySelectorAll('.fp-btn').forEach(b => b.classList.remove('fp-active'));
          this.classList.add('fp-active');
          // Call the global filter function
          if (typeof window.filterMap === 'function') {
            window.filterMap(type);
          }
          // Sync external buttons
          document.querySelectorAll('[id^="filter-"]').forEach(b => {
            b.classList.remove('active', 'btn-primary');
            b.classList.add('btn-outline-primary');
          });
          const extBtn = document.getElementById('filter-' + type);
          if (extBtn) {
            extBtn.classList.add('active');
            extBtn.classList.remove('btn-outline-primary');
            extBtn.classList.add('btn-primary');
          }
        });
      });

      // Distance toggle
      const fpDistBtn = container.querySelector('#fp-toggle-distance');
      fpDistBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (typeof window.toggleDistanceLabels === 'function') {
          window.toggleDistanceLabels();
        }
        // Sync state
        if (distanceLabelsVisible) {
          this.textContent = 'Jarak: ON';
          this.classList.add('fp-on');
        } else {
          this.textContent = 'Jarak: OFF';
          this.classList.remove('fp-on');
        }
      });

      // Name toggle
      const fpNameBtn = container.querySelector('#fp-toggle-name');
      fpNameBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (typeof window.toggleNameLabels === 'function') {
          window.toggleNameLabels();
        }
        // Sync state
        if (nameLabelsVisible) {
          this.textContent = 'Nama: ON';
          this.classList.add('fp-on');
        } else {
          this.textContent = 'Nama: OFF';
          this.classList.remove('fp-on');
        }
      });

      return container;
    }
  });
  map.addControl(new L.Control.FilterPanel());

  // Marker Server Pusat
  L.marker(centerLatLng)
    .addTo(map)
    .bindPopup('<b>Server Kang Wifi</b>')
    .openPopup();


  function tampilkanJarakGaris(p1, p2, satuan = 'm', type = 'all') {
    const distance = map.distance(p1, p2); // dalam meter
    const midPoint = [
      (p1[0] + p2[0]) / 2,
      (p1[1] + p2[1]) / 2
    ];

    let label = satuan === 'km' && distance > 1000
      ? (distance / 1000).toFixed(2) + ' km'
      : distance.toFixed(0) + ' m';

    const tooltip = L.tooltip({
      permanent: true,
      direction: 'center',
      className: 'distance-label'
    })
    .setLatLng(midPoint)
    .setContent(label)
    .addTo(map);
    
    // Simpan type untuk filter
    tooltip.options.type = type;
    allDistanceLabels.push(tooltip);
    
    return tooltip;
  }


  // Fungsi untuk membuat animated polyline modern dengan efek garis bergerak
  function createAnimatedPolyline(coordinates, options = {}) {
    const defaultOptions = {
      color: '#3388ff',
      weight: 3,
      opacity: 0.85,
      smoothFactor: 1
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    
    // Buat polyline dengan dash array untuk efek animasi
    const polyline = L.polyline(coordinates, {
      color: finalOptions.color,
      weight: finalOptions.weight,
      opacity: finalOptions.opacity,
      smoothFactor: finalOptions.smoothFactor,
      className: 'animated-polyline'
    });
    
    // Tambahkan animasi setelah polyline ditambahkan ke map
    polyline.on('add', function() {
      const path = this._path;
      if (path) {
        // Set stroke-dasharray untuk efek garis putus-putus
        const dashLength = 15;
        const gapLength = 8;
        path.setAttribute('stroke-dasharray', `${dashLength},${gapLength}`);
        
        // Buat animasi dengan menggunakan SVG animate
        const animate = document.createElementNS('http://www.w3.org/2000/svg', 'animate');
        animate.setAttribute('attributeName', 'stroke-dashoffset');
        animate.setAttribute('from', '0');
        animate.setAttribute('to', dashLength + gapLength);
        animate.setAttribute('dur', '1s');
        animate.setAttribute('repeatCount', 'indefinite');
        path.appendChild(animate);
        
        // Tambahkan efek glow/shadow
        path.style.filter = `drop-shadow(0 0 3px ${finalOptions.color})`;
        path.style.transition = 'opacity 0.3s ease';
      }
    });
    
    return polyline;
  }

let odcMap = {};
let odpMap = {};
let odpPending = [];
let allMarkers = []; // Simpan semua marker untuk filter
let allPolylines = []; // Simpan semua polyline untuk filter
let allDistanceLabels = []; // Simpan semua label jarak untuk filter
let allNameLabels = []; // Simpan semua label nama untuk filter
let nameLabelsVisible = false; // Status tampilan label nama
let distanceLabelsVisible = true; // Status tampilan label jarak

// Fungsi untuk filter tampilan
window.filterMap = function(type) {
  // Filter markers terlebih dahulu
  allMarkers.forEach(marker => {
    const markerType = marker.options.type;
    if (type === 'all' || markerType === type) {
      if (!map.hasLayer(marker)) {
        marker.addTo(map);
      }
    } else {
      if (map.hasLayer(marker)) {
        map.removeLayer(marker);
      }
    }
  });

  // Filter polylines - tampilkan jika type sesuai dengan filter
  allPolylines.forEach(polyline => {
    if (!polyline || !polyline.options) return; // Skip jika polyline tidak valid
    
    const polylineType = polyline.options.type;
    let shouldShow = false;
    
    if (type === 'all') {
      shouldShow = true;
    } else if (type === 'odc') {
      // Tampilkan garis server->ODC (type: 'odc')
      shouldShow = polylineType === 'odc';
    } else if (type === 'odp') {
      // Tampilkan garis ODC->ODP (type: 'odp') dan ODP->ODP parent (type: 'odp_parent')
      shouldShow = polylineType === 'odp' || polylineType === 'odp_parent';
    } else if (type === 'user') {
      // Tampilkan garis ODP->User (type: 'user')
      shouldShow = polylineType === 'user';
    }
    
    try {
      if (shouldShow) {
        // Pastikan polyline ditambahkan ke map
        if (!map.hasLayer(polyline)) {
          polyline.addTo(map);
        }
      } else {
        // Hapus polyline dari map jika tidak sesuai filter
        if (map.hasLayer(polyline)) {
          map.removeLayer(polyline);
        }
      }
    } catch (e) {
      console.error('Error filtering polyline:', e);
    }
  });

  // Filter distance labels
  allDistanceLabels.forEach(label => {
    const labelType = label.options.type;
    let shouldShow = false;
    
    if (type === 'all') {
      shouldShow = true;
    } else if (type === 'odc') {
      shouldShow = labelType === 'odc';
    } else if (type === 'odp') {
      shouldShow = labelType === 'odp';
    } else if (type === 'user') {
      shouldShow = labelType === 'user';
    }
    
    // Hanya tampilkan jika sesuai filter DAN distance labels visible
    if (shouldShow && distanceLabelsVisible) {
      if (!map.hasLayer(label)) {
        label.addTo(map);
      }
    } else {
      if (map.hasLayer(label)) {
        map.removeLayer(label);
      }
    }
  });

  // Filter name labels
  allNameLabels.forEach(label => {
    const labelType = label.options.type;
    let shouldShow = false;
    
    if (type === 'all') {
      shouldShow = true;
    } else if (type === 'odc') {
      shouldShow = labelType === 'odc';
    } else if (type === 'odp') {
      shouldShow = labelType === 'odp';
    } else if (type === 'user') {
      shouldShow = labelType === 'user';
    }
    
    // Hanya tampilkan jika sesuai filter DAN name labels visible
    if (shouldShow && nameLabelsVisible) {
      if (!map.hasLayer(label)) {
        label.addTo(map);
      }
    } else {
      if (map.hasLayer(label)) {
        map.removeLayer(label);
      }
    }
  });
}

// Fungsi untuk toggle show/hide jarak
window.toggleDistanceLabels = function() {
  distanceLabelsVisible = !distanceLabelsVisible;
  
  const toggleBtn = document.getElementById('toggle-distance');
  const labelText = document.getElementById('distance-label-text');
  
  if (distanceLabelsVisible) {
    labelText.textContent = 'Sembunyikan';
    toggleBtn.classList.remove('btn-outline-secondary');
    toggleBtn.classList.add('btn-secondary');
  } else {
    labelText.textContent = 'Tampilkan';
    toggleBtn.classList.remove('btn-secondary');
    toggleBtn.classList.add('btn-outline-secondary');
  }
  
  // Update semua distance labels berdasarkan status
  allDistanceLabels.forEach(label => {
    if (distanceLabelsVisible) {
      // Tampilkan label jika sesuai dengan filter aktif
      const currentFilter = document.querySelector('[id^="filter-"].active')?.id?.replace('filter-', '') || 'all';
      const labelType = label.options.type;
      let shouldShow = false;
      
      if (currentFilter === 'all') {
        shouldShow = true;
      } else if (currentFilter === 'odc') {
        shouldShow = labelType === 'odc';
      } else if (currentFilter === 'odp') {
        shouldShow = labelType === 'odp';
      } else if (currentFilter === 'user') {
        shouldShow = labelType === 'user';
      }
      
      if (shouldShow && !map.hasLayer(label)) {
        label.addTo(map);
      }
    } else {
      // Sembunyikan semua label
      if (map.hasLayer(label)) {
        map.removeLayer(label);
      }
    }
  });
}

// Fungsi untuk membuat label nama
function tampilkanNamaLabel(position, nama, type = 'all') {
  const nameLabel = L.tooltip({
    permanent: true,
    direction: 'top',
    className: 'name-label',
    offset: [0, -10]
  })
  .setLatLng(position)
  .setContent(nama);
  
  // Simpan type untuk filter
  nameLabel.options.type = type;
  allNameLabels.push(nameLabel);
  
  return nameLabel;
}


// Fungsi untuk toggle show/hide nama
window.toggleNameLabels = function() {
  nameLabelsVisible = !nameLabelsVisible;
  
  const toggleBtn = document.getElementById('toggle-name');
  const labelText = document.getElementById('name-label-text');
  
  if (nameLabelsVisible) {
    labelText.textContent = 'Sembunyikan';
    toggleBtn.classList.remove('btn-outline-secondary');
    toggleBtn.classList.add('btn-secondary');
  } else {
    labelText.textContent = 'Tampilkan';
    toggleBtn.classList.remove('btn-secondary');
    toggleBtn.classList.add('btn-outline-secondary');
  }
  
  // Update semua name labels berdasarkan status
  allNameLabels.forEach(label => {
    if (nameLabelsVisible) {
      // Tampilkan label jika sesuai dengan filter aktif
      const currentFilter = document.querySelector('[id^="filter-"].active')?.id?.replace('filter-', '') || 'all';
      const labelType = label.options.type;
      let shouldShow = false;
      
      if (currentFilter === 'all') {
        shouldShow = true;
      } else if (currentFilter === 'odc') {
        shouldShow = labelType === 'odc';
      } else if (currentFilter === 'odp') {
        shouldShow = labelType === 'odp';
      } else if (currentFilter === 'user') {
        shouldShow = labelType === 'user';
      }
      
      if (shouldShow && !map.hasLayer(label)) {
        label.addTo(map);
      }
    } else {
      // Sembunyikan semua label
      if (map.hasLayer(label)) {
        map.removeLayer(label);
      }
    }
  });
}

fetch(`/mapping/json/mapping/show/user`)
  .then(response => response.json())
  .then(res => {
    const data = res.data || [];

    data.forEach(item => {
      const lat = parseFloat(item.latitude);
      const lng = parseFloat(item.longitude);
      if (isNaN(lat) || isNaN(lng)) return;

      const position = [lat, lng];

      let iconClass = '';
      if (item.type === 'ODC') iconClass = 'odc-icon';
      if (item.type === 'ODP') iconClass = 'odp-icon';

      const icon = L.divIcon({
        className: '',
        html: `<span class="custom-marker ${iconClass}"></span>`,
        iconSize: [16, 16],
        iconAnchor: [8, 8]
      });

      if (item.type === 'ODC') {
        // Simpan marker & posisi ODC
        const marker = L.marker(position, { icon, type: 'odc' }).addTo(map);
        marker.bindPopup(`<b>ODC</b><br>Nama: ${item.nama}
        <br>Port: ${item.port}
        <br>Port Sisa: ${item.portSisa}
        <br>Port OLT: ${item.portOlt}
        <br>Total ODP: ${item.totalOdp}
        <br>Total User: ${item.countUser}
        `);
        odcMap[item.id] = position;
        allMarkers.push(marker);
        
        // Tambahkan label nama
        const nameLabel = tampilkanNamaLabel(position, item.nama, 'odc');
        if (nameLabelsVisible) {
          nameLabel.addTo(map);
        }

        // Garis dari server pusat ke ODC dengan animasi modern
        const polyline = createAnimatedPolyline([centerLatLng, position], {
          color: '#ff9800',
          weight: 3,
          opacity: 0.85,
          type: 'odc'
        });
        polyline.addTo(map);
        allPolylines.push(polyline);
        
        // Tampilkan jarak secara otomatis
        tampilkanJarakGaris(centerLatLng, position, 'm', 'odc');
      }

      else if (item.type === 'ODP') {
        // Simpan marker & posisi ODP
        const marker = L.marker(position, { icon, type: 'odp' }).addTo(map);
        marker.bindPopup(`<b>ODP</b><br>Nama: ${item.nama}<br>Port: ${item.port}<br>Port Sisa: ${item.portSisa}
        <br>Port ODC: ${item.portOdc}
        <br>Total User: ${item.countUser}
        `);
        odpMap[item.id] = position;
        allMarkers.push(marker);
        
        // Tambahkan label nama
        const nameLabel = tampilkanNamaLabel(position, item.nama, 'odp');
        if (nameLabelsVisible) {
          nameLabel.addTo(map);
        }

        // Jika ODC-nya sudah tersedia dengan animasi modern
        if (item.idOdc && odcMap[item.idOdc]) {
          const polyline = createAnimatedPolyline([odcMap[item.idOdc], position], {
            color: '#4caf50',
            weight: 3,
            opacity: 0.85,
            type: 'odp'
          });
          polyline.addTo(map);
          allPolylines.push(polyline);
          // Tampilkan jarak secara otomatis
          tampilkanJarakGaris(odcMap[item.idOdc], position, 'm', 'odp');
        }

        // Garis dari ODP ke ODP parent (warna ungu) dengan animasi modern
        if (item.idOdp) {
          if (odpMap[item.idOdp]) {
            const polyline = createAnimatedPolyline([odpMap[item.idOdp], position], {
              color: '#9c27b0',
              weight: 3,
              opacity: 0.85,
              type: 'odp_parent'
            });
            polyline.addTo(map);
            allPolylines.push(polyline);
            // Tampilkan jarak secara otomatis
            tampilkanJarakGaris(odpMap[item.idOdp], position, 'm', 'odp');
          } else {
            odpPending.push({ from: item.idOdp, to: position });
          }
        }
      }

      else if (item.type === 'USER') {
        const userIcon = L.divIcon({
          className: '',
          html: `<span class="custom-marker" style="background-color: blue;"></span>`,
          iconSize: [16, 16],
          iconAnchor: [8, 8]
        });

        const marker = L.marker(position, { icon: userIcon, type: 'user' }).addTo(map);
        marker.bindPopup(`<b>USER</b>
        <br>ID User: ${item.idUser}
        <br>Nama: ${item.nama}
        <br>Kategori: ${item.kategori}
        <br>Paket: ${item.paket}`);
        allMarkers.push(marker);

        // Jika ODP-nya sudah tersedia dengan animasi modern
        if (item.idOdp && odpMap[item.idOdp]) {
          const polyline = createAnimatedPolyline([odpMap[item.idOdp], position], {
            color: '#2196f3',
            weight: 3,
            opacity: 0.85,
            type: 'user'
          });
          polyline.addTo(map);
          allPolylines.push(polyline);
          // Tampilkan jarak secara otomatis
          tampilkanJarakGaris(odpMap[item.idOdp], position, 'm', 'user');
        }
      }
    });

    // Gambar garis ODP-ODP yang pending dengan animasi modern
    odpPending.forEach(p => {
      if (odpMap[p.from]) {
        const polyline = createAnimatedPolyline([odpMap[p.from], p.to], {
          color: '#9c27b0',
          weight: 3,
          opacity: 0.85,
          type: 'odp_parent'
        });
        polyline.addTo(map);
        allPolylines.push(polyline);
        // Tampilkan jarak secara otomatis
        tampilkanJarakGaris(odpMap[p.from], p.to, 'm', 'odp');
      }
    });

    // Update fungsi filter untuk update button state setelah filterMap didefinisikan
    if (typeof window.filterMap !== 'undefined') {
      const originalFilterMap = window.filterMap;
      window.filterMap = function(type) {
        originalFilterMap(type);

        // Update external button states
        document.querySelectorAll('[id^="filter-"]').forEach(btn => {
          btn.classList.remove('active');
          btn.classList.add('btn-outline-primary');
          btn.classList.remove('btn-primary');
        });

        const activeBtn = document.getElementById(`filter-${type}`);
        if (activeBtn) {
          activeBtn.classList.add('active');
          activeBtn.classList.remove('btn-outline-primary');
          activeBtn.classList.add('btn-primary');
        }

        // Sync in-map filter panel buttons
        document.querySelectorAll('.fp-btn').forEach(b => b.classList.remove('fp-active'));
        const fpBtn = document.querySelector(`.fp-btn[data-fp-filter="${type}"]`);
        if (fpBtn) fpBtn.classList.add('fp-active');
      };
    }

    // Sync in-map toggle states after data loaded
    const fpDist = document.getElementById('fp-toggle-distance');
    const fpName = document.getElementById('fp-toggle-name');
    if (fpDist) {
      fpDist.textContent = distanceLabelsVisible ? 'Jarak: ON' : 'Jarak: OFF';
      fpDist.classList.toggle('fp-on', distanceLabelsVisible);
    }
    if (fpName) {
      fpName.textContent = nameLabelsVisible ? 'Nama: ON' : 'Nama: OFF';
      fpName.classList.toggle('fp-on', nameLabelsVisible);
    }
  })
  .catch(error => {
    console.error("Gagal load data mapping:", error);
  });




</script>
@endsection

@section('content')
<div class="card p-3">
  <div class="row g-4 align-items-start">
    <div class="col-md-12">
      <div id="formMapping">
        {{-- Filter Section --}}
        <div class="row mb-3 filter-overlay">
          <div class="col-md-12">
            <div class="card shadow-lg">
              <div class="card-body">
                <h6 class="card-title mb-3">Filter Tampilan</h6>
                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                  <div class="btn-group" role="group" aria-label="Filter tampilan">
                    <button type="button" class="btn btn-primary active" onclick="filterMap('all')" id="filter-all">
                      <i class="mdi mdi-view-grid"></i> Semua
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="filterMap('odc')" id="filter-odc">
                      <i class="mdi mdi-server"></i> ODC Saja
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="filterMap('odp')" id="filter-odp">
                      <i class="mdi mdi-router"></i> ODP Saja
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="filterMap('user')" id="filter-user">
                      <i class="mdi mdi-account"></i> Pelanggan Saja
                    </button>
                  </div>
                  <div class="d-flex gap-2" style="display: flex !important; visibility: visible !important; opacity: 1 !important;">
                    <button type="button" class="btn btn-secondary" onclick="toggleDistanceLabels()" id="toggle-distance" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important;">
                      <i class="mdi mdi-ruler"></i> <span id="distance-label-text">Sembunyikan</span> Jarak
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="toggleNameLabels()" id="toggle-name" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important;">
                      <i class="mdi mdi-label"></i> <span id="name-label-text">Tampilkan</span> Nama
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Map Section --}}
        <div class="row">
          <div class="col-md-12">
            <div id="map" style="width: 100%; height: 500px; border-radius: 8px;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


@endsection