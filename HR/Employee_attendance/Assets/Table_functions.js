$(document).ready(function() {
    let searchDebounceTimer;
    $('#searchInput').on('keyup', function() {
        const searchTerm = $(this).val();
        const searchDate = $('#searchDate').val();

        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(function() {
            searchAttendance(searchTerm, searchDate);
        }, 300);
    });

    $('#searchDate').on('change', function() {
        const searchTerm = $('#searchInput').val();
        const searchDate = $(this).val();
        searchAttendance(searchTerm, searchDate);
    });

    $('#attendanceTable tbody').on('click', 'tr', function () {
        /* fetch('./Modules/get_attendance_details.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'attendanceId=' + rowData[0]
        }) */
        const rowData = $('#attendanceTable').DataTable().row(this).data();

        if (!rowData) {
            return;
        }

        $('#deleteButton').off('click').on('click', function() {
            configAttendance(rowData[0], $('#searchInput').val(), attendanceTable, 'delete');
        });
        $('#saveButton').off('click').on('click', function() {
            configAttendance(rowData[0], $('#searchInput').val(), attendanceTable, 'update');
        });
        
        $('#modalName').val(rowData[1]);
        $('#modalDepartment').val(rowData[2]);
        $('#modalDate').val(rowData[3]);
        $('#modalLocation').val(rowData[4]);
        $('#modalClockIn').val((rowData[5]));
        $('#modalClockInStatus').val($('<div>').html(rowData[6]).text());
        $('#modalClockOut').val((rowData[7]));
        $('#modalClockOutStatus').val($('<div>').html(rowData[8]).text());
        $('#allowOvertime').prop('checked', rowData[9] == 1);
        $('#modalDuration').val(rowData[10]);
        $('#modalUserLoc').val(rowData[11]);
        $('#modalCoordinates').val(rowData[12]);
        $('#Manual-modify').prop('checked', false);

            /* allowOvertimeToggle(rowData[11] == 1); */

        bootstrap.Modal.getOrCreateInstance(document.getElementById('attendanceModal')).show();
    });

});
