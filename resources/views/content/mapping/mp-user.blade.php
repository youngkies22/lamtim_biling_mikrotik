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

    0%,
    100% {
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
        attributes: true
        , attributeFilter: ['class']
      });
    });
  });

</script>
<script>
  const paketData = @json(Helper::getPaketGrouped());

  document.getElementById('kategori').addEventListener('change', function() {
    const kategoriId = this.value.toString(); // pastikan string
    const paketSelect = document.getElementById('paket');
    paketSelect.innerHTML = '<option value="00">Pilih</option>';

    if (paketData[kategoriId]) {
      paketData[kategoriId].forEach(paket => {
        const option = document.createElement('option');
        option.value = paket.id;
        option.text = paket.nama;
        paketSelect.appendChild(option);
      });
    }
    // console.log('kategoriId:', kategoriId);
    // console.log('paketData keys:', Object.keys(paketData));
    // console.log('paketData[kategoriId]:', paketData[kategoriId]);
  });

  $(document).ready(function() {
    function loadSecretApi(idMikrotik) {
      const $secretSelect = $('#secretapi-select');
      $secretSelect.html('<option>Loading...</option>');

      $.get('/select/secret-api/' + idMikrotik, function(res) {

        if (res.status) {
          @php
          $dariDb = $query - > user_mikrotik - > namaMikrotikUser ? ? '';
          @endphp
          let dariDb = @json($dariDb);

          let options = '<option value="">Pilih Secret</option>';
          res.data.forEach(function(item) {
            let selected = item.name === dariDb ? 'selected' : '';
            options += '<option value="' + item.name + '" ' + selected +
              ' data-id="' + (item['.id'] || '') + '"' +
              ' data-service="' + (item.service || '') + '"' +
              ' data-profile="' + (item.profile || '') + '"' +
              ' data-password="' + (item.password || '') + '"' +
              ' data-local="' + (item['local-address'] || '') + '"' +
              ' data-remote="' + (item['remote-address'] || '') + '">' +
              item.name +
              '</option>';
          });
          $secretSelect.html(options);

          // Jika ada secret yang sudah dipilih (selected), isi field-field terkait
          const selectedOption = $secretSelect.find('option:selected');
          if (selectedOption.length && selectedOption.val()) {
            $('#id-api').val(selectedOption.data('id') || '');
            $('#service-api').val(selectedOption.data('service') || '');
            $('#profile-api').val(selectedOption.data('profile') || '');
            $('#password-api').val(selectedOption.data('password') || '');
          }
        } else {
          $secretSelect.html('<option value="">Tidak ditemukan</option>');
          console.error(res.message);
        }
      }).fail(function() {
        $secretSelect.html('<option value="">Gagal ambil data</option>');
      });
    }
    $('#secretapi-select').on('change', function() {
      const selected = $(this).find('option:selected');

      $('#id-api').val(selected.data('id') || '');
      $('#service-api').val(selected.data('service') || '');
      $('#profile-api').val(selected.data('profile') || '');
      $('#password-api').val(selected.data('password') || '');

      // Trigger change untuk memastikan nilai ter-update
      $('#id-api').trigger('change');
      $('#service-api').trigger('change');
      $('#profile-api').trigger('change');
      $('#password-api').trigger('change');
    });

    // Saat aksi berubah
    $('select[name="aksi"]').on('change', function() {
      const aksi = $(this).val();
      const idMikrotik = $('#idmikrotik').val();

      if (aksi === '1') {
        // Tampilkan input manual
        $('#secretapi-input').removeClass('d-none');

        $('#manual-secret-api').removeClass('d-none');

        // Sembunyikan input API otomatis
        $('#secretapi-select').addClass('d-none');
        $('#secret-mikrotip-api').addClass('d-none');
      } else if (aksi === '2') {
        // Tampilkan input API otomatis
        $('#secretapi-select').removeClass('d-none');
        $('#secret-mikrotip-api').removeClass('d-none');

        // Sembunyikan input manual
        $('#secretapi-input').addClass('d-none');

        $('#manual-secret-api').addClass('d-none');

        // Load API jika idMikrotik sudah dipilih
        if (idMikrotik && idMikrotik !== '00') {
          loadSecretApi(idMikrotik);
        }
      }
    });


    // Saat server berubah
    $('#idmikrotik').on('change', function() {
      const aksi = $('select[name="aksi"]').val();
      const idMikrotik = $(this).val();
      if (aksi === '2' && idMikrotik && idMikrotik !== '00') {
        loadSecretApi(idMikrotik);
      }
    });

    // Inisialisasi: Load secret saat halaman pertama kali dimuat jika sudah ada mikrotik dan aksi = 2
    const idMikrotik = $('#idmikrotik').val();
    const aksi = $('select[name="aksi"]').val();
    @if($query - > user_mikrotik ? - > id !== null)
    const hasUserMikrotik = true;
    @else
    const hasUserMikrotik = false;
    @endif

    // Jika sudah ada mikrotik dipilih, aksi = 2 (API), dan sudah ada user_mikrotik
    if (idMikrotik && idMikrotik !== '00' && aksi === '2') {
      // Delay sedikit untuk memastikan DOM sudah ready
      setTimeout(function() {
        loadSecretApi(idMikrotik);
      }, 300);
    }

    $('#submitMapping').click(function() {
      const aksi = $('select[name="aksi"]').val();

      // Ambil data umum
      let formData = {
        aksi: aksi
        , kategori: $('#kategori').val()
        , paket: $('#paket').val()
        , idmikrotik: $('select[name="idmikrotik"]').val()
        , nama: $('#nama').val()
        , wa: $('#wa').val()
        , odp: $('select[name="odp"]').val(), // Pastikan select ODP punya name="odp"
        idd: $('#idd').val()
        , latitude: $('#lat').val()
        , longitude: $('#lng').val()
        , port: $('#port').val()
      , };

      if (aksi === '1') {
        // Ambil dari manual input
        formData.secretapi = $('#secretapi-input').val();
        formData.password = $('#password').val();
      } else if (aksi === '2') {
        // Ambil dari pilihan API
        const selected = $('#secretapi-select option:selected');
        formData.secretapi = selected.val();
        formData.id_api = $('#id-api').val();
        formData.service_api = $('#service-api').val();
        formData.profile_api = $('#profile-api').val();
        formData.password = $('#password-api').val();
      }

      $.ajax({
        url: '{{ route("user.mapping.store") }}'
        , type: 'POST'
        , data: formData
        , headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
        , success: function(response) {
          toastr.success(response.message);
        }
        , error: function(xhr) {
          let message = 'Terjadi kesalahan.';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
          }
          toastr.error(message);
        }
      });
    });


  });

