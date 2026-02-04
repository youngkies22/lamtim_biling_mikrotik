@extends('layouts/layoutMaster')

@section('title', 'Data Pelanggan Mapping')

@section("vendor-style")
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<!-- Leaflet fullscreen CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.fullscreen@1.6.0/Control.FullScreen.css" />
<style>
  #map {
    height: 100vh;
    position: relative;
    z-index: 1;
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
</style>
@endsection


@section("vendor-script")
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<!-- Leaflet fullscreen JS -->
<script src="https://unpkg.com/leaflet.fullscreen@1.6.0/Control.FullScreen.js"></script>
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

  // Inisialisasi peta dengan fullscreenControl
  const map = L.map('map', {
    fullscreenControl: true // Aktifkan tombol fullscreen
  }).setView(centerLatLng, 13);

  // Tile layer dari OpenStreetMap
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 35,
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  // Marker Server Pusat
  L.marker(centerLatLng)
    .addTo(map)
    .bindPopup('<b>Server Kang Wifi</b>')
    .openPopup();


  let allDistanceLabels = []; // Simpan semua label jarak untuk filter

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

// Fungsi untuk filter tampilan
function filterMap(type) {
  // Filter markers
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

  // Filter polylines dengan logika yang lebih kompleks
  allPolylines.forEach(polyline => {
    const polylineType = polyline.options.type;
    let shouldShow = false;
    
    if (type === 'all') {
      shouldShow = true;
    } else if (type === 'odc') {
      // Tampilkan garis server->ODC dan ODC->ODP
      shouldShow = polylineType === 'odc';
    } else if (type === 'odp') {
      // Tampilkan garis ODC->ODP dan ODP->ODP parent
      shouldShow = polylineType === 'odp' || polylineType === 'odp_parent';
    } else if (type === 'user') {
      // Tampilkan garis ODP->User
      shouldShow = polylineType === 'user';
    }
    
    if (shouldShow) {
      if (!map.hasLayer(polyline)) {
        polyline.addTo(map);
      }
    } else {
      if (map.hasLayer(polyline)) {
        map.removeLayer(polyline);
      }
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
    
    if (shouldShow) {
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
  })
  .catch(error => {
    console.error("Gagal load data mapping:", error);
  });



    //pilih area untuk di pindai
   let marker;
     map.on('click', function (e) {
    const lat = e.latlng.lat.toFixed(6);
    const lng = e.latlng.lng.toFixed(6);

    document.getElementById('lat').value = lat;
    document.getElementById('lng').value = lng;

    if (marker) {
      map.removeLayer(marker);
    }

    marker = L.marker([lat, lng]).addTo(map)
      .bindPopup(`Koordinat: ${lat}, ${lng}`)
      .openPopup();
  });

</script>
@endsection

@section('content')
<div class="card p-3">
  <div class="row g-4 align-items-start">
    <div class="col-md-12">
      <div id="formMapping">
        {{-- Filter Section --}}
        <div class="row mb-3">
          <div class="col-md-12">
            <div class="card">
              <div class="card-body">
                <h6 class="card-title mb-3">Filter Tampilan</h6>
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

<script>
  // Update fungsi filter untuk update button state
  const originalFilterMap = filterMap;
  filterMap = function(type) {
    originalFilterMap(type);
    
    // Update button states
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
  };
</script>

@endsection