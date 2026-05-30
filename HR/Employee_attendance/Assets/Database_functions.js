function searchAttendance(searchTerm, searchDate) {
    const table = $('#attendanceTable').DataTable();
    $.ajax({
        url: './Modules/search_attendance.php',
        method: 'POST',
        data: {
            search: searchTerm,
            searchDate: searchDate
        },
        dataType: 'json',
        success: function(response) {
            table.clear().draw();
            if (response.data && response.data.length > 0) {
                response.data.forEach(function(row) {
                    table.row.add([
                        row.Attendance_id,
                        row.name,
                        row.department,
                        row.Date,
                        row.Location,
                        row.Clock_in,
                        row.Clockin_status_html,
                        row.Clock_out,
                        row.Clockout_status_html,
                        row.Duration,
                        row.AO,
                        row.Work_day_status
                    ]).draw();
                });
            }
        },
        error: function() {
            console.error('Search request failed');
        }
    });
};

function searchEmployee() {
    const searchTerm = $('#searchEmployeeInput').val();
    $.ajax({
        url: './Modules/search_employee.php', 
        method: 'POST',
        data: {
            search: searchTerm
        },
        dataType: 'json',
        success: function(response) {
            const employeeSuggestionDropdown = document.getElementById('employeeSuggestionDropdown');
            employeeSuggestionDropdown.classList.remove('d-none');
            $('#searchEmployeeInput').attr('aria-expanded', 'true');
            employeeSuggestionDropdown.innerHTML = '';
            response.forEach(function(employee) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'employee-suggestion-item';
                btn.setAttribute('role', 'option');
                btn.textContent = employee.name;
                btn.setAttribute('data-employee-id', employee.id || '');
                btn.setAttribute('data-employee-name', employee.name || '');
                btn.setAttribute('data-employee-department', employee.department || '');
                btn.setAttribute('data-employee-location', employee.location || '');
                employeeSuggestionDropdown.appendChild(btn);
            });
            // Handle employee search results here
        },
        error: function() {
            console.error('Employee search request failed');
        }
    });
};

function searchEmployeeLocation() {
    const searchTerm = $('#newModalLocation').val();
    $.ajax({
        url: './Modules/search_location.php',
        method: 'POST',
        data: {
            id: document.getElementById('newModalId').value,
            search: searchTerm
        },
        dataType: 'json',
        success: function(response) {
            const locationSuggestionDropdown = document.getElementById('locationSuggestionDropdown');
            locationSuggestionDropdown.classList.remove('d-none');
            $('#newModalLocation').attr('aria-expanded', 'true');
            locationSuggestionDropdown.innerHTML = '';
            console.log('Location search response:', response);
            response.forEach(function(location) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'employeeLocation-suggestion-item';
                btn.setAttribute('role', 'option');
                btn.textContent = location.name;
                btn.setAttribute('data-location-name', location.name || '');
                btn.setAttribute('data-location-coordinates', location.coordinates || '');
                locationSuggestionDropdown.appendChild(btn);
            });
        },
        error: function(response) {
            console.error('Location search request failed ', response);
        }   
    });
};