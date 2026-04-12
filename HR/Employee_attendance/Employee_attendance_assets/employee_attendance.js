const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const dateFilter = document.getElementById('dateFilter');
const attendanceTable = document.getElementById('attendanceTable');
const attendanceModalElement = document.getElementById('attendanceModal');
const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
const selectAllRows = document.getElementById('selectAllRows');
const modalClockIn = document.getElementById('modalClockIn');
const modalClockOut = document.getElementById('modalClockOut');
const modalStatus = document.getElementById('modalStatus');
const modalClockoutStatus = document.getElementById('modalClockoutStatus');
const overtimeDecisionWrapper = document.getElementById('overtimeDecisionWrapper');
const modalOvertimeDecision = document.getElementById('modalOvertimeDecision');
let isNA = false;
let tempval1 = '';
let tempval2 = '';
let tempval3 = '';
let tempval4 = '';

attendanceModalElement.addEventListener('hidden.bs.modal', returnProperties);

function filterAttendance() {
    const searchValue = searchInput.value.toLowerCase();
    const statusValue = statusFilter.value;
    const dateValue = dateFilter.value;

    Array.from(attendanceTable.rows).forEach(row => {
        const cells = row.cells;
        // Table includes hidden Attendance ID and Employee ID columns at indexes 0 and 1.
        const nameText = cells[2] ? cells[2].textContent.toLowerCase() : '';
        const deptText = cells[3] ? cells[3].textContent.toLowerCase() : '';
        const dateText = cells[4] ? cells[4].textContent.trim() : '';
        const statusText = cells[7] ? cells[7].textContent.trim() : '';

        const matchesSearch = searchValue === '' || nameText.includes(searchValue) || deptText.includes(searchValue);
        const matchesDate = dateValue === '' || dateText.includes(dateValue);
        const matchesStatus = statusValue === 'all' || statusText === statusValue;

        row.style.display = (matchesSearch && matchesDate && matchesStatus) ? '' : 'none';
    });
}

searchInput.addEventListener('input', filterAttendance);
statusFilter.addEventListener('change', filterAttendance);
dateFilter.addEventListener('change', filterAttendance);

function updateBulkDeleteState() {
    const selectedRows = document.querySelectorAll('.row-select:checked').length;
    bulkDeleteBtn.disabled = selectedRows === 0;
}

function deleteMultipleContent(attendanceIds) {
    if (!attendanceIds.length) {
        return;
    }

    if (confirm(`Are you sure you want to delete ${attendanceIds.length} selected attendance record(s)? This action cannot be undone.`)) {
        $.post('./Employee_attendance_modules/delete_attendance.php', {Attendance_IDs: attendanceIds}, function(response) {
            if (response === 'success') {
                alert('Selected records deleted successfully.');
                location.reload();
            } else {
                alert('Error deleting selected records: ' + response);
            }
        });
    }
}

function openRowModal(row) {
    const cells = Array.from(row.cells).map(cell => cell.textContent.trim());
    // Populate modal input fields with the selected row values
    document.getElementById('modalEmpId').value = cells[1] || '';
    document.getElementById('modalName').value = cells[2] || '';
    document.getElementById('modalDepartment').value = cells[3] || '';
    document.getElementById('modalDate').value = cells[4] || '';
    document.getElementById('modalLocation').value = cells[5] || '';
    document.getElementById('modalClockIn').value = stringTotime24H(cells[6]) || '';
    document.getElementById('modalStatus').value = cells[7] || '';
    document.getElementById('modalClockOut').value = stringTotime24H(cells[8]) || '';
    setClockoutStatusAndDecision(cells[9] || '');
    const attendanceModal = new bootstrap.Modal(document.getElementById('attendanceModal'));
    attendanceModal.show();
    document.getElementById('editBtn').onclick = () => editContent(row.cells[9].textContent);
    document.getElementById('closeBtn').onclick = () => returnProperties();
    document.getElementById('deleteBtn').onclick = () => deleteContent(row.cells[0].textContent);
    document.getElementById('saveBtn').onclick = () => saveInfo_toDB(row.cells[0].textContent, row.cells[6].textContent, row.cells[8].textContent);
    document.getElementById('NAbtn').onclick = () => setmodalClockinNA();
}

function editContent(hello) {
    document.getElementById('modalClockIn').readOnly = false;
    document.getElementById('modalClockOut').readOnly = false;
    document.getElementById('modalStatus').disabled = false;
    document.getElementById('modalClockoutStatus').disabled = false;
    if (!overtimeDecisionWrapper.classList.contains('d-none')) {
        modalOvertimeDecision.disabled = false;
    }
    document.getElementById('NAbtn').disabled = false;
    document.getElementById('saveBtn').classList.remove('d-none');
    document.getElementById('deleteBtn').classList.remove('d-none');
    tempval1 = document.getElementById('modalClockIn').value;
    tempval2 = document.getElementById('modalStatus').value;
    tempval3 = document.getElementById('modalClockOut').value;
    tempval4 = document.getElementById('modalClockoutStatus').value;
}

