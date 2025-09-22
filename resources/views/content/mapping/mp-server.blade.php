@extends('layouts/layoutMaster')

@section('title', 'Mapping Server')

@section("vendor-style")
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
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
</style>
@endsection


@section("vendor-script")
{{-- <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"
  integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin=""></script> --}}
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
@endsection

@section('page-script')
<script>
  const latitude = -5.129336074668655;
  const longitude = 105.6682460257199;

  const map = L.map('map').setView([latitude, longitude], 12);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  // Marker
  L.marker([latitude, longitude])
      .addTo(map)
      .bindPopup('Server Kang Wifi')
      .openPopup();

  // Area lingkup (radius 1000 meter)
  L.circle([latitude, longitude], {
      radius: 500,
      color: 'blue',
      fillColor: '#3f8df7',
      fillOpacity: 0.3
  }).addTo(map);

  //load odc odp
  const centerLatLng = [latitude, longitude]; // titik server pusat
  let odcMap = {}; // untuk menyimpan ODC by id
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

        // Garis dari server pusat ke ODC (warna oranye)
        L.polyline([centerLatLng, position], {
          color: 'orange',
          weight: 2,
          opacity: 0.7,
          dashArray: '4,6'
        }).addTo(map);

      } else if (item.type === 'ODP' && item.idOdc && odcMap[item.idOdc]) {
        const marker = L.marker(position, { icon }).addTo(map);
        marker.bindPopup(`<b>${item.type}</b><br>Nama : ${item.nama}<br>Port : ${item.port}<br>Port Sisa : ${item.portSisa}<br>Port ODC : ${item.portOdc}`);

        // Garis dari ODP ke ODC (warna hijau)
        L.polyline([odcMap[item.idOdc], position], {
          color: 'green',
          weight: 2,
          opacity: 0.7,
          dashArray: '2,4'
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