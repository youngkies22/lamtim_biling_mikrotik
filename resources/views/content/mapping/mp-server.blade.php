@extends('layouts/layoutMaster')

@section('title', 'Mapping Server')

@section("vendor-style")
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
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

  .odc-icon {
    background-color: orange;
  }

  .odp-icon {
    background-color: green;
  }

  /* Animasi untuk garis yang bergerak */
  .animated-line {
    stroke-dasharray: 10, 5;
    animation: dash 1s linear infinite;
  }

  @keyframes dash {
    to {
      stroke-dashoffset: -15;
    }
  }

  /* Glow effect untuk garis */
  .line-glow {
    filter: drop-shadow(0 0 3px currentColor);
  }
</style>
@endsection


@section("vendor-script")
{{-- <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"
  integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin=""></script> --}}
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

  const latitude = "{{ config('services.mapping.latitude-server') }}";
  const longitude = "{{ config('services.mapping.longitude-server') }}";
  const centerLatLng = [parseFloat(latitude), parseFloat(longitude)];

  const map = L.map('map').setView(centerLatLng, 12);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  // Marker
  L.marker(centerLatLng)
      .addTo(map)
      .bindPopup('Server Kang Wifi')
      .openPopup();

  // Area lingkup (radius 500 meter)
  L.circle(centerLatLng, {
      radius: 500,
      color: 'blue',
      fillColor: '#3f8df7',
      fillOpacity: 0.3
  }).addTo(map);

  //load odc odp
  // centerLatLng sudah didefinisikan di atas
  let odcMap = {}; // untuk menyimpan ODC by id
  let odpMap = {}; // untuk menyimpan ODP by id
  let odpPending = []; // ODP yang perlu digambar garis ke ODP lain
  fetch("{{ route('mapping.json.server') }}")
  .then(response => response.json())
  .then(data => {
   data.forEach(item => {
      const lat = parseFloat(item.latitude);
      const lng = parseFloat(item.longitude);

      if (isNaN(lat) || isNaN(lng)) return;

      const position = [lat, lng];

      const iconClass = item.type === 'ODC' ? 'odc-icon' : 'odp-icon';
      // Buat icon HTML
      const icon = L.divIcon({
        className: '', // kosongkan agar tidak override
        html: `<span class="custom-marker ${iconClass}"></span>`,
        iconSize: [16, 16],
        iconAnchor: [8, 8]
      });
      if (item.type === 'ODC') {
        // Tambahkan marker
        const marker = L.marker(position, { icon }).addTo(map);
        marker.bindPopup(`<b>${item.type}</b><br>Nama : ${item.nama}<br>Port : ${item.port}<br>Port Sisa : ${item.portSisa}<br>Port OLT : ${item.portOlt}`);

        // Simpan posisi ODC berdasarkan ID
        odcMap[item.id] = position;

        // Garis dari server pusat ke ODC (warna oranye) dengan animasi modern
        createAnimatedPolyline([centerLatLng, position], {
          color: '#ff9800',
          weight: 3,
          opacity: 0.85
        }).addTo(map);

      } else if (item.type === 'ODP') {
        const marker = L.marker(position, { icon }).addTo(map);
        marker.bindPopup(`<b>${item.type}</b><br>Nama : ${item.nama}<br>Port : ${item.port}<br>Port Sisa : ${item.portSisa}<br>Port ODC : ${item.portOdc}`);

        // Simpan posisi ODP
        odpMap[item.id] = position;

        // Garis dari ODP ke ODC (warna hijau) dengan animasi modern
        if (item.idOdc && odcMap[item.idOdc]) {
          createAnimatedPolyline([odcMap[item.idOdc], position], {
            color: '#4caf50',
            weight: 3,
            opacity: 0.85
          }).addTo(map);
        }

        // Garis dari ODP ke ODP parent (warna ungu) dengan animasi modern
        if (item.idOdp) {
          if (odpMap[item.idOdp]) {
            createAnimatedPolyline([odpMap[item.idOdp], position], {
              color: '#9c27b0',
              weight: 3,
              opacity: 0.85
            }).addTo(map);
          } else {
            odpPending.push({ from: item.idOdp, to: position });
          }
        }
      }
    });

    // Gambar garis ODP-ODP yang pending dengan animasi modern
    odpPending.forEach(p => {
      if (odpMap[p.from]) {
        createAnimatedPolyline([odpMap[p.from], p.to], {
          color: '#9c27b0',
          weight: 3,
          opacity: 0.85
        }).addTo(map);
      }
    });
  })
  .catch(error => {
    console.error("Gagal load data mapping:", error);
  });
  //load odc odp


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


  $('#submitMapping').on('click', function () {
    const data = {
      odc: $('select[name="odc"]').val(),
      odp: $('select[name="odp"]').val(),
      latitude: $('#lat').val(),
      longitude: $('#lng').val(),
      _token: '{{ csrf_token() }}'
    };

    $.ajax({
      url: "{{ route('mapping.store.server') }}",
      method: "POST",
      data: data,
      success: function (response) {
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

</script>


@endsection

@section('content')


<!-- DataTable with Buttons -->
<div class="card p-3">
  <div class="row g-4 align-items-start">

    <!-- Map Section -->
    <div class="col-md-6">
      <div id="map" style="width: 100%; height: 500px; border-radius: 8px;"></div>
    </div>

    <!-- Form Section -->

    <div class="col-md-6">
      <div id="formMapping">
        <div class="mb-3">
          <div class="input-group input-group-merge">
            <span id="basicSalary2" class="input-group-text"><i class='mdi mdi-server-plus'></i></span>
            <div class="form-floating form-floating-outline">
              <select name="odc" class="form-select">
                <option value="00">Pilih</option>
                @foreach (Helper::getOdc() as $val)
                <option value="{{ encrypt($val->id) }}">{{ $val->nama }}</option>
                @endforeach
              </select>
              <label for="basicSalary">ODC</label>
            </div>
          </div>
        </div>
        <div class="mb-3">
          <div class="input-group input-group-merge">
            <span id="basicSalary2" class="input-group-text"><i class='mdi mdi-server-plus'></i></span>
            <div class="form-floating form-floating-outline">
              <select name="odp" class="form-select">
                <option value="00">Pilih</option>
                @foreach (Helper::getOdp() as $val)
                <option value="{{ encrypt($val->id)  }}">{{ $val->nama }}</option>
                @endforeach
              </select>
              <label for="basicSalary">ODP</label>
            </div>
          </div>

        </div>
        <div class="mb-3">
          <span class="text-muted">Pilih Salah Satu yang kan di mapping</span>

        </div>

        <div class="mb-3">
          <label for="lat" class="form-label">Latitude</label>
          <input type="text" id="lat" name="latitude" class="form-control" readonly />
        </div>

        <div class="mb-3">
          <label for="lng" class="form-label">Longitude</label>
          <input type="text" id="lng" name="longitude" class="form-control" readonly />
        </div>

        <div class="mb-3">
          <button type="button" id="submitMapping" class="btn btn-primary">Simpan</button>
        </div>
      </div>
    </div>

  </div>
</div>


@endsection