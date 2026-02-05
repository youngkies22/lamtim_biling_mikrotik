@extends('layouts/layoutMaster')

@section('title', 'Google Map')

@section("vendor-style")
<style>
  #google-map {
    height: 100vh;
    width: 100%;
    position: relative;
    z-index: 1;
  }

  .map-controls {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 1000;
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    min-width: 200px;
  }

  .map-controls h5 {
    margin: 0 0 10px 0;
    font-size: 14px;
    font-weight: 600;
    color: #333;
  }

  .map-controls .form-check {
    margin-bottom: 8px;
  }

  .map-controls .form-check-label {
    font-size: 13px;
    cursor: pointer;
  }

  .map-info {
    position: absolute;
    bottom: 20px;
    left: 20px;
    z-index: 1000;
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    max-width: 300px;
  }

  .map-info h6 {
    margin: 0 0 10px 0;
    font-size: 14px;
    font-weight: 600;
    color: #333;
  }

  .map-info p {
    margin: 5px 0;
    font-size: 12px;
    color: #666;
  }

  .info-window-content {
    padding: 5px;
  }

  .info-window-content h6 {
    margin: 0 0 5px 0;
    font-size: 14px;
    font-weight: 600;
    color: #333;
  }

  .info-window-content p {
    margin: 3px 0;
    font-size: 12px;
    color: #666;
  }
</style>
@endsection

@section("page-style")
@endsection

@section("vendor-script")
@php
  $apiKey = config('services.google.maps_api_key');
  if (empty($apiKey)) {
    $apiKey = 'YOUR_API_KEY';
  }
@endphp
<script>
  // Error handling untuk Google Maps API
  window.gm_authFailure = function() {
    console.error('Google Maps API authentication failed. Please check your API key.');
    document.getElementById('google-map').innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100vh; flex-direction: column; padding: 20px; text-align: center;"><h4>Error Loading Google Maps</h4><p>Please check your API key or disable ad blocker.</p><p style="font-size: 12px; color: #666;">If you see this message, the Google Maps API key may be invalid or blocked by browser extensions.</p></div>';
  };

  // Load Google Maps API dengan callback
  function loadGoogleMapsScript() {
    const script = document.createElement('script');
    script.src = 'https://maps.googleapis.com/maps/api/js?key={{ $apiKey }}&libraries=geometry&callback=initMap';
    script.async = true;
    script.defer = true;
    script.onerror = function() {
      console.error('Failed to load Google Maps API script.');
      document.getElementById('google-map').innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100vh; flex-direction: column; padding: 20px; text-align: center;"><h4>Error Loading Google Maps</h4><p>The Google Maps API script failed to load.</p><p style="font-size: 12px; color: #666;">This may be caused by:<br>1. Ad blocker or browser extension blocking the request<br>2. Invalid API key<br>3. Network connectivity issues</p><p style="font-size: 12px; color: #666; margin-top: 10px;">Please check your browser console for more details.</p></div>';
    };
    document.head.appendChild(script);
  }

  // Load script when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadGoogleMapsScript);
  } else {
    loadGoogleMapsScript();
  }
</script>
@endsection

