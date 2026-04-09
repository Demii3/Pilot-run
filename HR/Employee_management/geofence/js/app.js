const map = L.map('map').setView([14.5995, 120.9842], 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap contributors'
}).addTo(map);

const drawnItems = new L.FeatureGroup();
map.addLayer(drawnItems);

const geofenceLayer = new L.LayerGroup().addTo(map);
const markerLayer = new L.LayerGroup().addTo(map);

const customPinIcon = L.icon({
  iconUrl: 'img/pin.svg',
  iconSize: [24, 36],
  iconAnchor: [12, 36],
  popupAnchor: [0, -34]
});

function addPin(latlng, title = 'Pin') {
  L.marker(latlng, { icon: customPinIcon })
    .bindPopup(title)
    .addTo(markerLayer);
}

const drawControl = new L.Control.Draw({
  edit: {
    featureGroup: drawnItems,
    poly: {
      allowIntersection: false
    }
  },
  draw: {
    polygon: true,
    polyline: false,
    rectangle: false,
    circle: false,
    marker: false,
    circlemarker: false
  }
});
map.addControl(drawControl);

let currentLayer = null;
let editingGeofenceId = null;

map.on(L.Draw.Event.CREATED, function (event) {
  if (currentLayer && drawnItems.hasLayer(currentLayer)) {
    drawnItems.removeLayer(currentLayer);
  }
  currentLayer = event.layer;
  drawnItems.addLayer(currentLayer);
});

function cancelEditMode() {
  editingGeofenceId = null;
  document.getElementById('save-geofence').textContent = 'Save Geofence';
  document.getElementById('geofence-name').value = '';
}

function normalizeCoordinates(coords) {
  const normalized = coords.slice();
  const first = normalized[0];
  const last = normalized[normalized.length - 1];
  if (!first || !last) return normalized;
  if (first[0] !== last[0] || first[1] !== last[1]) {
    normalized.push(first);
  }
  return normalized;
}

function addCornerPins(coordinates, title = 'Corner') {
  coordinates.forEach((coord, index) => {
    const [lat, lng] = coord;
    if (
      index === coordinates.length - 1 &&
      coordinates[0][0] === lat &&
      coordinates[0][1] === lng
    ) {
      return;
    }
    addPin([lat, lng], title);
  });
}

function updateGeofenceList(geofences) {
  const list = document.getElementById('geofence-list');
  list.innerHTML = '<h3>Existing Geofences:</h3>';

  if (!geofences.length) {
    const empty = document.createElement('p');
    empty.textContent = 'No geofences saved.';
    list.appendChild(empty);
    return;
  }

  geofences.forEach(gf => {
    const item = document.createElement('div');
    item.className = 'geofence-item';

    const title = document.createElement('span');
    title.className = 'geofence-title';
    title.textContent = gf.name;
    title.addEventListener('click', () => zoomToGeofence(gf));

    const actions = document.createElement('div');
    actions.className = 'geofence-actions';

    const editButton = document.createElement('button');
    editButton.textContent = 'Edit';
    editButton.addEventListener('click', event => {
      event.stopPropagation();
      startEditGeofence(gf);
    });

    const deleteButton = document.createElement('button');
    deleteButton.textContent = 'Delete';
    deleteButton.addEventListener('click', event => {
      event.stopPropagation();
      deleteGeofence(gf.id);
    });

    actions.appendChild(editButton);
    actions.appendChild(deleteButton);
    item.appendChild(title);
    item.appendChild(actions);
    list.appendChild(item);
  });
}

function loadGeofences() {
  geofenceLayer.clearLayers();
  markerLayer.clearLayers();

  fetch('geofences.php')
    .then(res => res.json())
    .then(geofences => {
      geofences.forEach(gf => {
        const poly = L.polygon(gf.coordinates).addTo(geofenceLayer);
        poly.bindPopup(gf.name);
        poly.geofenceId = gf.id;
        addCornerPins(gf.coordinates, gf.name);
      });
      updateGeofenceList(geofences);
    })
    .catch(error => {
      console.error('Error loading geofences:', error);
      updateGeofenceList([]);
    });
}

function saveGeofence(payload) {
  const url = payload.id ? `geofences.php?id=${payload.id}` : 'geofences.php';
  const method = payload.id ? 'PUT' : 'POST';

  return fetch(url, {
    method,
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      name: payload.name,
      coordinates: payload.coordinates
    })
  }).then(res => res.json());
}

function startEditGeofence(gf) {
  cancelEditMode();
  editingGeofenceId = gf.id;
  document.getElementById('geofence-name').value = gf.name;
  drawnItems.clearLayers();

  currentLayer = L.polygon(gf.coordinates).addTo(drawnItems);
  if (currentLayer.editing && currentLayer.editing.enable) {
    currentLayer.editing.enable();
  }

  map.fitBounds(currentLayer.getBounds(), { maxZoom: 16, duration: 0.8 });
  document.getElementById('save-geofence').textContent = 'Update Geofence';
}

function deleteGeofence(id) {
  if (!confirm('Delete this geofence?')) return;

  fetch(`geofences.php?id=${id}`, { method: 'DELETE' })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        if (editingGeofenceId === id) {
          cancelEditMode();
          drawnItems.clearLayers();
          currentLayer = null;
        }
        loadGeofences();
      } else {
        alert('Failed to delete geofence.');
      }
    })
    .catch(error => {
      console.error('Error deleting geofence:', error);
    });
}

function zoomToGeofence(gf) {
  if (!gf || !gf.coordinates || !gf.coordinates.length) return;
  const bounds = L.latLngBounds(gf.coordinates);
  map.flyToBounds(bounds, { maxZoom: 16, duration: 1.2 });
}

document.getElementById('save-geofence').addEventListener('click', () => {
  const name = document.getElementById('geofence-name').value.trim();
  if (!name) {
    alert('Please enter a geofence name.');
    return;
  }

  if (!currentLayer) {
    alert('Please draw a geofence first.');
    return;
  }

  let coordinates = currentLayer.getLatLngs();
  if (Array.isArray(coordinates[0])) {
    coordinates = coordinates[0];
  }

  coordinates = coordinates.map(latlng => [latlng.lat, latlng.lng]);
  coordinates = normalizeCoordinates(coordinates);

  saveGeofence({
    id: editingGeofenceId,
    name,
    coordinates
  })
    .then(data => {
      if (data.success) {
        cancelEditMode();
        drawnItems.clearLayers();
        currentLayer = null;
        loadGeofences();
      } else {
        alert(data.error || 'Unable to save geofence.');
      }
    })
    .catch(error => {
      console.error('Error saving geofence:', error);
    });
});

document.getElementById('clear-drawing').addEventListener('click', () => {
  if (currentLayer && drawnItems.hasLayer(currentLayer)) {
    drawnItems.removeLayer(currentLayer);
  }
  drawnItems.clearLayers();
  currentLayer = null;
  cancelEditMode();
});

loadGeofences();
