const createAttendanceModal = document.getElementById('createAttendanceModal');
const searchEmployeeInput = document.getElementById('searchEmployeeInput');
const searchLocationInput = document.getElementById('newModalLocation');
const employeeSuggestionDropdown = document.getElementById('employeeSuggestionDropdown');
const locationSuggestionDropdown = document.getElementById('locationSuggestionDropdown');
const newModalId = document.getElementById('newModalId');
const newModalName = document.getElementById('newModalName');
const newModalDepartment = document.getElementById('newModalDepartment');
const newModalLocation = document.getElementById('newModalLocation');
const newModalDuration = document.getElementById('newModalDuration');
const newModalClockIn = document.getElementById('newModalClockIn');
const newModalClockOut = document.getElementById('newModalClockOut');
const clockInStatus = document.getElementById('newModalClockInStatus');
const clockOutStatus = document.getElementById('newModalClockOutStatus');
const includeLunchBreakCreate = document.getElementById('Include-lunchbreak-create');
const newModalAbsentButton = document.getElementById('NewModalAbsentButton');
const newModalOnLeaveButton = document.getElementById('NewModalOnLeaveButton');
let tempDuration = '';

if (createAttendanceModal) {
    createAttendanceModal.addEventListener('show.bs.modal', function() {
        newModalName.classList.add('readonly-empty');
        newModalDepartment.classList.add('readonly-empty');
        newModalLocation.readOnly = true;
        newModalLocation.classList.add('readonly-empty');
    });

    createAttendanceModal.addEventListener('hidden.bs.modal', function() {
        // Clear all fields when the modal is hidden
        newModalId.value = '';
        newModalName.value = '';
        newModalDepartment.value = '';
        newModalLocation.value = '';
        newModalClockIn.value = '';
        newModalClockOut.value = '';
        clockOutStatus.value = 'Absent';
        includeLunchBreakCreate.checked = false;
        newModalDuration.value = '';
        tempDuration = '';
        employeeSuggestionDropdown.innerHTML = '';
        employeeSuggestionDropdown.classList.add('d-none');
        locationSuggestionDropdown.innerHTML = '';
        locationSuggestionDropdown.classList.add('d-none');
    });
}

if (employeeSuggestionDropdown) {
    employeeSuggestionDropdown.addEventListener('mousedown', function(e) {
        const option = e.target.closest('.employee-suggestion-item');
        if (!option) return;

        console.log('Selected employee:', {
            id: option.getAttribute('data-id'),
            name: option.getAttribute('data-name'),
            department: option.getAttribute('data-department')
        });

        newModalLocation.value = '';

        // Populate the new modal fields with the selected employee's information
        newModalId.value = option.getAttribute('data-id');
        newModalName.value = option.getAttribute('data-name');
        newModalDepartment.value = option.getAttribute('data-department');
        newModalName.classList.remove('readonly-empty');
        newModalDepartment.classList.remove('readonly-empty');
        newModalLocation.readOnly = false;
        newModalLocation.classList.remove('readonly-empty');
    });
}

if (locationSuggestionDropdown) {
    locationSuggestionDropdown.addEventListener('mousedown', function(e) {
        const option = e.target.closest('.location-suggestion-item');
        if (!option) return;

        console.log('Selected location:', {
            name: option.getAttribute('data-name'),
            coordinates: option.getAttribute('data-coordinates')
        });

        // Populate the new modal fields with the selected location's information
        newModalLocation.value = option.getAttribute('data-name');
    });
}

searchEmployeeInput.addEventListener('focus', function() {
    empSearch();        //Found at Database_functions.js
});

searchEmployeeInput.addEventListener('focusout', function() {
    employeeSuggestionDropdown.innerHTML = '';
    employeeSuggestionDropdown.classList.add('d-none');
});

searchEmployeeInput.addEventListener('input', function() {
    employeeSuggestionDropdown.innerHTML = '';
    const searchTerm = searchEmployeeInput.value;

    if (searchTerm.length < 2) {
        employeeSuggestionDropdown.classList.add('d-none');
        return;
    }

    empSearch();        //Found at Database_functions.js
});

