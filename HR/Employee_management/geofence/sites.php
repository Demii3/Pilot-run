<?php
  session_start();
  if (!isset($_SESSION['login']) || $_SESSION['empType'] != "HR") {
    header("location: ../../../");
    exit();
  }
  
  // Prevent caching to avoid showing logged-in content on back button
  header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
  header('Cache-Control: post-check=0, pre-check=0', FALSE);
  header('Pragma: no-cache');
  header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Sites</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="../employee_module.css" />
  <link rel="stylesheet" href="css/style.css" />
  <script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin ="anonymous"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script src="../../../Modules/universal_logout_handler.js"></script>
</head>
<body class="geofence-app">
  <?php
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/Pilot-run';
  ?>
  <nav class="custom-navbar">
    <div class="nav-left">
      <a class="logo-circle" href="../../index.php" aria-label="Go to Home">
        <img src="<?php echo $baseUrl; ?>/Images/logo.jpg" alt="Logo">
      </a>
      <span class="company-name">Chengshi <br>Construction Corp</span>
    </div>

    <div class="nav-right">
      <button class="avatar" onclick="toggleMenu()">
        <img src="<?php echo $baseUrl; ?>/Images/profilepic.jpg" alt="User">
      </button>

      <div id="profileMenu" class="dropdown-menu">
        <div class="profile-header">
          <img src="<?php echo $baseUrl; ?>/Images/profilepic.jpg" alt="User">
          <span>User</span>
        </div>
        <a href="#" class="profile-item"> Settings & Privacy </a>
        <a href="#" class="profile-item"> Help & Support </a>
        <a href="<?php echo $baseUrl; ?>/Modules/logout_process.php" class="profile-item" onclick="return handleLogout(event);"> Logout </a>
      </div>
    </div>
  </nav>

  <script>
    // Set global attendance active status for logout handler
    window.isAttendanceActive = false; // HR users don't have attendance active status
    
    function toggleMenu() {
      document.getElementById("profileMenu").classList.toggle("active");
    }

    document.addEventListener("click", function(e) {
      const menu = document.getElementById("profileMenu");
      const avatar = document.querySelector(".avatar");

      if (!avatar.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove("active");
      }
    });
  </script>

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
        <div style="display:flex; gap:20px; padding:20px; min-height:400px;">
          <!-- Left: Form -->
          <div style="flex:1; min-width:300px;">
            <form id="assignForm">
              <div style="margin-bottom: 15px;">
                <label for="siteSearch" style="display:block; margin-bottom:8px; font-weight:500;">Select Geofence Site</label>
                <div style="position:relative;">
                  <input id="siteSearch" type="text" autocomplete="off" placeholder="Type to search sites" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-family:'Poppins',sans-serif;">
                  <input id="siteSelect" name="geofence_id" type="hidden">
                  <div id="siteSuggestions" style="display:none; position:absolute; left:0; right:0; top:calc(100% + 4px); background:#fff; border:1px solid #d1d5db; border-radius:6px; box-shadow:0 10px 15px rgba(0,0,0,0.08); max-height:220px; overflow-y:auto; z-index:20;"></div>
                </div>
              </div>
              <div style="margin-bottom: 15px;">
                <label for="employeeSearch" style="display:block; margin-bottom:8px; font-weight:500;">Select Employee</label>
                <div style="position:relative;">
                  <input id="employeeSearch" type="text" autocomplete="off" placeholder="Type to search employees" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-family:'Poppins',sans-serif;">
                  <input id="employeeSelect" name="employee_id" type="hidden">
                  <div id="employeeSuggestions" style="display:none; position:absolute; left:0; right:0; top:calc(100% + 4px); background:#fff; border:1px solid #d1d5db; border-radius:6px; box-shadow:0 10px 15px rgba(0,0,0,0.08); max-height:220px; overflow-y:auto; z-index:20;"></div>
                </div>
              </div>
              <button type="submit" style="background:#3b82f6; color:white; border:none; padding:10px 20px; border-radius:6px; font-weight:600; cursor:pointer; width:100%; margin-bottom:10px;">Assign Employee</button>
              <button type="button" id="viewAssignedBtn" style="background:#10b981; color:white; border:none; padding:10px 20px; border-radius:6px; font-weight:600; cursor:pointer; width:100%;">View Assigned Employees</button>
            </form>
            <div id="assignMsg" style="margin-top:15px;"></div>
          </div>

          <!-- Right: Map Preview -->
          <div style="flex:1; min-width:300px; background:#f3f4f6; border-radius:6px; overflow:hidden; display:none;" id="mapContainer">
            <div id="assignMap" style="width:100%; height:100%;"></div>
          </div>
        </div>
      </div>

      <!-- Modal for Viewing Assigned Employees -->
      <div id="assignedEmployeesModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center; padding:20px;">
        <div style="background:white; border-radius:8px; width:100%; max-width:900px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 25px rgba(0,0,0,0.15);">
          <div style="padding:20px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:18px; font-weight:600;">Assigned Employees</h3>
            <button id="closeAssignedModal" style="background:none; border:none; font-size:24px; cursor:pointer; color:#999;">&times;</button>
          </div>
          <div style="padding:20px;">
            <div class="table-responsive">
              <table id="assignedEmployeesTable" class="table table-sm table-bordered">
                <thead style="background:#f3f4f6;">
                  <tr>
                    <th style="text-align:center; width:50px;">#</th>
                    <th>Employee</th>
                    <th>Site</th>
                    <th style="text-align:center; width:180px;">Actions</th>
                  </tr>
                </thead>
                <tbody id="assignedTableBody"></tbody>
              </table>
            </div>
            <div id="assignedTableEmpty" style="color:#999; font-size:13px; margin-top:10px; text-align:center;">Loading assignments...</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="bg-container">
    <img src="../../../Images/bgimg.jpg" class="bg-image" alt="Background">
    <div class="overlay"></div>
  </div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script>
    let savedGeofences = [];
    let geofencesForAssign = [];
    let employeesForAssign = [];
    let assignmentMapInstance = null;

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
      document.getElementById('mapContainer').style.display = 'none';
      
      // Update active button
      const sidebarButtons = document.querySelectorAll('.sidebar button');
      sidebarButtons.forEach(btn => btn.classList.remove('active'));
      const assignBtn = Array.from(sidebarButtons).find(btn => btn.textContent.includes('Assign'));
      if (assignBtn) assignBtn.classList.add('active');
      
      loadGeofencesForAssign();
      loadEmployeesForAssign();
    }

    function getGeofenceOptions() {
      return geofencesForAssign.map(function(gf) {
        return {
          id: String(gf.id),
          label: gf.name || ''
        };
      });
    }

    function getEmployeeOptions() {
      return employeesForAssign.map(function(emp) {
        return {
          id: String(emp.id),
          label: emp.name || ''
        };
      });
    }

    function renderSuggestionMenu(kind, items) {
      const menu = document.getElementById(kind + 'Suggestions');
      menu.innerHTML = '';

      if (!items.length) {
        const empty = document.createElement('div');
        empty.textContent = 'No matches found';
        empty.style.cssText = 'padding:10px 12px; color:#999; font-size:13px;';
        menu.appendChild(empty);
        menu.style.display = 'block';
        return;
      }

      items.forEach(function(item) {
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = item.label;
        button.style.cssText = 'display:block; width:100%; text-align:left; padding:10px 12px; border:none; background:#fff; cursor:pointer; font-family:\'Poppins\',sans-serif; font-size:14px;';
        button.addEventListener('mouseenter', function() {
          button.style.background = '#eff6ff';
        });
        button.addEventListener('mouseleave', function() {
          button.style.background = '#fff';
        });
        button.addEventListener('mousedown', function(e) {
          e.preventDefault();
          if (kind === 'site') {
            selectGeofence(item);
          } else {
            selectEmployee(item);
          }
        });
        menu.appendChild(button);
      });

      menu.style.display = 'block';
    }

    function filterSuggestionMenu(kind) {
      const input = document.getElementById(kind + 'Search');
      const menu = document.getElementById(kind + 'Suggestions');
      const hidden = document.getElementById(kind === 'site' ? 'siteSelect' : 'employeeSelect');
      const term = input.value.trim().toLowerCase();
      const options = kind === 'site' ? getGeofenceOptions() : getEmployeeOptions();

      hidden.value = '';
      menu.innerHTML = '';

      if (!term) {
        menu.style.display = 'none';
        if (kind === 'site') {
          document.getElementById('mapContainer').style.display = 'none';
        }
        return;
      }

      const filtered = options.filter(function(item) {
        return item.label.toLowerCase().includes(term);
      }).slice(0, 8);

      renderSuggestionMenu(kind, filtered);
    }

    function selectGeofence(item) {
      document.getElementById('siteSearch').value = item.label;
      document.getElementById('siteSelect').value = item.id;
      document.getElementById('siteSuggestions').style.display = 'none';
      handleSiteSelectChange();
    }

    function selectEmployee(item) {
      document.getElementById('employeeSearch').value = item.label;
      document.getElementById('employeeSelect').value = item.id;
      document.getElementById('employeeSuggestions').style.display = 'none';
    }

    function clearSuggestionMenu(kind) {
      const menu = document.getElementById(kind + 'Suggestions');
      setTimeout(function() {
        menu.style.display = 'none';
      }, 150);
    }

    function attachTypeaheadHandlers(kind) {
      const input = document.getElementById(kind + 'Search');
      const hidden = document.getElementById(kind === 'site' ? 'siteSelect' : 'employeeSelect');

      input.addEventListener('input', function() {
        hidden.value = '';
        filterSuggestionMenu(kind);
      });

      input.addEventListener('focus', function() {
        filterSuggestionMenu(kind);
      });

      input.addEventListener('blur', function() {
        clearSuggestionMenu(kind);
      });
    }

    attachTypeaheadHandlers('site');
    attachTypeaheadHandlers('employee');

    function showSitesSection() {
      document.getElementById('sitesSection').style.display = 'block';
      document.getElementById('assignSection').style.display = 'none';
      
      // Clean up map
      if (assignmentMapInstance) {
        assignmentMapInstance.remove();
        assignmentMapInstance = null;
      }
      
      // Update active button
      const sidebarButtons = document.querySelectorAll('.sidebar button');
      sidebarButtons.forEach(btn => btn.classList.remove('active'));
      const sitesBtn = Array.from(sidebarButtons).find(btn => btn.textContent.includes('Manage Sites'));
      if (sitesBtn) sitesBtn.classList.add('active');
      
      loadSites();
    }

    function loadGeofencesForAssign() {
      fetch('../api_geofences.php')
        .then(function(res) { return res.json(); })
        .then(function(data) {
          // Handle both array response and object with success flag
          const geofences = Array.isArray(data) ? data : (data.geofences || []);
          geofencesForAssign = geofences;

          filterSuggestionMenu('site');
        })
        .catch(function(err) {
          console.error('Error loading geofences:', err);
          document.getElementById('siteSuggestions').innerHTML = '<div style="padding:10px 12px; color:#999; font-size:13px;">Error loading sites</div>';
        });
    }

    function loadEmployeesForAssign() {
      fetch('../api_employees.php')
        .then(function(res) { return res.json(); })
        .then(function(data) {
          employeesForAssign = Array.isArray(data) ? data : (data.data || data.employees || []);
          filterSuggestionMenu('employee');
        })
        .catch(function(err) {
          console.error('Error loading employees:', err);
          document.getElementById('employeeSuggestions').innerHTML = '<div style="padding:10px 12px; color:#999; font-size:13px;">Error loading employees</div>';
        });
    }

    function handleSiteSelectChange() {
      const siteId = document.getElementById('siteSelect').value;
      if (!siteId) {
        document.getElementById('mapContainer').style.display = 'none';
        return;
      }

      const selectedGeofence = geofencesForAssign.find(gf => gf.id == siteId);
      if (!selectedGeofence) return;

      displayAssignmentMap(selectedGeofence);
    }

    function displayAssignmentMap(geofence) {
      const mapContainer = document.getElementById('mapContainer');
      mapContainer.style.display = 'block';

      // Parse coordinates - handle both array and string formats
      let coordinates = [];
      try {
        // If already an array (from JSON decode), use directly
        if (Array.isArray(geofence.coordinates)) {
          coordinates = geofence.coordinates;
        } else if (typeof geofence.coordinates === 'string') {
          // If string, try to parse as JSON
          coordinates = JSON.parse(geofence.coordinates);
        } else {
          throw new Error('Invalid coordinate format');
        }
        
        // Validate coordinates
        if (!Array.isArray(coordinates) || coordinates.length < 3) {
          throw new Error('Invalid coordinate array - need at least 3 points for polygon');
        }
      } catch (e) {
        console.error('Error parsing coordinates:', e);
        console.log('Geofence data:', geofence);
        mapContainer.innerHTML = '<div style="padding:20px; display:flex; align-items:center; justify-content:center; height:100%; color:#999; text-align:center;">Invalid or missing map data</div>';
        return;
      }

      // Destroy existing map
      if (assignmentMapInstance) {
        assignmentMapInstance.remove();
        assignmentMapInstance = null;
      }

      // Create new map
      setTimeout(function() {
        try {
          const mapDiv = document.getElementById('assignMap');
          mapDiv.innerHTML = ''; // Clear any previous error messages
          
          assignmentMapInstance = L.map('assignMap', {
            zoomControl: true,
            attributionControl: false,
            dragging: true,
            scrollWheelZoom: true,
            doubleClickZoom: true
          });

          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '',
            maxZoom: 19
          }).addTo(assignmentMapInstance);

          // Create polygon
          const polygon = L.polygon(coordinates, {
            color: '#3b82f6',
            weight: 2,
            fillOpacity: 0.2,
            fillColor: '#3b82f6'
          }).addTo(assignmentMapInstance);

          // Fit bounds
          assignmentMapInstance.fitBounds(polygon.getBounds(), { padding: [50, 50] });

          // Invalidate size to fix rendering
          setTimeout(function() {
            assignmentMapInstance.invalidateSize();
            assignmentMapInstance.fitBounds(polygon.getBounds(), { padding: [50, 50] });
          }, 100);
        } catch (mapError) {
          console.error('Map initialization error:', mapError);
          document.getElementById('assignMap').innerHTML = '<div style="padding:20px; color:#999;">Error initializing map</div>';
        }
      }, 50);
    }

    document.getElementById('assignForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const geofenceId = document.getElementById('siteSelect').value;
      const employeeId = document.getElementById('employeeSelect').value;
      const msgDiv = document.getElementById('assignMsg');
      const payload = { geofence_id: geofenceId, employee_id: employeeId };
      console.log('Selected geofence ID:', geofenceId);
      console.log('Selected employee ID:', employeeId);

      if (!geofenceId || !employeeId) {
        msgDiv.innerHTML = '<div style="background:#fee2e2; color:#991b1b; padding:10px; border-radius:6px; font-size:13px;">Please select both site and employee</div>';
        return;
      }

      msgDiv.innerHTML = '<div style="color:#666; font-size:13px;">Assigning...</div>';

      fetch('../api_assign_employee.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      })
      .then(function(res) { return res.text(); })
      .then(function(text) {
        let data;
        try {
          data = JSON.parse(text);
        } catch (parseError) {
          const preview = (text || '').trim().slice(0, 160);
          throw new Error('Server returned non-JSON response' + (preview ? ': ' + preview : ''));
        }
        return data;
      })
      .then(function(data) {
        console.log('API response:', data);
        if (data.success) {
          msgDiv.innerHTML = '<div style="background:#d1fae5; color:#065f46; padding:10px; border-radius:6px; font-size:13px;">Employee assigned successfully!</div>';
          document.getElementById('assignForm').reset();
          setTimeout(function() {
            msgDiv.innerHTML = '';
          }, 3000);
        } else {
          msgDiv.innerHTML = '<div style="background:#fee2e2; color:#991b1b; padding:10px; border-radius:6px; font-size:13px;">' + (data.message) + '</div>';
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

    // View Assigned Employees Modal Functions
    let editAssignmentId = null;
    let assignedTable = null;

    function openAssignedEmployeesModal() {
      document.getElementById('assignedEmployeesModal').style.display = 'flex';
      loadAssignedEmployees();
    }

    function closeAssignedEmployeesModal() {
      document.getElementById('assignedEmployeesModal').style.display = 'none';
      editAssignmentId = null;
    }

    function loadAssignedEmployees() {
      // Use the local HR_features API for compatibility
      const apiUrl = '../../../HR_features/api_assignments_list.php';
      
      fetch(apiUrl)
        .then(function(res) { return res.json(); })
        .then(function(data) {
          if (!data.success) {
            alert(data.message || 'Unable to load assignments');
            return;
          }
          
          const assignments = data.data || [];
          
          // Destroy existing DataTable if it exists
          if ($.fn.DataTable.isDataTable('#assignedEmployeesTable')) {
            $('#assignedEmployeesTable').DataTable().destroy();
          }
          
          // Clear table body
          const $body = document.getElementById('assignedTableBody');
          $body.innerHTML = '';
          
          if (assignments.length === 0) {
            document.getElementById('assignedTableEmpty').textContent = 'No assignments found.';
            document.getElementById('assignedTableEmpty').style.display = 'block';
            return;
          }
          
          document.getElementById('assignedTableEmpty').style.display = 'none';
          
          assignments.forEach(function(item, index) {
            const employeeText = item.employee_name + (item.employee_username ? ' (' + item.employee_username + ')' : '');
            const row = document.createElement('tr');
            row.innerHTML = '<td style="text-align:center;">' + (index + 1) + '</td>' +
              '<td>' + employeeText + '</td>' +
              '<td>' + item.site_name + '</td>' +
              '<td style="text-align:center;">' +
              '<button type="button" class="btn btn-sm btn-outline-primary edit-assignment-btn" data-id="' + item.tb_id + '" data-employee="' + item.employee_id + '" data-site="' + item.site_id + '" style="margin-right:5px;">Edit</button>' +
              '<button type="button" class="btn btn-sm btn-outline-danger delete-assignment-btn" data-id="' + item.tb_id + '">Delete</button>' +
              '</td>';
            $body.appendChild(row);
          });
          
          // Initialize DataTable after DOM is updated
          setTimeout(function() {
            try {
              assignedTable = $('#assignedEmployeesTable').DataTable({
                paging: true,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, -1], [5, 10, 25, "All"]],
                searching: true,
                ordering: true,
                language: {
                  search: 'Search:',
                  lengthMenu: 'Show _MENU_ entries',
                  info: 'Showing _START_ to _END_ of _TOTAL_ entries'
                },
                responsive: true,
                columnDefs: [
                  { orderable: false, targets: 3 }
                ]
              });
              
              // Attach event handlers after DataTable is initialized
              attachAssignmentHandlers();
            } catch (e) {
              console.error('DataTable initialization error:', e);
              attachAssignmentHandlers();
            }
          }, 50);
        })
        .catch(function(err) {
          alert('Failed to load assignments: ' + err.message);
          console.error('Error loading assignments:', err);
        });
    }
    
    function attachAssignmentHandlers() {
          // Attach event handlers
          document.querySelectorAll('.edit-assignment-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
              editAssignmentId = parseInt(this.getAttribute('data-id'));
              const employeeMatch = employeesForAssign.find(function(emp) {
                return String(emp.id) === String(this.getAttribute('data-employee'));
              }, this);
              const siteMatch = geofencesForAssign.find(function(gf) {
                return String(gf.id) === String(this.getAttribute('data-site'));
              }, this);

              if (employeeMatch) {
                document.getElementById('employeeSearch').value = employeeMatch.name;
                document.getElementById('employeeSelect').value = employeeMatch.id;
              }
              if (siteMatch) {
                document.getElementById('siteSearch').value = siteMatch.name;
                document.getElementById('siteSelect').value = siteMatch.id;
                handleSiteSelectChange();
              }
              
              // Highlight the select fields
              document.getElementById('employeeSearch').style.borderColor = '#f59e0b';
              document.getElementById('siteSearch').style.borderColor = '#f59e0b';
              
              document.getElementById('assignMsg').innerHTML = '<div style="background:#fef3c7; color:#92400e; padding:10px; border-radius:6px; font-size:13px;">Editing assignment. Update fields and click "Assign Employee" to save or refresh to cancel.</div>';
              closeAssignedEmployeesModal();
            });
          });
          
          document.querySelectorAll('.delete-assignment-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
              const assignmentId = parseInt(this.getAttribute('data-id'));
              if (!confirm('Delete this assignment?')) return;
              
              fetch('../../../HR_features/api_assignment_delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'assignment_id=' + assignmentId
              })
              .then(function(res) { return res.json(); })
              .then(function(data) {
                if (data.success) {
                  loadAssignedEmployees();
                  alert(data.message || 'Assignment deleted');
                } else {
                  alert(data.message || 'Delete failed');
                }
              })
              .catch(function(err) {
                alert('Error: ' + err.message);
              });
            });
          });
    }

    document.getElementById('viewAssignedBtn').addEventListener('click', openAssignedEmployeesModal);
    document.getElementById('closeAssignedModal').addEventListener('click', closeAssignedEmployeesModal);
    
    // Close modal when clicking outside
    document.getElementById('assignedEmployeesModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeAssignedEmployeesModal();
      }
    });
  </script>
</body>
</html>