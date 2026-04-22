$(document).ready(function() {
    const attendanceTable = $('#attendanceTable').DataTable({
        order: [[0, 'desc']],
        autoWidth: false,
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: 'pageLength',
            bottomEnd: ['info', 'paging']
        },
        columnDefs: [
            {
                targets: [0, 1, 11],
                visible: false,
                searchable: false
            },
            {
                targets: 3,
                width: '100px'
            }
        ]
    });

    makeColumnsResizable('#attendanceTable');
    searchAttendance('', '', attendanceTable);

    let searchDebounceTimer;

    $('#searchInput').on('keyup', function() {
        const searchTerm = $(this).val();
        const searchDate = $('#searchDate').val();

        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(function() {
            searchAttendance(searchTerm, searchDate, attendanceTable);
        }, 300);
    });

    $('#searchDate').on('change', function() {
        const searchTerm = $('#searchInput').val();
        const searchDate = $(this).val();
        searchAttendance(searchTerm, searchDate, attendanceTable);
    });

    $('#attendanceTable tbody').on('click', 'tr', function () {
        const rowData = attendanceTable.row(this).data();

        if (!rowData) {
            return;
        }

        $('#deleteButton').off('click').on('click', function() {
            configAttendance(rowData[0], $('#searchInput').val(), attendanceTable, 'delete');
        });
        $('#saveButton').off('click').on('click', function() {
            configAttendance(rowData[0], $('#searchInput').val(), attendanceTable, 'update');
        });
        $('#modalName').val(rowData[2]);
        $('#modalDepartment').val(rowData[3]);
        $('#modalDate').val(rowData[4]);
        $('#modalLocation').val(rowData[5]);
        $('#modalClockIn').val(convert12HourTo24Hour(rowData[6]));
        $('#modalClockInStatus').val($('<div>').html(rowData[7]).text());
        $('#modalClockOut').val(convert12HourTo24Hour(rowData[8]));
        $('#modalClockOutStatus').val($('<div>').html(rowData[9]).text());

        bootstrap.Modal.getOrCreateInstance(document.getElementById('attendanceModal')).show();
    });

    $('#attendanceModal').on('hidden.bs.modal', function () {
        if ($('#editButton').text() === 'Cancel') {
            editAttendance();
        } else {
            return;
        };
    });
});

let attendanceEditInitialValues = null;

function makeColumnsResizable(tableSelector) {
    const table = document.querySelector(tableSelector);
    if (!table) {
        return;
    }

    const headers = table.querySelectorAll('thead th');

    headers.forEach((th, columnIndex) => {
        if (th.classList.contains('hide-me')) {
            return;
        }

        if (th.querySelector('.col-resizer')) {
            return;
        }

        const resizer = document.createElement('span');
        resizer.className = 'col-resizer';
        th.appendChild(resizer);

        let startX = 0;
        let startWidth = 0;

        const onMouseMove = (event) => {
            const nextWidth = Math.max(90, startWidth + (event.pageX - startX));
            applyColumnWidth(table, columnIndex, nextWidth);
        };

        const onMouseUp = () => {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        };

        resizer.addEventListener('mousedown', (event) => {
            event.preventDefault();
            startX = event.pageX;
            startWidth = th.offsetWidth;

            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });
    });
}

function applyColumnWidth(table, columnIndex, width) {
    table.querySelectorAll('tr').forEach((row) => {
        const cell = row.children[columnIndex];
        if (!cell) {
            return;
        }

        cell.style.width = width + 'px';
        cell.style.minWidth = width + 'px';
        cell.style.maxWidth = width + 'px';
    });
}

function toggleMenu() {
    document.getElementById("profileMenu").classList.toggle("active");
}

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
                console.log(response);
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
                        row.AO
                    ]).draw();
                });
            }
        },
        error: function() {
            console.error('Search request failed');
        }
    });
}

document.addEventListener("click", function(e) {
    const menu = document.getElementById("profileMenu");
    const avatar = document.querySelector(".avatar");

    if (!avatar.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove("active");
    }
});