@section("page-script")
<script>
  let map;
  let markers = [];
  let infoWindows = [];
  let odcMarkers = [];
  let odpMarkers = [];
  let userMarkers = [];

  // Map center from config
  const mapCenter = {
    lat: parseFloat("{{ $mapCenter['latitude'] }}"),
    lng: parseFloat("{{ $mapCenter['longitude'] }}")
  };

  // Initialize Google Map
  function initMap() {
    try {
      // Check if google.maps is available
      if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
        throw new Error('Google Maps API is not loaded');
      }

      map = new google.maps.Map(document.getElementById('google-map'), {
        center: mapCenter,
        zoom: 12,
        mapTypeId: 'roadmap',
        styles: [
          {
            featureType: 'poi',
            elementType: 'labels',
            stylers: [{ visibility: 'off' }]
          }
        ]
      });

      // Load map data
      loadMapData();
    } catch (error) {
      console.error('Error initializing Google Map:', error);
      document.getElementById('google-map').innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100vh; flex-direction: column; padding: 20px; text-align: center;"><h4>Error Initializing Map</h4><p>' + error.message + '</p><p style="font-size: 12px; color: #666;">Please refresh the page or check your browser console.</p></div>';
    }
  }

  // Load map data from API
  function loadMapData() {
    fetch('{{ route("google-map.data") }}')
      .then(response => response.json())
      .then(data => {
        if (data.status && data.data) {
          displayMapData(data.data);
        }
      })
      .catch(error => {
        console.error('Error loading map data:', error);
      });
  }

  // Display map data
  function displayMapData(data) {
    // Clear existing markers
    clearAllMarkers();

    // Update counts
    document.getElementById('odc-count').textContent = data.odcs ? data.odcs.length : 0;
    document.getElementById('odp-count').textContent = data.odps ? data.odps.length : 0;
    document.getElementById('user-count').textContent = data.users ? data.users.length : 0;

    // Display ODCs
    if (data.odcs && data.odcs.length > 0) {
      data.odcs.forEach(odc => {
        if (odc.latitude && odc.longitude) {
          const marker = createMarker({
            lat: parseFloat(odc.latitude),
            lng: parseFloat(odc.longitude)
          }, {
            title: odc.nama,
            type: 'ODC',
            icon: {
              url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
              scaledSize: new google.maps.Size(32, 32)
            }
          }, {
            nama: odc.nama,
            type: 'ODC',
            port: odc.port || '-',
            portSisa: odc.portSisa || '-',
            portOlt: odc.portOlt || '-'
          });
          odcMarkers.push(marker);
          markers.push(marker);
        }
      });
    }

    // Display ODPs
    if (data.odps && data.odps.length > 0) {
      data.odps.forEach(odp => {
        if (odp.latitude && odp.longitude) {
          const marker = createMarker({
            lat: parseFloat(odp.latitude),
            lng: parseFloat(odp.longitude)
          }, {
            title: odp.nama,
            type: 'ODP',
            icon: {
              url: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png',
              scaledSize: new google.maps.Size(32, 32)
            }
          }, {
            nama: odp.nama,
            type: 'ODP',
            port: odp.port || '-',
            portSisa: odp.portSisa || '-',
            portOdc: odp.portOdc || '-'
          });
          odpMarkers.push(marker);
          markers.push(marker);
        }
      });
    }

    // Display Users
    if (data.users && data.users.length > 0) {
      data.users.forEach(user => {
        if (user.latitude && user.longitude) {
          const marker = createMarker({
            lat: parseFloat(user.latitude),
            lng: parseFloat(user.longitude)
          }, {
            title: user.nama,
            type: 'USER',
            icon: {
              url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
              scaledSize: new google.maps.Size(32, 32)
            }
          }, {
            nama: user.nama,
            type: 'Pelanggan'
          });
          userMarkers.push(marker);
          markers.push(marker);
        }
      });
    }

    // Fit bounds to show all markers
    if (markers.length > 0) {
      const bounds = new google.maps.LatLngBounds();
      markers.forEach(marker => {
        bounds.extend(marker.getPosition());
      });
      map.fitBounds(bounds);
    }
  }

  // Create marker with info window
  function createMarker(position, options, infoData) {
    const marker = new google.maps.Marker({
      position: position,
      map: map,
      title: options.title,
      icon: options.icon
    });

    // Create info window content
    const infoWindowContent = `
      <div class="info-window-content">
        <h6>${infoData.nama}</h6>
        <p><strong>Type:</strong> ${infoData.type}</p>
        ${infoData.port ? `<p><strong>Port:</strong> ${infoData.port}</p>` : ''}
        ${infoData.portSisa ? `<p><strong>Port Sisa:</strong> ${infoData.portSisa}</p>` : ''}
        ${infoData.portOlt ? `<p><strong>Port OLT:</strong> ${infoData.portOlt}</p>` : ''}
        ${infoData.portOdc ? `<p><strong>Port ODC:</strong> ${infoData.portOdc}</p>` : ''}
      </div>
    `;

    const infoWindow = new google.maps.InfoWindow({
      content: infoWindowContent
    });

    marker.addListener('click', function() {
      // Close all info windows
      infoWindows.forEach(iw => iw.close());
      // Open this info window
      infoWindow.open(map, marker);
    });

    infoWindows.push(infoWindow);
    return marker;
  }

  // Clear all markers
  function clearAllMarkers() {
    markers.forEach(marker => marker.setMap(null));
    markers = [];
    odcMarkers = [];
    odpMarkers = [];
    userMarkers = [];
    infoWindows = [];
  }

  // Filter markers by type
  function filterMarkers(type) {
    // Hide all markers first
    markers.forEach(marker => marker.setMap(null));

    // Show markers based on type
    if (type === 'all') {
      markers.forEach(marker => marker.setMap(map));
    } else if (type === 'odc') {
      odcMarkers.forEach(marker => marker.setMap(map));
    } else if (type === 'odp') {
      odpMarkers.forEach(marker => marker.setMap(map));
    } else if (type === 'user') {
      userMarkers.forEach(marker => marker.setMap(map));
    }
  }

  // Event listeners for filter checkboxes
  document.addEventListener('DOMContentLoaded', function() {
    const filterAll = document.getElementById('filter-all');
    const filterOdc = document.getElementById('filter-odc');
    const filterOdp = document.getElementById('filter-odp');
    const filterUser = document.getElementById('filter-user');

    if (filterAll) {
      filterAll.addEventListener('change', function() {
        if (this.checked) {
          filterOdc.checked = false;
          filterOdp.checked = false;
          filterUser.checked = false;
          filterMarkers('all');
        }
      });
    }

    if (filterOdc) {
      filterOdc.addEventListener('change', function() {
        if (this.checked) {
          filterAll.checked = false;
          filterOdp.checked = false;
          filterUser.checked = false;
          filterMarkers('odc');
        }
      });
    }

    if (filterOdp) {
      filterOdp.addEventListener('change', function() {
        if (this.checked) {
          filterAll.checked = false;
          filterOdc.checked = false;
          filterUser.checked = false;
          filterMarkers('odp');
        }
      });
    }

    if (filterUser) {
      filterUser.addEventListener('change', function() {
        if (this.checked) {
          filterAll.checked = false;
          filterOdc.checked = false;
          filterOdp.checked = false;
          filterMarkers('user');
        }
      });
    }
  });

  // Initialize map when page loads - make it globally available
  window.initMap = initMap;

  // Fallback: jika Google Maps gagal dimuat setelah 10 detik
  setTimeout(function() {
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
      const mapElement = document.getElementById('google-map');
      if (mapElement && !mapElement.querySelector('div[style*="Error"]')) {
        mapElement.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100vh; flex-direction: column; padding: 20px; text-align: center; background: #f5f5f5;"><h4 style="color: #d32f2f;">⚠️ Google Maps Gagal Dimuat</h4><p style="margin: 10px 0;">Kemungkinan penyebab:</p><ul style="text-align: left; font-size: 14px; color: #666;"><li>Ad blocker atau extension browser memblokir request</li><li>API key tidak valid atau belum diatur</li><li>Masalah koneksi internet</li></ul><p style="margin-top: 20px; font-size: 12px; color: #999;">Silakan cek console browser (F12) untuk detail error.</p><button onclick="location.reload()" style="margin-top: 15px; padding: 8px 20px; background: #696cff; color: white; border: none; border-radius: 4px; cursor: pointer;">Refresh Halaman</button></div>';
      }
    }
  }, 10000);