</script>
<script>
  const latitude = "{{ config('services.mapping.latitude-server') }}";
  const longitude = "{{ config('services.mapping.longitude-server') }}";
  const centerLatLng = [parseFloat(latitude), parseFloat(longitude)];

  // Inisialisasi peta dengan fullscreenControl
  const map = L.map('map', {
    fullscreenControl: true // Aktifkan tombol fullscreen
  }).setView(centerLatLng, 13);

  // Tile layer dari OpenStreetMap
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 35
    , attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  // Marker Server Pusat
  L.marker(centerLatLng)
    .addTo(map)
    .bindPopup('<b>Server Kang Wifi</b>')
    .openPopup();

  // Fungsi untuk membuat animated polyline modern dengan efek garis bergerak
  function createAnimatedPolyline(coordinates, options = {}) {
    const defaultOptions = {
      color: '#3388ff'
      , weight: 3
      , opacity: 0.85
      , smoothFactor: 1
    };

    const finalOptions = {
      ...defaultOptions
      , ...options
    };

    // Buat polyline dengan dash array untuk efek animasi
    const polyline = L.polyline(coordinates, {
      color: finalOptions.color
      , weight: finalOptions.weight
      , opacity: finalOptions.opacity
      , smoothFactor: finalOptions.smoothFactor
      , className: 'animated-polyline'
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

  function tampilkanJarakGaris(p1, p2, satuan = 'm') {
    const distance = map.distance(p1, p2); // dalam meter
    const midPoint = [
      (p1[0] + p2[0]) / 2
      , (p1[1] + p2[1]) / 2
    ];

    let label = satuan === 'km' && distance > 1000 ?
      (distance / 1000).toFixed(2) + ' km' :
      distance.toFixed(0) + ' m';

    L.tooltip({
        permanent: true
        , direction: 'center'
        , className: 'distance-label'
      })
      .setLatLng(midPoint)
      .setContent(label)
      .addTo(map);
  }


  let odcMap = {};
  let odpMap = {};
  let odpPending = [];

  const userId = "{{ $id }}";
  @if(!empty($query - > user_mikrotik ? - > namaMikrotikUser))
  fetch(`/mapping/json/mapping/user/${userId}`)
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
          className: ''
          , html: `<span class="custom-marker ${iconClass}"></span>`
          , iconSize: [16, 16]
          , iconAnchor: [8, 8]
        });

        if (item.type === 'ODC') {
          // Simpan marker & posisi ODC
          const marker = L.marker(position, {
            icon
          }).addTo(map);
          marker.bindPopup(`<b>ODC</b><br>Nama: ${item.nama}<br>Port: ${item.port}<br>Port Sisa: ${item.portSisa}<br>Port OLT: ${item.portOlt}`);
          odcMap[item.id] = position;

          // Garis dari server pusat ke ODC dengan animasi modern
          createAnimatedPolyline([centerLatLng, position], {
            color: '#ff9800'
            , weight: 3
            , opacity: 0.85
          }).addTo(map);
          tampilkanJarakGaris(centerLatLng, position);
        } else if (item.type === 'ODP') {
          // Simpan marker & posisi ODP
          const marker = L.marker(position, {
            icon
          }).addTo(map);
          marker.bindPopup(`<b>ODP</b><br>Nama: ${item.nama}<br>Port: ${item.port}<br>Port Sisa: ${item.portSisa}<br>Port ODC: ${item.portOdc}`);
          odpMap[item.id] = position;

          // Jika ODC-nya sudah tersedia dengan animasi modern
          if (item.idOdc && odcMap[item.idOdc]) {
            createAnimatedPolyline([odcMap[item.idOdc], position], {
              color: '#4caf50'
              , weight: 3
              , opacity: 0.85
            }).addTo(map);
            tampilkanJarakGaris(odcMap[item.idOdc], position);
          }

          // Garis dari ODP ke ODP parent (warna ungu) dengan animasi modern
          if (item.idOdp) {
            if (odpMap[item.idOdp]) {
              createAnimatedPolyline([odpMap[item.idOdp], position], {
                color: '#9c27b0'
                , weight: 3
                , opacity: 0.85
              }).addTo(map);
              tampilkanJarakGaris(odpMap[item.idOdp], position);
            } else {
              odpPending.push({
                from: item.idOdp
                , to: position
              });
            }
          }
        } else if (item.type === 'USER') {
          const userIcon = L.divIcon({
            className: ''
            , html: `<span class="custom-marker" style="background-color: blue;"></span>`
            , iconSize: [16, 16]
            , iconAnchor: [8, 8]
          });

          const marker = L.marker(position, {
            icon: userIcon
          }).addTo(map);
          marker.bindPopup(`<b>USER</b><br>ID User: ${item.idUser}`);

          // Jika ODP-nya sudah tersedia dengan animasi modern
          if (item.idOdp && odpMap[item.idOdp]) {
            createAnimatedPolyline([odpMap[item.idOdp], position], {
              color: '#2196f3'
              , weight: 3
              , opacity: 0.85
            }).addTo(map);
            tampilkanJarakGaris(odpMap[item.idOdp], position);
          }
        }
      });

      // Gambar garis ODP-ODP yang pending dengan animasi modern
      odpPending.forEach(p => {
        if (odpMap[p.from]) {
          createAnimatedPolyline([odpMap[p.from], p.to], {
            color: '#9c27b0'
            , weight: 3
            , opacity: 0.85
          }).addTo(map);
        }
      });
    })
    .catch(error => {
      console.error("Gagal load data mapping:", error);
    });

  @else
  //load odc odp jika blm di maping lokasi user
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
          html: `<span class="custom-marker ${iconClass}"></span>`
          , iconSize: [16, 16]
          , iconAnchor: [8, 8]
        });
        if (item.type === 'ODC') {
          // Tambahkan marker
          const marker = L.marker(position, {
            icon
          }).addTo(map);
          marker.bindPopup(`<b>${item.type}</b><br>Nama : ${item.nama}<br>Port : ${item.port}<br>Port Sisa : ${item.portSisa}<br>Port OLT : ${item.portOlt}`);

          // Simpan posisi ODC berdasarkan ID
          odcMap[item.id] = position;

          // Garis dari server pusat ke ODC (warna oranye) dengan animasi modern
          createAnimatedPolyline([centerLatLng, position], {
            color: '#ff9800'
            , weight: 3
            , opacity: 0.85
          }).addTo(map);

        } else if (item.type === 'ODP') {
          const marker = L.marker(position, {
            icon
          }).addTo(map);
          marker.bindPopup(`<b>${item.type}</b><br>Nama : ${item.nama}<br>Port : ${item.port}<br>Port Sisa : ${item.portSisa}<br>Port ODC : ${item.portOdc}`);

          // Simpan posisi ODP
          odpMap[item.id] = position;

          // Garis dari ODP ke ODC (warna hijau) dengan animasi modern
          if (item.idOdc && odcMap[item.idOdc]) {
            createAnimatedPolyline([odcMap[item.idOdc], position], {
              color: '#4caf50'
              , weight: 3
              , opacity: 0.85
            }).addTo(map);
          }

          // Garis dari ODP ke ODP parent (warna ungu) dengan animasi modern
          if (item.idOdp) {
            if (odpMap[item.idOdp]) {
              createAnimatedPolyline([odpMap[item.idOdp], position], {
                color: '#9c27b0'
                , weight: 3
                , opacity: 0.85
              }).addTo(map);
            } else {
              odpPending.push({
                from: item.idOdp
                , to: position
              });
            }
          }
        }
      });

      // Gambar garis ODP-ODP yang pending
      odpPending.forEach(p => {
        if (odpMap[p.from]) {
          L.polyline([odpMap[p.from], p.to], {
            color: 'purple'
            , weight: 2
            , opacity: 0.7
            , dashArray: '3,5'
          }).addTo(map);
        }
      });
    })
    .catch(error => {
      console.error("Gagal load data mapping:", error);
    });
  //load odc odp
  @endif

  //pilih area untuk di pindai
  let marker;
  map.on('click', function(e) {
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

        {{-- Baris 1: ODC, ODP, Keterangan --}}
        <div class="row">
          <div class="col-md-3 mb-3">
            <div class="input-group input-group-merge">
              <div class="form-floating form-floating-outline w-100">
                <select required name="aksi" class="form-select">
                  <option value="1">Buat Data Ke Miktorik</option>
                  <option {{ optional($query->user_mikrotik)->id !== null ? 'selected' : '' }} value="2">Ambil Data
                    Miktorik
                  </option>
                </select>
                <label>AKSI</label>
              </div>
            </div>
          </div>

          <div class="col-md-3 mb-3">
            <div class="input-group input-group-merge">
              <div class="form-floating form-floating-outline w-100">
                <select required name="kategori" id="kategori" class="form-select">
                  <option value="00">Pilih</option>
                  @foreach (Helper::getKategori() as $val)
                  <option {{ $query->user_mikrotik?->idKategori == $val->id ? 'selected' : '' }} value="{{ $val->id }}">{{ $val->nama }}</option>
                  @endforeach
                </select>
                <label>Kategori</label>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="input-group input-group-merge">
              <div class="form-floating form-floating-outline w-100">
                <select required name="paket" id="paket" class="form-select">
                  <option value="00">Pilih</option>
                  @foreach (Helper::getPaket() as $val)
                  <option {{ $query->user_mikrotik?->idPaket == $val->id ? 'selected' : '' }} value="{{ $val->id }}">{{ $val->nama }}</option>
                  @endforeach
                </select>
                <label>Paket</label>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="input-group input-group-merge">
              <div class="form-floating form-floating-outline w-100">
                <select required id="odp" name="odp" class="form-select">
                  <option value="">Pilih</option>
                  @foreach (Helper::getOdp() as $val)
                  <option {{ $query->user_mikrotik?->idOdp == $val->id ? 'selected' : '' }} value="{{ $val->id
                    }}">{{ $val->nama }}</option>
                  @endforeach
                </select>
                <label>ODP</label>
              </div>
            </div>
          </div>
        </div>
        {{-- baris mikrotik --}}
        <div class="row">
          <div class="col-md-4 mb-3">
            <div class="input-group input-group-merge">
              <div class="form-floating form-floating-outline w-100">
                <select required id="idmikrotik" name="idmikrotik" class="form-select">
                  <option value="00">Pilih</option>
                  @foreach (Helper::getMikrotik() as $val)
                  <option {{ $query->user_mikrotik?->idMikrotik == $val->id ? 'selected' : '' }} value="{{ $val->id
                    }}">{{
                    $val->kode }} | {{ $val->nama }}</option>
                  @endforeach
                </select>
                <label>Server Mikrotik</label>
              </div>
            </div>
          </div>
          <div class="col-md-2 mb-3">
            <div class="input-group input-group-merge">
              <div class="form-floating form-floating-outline w-100">
                <input type="number" id="port" name="port" class="form-control" value="{{ $query->user_mikrotik?->portOdp ?? null }}" autocomplete="off" />
                <label>Lokasi Port ODP</label>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="input-group input-group-merge">
              <div class="form-floating form-floating-outline w-100">
                <input type="text" id="nama" name="nama" class="form-control" value="{{ $query->name ?? null }}" readonly disabled />
                <input type="hidden" id="idd" name="idd" class="form-control" value="{{ $id }}" readonly />
                <label>Nama</label>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="input-group input-group-merge">
              <div class="form-floating form-floating-outline w-100">
                <input type="text" id="wa" name="wa" class="form-control" value="{{ $query->wa ?? null }}" readonly disabled />
                <label>Wa</label>
              </div>
            </div>
          </div>



          <!-- Secret API Section - Dropdown dan Input -->
          <div id="secret-mikrotip-api" class="row {{ $query->user_mikrotik?->id === null ? 'd-none' : '' }}">
            <div class="col-md-4 mb-3">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline w-100">
                  <select name="secretapi" id="secretapi-select" class="form-select">
                    <option value="">Pilih Secret </option>
                  </select>
                  <label>Secret</label>
                </div>
              </div>
            </div>

            <div class="col-md-1 mb-3">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline w-100">
                  <input readonly value="{{ $query->user_mikrotik?->idMikrotikUser ?? '' }}" type="text" name="id-api" id="id-api" class="form-control" />
                  <label>Id Api</label>
                </div>
              </div>
            </div>

            <div class="col-md-2 mb-3">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline w-100">
                  <input readonly value="{{ $query->user_mikrotik?->serviceMikrotikUser ?? '' }}" type="text" name="service-api" id="service-api" class="form-control" />
                  <label>Service Api</label>
                </div>
              </div>
            </div>

            <div class="col-md-2 mb-3">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline w-100">
                  <input readonly value="{{ $query->user_mikrotik?->profileMikrotikUser ?? '' }}" type="text" name="profile-api" id="profile-api" class="form-control" />
                  <label>Paket Api</label>
                </div>
              </div>
            </div>
            <div class="col-md-2 mb-3">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline w-100">
                  <input readonly value="{{ $query->user_mikrotik?->password ?? '' }}" type="text" name="password-api" id="password-api" class="form-control" />
                  <label>password Api</label>
                </div>
              </div>
            </div>
          </div>



          <!-- Manual Secret Input & Password -->
          <div id="manual-secret-api" class="row {{ $query->user_mikrotik?->id !== null ? 'd-none' : '' }}">
            <div class="col-md-3 mb-3">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline w-100">
                  <input type="text" name="secretapi" id="secretapi-input" class="form-control" placeholder="mryes22@codeteam.id" value="{{ $query->user_mikrotik?->namaMikrotikUser ?? '' }}" />
                  <label>Secret</label>
                </div>
              </div>
            </div>

            <div class="col-md-4 mb-3">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline w-100">
                  <input type="text" id="password" name="password" class="form-control" value="{{ $query->user_mikrotik?->password ?? '' }}" />
                  <label>Password Secret</label>
                </div>
              </div>
            </div>
          </div>

        </div>
        <div class="row">
          <div class="col-md-4 mb-3">
            <label for="lat" class="form-label">Latitude</label>
            <input value=" {{ $query->user_mikrotik?->latitude ?? '' }}" type="text" id="lat" name="latitude" class="form-control" readonly />
          </div>

          <div class="col-md-4 mb-3">
            <label for="lng" class="form-label">Longitude</label>
            <input value=" {{ $query->user_mikrotik?->longitude ?? '' }}" type="text" id="lng" name="longitude" class="form-control" readonly />
          </div>
        </div>
        <div class="row">
          <div class="col-md-4 mb-3">
            <div class="col-md-4 mb-3 d-flex align-items-end">
              <button type="button" id="submitMapping" class="btn btn-primary me-2 w-100 ">Simpan</button>
              <button type="button" class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#infoAksiModal">
                <i class="fas fa-info-circle"></i> Info</button>
            </div>
          </div>
        </div>
        {{-- Baris 2: Latitude, Longitude, Tombol --}}
        <div class="row">
          <div class="col-md-12 ">
            <div id="map" style="width: 100%; height: 500px; border-radius: 8px;"></div>
          </div>
        </div>
      </div>
    </div>


  </div>
