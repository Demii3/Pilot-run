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
let isNA = false;
let tempval1 = '';
let tempval2 = '';

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
    const attendanceModal = new bootstrap.Modal(document.getElementById('attendanceModal'));
    attendanceModal.show();
    document.getElementById('editBtn').onclick = () => editContent();
    document.getElementById('closeBtn').onclick = () => returnProperties();
    document.getElementById('deleteBtn').onclick = () => deleteContent(row.cells[0].textContent);
    document.getElementById('saveBtn').onclick = () => saveInfo_toDB(row.cells[0].textContent, row.cells[6].textContent, row.cells[8].textContent);
    document.getElementById('NAbtn').onclick = () => setmodalClockinNA();
}

function editContent() {
    document.getElementById('modalClockIn').readOnly = false;
    document.getElementById('modalClockOut').readOnly = false;
    document.getElementById('modalStatus').disabled = false;
    document.getElementById('NAbtn').disabled = false;
    document.getElementById('saveBtn').classList.remove('d-none');
    document.getElementById('deleteBtn').classList.remove('d-none');
    tempval1 = document.getElementById('modalClockIn').value;
    tempval2 = document.getElementById('modalStatus').value;
}

function returnProperties() {
    document.getElementById('modalClockIn').readOnly = true;
    document.getElementById('modalClockOut').readOnly = true;
    document.getElementById('modalStatus').disabled = true;
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

    $.post('./Employee_attendance_modules/save_attendance.php',
        {
            Attendance_ID: Attendance_ID,
            newClock_in: new_clockIn,
            newClock_out: new_clockOut,
            old_clockIn: old_clockIn,
            old_clockOut: old_clockOut
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
    const [time, modifier] = timeStr.split(' ');
    let [hours, minutes, seconds] = time.split(':').map(Number);

    if (modifier === 'PM' && hours !== 12) {
        hours += 12;
    } else if (modifier === 'AM' && hours === 12) {
        hours = 0;
    }

    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

modalClockIn.addEventListener('change', () => {
    const clockInValue = modalClockIn.value;

    if (clockInValue <= '08:00') {
        modalStatus.value = 'On-time';
    } else {
        modalStatus.value = 'Late';
    }
});

modalStatus.addEventListener('change', () => {
    const modalStatusvalue = modalStatus.value;

    switch (modalStatusvalue) {
        case 'On-time':
            if (tempval2 == 'On-time') {
                return;
            } else {
                modalClockIn.value = '08:00';
            }
            break;
        case 'Late':
            if (modalClockIn.value === 'N/A' || modalClockIn.value <= '08:00') {
                modalClockIn.value = '08:01';
            }
            break;
        case 'Absent':
            modalClockIn.value = 'N/A';
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
