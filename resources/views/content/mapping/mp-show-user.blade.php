@extends('layouts/layoutMaster')

@section('title', 'Data Pelanggan Mapping')

@section("vendor-style")
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<!-- Leaflet fullscreen CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.fullscreen@1.6.0/Control.FullScreen.css" />
<style>
  #map {
    height: 100vh;
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
</style>
@endsection


@section("vendor-script")
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<!-- Leaflet fullscreen JS -->
<script src="https://unpkg.com/leaflet.fullscreen@1.6.0/Control.FullScreen.js"></script>
@endsection

@section('page-script')

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
    maxZoom: 35,
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  // Marker Server Pusat
  L.marker(centerLatLng)
    .addTo(map)
    .bindPopup('<b>Server Kang Wifi</b>')
    .openPopup();


  function tampilkanJarakGaris(p1, p2, satuan = 'm') {
    const distance = map.distance(p1, p2); // dalam meter
    const midPoint = [
      (p1[0] + p2[0]) / 2,
      (p1[1] + p2[1]) / 2
    ];

    let label = satuan === 'km' && distance > 1000
      ? (distance / 1000).toFixed(2) + ' km'
      : distance.toFixed(0) + ' m';

    L.tooltip({
      permanent: true,
      direction: 'center',
      className: 'distance-label'
    })
    .setLatLng(midPoint)
    .setContent(label)
    .addTo(map);
  }


let odcMap = {};
let odpMap = {};


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
        const marker = L.marker(position, { icon }).addTo(map);
        marker.bindPopup(`<b>ODC</b><br>Nama: ${item.nama}
        <br>Port: ${item.port}
        <br>Port Sisa: ${item.portSisa}
        <br>Port OLT: ${item.portOlt}
        <br>Total ODP: ${item.totalOdp}
        <br>Total User: ${item.countUser}
        `);
        odcMap[item.id] = position;

        // Garis dari server pusat ke ODC
        L.polyline([centerLatLng, position], {
          color: 'orange',
          weight: 2,
          opacity: 0.7,
          dashArray: '4,6'
        }).addTo(map);
        marker.on('click', () => {
          tampilkanJarakGaris(centerLatLng, position);
        });
      }

      else if (item.type === 'ODP') {
        // Simpan marker & posisi ODP
        const marker = L.marker(position, { icon }).addTo(map);
        marker.bindPopup(`<b>ODP</b><br>Nama: ${item.nama}<br>Port: ${item.port}<br>Port Sisa: ${item.portSisa}
        <br>Port ODC: ${item.portOdc}
        <br>Total User: ${item.countUser}
        `);
        odpMap[item.id] = position;

        // Jika ODC-nya sudah tersedia
        if (item.idOdc && odcMap[item.idOdc]) {
          L.polyline([odcMap[item.idOdc], position], {
            color: 'green',
            weight: 2,
            opacity: 0.7,
            dashArray: '2,4'
          }).addTo(map);
          marker.on('click', () => {
            tampilkanJarakGaris(odcMap[item.idOdc], position);
          });
        }
      }

      else if (item.type === 'USER') {
        const userIcon = L.divIcon({
          className: '',
          html: `<span class="custom-marker" style="background-color: blue;"></span>`,
          iconSize: [16, 16],
          iconAnchor: [8, 8]
        });

        const marker = L.marker(position, { icon: userIcon }).addTo(map);
        marker.bindPopup(`<b>USER</b>
        <br>ID User: ${item.idUser}
        <br>Nama: ${item.nama}
        <br>Kategori: ${item.kategori}
        <br>Paket: ${item.paket}`);

        // Jika ODP-nya sudah tersedia
        if (item.idOdp && odpMap[item.idOdp]) {
          L.polyline([odpMap[item.idOdp], position], {
            color: 'blue',
            weight: 2,
            opacity: 0.6,
            dashArray: '2,2'
          }).addTo(map);
          marker.on('click', () => {
            tampilkanJarakGaris(odpMap[item.idOdp], position);
          });
        }
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



@endsection