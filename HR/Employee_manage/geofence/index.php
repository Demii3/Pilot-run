<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Geofence Attendance Monitor</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <h1>Geofence Attendance Monitor</h1>
  <div id="main">
    <aside id="sidebar">
      <div id="controls">
        <input type="text" id="geofence-name" placeholder="Geofence Name">
        <button id="save-geofence">Save Geofence</button>
        <button id="clear-drawing">Clear Drawing</button>
      </div>
      <div id="geofence-list"></div>
    </aside>
    <div id="map"></div>
  </div>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
  <script src="js/app.js"></script>
</body>
</html>
