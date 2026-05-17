let attendanceEditInitialValues = null;
let allowOvertimeInitialValue = 0;
let map = null;
let markersLayer = null;
let geofenceLayer = null;

document.addEventListener("click", function(e) {
    const menu = document.getElementById("profileMenu");
    const avatar = document.querySelector(".avatar");

    if (!avatar.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove("active");
    }
});

function editAttendance() {
    const editbtn = document.getElementById('editButton');
    const deletebtn = document.getElementById('deleteButton');
    const savebtn = document.getElementById('saveButton');
    const morebtn = document.getElementById('moreButton');
    const dateInput = document.getElementById('modalDate');
    const clockInInput = document.getElementById('modalClockIn');
    const clockInStatusInput = document.getElementById('modalClockInStatus');
    const clockOutInput = document.getElementById('modalClockOut');
    const clockOutStatusInput = document.getElementById('modalClockOutStatus');
    const allowOvertimeInput = document.getElementById('allowOvertime');
    const absentbtn = document.getElementById('absentButton');
    const onLeavebtn = document.getElementById('onLeaveButton');

    if (editbtn.innerText === 'Edit') {
        attendanceEditInitialValues = getAttendanceModalValues();
        editbtn.innerText = 'Cancel';
        editbtn.classList.remove('btn-warning');
        editbtn.classList.add('btn-primary');
        deletebtn.classList.remove('d-none');
        savebtn.classList.remove('d-none');
        morebtn.classList.remove('d-none');
        allowOvertimeInput.disabled = false;
        absentbtn.classList.remove('d-none');
        onLeavebtn.classList.remove('d-none');
        dateInput.readOnly = false;
        clockInInput.readOnly = false;
        clockInStatusInput.disabled = false;
        clockOutInput.readOnly = false;
        clockOutStatusInput.disabled = false;
    } else {
        setAttendanceModalValues(attendanceEditInitialValues);
        attendanceEditInitialValues = null;
        editbtn.innerText = 'Edit';
        editbtn.classList.remove('btn-primary');
        editbtn.classList.add('btn-warning');
        deletebtn.classList.add('d-none');
        savebtn.classList.add('d-none');
        morebtn.classList.add('d-none');
        allowOvertimeInput.disabled = true;
        absentbtn.classList.add('d-none');
        onLeavebtn.classList.add('d-none');
        dateInput.readOnly = true;
        clockInInput.readOnly = true;
        clockInStatusInput.disabled = true;
        clockOutInput.readOnly = true;
        clockOutStatusInput.disabled = true;
        if (morebtn.textContent == 'Less') {
            moreAttendance();
        }
    }

};

function setAttendanceModalValues(values) {
    if (!values) {
        return;
    }

    document.getElementById('modalClockIn').value = values.clockIn;
    document.getElementById('modalClockInStatus').value = values.clockInStatus;
    document.getElementById('modalClockOut').value = values.clockOut;
    document.getElementById('modalClockOutStatus').value = values.clockOutStatus;
    document.getElementById('allowOvertime').checked = values.allowOvertime;
};

function allowOvertimeToggle(value) {
    if(value) {
        document.getElementById('modalClockOutStatus').querySelector('option[value="Over-time"]').disabled = false;
    } else {
        document.getElementById('modalClockOutStatus').querySelector('option[value="Over-time"]').disabled = true;
    }
};

function setAttendanceStatus(action) {
    const clockInInput = document.getElementById('modalClockIn');
    const clockInStatusInput = document.getElementById('modalClockInStatus');
    const clockOutInput = document.getElementById('modalClockOut');
    const clockOutStatusInput = document.getElementById('modalClockOutStatus');

    if (!allowOvertimeInitialValue) {
        switch(action) {
            case 'Absent':
                clockInStatusInput.value = 'Absent';
                clockOutStatusInput.value = 'Absent';
                clockInInput.value = '--:--';
                clockOutInput.value = '--:--';
                break;
            case 'On-Leave':
                clockInStatusInput.value = 'On-leave';
                clockOutStatusInput.value = 'On-leave';
                clockInInput.value = '08:00';
                clockOutInput.value = '17:00';
                break;
        };
        allowOvertimeInitialValue = 1;
    } else {
        clockInStatusInput.value = attendanceEditInitialValues.clockInStatus;
        clockOutStatusInput.value = attendanceEditInitialValues.clockOutStatus;
        clockInInput.value = attendanceEditInitialValues.clockIn;
        clockOutInput.value = attendanceEditInitialValues.clockOut;
        allowOvertimeInitialValue = 0;
    };
};

function getAttendanceModalValues() {
    return {
        clockIn: document.getElementById('modalClockIn').value,
        clockInStatus: document.getElementById('modalClockInStatus').value,
        clockOut: document.getElementById('modalClockOut').value,
        clockOutStatus: document.getElementById('modalClockOutStatus').value,
        allowOvertime: document.getElementById('allowOvertime').checked
    };
};