function getAttendanceModalValues() {
    return {
        clockIn: document.getElementById('modalClockIn').value,
        clockInStatus: document.getElementById('modalClockInStatus').value,
        clockOut: document.getElementById('modalClockOut').value,
        clockOutStatus: document.getElementById('modalClockOutStatus').value
    };
}

function setAttendanceModalValues(values) {
    if (!values) {
        return;
    }

    document.getElementById('modalClockIn').value = values.clockIn;
    document.getElementById('modalClockInStatus').value = values.clockInStatus;
    document.getElementById('modalClockOut').value = values.clockOut;
    document.getElementById('modalClockOutStatus').value = values.clockOutStatus;
}

function editAttendance() {
    const editbtn = document.getElementById('editButton');
    const deletebtn = document.getElementById('deleteButton');
    const savebtn = document.getElementById('saveButton');
    const clockInInput = document.getElementById('modalClockIn');
    const clockInStatusInput = document.getElementById('modalClockInStatus');
    const clockOutInput = document.getElementById('modalClockOut');
    const clockOutStatusInput = document.getElementById('modalClockOutStatus');
    const absentbtn = document.getElementById('absentButton');
    const onLeavebtn = document.getElementById('onLeaveButton');

    if (editbtn.innerText === 'Edit') {
        attendanceEditInitialValues = getAttendanceModalValues();
        editbtn.innerText = 'Cancel';
        editbtn.classList.remove('btn-warning');
        editbtn.classList.add('btn-primary');
        deletebtn.classList.remove('d-none');
        savebtn.classList.remove('d-none');
        absentbtn.classList.remove('d-none');
        onLeavebtn.classList.remove('d-none');
        clockInInput.readOnly = false;
        clockInStatusInput.readOnly = false;
        clockOutInput.readOnly = false;
        clockOutStatusInput.readOnly = false;
    } else {
        setAttendanceModalValues(attendanceEditInitialValues);
        attendanceEditInitialValues = null;
        editbtn.innerText = 'Edit';
        editbtn.classList.remove('btn-primary');
        editbtn.classList.add('btn-warning');
        deletebtn.classList.add('d-none');
        savebtn.classList.add('d-none');
        absentbtn.classList.add('d-none');
        onLeavebtn.classList.add('d-none');
        clockInInput.readOnly = true;
        clockInStatusInput.readOnly = true;
        clockOutInput.readOnly = true;
        clockOutStatusInput.readOnly = true;
    }

};

function convert12HourTo24Hour(time12h) {
    const [time, modifier] = time12h.split(' ');
    let [hours, minutes, seconds] = time.split(':').map(Number);

    if (modifier === 'PM' && hours !== 12) {
        hours += 12;
    } else if (modifier === 'AM' && hours === 12) {
        hours = 0;
    }

    return hours.toString().padStart(2, '0') + ':' + minutes.toString().padStart(2, '0');
};

function convert24HourTo12Hour(time24h) {
    const [time] = time24h.split(' ');
    let [hours, minutes, seconds] = time.split(':').map(Number);

    const modifier = hours >= 12 ? 'PM' : 'AM';
    if (hours > 12) {
        hours -= 12;
    } else if (hours === 0) {
        hours = 12;
    }

    return hours.toString() + ':' + minutes.toString().padStart(2, '0') + ' ' + modifier;
};

function configAttendance(attendance_id, searchTerm, table, action) {
    const message = action == 'delete' ? 'delete this record?' : 'save this record?';
    if (!confirm('Are you sure you want to ' + message)) {
        return;
    } else {
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
                data.clockIn = convert24HourTo12Hour(document.getElementById('modalClockIn').value);
                data.clockInStatus = document.getElementById('modalClockInStatus').value;
                data.clockOut = convert24HourTo12Hour(document.getElementById('modalClockOut').value);
                data.clockOutStatus = document.getElementById('modalClockOutStatus').value;
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

