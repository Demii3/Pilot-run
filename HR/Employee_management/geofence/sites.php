<?php
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Sites</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
        <a href="#" class="profile-item"> Settings & Privacy </a>
        <a href="#" class="profile-item"> Help & Support </a>
        <a href="#" class="profile-item"> Logout </a>
      </div>
    </div>
  </nav>

  <div class="employee-container">
    <div class="sidebar">
      <h2>Employee Management</h2>
      <button onclick="window.location.href='../index.php?section=employees'">Manage Employees</button>
      <button class="active" onclick="showSitesSection()">Manage Sites</button>
      <button onclick="showAssignSection()">Assign Employees</button>
    </div>

      <div id="sitesSection" class="card sites-card">
        <div class="sites-header">
          <h3>Saved Sites</h3>
          <div class="sites-actions">
            <input id="geofence-search" type="search" placeholder="Search by name or address...">
            <a class="site-button" href="add_site.php">Add Site</a>
          </div>
        </div>
        <div id="geofence-list"></div>
      </div>

      <div id="assignSection" class="card sites-card" style="display:none;">
        <div class="sites-header">
          <h3>Assign Employee to Site</h3>
        </div>
        <div style="padding: 20px;">
          <form id="assignForm">
            <div style="margin-bottom: 15px;">
              <label style="display:block; margin-bottom:8px; font-weight:500;">Select Geofence Site</label>
              <select id="siteSelect" name="geofence_id" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-family:'Poppins',sans-serif;">
                <option value="">-- choose site --</option>
              </select>
            </div>
            <div style="margin-bottom: 15px;">
              <label style="display:block; margin-bottom:8px; font-weight:500;">Select Employee</label>
              <select id="employeeSelect" name="employee_id" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-family:'Poppins',sans-serif;">
                <option value="">-- choose employee --</option>
              </select>
            </div>
            <button type="submit" style="background:#3b82f6; color:white; border:none; padding:10px 20px; border-radius:6px; font-weight:600; cursor:pointer;">Assign Employee</button>
          </form>
          <div id="assignMsg" style="margin-top:15px;"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="bg-container">
    <img src="../../../Images/bgimg.jpg" class="bg-image" alt="Background">
    <div class="overlay"></div>
  </div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    let savedGeofences = [];

    function toggleMenu() {
      document.getElementById('profileMenu').classList.toggle('active');
    }

    document.addEventListener('click', function(e) {
      const menu = document.getElementById('profileMenu');
      const avatar = document.querySelector('.avatar');

      if (!avatar.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove('active');
      }
    });

    function getPolygonCentroid(coords) {
      let x = 0;
      let y = 0;
      coords.forEach(function(pair) {
        x += pair[0];
        y += pair[1];
      });
      return coords.length ? { lat: x / coords.length, lng: y / coords.length } : null;
    }

    function reverseGeocode(lat, lng) {
      return fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + lat + '&lon=' + lng + '&zoom=18&addressdetails=1&accept-language=en')
        .then(function(res) { return res.json(); })
        .then(function(data) { return data.display_name || 'Address not found'; })
        .catch(function() { return 'Address not found'; });
    }

    function createMiniMap(containerId, coordinates) {
      if (!Array.isArray(coordinates) || coordinates.length < 3) {
        return;
      }

      const container = document.getElementById(containerId);
      if (!container) {
        return;
      }

      const render = function() {
        try {
          const map = L.map(containerId, {
            zoomControl: false,
            attributionControl: false,
            dragging: false,
            scrollWheelZoom: false,
            doubleClickZoom: false,
            boxZoom: false,
            keyboard: false,
            tap: false,
            touchZoom: false,
            inertia: false
          });

          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '' }).addTo(map);
          const polygon = L.polygon(coordinates, { color: '#3388ff', weight: 2, fillOpacity: 0.15 }).addTo(map);
          map.fitBounds(polygon.getBounds(), { padding: [8, 8] });

          setTimeout(function() {
            map.invalidateSize();
            map.fitBounds(polygon.getBounds(), { padding: [8, 8] });
          }, 180);
        } catch (error) {
          container.textContent = 'No map data';
          container.classList.add('preview-empty');
        }
      };

      requestAnimationFrame(function() {
        requestAnimationFrame(render);
      });
    }

    function getSafeCoordinates(rawCoords) {
      if (!Array.isArray(rawCoords)) return [];
      return rawCoords.filter(function(pair) {
        return Array.isArray(pair) && pair.length >= 2 && !isNaN(pair[0]) && !isNaN(pair[1]);
      }).map(function(pair) {
        return [Number(pair[0]), Number(pair[1])];
      });
    }

    function updateGeofenceList(geofences) {
      const list = document.getElementById('geofence-list');
      list.innerHTML = '' +
        '<div class="geofence-table-header">' +
          '<span>Name</span>' +
          '<span>Address</span>' +
          '<span>Map</span>' +
          '<span>Actions</span>' +
        '</div>';

      if (!geofences.length) {
        list.insertAdjacentHTML('beforeend', '<p>No geofences saved.</p>');
        return;
      }

      geofences.forEach(function(gf) {
        const safeName = (gf.name || '').trim() || 'Unnamed site';
        const safeCoords = getSafeCoordinates(gf.coordinates);

        const row = document.createElement('div');
        row.className = 'geofence-row';
        row.addEventListener('click', function() {
          window.location.href = 'edit_site.php?id=' + gf.id;
        });

        const nameCell = document.createElement('div');
        nameCell.className = 'geofence-cell';
        nameCell.textContent = safeName;

        const addressCell = document.createElement('div');
        addressCell.className = 'geofence-cell';
        addressCell.textContent = gf.address || 'Loading address...';

        const previewCell = document.createElement('div');
        previewCell.className = 'geofence-cell preview-cell';
        const preview = document.createElement('div');
        preview.className = 'geofence-preview';
        preview.id = 'preview-' + gf.id;
        previewCell.appendChild(preview);

        const actionsCell = document.createElement('div');
        actionsCell.className = 'geofence-cell actions-cell';
        actionsCell.addEventListener('click', function(event) { event.stopPropagation(); });

        const editButton = document.createElement('button');
        editButton.textContent = 'Edit';
        editButton.addEventListener('click', function() {
          window.location.href = 'edit_site.php?id=' + gf.id;
        });

        const deleteButton = document.createElement('button');
        deleteButton.textContent = 'Delete';
        deleteButton.addEventListener('click', function() {
          if (!confirm('Delete this site?')) return;

          fetch('geofences.php?id=' + gf.id, { method: 'DELETE' })
            .then(function(res) { return res.json(); })
            .then(function(data) {
              if (data.success) {
                loadSites();
              } else {
                alert('Delete failed.');
              }
            })
            .catch(function() { alert('Delete failed.'); });
        });

        actionsCell.appendChild(editButton);
        actionsCell.appendChild(deleteButton);

        row.appendChild(nameCell);
        row.appendChild(addressCell);
        row.appendChild(previewCell);
        row.appendChild(actionsCell);
        list.appendChild(row);

        const centroid = getPolygonCentroid(safeCoords);
        if (centroid && !gf.address) {
          reverseGeocode(centroid.lat, centroid.lng).then(function(address) {
            gf.address = address;
            addressCell.textContent = address;
          });
        }

        if (safeCoords.length >= 3) {
          createMiniMap(preview.id, safeCoords);
        } else {
          preview.textContent = 'No map data';
          preview.classList.add('preview-empty');
        }
      });
    }

    function loadSites() {
      fetch('geofences.php')
        .then(function(res) { return res.json(); })
        .then(function(data) {
          savedGeofences = data;
          updateGeofenceList(savedGeofences);
        })
        .catch(function() {
          document.getElementById('geofence-list').innerHTML = '<p>Unable to load geofences.</p>';
        });
    }

    document.getElementById('geofence-search').addEventListener('input', function(event) {
      const query = event.target.value.trim().toLowerCase();
      const filtered = savedGeofences.filter(function(gf) {
        const name = gf.name.toLowerCase();
        const address = (gf.address || '').toLowerCase();
        return name.includes(query) || address.includes(query);
      });

      updateGeofenceList(filtered);
    });

    // Assign Employees Functions
    function showAssignSection() {
      document.getElementById('sitesSection').style.display = 'none';
      document.getElementById('assignSection').style.display = 'block';
      
      // Update active button
      const sidebarButtons = document.querySelectorAll('.sidebar button');
      sidebarButtons.forEach(btn => btn.classList.remove('active'));
      const assignBtn = Array.from(sidebarButtons).find(btn => btn.textContent.includes('Assign'));
      if (assignBtn) assignBtn.classList.add('active');
      
      loadGeofencesForAssign();
      loadEmployeesForAssign();
    }

    function showSitesSection() {
      document.getElementById('sitesSection').style.display = 'block';
      document.getElementById('assignSection').style.display = 'none';
      
      // Update active button
      const sidebarButtons = document.querySelectorAll('.sidebar button');
      sidebarButtons.forEach(btn => btn.classList.remove('active'));
      const sitesBtn = Array.from(sidebarButtons).find(btn => btn.textContent.includes('Manage Sites'));
      if (sitesBtn) sitesBtn.classList.add('active');
      
      loadSites();
    }

    function loadGeofencesForAssign() {
      fetch('geofences.php')
        .then(function(res) { return res.json(); })
        .then(function(data) {
          const select = document.getElementById('siteSelect');
          select.innerHTML = '<option value="">-- choose site --</option>';
          data.forEach(function(gf) {
            const option = document.createElement('option');
            option.value = gf.id;
            option.textContent = gf.name;
            select.appendChild(option);
          });
        })
        .catch(function(err) {
          console.error('Error loading geofences:', err);
        });
    }

    function loadEmployeesForAssign() {
      fetch('../api_employees.php')
        .then(function(res) { return res.json(); })
        .then(function(data) {
          const select = document.getElementById('employeeSelect');
          select.innerHTML = '<option value="">-- choose employee --</option>';
          data.forEach(function(emp) {
            const option = document.createElement('option');
            option.value = emp.id;
            option.textContent = emp.name;
            select.appendChild(option);
          });
        })
        .catch(function(err) {
          console.error('Error loading employees:', err);
        });
    }

    document.getElementById('assignForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const geofenceId = document.getElementById('siteSelect').value;
      const employeeId = document.getElementById('employeeSelect').value;
      const msgDiv = document.getElementById('assignMsg');

      if (!geofenceId || !employeeId) {
        msgDiv.innerHTML = '<div style="background:#fee2e2; color:#991b1b; padding:10px; border-radius:6px; font-size:13px;">Please select both site and employee</div>';
        return;
      }

      msgDiv.innerHTML = '<div style="color:#666; font-size:13px;">Assigning...</div>';

      fetch('../api_assign_employee.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'geofence_id=' + geofenceId + '&employee_id=' + employeeId
      })
      .then(function(res) { return res.json(); })
      .then(function(data) {
        if (data.success) {
          msgDiv.innerHTML = '<div style="background:#d1fae5; color:#065f46; padding:10px; border-radius:6px; font-size:13px;">Employee assigned successfully!</div>';
          document.getElementById('assignForm').reset();
          setTimeout(function() {
            msgDiv.innerHTML = '';
          }, 3000);
        } else {
          msgDiv.innerHTML = '<div style="background:#fee2e2; color:#991b1b; padding:10px; border-radius:6px; font-size:13px;">' + (data.message || 'Failed to assign employee') + '</div>';
        }
      })
      .catch(function(err) {
        msgDiv.innerHTML = '<div style="background:#fee2e2; color:#991b1b; padding:10px; border-radius:6px; font-size:13px;">Error: ' + err.message + '</div>';
      });
    });

    // Check if should show assign section on page load
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'assign') {
      showAssignSection();
    } else {
      loadSites();
    }
  </script>
</body>
</html>