searchLocationInput.addEventListener('focus', function() {
    locSearch();        //Found at Database_functions.js
});

searchLocationInput.addEventListener('focusout', function() {
    locationSuggestionDropdown.innerHTML = '';
    locationSuggestionDropdown.classList.add('d-none');
});

searchLocationInput.addEventListener('input', function() {
    const searchTerm = searchLocationInput.value;

    if (searchTerm.length < 2) {
        locationSuggestionDropdown.classList.add('d-none');
        return;
    }

    locSearch();        //Found at Database_functions.js
});

function CalculateWorkingHours(clockIn, clockOut) {
    const clockInTime = newModalClockIn.value;
    const clockOutTime = newModalClockOut.value;
    const allowLunchBreak = document.getElementById('Include-lunchbreak-create').checked;

    if (!clockInTime || !clockOutTime) return '0:00';

    // Convert times to minutes
    const [inHours, inMinutes] = clockInTime.split(':').map(Number);
    const [outHours, outMinutes] = clockOutTime.split(':').map(Number);

    const inTotalMinutes = inHours * 60 + inMinutes;
    const outTotalMinutes = outHours * 60 + outMinutes;

    // Calculate the difference
    let diffMinutes = outTotalMinutes - inTotalMinutes;

    // Handle overnight shifts
    if (diffMinutes < 0) {
        diffMinutes += 24 * 60;
    }

    // Convert back to hours and minutes
    const hours = Math.floor(diffMinutes / 60);
    const minutes = diffMinutes % 60;

    return allowLunchBreak ? diffMinutes : diffMinutes - 60; // Subtract 60 minutes for lunch break if not allowed
}

if (newModalClockIn && newModalClockOut && includeLunchBreakCreate && newModalDuration) {
    newModalClockIn.addEventListener('change', function() {
        newModalDuration.value = CalculateWorkingHours(newModalClockIn.value, newModalClockOut.value);
    });
    newModalClockOut.addEventListener('change', function() {
        newModalDuration.value = CalculateWorkingHours(newModalClockIn.value, newModalClockOut.value);
    });
    includeLunchBreakCreate.addEventListener('change', function() {
        const includeLunch = includeLunchBreakCreate.checked;
        if (clockOutStatus.value === 'Present') {
            if (includeLunch) newModalDuration.value = parseInt(newModalDuration.value) + 60;
            if (!includeLunch) newModalDuration.value = parseInt(newModalDuration.value) - 60;
        } else {
            newModalDuration.value = CalculateWorkingHours(newModalClockIn.value, newModalClockOut.value);
        }
    });
}

if (clockOutStatus) {
    clockOutStatus.addEventListener('change', function() {
        if (clockOutStatus.value === 'Present') {
            if (newModalDuration.value > 480) {
                newModalDuration.value = 480; // Cap at 8 hours (480 minutes)
            }
        }
        else if (clockOutStatus.value === 'Under-time') {
            if (newModalDuration.value > 480) {
                newModalDuration.value = 479; // Set to 7 hours and 59 minutes (479 minutes) if it's under-time but exceeds 8 hours
            }
        } else {
            newModalDuration.value = CalculateWorkingHours(newModalClockIn.value, newModalClockOut.value);
        }
    });
}

if (newModalAbsentButton) {
    newModalAbsentButton.addEventListener('click', function() {
        newModalClockIn.value = '';
        newModalClockOut.value = '';
        clockInStatus.value = 'Absent';
        clockOutStatus.value = 'Absent';
        newModalDuration.value = '0';
    });   
}

if (newModalOnLeaveButton) {
    newModalOnLeaveButton.addEventListener('click', function() {
        newModalClockIn.value = '';
        newModalClockOut.value = '';
        clockInStatus.value = 'On-leave';
        clockOutStatus.value = 'On-leave';
        newModalDuration.value = '480';
    });   
}