function manualMode() {
    const value = document.getElementById('Manual-modify').checked;

    const allowOvertimeInput = document.getElementById('allowOvertimeContainer');
    const allowOvertimeInputField = document.getElementById('allowOvertime');
    const statusButtons = document.getElementById('statusButtons');

    const onleaveOptionIn = document.getElementById('modalClockInStatus').querySelector('option[value="On-leave"]');
    const absentOptionIn = document.getElementById('modalClockInStatus').querySelector('option[value="Absent"]');

    const overTimeOption = document.getElementById('modalClockOutStatus').querySelector('option[value="Over-time"]');
    const onleaveOptionOut = document.getElementById('modalClockOutStatus').querySelector('option[value="On-leave"]');
    const absentOptionOut = document.getElementById('modalClockOutStatus').querySelector('option[value="Absent"]');
    if (value) {
        allowOvertimeInputField.checked = true;
        allowOvertimeInput.classList.add('d-none');
        statusButtons.classList.add('d-none');
        onleaveOptionIn.disabled = false;
        absentOptionIn.disabled = false;
        overTimeOption.disabled = false;
        onleaveOptionOut.disabled = false;
        absentOptionOut.disabled = false;

    } else {
        allowOvertimeInput.classList.remove('d-none');
        statusButtons.classList.remove('d-none');
        onleaveOptionIn.disabled = true;
        absentOptionIn.disabled = true;
        overTimeOption.disabled = true;
        onleaveOptionOut.disabled = true;
        absentOptionOut.disabled = true;
    };
};

function moreAttendance() {
    const morebtn = document.getElementById('moreButton');
    const additionalSettings = document.getElementById('additionalSettings');
    if (morebtn.innerText === 'More') {
        morebtn.innerText = 'Less';
        additionalSettings.classList.remove('d-none');
    } else {
        morebtn.innerText = 'More';
        manualMode();
        additionalSettings.classList.add('d-none');
    }
};

function populateModalEmpInfo(data) {
    const newModalId = document.getElementById('newModalId');
    const newModalName = document.getElementById('newModalName');
    const newModalDepartment = document.getElementById('newModalDepartment');

    newModalId.value = data.id || '';
    newModalName.value = data.name || '';
    newModalDepartment.value = data.department || '';

    updateReadonlyEmptyState('#createAttendanceModal input[readonly]');

};

function populateModalLocInfo(data) {
    const newModalLocation = document.getElementById('newModalLocation');
    const newModalLocationCoordinates = document.getElementById('newModalLocationCoordinates');

    newModalLocation.value = data.name || '';
    newModalLocationCoordinates.value = data.coordinates || '';

    updateReadonlyEmptyState('#createAttendanceModal input[readonly]');

};

function updateReadonlyEmptyState(selector) {
    const readonlyInputs = document.querySelectorAll(selector);

    readonlyInputs.forEach((input) => {
        const isEmpty = !input.value || input.value.trim() === '';
        input.classList.toggle('readonly-empty', isEmpty);
    });
}

function clearCreateAttendanceModal() {
    const modal = document.getElementById('createAttendanceModal');

    if (!modal) {
        return;
    }

    modal.querySelectorAll('input').forEach((input) => {
        if (input.type === 'checkbox' || input.type === 'radio') {
            input.checked = false;
            return;
        }

        input.value = '';
    });

    const employeeDropdown = document.getElementById('employeeSuggestionDropdown');
    if (employeeDropdown) {
        employeeDropdown.classList.add('d-none');
        employeeDropdown.innerHTML = '';
    }

    const locationDropdown = document.getElementById('locationSuggestionDropdown');
    if (locationDropdown) {
        locationDropdown.classList.add('d-none');
        locationDropdown.innerHTML = '';
    }

    const employeeInput = document.getElementById('searchEmployeeInput');
    if (employeeInput) {
        employeeInput.setAttribute('aria-expanded', 'false');
    }

    const locationInput = document.getElementById('newModalLocation');
    if (locationInput) {
        locationInput.setAttribute('aria-expanded', 'false');
    }

    updateReadonlyEmptyState('#createAttendanceModal input[readonly]');
}

function viewLocationOnMap() {
    const userLocInput = document.getElementById('modalUserLoc').value;
    const geofenceLoc = document.getElementById('modalCoordinates').value;

    if (!userLocInput || !geofenceLoc) {
        if (map) map.remove();
        document.getElementById('userLocModalBody').querySelector('span').classList.remove('d-none');
        const userLocModal = document.getElementById('userLocModal');
        const userLocModalBody = document.getElementById('userLocModalBody');
        const modal = bootstrap.Modal.getOrCreateInstance(userLocModal);
        modal.show();
        return;
    }

    const userLocArray = userLocInput.slice(1, -1).split(',');
    const userLat = parseFloat(userLocArray[0].slice(1, -1));
    const userLng = parseFloat(userLocArray[1].slice(1, -1));
    const userLocModal = document.getElementById('userLocModal');
    const userLocModalBody = document.getElementById('userLocModalBody');
    const modal = bootstrap.Modal.getOrCreateInstance(userLocModal);
    modal.show();

    if (map) {
        markersLayer.clearLayers();
        if (geofenceLayer) {
            geofenceLayer.remove();
        }
        geofenceLayer = L.polygon(JSON.parse(geofenceLoc), {
        color: 'blue',
        fillColor: '#3388ff',
        fillOpacity: 0.2
    }).addTo(map);
        const marker = L.marker([userLat, userLng]).addTo(markersLayer);
        map.setView([userLat, userLng], 18);
        return;
    }

    map = L.map(userLocModalBody).setView([userLat, userLng], 18);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    markersLayer = L.layerGroup().addTo(map);
    const marker = L.marker([userLat, userLng]).addTo(markersLayer);

    geofenceLayer = L.polygon(JSON.parse(geofenceLoc), {
        color: 'blue',
        fillColor: '#3388ff',
        fillOpacity: 0.2
    }).addTo(map);

    // ensure tiles render correctly after showing modal
    setTimeout(function() { try { map.invalidateSize(); } catch (e) { console.warn('invalidateSize failed', e); } }, 300);
}