function returnProperties() {
    document.getElementById('modalClockIn').readOnly = true;
    document.getElementById('modalClockOut').readOnly = true;
    document.getElementById('modalStatus').disabled = true;
    document.getElementById('modalClockoutStatus').disabled = true;
    modalOvertimeDecision.disabled = true;
    document.getElementById('NAbtn').disabled = true;
    document.getElementById('saveBtn').classList.add('d-none');
    document.getElementById('deleteBtn').classList.add('d-none');
}

function deleteContent(Attendance_ID) {
    if (confirm('Are you sure you want to delete this attendance record?. This action cannot be undone.')) {
        $.post('./Employee_attendance_modules/delete_attendance.php', {Attendance_ID: Attendance_ID}, function(response) {
            if (response === 'success') {
                alert('Record deleted successfully.');
                location.reload();
            } else {
                alert('Error deleting record: ' + response);
            }
        });
    } else {
        return;
    }
}

bulkDeleteBtn.addEventListener('click', () => {
    const selectedIds = Array.from(document.querySelectorAll('.attendance-table tbody tr'))
        .filter(row => {
            const checkbox = row.querySelector('.row-select');
            return checkbox && checkbox.checked;
        })
        .map(row => row.cells[0].textContent.trim());

    deleteMultipleContent(selectedIds);
});

selectAllRows.addEventListener('change', event => {
    document.querySelectorAll('.row-select').forEach(checkbox => {
        checkbox.checked = event.target.checked;
    });
    updateBulkDeleteState();
});

function saveInfo_toDB(Attendance_ID, old_clockIn, old_clockOut) {
    const new_clockIn = document.getElementById('modalClockIn').value;
    const new_clockOut = document.getElementById('modalClockOut').value;
    let formattedClockIn = formatTimeForDatabase(new_clockIn);
    let formattedClockOut = formatTimeForDatabase(new_clockOut);
    let clockInStatusToSave = modalStatus.value;
    let clockOutStatusToSave = modalClockoutStatus.value;

    const lunchAdjusted = applyLunchBreakRules(formattedClockIn, formattedClockOut);
    formattedClockIn = lunchAdjusted.clockIn;
    formattedClockOut = lunchAdjusted.clockOut;
    const duration = lunchAdjusted.duration;

    if (formattedClockIn === 'N/A' && formattedClockOut === 'N/A') {
        clockInStatusToSave = 'Absent';
        clockOutStatusToSave = 'Absent';
    }

    if (clockOutStatusToSave === 'Over-time') {
        clockOutStatusToSave = `Over-time (${modalOvertimeDecision.value})`;
    }

    if (!formattedClockIn || !formattedClockOut) {
        alert('Clock In and Clock Out times are required.');
        return;
    }

    $.post('./Employee_attendance_modules/save_attendance.php',
        {
            Attendance_ID: Attendance_ID,
            newClock_in: formattedClockIn,
            newClock_in_status: clockInStatusToSave,
            newClock_out_status: clockOutStatusToSave,
            newClock_out: formattedClockOut,
            duration: duration
        },
        function(response) {
            if (response === 'success') {
                alert('Record updated successfully.');
                location.reload();
            } else {
                alert('Error updating record: ' + response);
            }
        }
    );
}

function setmodalClockinNA() {
    if (isNA) {
        modalClockIn.value = tempval1;
        modalStatus.value = tempval2;
        isNA = false;
        document.getElementById('NAbtn').textContent = isNA ? 'Prev. val.' : 'N/A';
    } else {
        modalClockIn.value = 'N/A';
        modalStatus.value = 'Absent';
        isNA = true;
        document.getElementById('NAbtn').textContent = isNA ? 'Prev. val.' : 'N/A';
    }
}