</script>
@endsection

@section('content')
<div class="container-fluid p-0">
  <div class="row g-0">
    <div class="col-12">
      <!-- Open in New Tab Button -->
      <div style="position: absolute; top: 10px; left: 10px; z-index: 1000;">
        <a href="{{ route('google-map.standalone') }}" target="_blank" class="btn btn-primary" style="box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);">
          <i class="mdi mdi-open-in-new"></i> Buka di Tab Baru
        </a>
      </div>

      <!-- Map Controls -->
      <div class="map-controls">
        <h5>Filter Tampilan</h5>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="filter" id="filter-all" checked>
          <label class="form-check-label" for="filter-all">
            Semua
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="filter" id="filter-odc">
          <label class="form-check-label" for="filter-odc">
            ODC Saja
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="filter" id="filter-odp">
          <label class="form-check-label" for="filter-odp">
            ODP Saja
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="filter" id="filter-user">
          <label class="form-check-label" for="filter-user">
            Pelanggan Saja
          </label>
        </div>
      </div>

      <!-- Map Info -->
      <div class="map-info">
        <h6>Informasi</h6>
        <p><strong>Total ODC:</strong> <span id="odc-count">0</span></p>
        <p><strong>Total ODP:</strong> <span id="odp-count">0</span></p>
        <p><strong>Total Pelanggan:</strong> <span id="user-count">0</span></p>
      </div>

      <!-- Google Map -->
      <div id="google-map"></div>
    </div>
  </div>
</div>
@endsection