</div>
<div class="modal fade" id="infoAksiModal" tabindex="-1" aria-labelledby="infoAksiModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="infoAksiModalLabel">Penjelasan AKSI & PAKET</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body " style="font-size: 0.95rem;">
        <p>✅ Jika <strong>AKSI</strong> dipilih <em>buat data ke mikrotik</em>, maka akan dibuatkan user secret PPPoE
          langsung dari aplikasi.</p>
        <p>✅ Jika <strong>AKSI</strong> dipilih <em>ambil data mikrotik</em>, berarti user secret sudah dibuat manual di
          Mikrotik, lalu tinggal dipetakan (mapping) ke aplikasi.</p>
        <p>🔁 Untuk <strong>update data</strong>, cukup pilih AKSI <em>ambil data mikrotik</em> dan biarkan kolom secret
          kosong.</p>
        <p>✏️ Jika ingin <strong>mengganti user secret</strong> pelanggan dari halaman ini, pilih AKSI <em>buat data ke
            mikrotik</em>. Sistem akan otomatis membuat atau memperbarui secret tersebut.</p>
        <p>📦 <strong>PAKET</strong> adalah data yang sudah diinput sebelumnya melalui menu <em>Internet Paket</em> dan
          <strong>wajib sama persis</strong> dengan nama paket di Mikrotik (profil).
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>


@endsection