function stringTotime24H(timeStr) {
    if (!timeStr || timeStr === 'N/A') {
        return '';
    }

    const normalized = String(timeStr).trim();

    // Already 24-hour format (HH:MM or HH:MM:SS)
    if (/^\d{2}:\d{2}(:\d{2})?$/.test(normalized)) {
        const [h, m, s = '00'] = normalized.split(':');
        return `${h}:${m}:${s}`;
    }

    const parts = normalized.split(' ');
    if (parts.length < 2) {
        return '';
    }

    const [time, rawModifier] = parts;
    const modifier = rawModifier.toUpperCase();
    let [hours, minutes, seconds] = time.split(':').map(Number);

    if (Number.isNaN(hours) || Number.isNaN(minutes) || Number.isNaN(seconds)) {
        return '';
    }

    if (modifier === 'PM' && hours !== 12) {
        hours += 12;
    } else if (modifier === 'AM' && hours === 12) {
        hours = 0;
    }

    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

function toggleOvertimeDecisionField() {
    const showOvertimeDecision = modalClockoutStatus.value === 'Over-time';
    overtimeDecisionWrapper.classList.toggle('d-none', !showOvertimeDecision);
    modalOvertimeDecision.disabled = !showOvertimeDecision || modalClockoutStatus.disabled;
}

function setClockoutStatusAndDecision(rawStatus) {
    const statusText = (rawStatus || '').trim();
    const decisionMatch = statusText.match(/Over-time\s*\((Allowed|Rejected)\)/i);

    if (decisionMatch) {
        modalClockoutStatus.value = 'Over-time';
        modalOvertimeDecision.value = decisionMatch[1].charAt(0).toUpperCase() + decisionMatch[1].slice(1).toLowerCase();
    } else if (statusText === 'Allowed' || statusText === 'Rejected') {
        modalClockoutStatus.value = 'Over-time';
        modalOvertimeDecision.value = statusText;
    } else if (statusText === 'Over-time') {
        modalClockoutStatus.value = 'Over-time';
        modalOvertimeDecision.value = 'Allowed';
    } else if (statusText === 'Under-time' || statusText === 'Absent' || statusText === 'Present') {
        modalClockoutStatus.value = statusText;
        modalOvertimeDecision.value = 'Allowed';
    } else {
        modalClockoutStatus.value = 'Present';
        modalOvertimeDecision.value = 'Allowed';
    }

    toggleOvertimeDecisionField();
}

function formatTimeForDatabase(timeValue) {
    if (!timeValue || timeValue === 'N/A') {
        return '';
    }

    const [hoursPart, minutesPart, secondsPart] = timeValue.split(':');
    const hours = Number(hoursPart);
    const minutes = Number(minutesPart);
    const seconds = Number(secondsPart || '0');

    if (Number.isNaN(hours) || Number.isNaN(minutes) || Number.isNaN(seconds)) {
        return '';
    }

    const suffix = hours >= 12 ? 'PM' : 'AM';
    const displayHours = hours % 12 === 0 ? 12 : hours % 12;

    return `${displayHours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')} ${suffix}`;
}

function calculateDurationInMinutes(clockInValue, clockOutValue) {
    if (!clockInValue || !clockOutValue || clockInValue === 'N/A' || clockOutValue === 'N/A') {
        return 0;
    }

    // Parse time in HH:MM:SS AM/PM format
    const parseTime = (timeStr) => {
        const [time, modifier] = timeStr.split(' ');
        let [hours, minutes, seconds] = time.split(':').map(Number);

        if (modifier === 'PM' && hours !== 12) {
            hours += 12;
        } else if (modifier === 'AM' && hours === 12) {
            hours = 0;
        }

        return { hours, minutes, seconds };
    };

    try {
        const clockIn = parseTime(clockInValue);
        const clockOut = parseTime(clockOutValue);

        if (Number.isNaN(clockIn.hours) || Number.isNaN(clockIn.minutes) || Number.isNaN(clockIn.seconds) ||
            Number.isNaN(clockOut.hours) || Number.isNaN(clockOut.minutes) || Number.isNaN(clockOut.seconds)) {
            return 0;
        }

        const inTotalSeconds = clockIn.hours * 3600 + clockIn.minutes * 60 + clockIn.seconds;
        const outTotalSeconds = clockOut.hours * 3600 + clockOut.minutes * 60 + clockOut.seconds;

        let durationSeconds = outTotalSeconds - inTotalSeconds;

        if (durationSeconds < 0) {
            durationSeconds += 24 * 3600;
        }

        return Math.floor(durationSeconds / 60);
    } catch (e) {
        return 0;
    }
}

function parseAmPmTimeToMinutes(timeValue) {
    if (!timeValue || timeValue === 'N/A') {
        return null;
    }

    const [timePart, modifier] = timeValue.trim().split(' ');
    if (!timePart || !modifier) {
        return null;
    }

    const [hoursPart, minutesPart] = timePart.split(':');
    let hours = Number(hoursPart);
    const minutes = Number(minutesPart);

    if (Number.isNaN(hours) || Number.isNaN(minutes)) {
        return null;
    }

    if (modifier === 'PM' && hours !== 12) {
        hours += 12;
    } else if (modifier === 'AM' && hours === 12) {
        hours = 0;
    }

    return (hours * 60) + minutes;
}

function isWithinLunchBreak(minutesValue) {
    const lunchStart = (11 * 60) + 59; // 11:59 AM
    const lunchEnd = (12 * 60) + 59;   // 12:59 PM
    return minutesValue >= lunchStart && minutesValue <= lunchEnd;
}

function applyLunchBreakRules(clockInValue, clockOutValue) {
    let adjustedClockIn = clockInValue;
    let adjustedClockOut = clockOutValue;

    const clockInMinutes = parseAmPmTimeToMinutes(clockInValue);
    const clockOutMinutes = parseAmPmTimeToMinutes(clockOutValue);

    if (clockInMinutes === null || clockOutMinutes === null) {
        return {
            clockIn: adjustedClockIn,
            clockOut: adjustedClockOut,
            duration: calculateDurationInMinutes(adjustedClockIn, adjustedClockOut)
        };
    }

    const lunchStart = (11 * 60) + 59;
    const lunchEnd = (12 * 60) + 59;
    const clockInBelowLunch = clockInMinutes < lunchStart;
    const clockOutExceedsLunch = clockOutMinutes > lunchEnd;
    const clockInWithinLunch = isWithinLunchBreak(clockInMinutes);
    const clockOutWithinLunch = isWithinLunchBreak(clockOutMinutes);

    if (clockInWithinLunch && clockOutWithinLunch) {
        adjustedClockIn = 'N/A';
        adjustedClockOut = 'N/A';
        return {
            clockIn: adjustedClockIn,
            clockOut: adjustedClockOut,
            duration: 0
        };
    }

    if (clockInBelowLunch && clockOutWithinLunch) {
        adjustedClockOut = '11:59:00 AM';
    }

    if (clockInWithinLunch && clockOutExceedsLunch) {
        adjustedClockIn = '01:00:00 PM';
    }

    let duration = calculateDurationInMinutes(adjustedClockIn, adjustedClockOut);

    if (clockInBelowLunch && clockOutExceedsLunch) {
        duration = Math.max(0, duration - 60);
    }

    return {
        clockIn: adjustedClockIn,
        clockOut: adjustedClockOut,
        duration: duration
    };
}

modalClockIn.addEventListener('change', () => {
    const clockInValue = modalClockIn.value;

    if (clockInValue <= '08:00') {
        modalStatus.value = 'On-time';
    } else {
        modalStatus.value = 'Late';
    }
});

modalClockOut.addEventListener('change', () => {
    const clockOutValue = modalClockOut.value;

    if (clockOutValue > '17:00') {
        modalClockoutStatus.value = 'Over-time';
    } else if (clockOutValue === 'N/A') {
        modalClockoutStatus.value = 'Absent';
    } else if (clockOutValue < '17:00') {
        modalClockoutStatus.value = 'Under-time';
    } else {
        modalClockoutStatus.value = 'Present';
    }

    toggleOvertimeDecisionField();
});

modalStatus.addEventListener('change', () => {
    const modalStatusvalue = modalStatus.value;

    switch (modalStatusvalue) {
        case 'On-time':
            if (tempval2 == 'On-time') {
                return;
            } else {
                modalClockIn.value = '08:00:00';
                alert(modalClockIn.value);
            }
            break;
        case 'Late':
            if (modalClockIn.value === 'N/A' || modalClockIn.value <= '08:00:00') {
                modalClockIn.value = '08:01:00';
            }
            break;
        case 'Absent':
            modalClockIn.value = 'N/A';
            break;
    }
});

modalClockoutStatus.addEventListener('change', () => {
    const modalClockoutStatusvalue = modalClockoutStatus.value;
    toggleOvertimeDecisionField();

    switch (modalClockoutStatusvalue) {
        case 'Present':
            if (tempval4 == 'Present') {
                return;
            } else {
                modalClockOut.value = '17:00:00';
                alert(modalClockOut.value);
            }
            break;
        case 'Absent':
            modalClockOut.value = 'N/A';
            break;
    }
});

document.querySelectorAll('.attendance-table tbody tr[role="button"]').forEach(row => {
    row.addEventListener('click', () => openRowModal(row));

    const checkbox = row.querySelector('.row-select');
    if (checkbox) {
        checkbox.addEventListener('click', event => {
            event.stopPropagation();
            updateBulkDeleteState();
        });

        checkbox.addEventListener('change', event => {
            event.stopPropagation();
            updateBulkDeleteState();
        });

        checkbox.addEventListener('keydown', event => {
            event.stopPropagation();
        });
    }

    // Keyboard activation for Enter and Space
    row.addEventListener('keydown', event => {
        if (event.target && event.target.classList.contains('row-select')) {
            return;
        }
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openRowModal(row);
        }
    });
});
