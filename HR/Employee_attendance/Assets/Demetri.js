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
                targets: [0, 1],
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
        $('#allowOvertime').prop('checked', rowData[11] == 1);
        allowOvertimeToggle(rowData[11] == 1);

        bootstrap.Modal.getOrCreateInstance(document.getElementById('attendanceModal')).show();
    });

    $('#attendanceModal').on('hidden.bs.modal', function () {
        if ($('#moreButton').text() === 'Less') {
            moreAttendance();
        }
        
        if ($('#editButton').text() === 'Cancel') {
            editAttendance();
        }
    });

    $('#modalClockIn').on('change', function() {
        const clockInTime = $(this).val();
        if (convertTimetoMin(clockInTime) <= convertTimetoMin('8:00 AM')) {
            $('#modalClockInStatus').val('On-time');
        }
        else if (convertTimetoMin(clockInTime) > convertTimetoMin('8:00 AM')) {
            $('#modalClockInStatus').val('Late');
        }
    });

    $('#modalClockOut').on('change', function() {
        const clockOutTime = $(this).val();
        if (convertTimetoMin(clockOutTime) < convertTimetoMin('5:00 PM')) {
            $('#modalClockOutStatus').val('Under-time');
        }
        else if (convertTimetoMin(clockOutTime) > convertTimetoMin('5:00 PM')) {
            $('#modalClockOutStatus').val('Over-time');
        } else {
            $('#modalClockOutStatus').val('Present');
        }
    });

    $('#modalClockInStatus').on('change', function() {
        const clockInStatus = $(this).val();
        if (clockInStatus === 'On-time') {
            $('#modalClockIn').val('08:00');
        } 
        else if (clockInStatus === 'Late') {
            $('#modalClockIn').val('08:01');
        };
    });

    $('#modalClockOutStatus').on('change', function() {
        const clockOutStatus = $(this).val();
        if (clockOutStatus === 'Present') {
            $('#modalClockOut').val('17:00');
        } 
        else if (clockOutStatus === 'Under-time') {
            $('#modalClockOut').val('16:59');
        };
    });

    $('#allowOvertime').on('change', function() {
        const allowOvertime = $(this).is(':checked');
        allowOvertimeToggle(allowOvertime);
    });
});

let attendanceEditInitialValues = null;
let allowOvertimeInitialValue = 0;

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
        clockOutStatus: document.getElementById('modalClockOutStatus').value,
        allowOvertime: document.getElementById('allowOvertime').checked
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
    document.getElementById('allowOvertime').checked = values.allowOvertime;
}

function editAttendance() {
    const editbtn = document.getElementById('editButton');
    const deletebtn = document.getElementById('deleteButton');
    const savebtn = document.getElementById('saveButton');
    const morebtn = document.getElementById('moreButton');
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
        clockInInput.readOnly = true;
        clockInStatusInput.disabled = true;
        clockOutInput.readOnly = true;
        clockOutStatusInput.disabled = true;
        if (morebtn.textContent == 'Less') {
            moreAttendance();
        }
    }

};

function moreAttendance() {
    const morebtn = document.getElementById('moreButton');
    const modalBody2 = document.getElementById('modalBody2');
    if (morebtn.innerText === 'More') {
        morebtn.innerText = 'Less';
        modalBody2.classList.remove('d-none');
    } else {
        morebtn.innerText = 'More';
        modalBody2.classList.add('d-none');
    }
};

function convert12HourTo24Hour(time12h) {
    if (!time12h || time12h === '--:--') {
        return time12h;
    };

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
    if (!time24h || time24h === '--:-- --') {
        return '--:-- --';
    };

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

function convertTimetoMin(time) {
    if (!time) {
        return time;
    };

    const [timePart, modifier] = time.split(' ');
    let [hours, minutes] = timePart.split(':').map(Number);

    if (modifier === 'PM' && hours !== 12) {
        hours += 12;
    } else if (modifier === 'AM' && hours === 12) {
        hours = 0;
    }

    return hours * 60 + minutes;
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
                data.duration = convertTimetoMin(document.getElementById('modalClockOut').value) - convertTimetoMin(document.getElementById('modalClockIn').value);
                data.allowOvertime = document.getElementById('allowOvertime').checked ? 1 : 0;
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

function allowOvertimeToggle(value) {
    if(value) {
        document.getElementById('modalClockOutStatus').querySelector('option[value="Over-time"]').disabled = false;
    } else {
        document.getElementById('modalClockOutStatus').querySelector('option[value="Over-time"]').disabled = true;
    }
};

