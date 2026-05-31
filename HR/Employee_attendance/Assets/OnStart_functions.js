$(document).ready(function() {
    // Initialize the page when the document is ready
    $('#attendanceTable').DataTable({
        dom: 'Bfrtipl',
        buttons: [
        'excel', 'print', 'colvis'
        ],
        columns: [
            { title: 'Attendance ID' },
            { title: 'Name' },
            { title: 'Date' },
            { title: 'Department' },
            { title: 'Location' },
            { title: 'Clock In' },
            { title: 'Clock In Status' },
            { title: 'Clock Out' },
            { title: 'Clock Out Status' },
            { title: 'Duration' },
            { title: 'AO' },
            { title: 'Work Class' }
        ],
        columnDefs: [
            {
                targets: [0, 4, 10],
                visible: false
            }
        ],
        order: [[0, 'desc']]
    });
    searchAttendance('', ''); // Load initial data with empty search

});