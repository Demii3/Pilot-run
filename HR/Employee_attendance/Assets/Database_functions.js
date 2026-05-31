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
                        row.Date,
                        row.department,
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

function empSearch() {
    const searchTerm = searchEmployeeInput.value;
    const payload = { searchTerm: searchTerm };
    fetch('./Modules/search_employee.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        console.log(data);
        // Populate the dropdown with employee suggestions
        locationSuggestionDropdown.innerHTML = '';
        data.forEach(employee => {
            let option = document.createElement('div');
            option.classList.add('col-md-12', 'employee-suggestion-item');
            option.setAttribute('data-id', employee.id);
            option.setAttribute('data-name', employee.name);
            option.setAttribute('data-department', employee.department);
            option.innerHTML = employee.name;
            employeeSuggestionDropdown.appendChild(option);
        });
        employeeSuggestionDropdown.classList.remove('d-none');
    });
}

function locSearch() {
    const searchTerm = searchLocationInput.value;
    const id = newModalId.value;
    const payload = { searchTerm: searchTerm, id: id };
    fetch('./Modules/search_location.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        console.log(data);
        // Populate the dropdown with location suggestions
        locationSuggestionDropdown.innerHTML = '';

        if (data.length === 0) {
            const option = document.createElement('div');
            option.classList.add('col-md-12', 'location-suggestion-item');
            option.innerHTML = 'No locations found';
            locationSuggestionDropdown.appendChild(option);
            locationSuggestionDropdown.classList.remove('d-none');
            return;
        }

        data.forEach(location => {
            const option = document.createElement('div');
            option.classList.add('col-md-12', 'location-suggestion-item');
            option.setAttribute('data-name', location.name);
            option.setAttribute('data-coordinates', location.coordinates);
            option.innerHTML = location.name;
            locationSuggestionDropdown.appendChild(option);
        });
        locationSuggestionDropdown.classList.remove('d-none');
    });
}

let createAttendanceNotifModal = null;
let createAttendanceNotifModalBodySpan = null;

document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('createAttendanceNotifModal');
    if (modalEl && window.bootstrap && bootstrap.Modal) {
        if (typeof bootstrap.Modal.getOrCreateInstance === 'function') {
            createAttendanceNotifModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        } else {
            try {
                createAttendanceNotifModal = new bootstrap.Modal(modalEl);
            } catch (e) {
                createAttendanceNotifModal = null;
            }
        }
    }

    createAttendanceNotifModalBodySpan = document.getElementById('createAttendanceNotifModalBodySpan');
});

function saveAttendance() {
    const to12HourTime = (timeValue) => {
        const normalized = String(timeValue || '').trim();

        if (!normalized || normalized === '--:--' || normalized === '--:-- --') {
            return normalized;
        }

        const parts = normalized.split(':');
        if (parts.length < 2) {
            return normalized;
        }

        let hours = Number(parts[0]);
        const minutes = parts[1];

        if (!Number.isFinite(hours)) {
            return normalized;
        }

        const suffix = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        if (hours === 0) {
            hours = 12;
        }

        return `${hours}:${minutes} ${suffix}`;
    };

    const requiredFields = [
        { id: 'newModalId', label: 'Employee ID' },
        { id: 'newModalName', label: 'Employee name' },
        { id: 'newModalDepartment', label: 'Department' },
        { id: 'newModalDate', label: 'Date' },
        { id: 'newModalLocation', label: 'Location' },
        { id: 'newModalClockIn', label: 'Clock In' },
        { id: 'newModalClockInStatus', label: 'Clock In Status' },
        { id: 'newModalClockOut', label: 'Clock Out' },
        { id: 'newModalClockOutStatus', label: 'Clock Out Status' }
    ];

    for (const field of requiredFields) {
        const element = document.getElementById(field.id);
        const value = element ? String(element.value || '').trim() : '';

        if (!value || value === '--:--' || value === '--:-- --') {
            if (createAttendanceNotifModalBodySpan) {
                createAttendanceNotifModalBodySpan.textContent = field.label + ' is required before saving attendance.';
                createAttendanceNotifModalBodySpan.classList.remove('d-none');
            }
            if (createAttendanceNotifModal && typeof createAttendanceNotifModal.show === 'function') {
                createAttendanceNotifModal.show();
            }
            return;
        }
    }

    const employeeId = document.getElementById('newModalId').value.trim();
    const date = document.getElementById('newModalDate').value.trim();
    const location = document.getElementById('newModalLocation').value.trim();
    const clockIn = to12HourTime(document.getElementById('newModalClockIn').value);
    const clockInStatus = document.getElementById('newModalClockInStatus').value;
    const clockOut = to12HourTime(document.getElementById('newModalClockOut').value);
    const clockOutStatus = document.getElementById('newModalClockOutStatus').value;
    const allowOvertime = (document.getElementById('newModalClockOutStatus').value == 'Over-time') ? 1 : 0;
    const workClassification = document.getElementById('newModalWorkClassification').value;
    const duration = parseInt(document.getElementById('newModalDuration').value);

    fetch('./Modules/save_attendance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            employeeId: employeeId,
            date: date,
            location: location,
            clockIn: clockIn,
            clockInStatus: clockInStatus,
            clockOut: clockOut,
            clockOutStatus: clockOutStatus,
            allowOvertime: allowOvertime,
            workClassification: workClassification,
            duration: duration
        })
    })
    .then(response => response.json())
    .then(data => {
        const message = data.message || data.msg || 'Request completed.';
        if (data.success) {
            if (createAttendanceNotifModalBodySpan) {
                createAttendanceNotifModalBodySpan.textContent = message;
                createAttendanceNotifModalBodySpan.classList.remove('d-none');
            }
            if (createAttendanceNotifModal && typeof createAttendanceNotifModal.show === 'function') {
                createAttendanceNotifModal.show();
            }
            document.getElementById('createAttendanceModal').querySelector('.btn-close').click();
            searchAttendance('', '');
        }
    })
    .catch(error => {
        console.error('Error saving attendance:', error);
        if (createAttendanceNotifModalBodySpan) {
            createAttendanceNotifModalBodySpan.textContent = 'An error occurred while saving attendance.';
            createAttendanceNotifModalBodySpan.classList.remove('d-none');
        }
        if (createAttendanceNotifModal && typeof createAttendanceNotifModal.show === 'function') {
            createAttendanceNotifModal.show();
        }
    });
};