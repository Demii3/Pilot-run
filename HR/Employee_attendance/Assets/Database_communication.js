function searchAttendance(searchTerm, searchDate, table) {
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
                        row.Emp_id,
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

function saveCreatedAttendance() {
    const employeeId = document.getElementById('newModalEmployeeId').value;
    const date = document.getElementById('newModalDate').value;
    const location = document.getElementById('newModalLocation').value;
    const clockIn = convert24HourTo12Hour(document.getElementById('newModalClockIn').value);
    const clockInStatus = document.getElementById('newModalClockInStatus').value;
    const clockOut = convert24HourTo12Hour(document.getElementById('newModalClockOut').value);
    const clockOutStatus = document.getElementById('newModalClockOutStatus').value;
    const allowOvertime = document.getElementById('allowOvertime').checked ? 1 : 0;
};

function configAttendance(attendance_id, searchTerm, table, action) {
    const message = action == 'delete' ? 'delete this record?' : 'save this record?';
    if (!confirm('Are you sure you want to ' + message)) {
        alert(document.getElementById('modalDate').value);
        return;
    } else {
            
        if (document.getElementById('Manual-modify').checked == true) {
            if (!confirm('You have enabled Manual Modify. Make sure these changes are correct before proceeding. Unentended changes may cause issues with attendance records. Do you want to proceed?')) {
            return;
            };
        };

        let url = '';
        let data = {
            id: attendance_id
        };

        switch(action) {
            case 'delete':
                url = './Modules/delete_attendance.php';
                break;
            case 'update':
                url = './Modules/update_attendance.php';
                data.date = document.getElementById('modalDate').value;
                data.clockIn = convert24HourTo12Hour(document.getElementById('modalClockIn').value);
                data.clockInStatus = document.getElementById('modalClockInStatus').value;
                data.clockOut = convert24HourTo12Hour(document.getElementById('modalClockOut').value);
                data.clockOutStatus = document.getElementById('modalClockOutStatus').value;
                data.duration = excludeLunchBreak(document.getElementById('modalClockIn').value, document.getElementById('modalClockOut').value);
                data.allowOvertime = document.getElementById('allowOvertime').checked ? 1 : 0;
                data.workClassification = document.getElementById('modalWorkClassification').value;
                break;
            default:
                console.error('Invalid action specified');
                return;
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: {data: data},
            dataType: 'json',
            success: function(response) {
                const message = response && response.msg ? response.msg : 'Request completed.';
                alert(message);
                document.getElementById('attendanceModal').querySelector('.btn-close').click();
                searchAttendance(searchTerm, $('#searchDate').val(), table);
            },
            error: function() {
                console.error('Request failed');
            }
        });
    }
};

function saveOptions() {
    $.ajax({
        url: './Modules/HR_settings.php',
        method: 'POST',
        data: {
                purpose: 'save_settings',
                override: $('#overideAll').is(':checked') ? 1 : 0,
                hideDepartment: $('#hideDepartment').is(':checked') ? 1 : 0,
                hideLocations: $('#hideLocations').is(':checked') ? 1 : 0,
                hideDuration: $('#hideDuration').is(':checked') ? 1 : 0
              },        
        dataType: 'json',
        success: function(response) {
            const message = response && response.msg ? response.msg : 'Options saved successfully.';
            alert(message);
            window.location.reload();
        }});
};