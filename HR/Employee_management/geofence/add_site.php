<?php
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Geofence Site</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
  <link rel="stylesheet" href="../employee_module.css" />
  <link rel="stylesheet" href="css/style.css" />
</head>
<body class="geofence-app">
  <nav class="custom-navbar">
    <div class="nav-left">
      <a class="logo-circle" href="../../index.php" aria-label="Go to Home">
        <img src="../../../Images/logo.jpg" alt="Logo">
      </a>
      <span class="company-name">Chengshi <br>Construction Corp</span>
    </div>
    <div class="nav-right">
      <button class="avatar" onclick="toggleMenu()">
        <img src="../../../Images/profilepic.jpg" alt="User">
      </button>
      <div id="profileMenu" class="dropdown-menu">
        <div class="profile-header">
          <img src="../../../Images/profilepic.jpg" alt="User">
          <span>User</span>
        </div>
      </div>
    </div>
  </nav>

  <div class="employee-container">
    <div class="sidebar">
      <h2>Employee Management</h2>
      <button onclick="window.location.href='../index.php?section=employees'">Manage Employees</button>
      <button class="active" onclick="window.location.href='sites.php'">Manage Sites</button>
    </div>

    <div class="content">
      <div class="card">
        <h2>Add Geofence Site</h2>
        <p>Draw a new site on the map and save it.</p>
      </div>

      <div class="card form-grid">
        <div class="form-panel">
          <label for="site-name">Site name</label>
          <input id="site-name" type="text" placeholder="Enter site name">
          <button id="save-site">Save Site</button>
          <button id="clear-drawing" type="button">Clear Drawing</button>
          <a class="site-button secondary" href="sites.php">Back to Sites</a>
          <p class="modal-instructions">Draw the polygon on the map, then click Save Site.</p>
        </div>
        <div id="map" class="page-map"></div>
      </div>
    </div>
  </div>

  <div class="bg-container">
    <img src="../../../Images/bgimg.jpg" class="bg-image" alt="Background">
    <div class="overlay"></div>
  </div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
  <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
  <script>
    function toggleMenu() {
      document.getElementById('profileMenu').classList.toggle('active');
    }

    const map = L.map('map').setView([14.5995, 120.9842], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: 'OpenStreetMap contributors' }).addTo(map);

    L.Control.geocoder({
      geocoder: L.Control.Geocoder.nominatim({
        countrycodes: 'ph',
        viewbox: '116.93,21.12,126.6,4.64',
        bounded: 1
      }),
      defaultMarkGeocode: false,
      collapsed: false,
      expand: 'click',
      showResultIcons: true,
      placeholder: 'Search PH address...'
    }).on('markgeocode', function(e) {
      if (e.geocode.bbox) {
        map.fitBounds(e.geocode.bbox);
      } else if (e.geocode.center) {
        map.setView(e.geocode.center, 16);
      }
    }).addTo(map);

    const drawnItems = new L.FeatureGroup().addTo(map);
    let currentLayer = null;

    const drawControl = new L.Control.Draw({
      edit: { featureGroup: drawnItems },
      draw: { polygon: true, polyline: false, rectangle: false, circle: false, marker: false, circlemarker: false }
    });
    map.addControl(drawControl);

    map.on(L.Draw.Event.CREATED, function(event) {
      if (currentLayer) drawnItems.removeLayer(currentLayer);
      currentLayer = event.layer;
      drawnItems.addLayer(currentLayer);
    });

    function normalizeCoordinates(coords) {
      if (!coords.length) return coords;
      const normalized = coords.slice();
      const first = normalized[0];
      const last = normalized[normalized.length - 1];
      if (first[0] !== last[0] || first[1] !== last[1]) normalized.push(first);
      return normalized;
    }

    document.getElementById('save-site').addEventListener('click', function() {
      const name = document.getElementById('site-name').value.trim();
      if (!name) return alert('Please enter a site name.');
      if (!currentLayer) return alert('Please draw a site polygon on the map.');

      let coordinates = currentLayer.getLatLngs();
      if (Array.isArray(coordinates[0])) coordinates = coordinates[0];
      coordinates = coordinates.map(function(latlng) { return [latlng.lat, latlng.lng]; });
      coordinates = normalizeCoordinates(coordinates);

      fetch('geofences.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name, coordinates: coordinates })
      })
        .then(function(res) { return res.json(); })
        .then(function(data) {
          if (data.success) {
            window.location.href = 'sites.php';
          } else {
            alert(data.error || 'Unable to save site.');
          }
        })
        .catch(function() { alert('Unable to save site.'); });
    });

    document.getElementById('clear-drawing').addEventListener('click', function() {
      if (currentLayer && drawnItems.hasLayer(currentLayer)) drawnItems.removeLayer(currentLayer);
      drawnItems.clearLayers();
      currentLayer = null;
    });
  </script>
</body>
</html>