$(document).ready(function() {
    function target_columns() {
        return $.ajax({
            url: './Modules/HR_settings.php',
            method: 'POST',
            data: {purpose: 'get_settings'},
            dataType: 'json'
        }).then(function(response) {
            let count = 0;
            const columns = [];
            for (const key in response.settings) {
                if (key != 'id') {
                    switch(key) {
                        case 'Manual_mode':
                            if (response.settings[key] == 1) {
                                columns.push(-1);
                            };
                            break;
                        default:
                            if (response.settings[key] == 1) {
                                console.log('Adding column index to hide:', key, 'at value', count);
                                columns.push(count);
                            };
                            count++;
                            break;
                    }
                } else {
                    continue;
                }
            };
            console.log('Columns to hide:', columns);
            return columns;
        }, function(error) {
            console.error('Failed to fetch settings:', error);
            return [];
        });
    };
    
    target_columns().then(function(columnsToHide) {
        const attendanceTable = $('#attendanceTable').DataTable({
            dom: 'Bfrtipl',
            order: [[0, 'desc']],
            autoWidth: false,
            columnDefs: [
                {
                    targets: removeNegativeNumbers(columnsToHide),
                    visible: false,
                    searchable: false
                },
                {
                    targets: [7, 9],
                    width: '200px'
                },
                {
                    targets: 10,
                    width: '80px'
                }
            ],
            buttons: [
                {
                    extend: 'excel',
                    exportOptions: {
                    columns: ':visible'
                    },
                     customize: function(xlsx) {
                        let letter = '';
                        const ws = xlsx.xl.worksheets['sheet1.xml'];
                        const sheetData = ws.querySelector('sheetData');
                        const rows = sheetData.querySelectorAll('row').length;
                        
                        // Get columns from first row
                        const secondRow = sheetData.querySelector('row:nth-child(2)');
                        const columns = secondRow ? secondRow.querySelectorAll('c').length : 0;

                        switch(columns) {
                            case 7:
                                letter = 'G';
                                break;
                            case 8:
                                letter = 'H';
                                break;
                            case 9:
                                letter = 'I';
                                break;
                        };

                        
                        // Add total row with formula
                        const totalRow = `<row r="${rows + 1}">
                            <c r="A${rows + 1}" t="inlineStr"><is><t>TOTAL</t></is></c>
                            <c r="${letter}${rows + 1}" t="f"><f>SUM(${letter}3:${letter}${rows})</f><v>0</v></c>
                        </row>`;
                        
                        ws.querySelector('sheetData').insertAdjacentHTML('beforeend', totalRow);
                    }
                },
                'print',
                'colvis'
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
            $('#Manual-modify').prop('checked', columnsToHide.includes(-1) ? true: false);
            manualMode();

            /* allowOvertimeToggle(rowData[11] == 1); */

            bootstrap.Modal.getOrCreateInstance(document.getElementById('attendanceModal')).show();
        });

        $('#optionsModal').on('show.bs.modal', function () {
            $('#overideAll').prop('checked', columnsToHide.includes(-1) ? true: false);
            $('#hideDepartment').prop('checked', columnsToHide.includes(3) ? true : false);
            $('#hideLocations').prop('checked', columnsToHide.includes(5) ? true : false);
            $('#hideDuration').prop('checked', columnsToHide.includes(10) ? true : false);
        });
   
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
        if ($('#Manual-modify').is(':checked')) {
            return;
        };

        const clockInTime = $(this).val();
        if (convert12HourTimetoMin(clockInTime) <= convert12HourTimetoMin('8:00 AM')) {
            $('#modalClockInStatus').val('On-time');
        }
        else if (convert12HourTimetoMin(clockInTime) > convert12HourTimetoMin('8:00 AM')) {
            $('#modalClockInStatus').val('Late');
        }
    });

    $('#modalClockOut').on('change', function() {
        if ($('#Manual-modify').is(':checked')) {
            return;
        };

        const clockOutTime = $(this).val();
        if (convert12HourTimetoMin(clockOutTime) < convert12HourTimetoMin('5:00 PM')) {
            $('#modalClockOutStatus').val('Under-time');
        }
        else if (convert12HourTimetoMin(clockOutTime) > convert12HourTimetoMin('5:00 PM')) {
            $('#modalClockOutStatus').val('Over-time');
        } else {
            $('#modalClockOutStatus').val('Present');

        }
    });

    $('#modalClockInStatus').on('change', function() {
        const clockInStatus = $(this).val();

        if ($('#Manual-modify').is(':checked')) {
            if (clockInStatus === 'Absent') {
                $('#modalClockIn').val('--:--');
            };
            return;
        };

        if (clockInStatus === 'On-time') {
            $('#modalClockIn').val('08:00');
        } 
        else if (clockInStatus === 'Late') {
            $('#modalClockIn').val('08:01');
        };
    });

    $('#modalClockOutStatus').on('change', function() {
        const clockOutStatus = $(this).val();

        if ($('#Manual-modify').is(':checked')) {
            if (clockOutStatus === 'Absent') {
                $('#modalClockOut').val('--:--');
            };
            return;
        };

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

    $('#searchEmployeeInput').on('input', function() {
        if ($(this).val().length > 3) {
            searchEmployee();
        } else {
            const dropdown = document.getElementById('employeeSuggestionDropdown');
            dropdown.innerHTML = '';
            dropdown.classList.add('d-none');
            this.setAttribute('aria-expanded', 'false');
        };
    });

    $('#searchEmployeeInput').on('focus', function() {
        searchEmployee();
    });

    $('#newModalLocation').on('input', function() {
        if ($(this).val().length > 1) {
            searchEmployeeLocation();
        } else {
            const dropdownLocation = document.getElementById('locationSuggestionDropdown');
            dropdownLocation.innerHTML = '';
            dropdownLocation.classList.add('d-none');
            this.setAttribute('aria-expanded', 'false');
        };
    });

    $('#newModalLocation').on('focus', function() {
        searchEmployeeLocation();
    });

    $('#searchEmployeeInput, #employeeSuggestionDropdown').on('focusout', function(event) {
        const nextTarget = event.relatedTarget;
        const wrapper = document.querySelector('.employee-search-wrapper');

        if (!wrapper) {
            return;
        }

        if (nextTarget && wrapper.contains(nextTarget)) {
            return;
        }

        const dropdown = document.getElementById('employeeSuggestionDropdown');
        if (dropdown) {
            dropdown.classList.add('d-none');
        }

        const input = document.getElementById('searchEmployeeInput');
        if (input) {
            input.setAttribute('aria-expanded', 'false');
        }
    });

    $('#clearCreateAttendanceButton').on('click', function() {
        clearCreateAttendanceModal();
    });

    $('#createAttendanceModal').on('hidden.bs.modal', function() {
        clearCreateAttendanceModal();
    });

    $(document).on('click', '.employee-suggestion-item', function() {
        const employeeData = {
            id: $(this).data('employee-id'),
            name: $(this).data('employee-name'),
            department: $(this).data('employee-department'),
            location: $(this).data('employee-location')
        };
        populateModal(employeeData);
        const dropdown = document.getElementById('employeeSuggestionDropdown');
        const input = document.getElementById('searchEmployeeInput');

        if (dropdown) {
            dropdown.classList.add('d-none');
        }

        if (input) {
            input.setAttribute('aria-expanded', 'false');
        }
    });

    $('#Manual-modify').on('change', function() {
        const manualModify = $(this).is(':checked');
        manualMode(manualModify);
        // Handle manual modify toggle logic here
    });

    updateReadonlyEmptyState('#createAttendanceModal input[readonly]');
});

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

function removeNegativeNumbers(array) {
    if (!Array.isArray(array)) {
        return [];
    }

    return array.filter((value) => value >= 0);
}

function toggleMenu() {
    document.getElementById("profileMenu").classList.toggle("active");
};