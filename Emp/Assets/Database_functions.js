function showHelloModal() {
    const modalElement = document.getElementById('helloModal');
    if (!modalElement) {
        return;
    }

    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const existingModal = bootstrap.Modal.getInstance(modalElement);
        if (existingModal) {
            existingModal.dispose();
        }

        new bootstrap.Modal(modalElement, {
            backdrop: false,
            focus: false,
            keyboard: true
        }).show();
        return;
    }

    if (typeof $ !== 'undefined' && typeof $(modalElement).modal === 'function') {
        $(modalElement).modal('show');
    }
}

function showGoodbyeModal() {
    const modalElement = document.getElementById('goodbyeModal');
    if (!modalElement) {
        return;
    }

    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const existingModal = bootstrap.Modal.getInstance(modalElement);
        if (existingModal) {
            existingModal.dispose();
        }

        new bootstrap.Modal(modalElement, {
            backdrop: false,
            focus: false,
            keyboard: true
        }).show();
        return;
    }

    if (typeof $ !== 'undefined' && typeof $(modalElement).modal === 'function') {
        $(modalElement).modal('show');
    }
}

function saveTimeIn () {
    const userId = document.getElementById('userId').value;
    const date = new Date().toISOString().split('T')[0]; // Get current date in YYYY-MM-DD format
    const location = document.getElementById('locationSelect').value;
    const coordinates = document.getElementById('locationSelect').options[document.getElementById('locationSelect').selectedIndex].getAttribute('data-coordinates');
    const timeIn = document.getElementById('time').textContent;

    showHelloModal();

    $.post('./Modules/save_clockin.php', {
        userId: userId,
        date: date,
        location: location,
        coordinates: coordinates,
        timeIn: timeIn,
        timeInStatus: checkTimeInStatus([timeIn.split(' ')[0], timeIn.split(' ')[1]])
    }, function(response) {
        console.log('Server response:', response);
    });
    document.getElementById('tapIn').innerText = 'Tap Out';
    document.getElementById('tapIn').classList.remove('btn-success');
    document.getElementById('tapIn').classList.add('btn-danger');
    document.getElementById('locationSelect').disabled = true; // Disable location select after tapping in
    document.getElementById('tapIn').removeEventListener('click', TapIn);
    document.getElementById('tapIn').addEventListener('click', TapOut);
    // Store attendance ID for later use
}

function saveTimeOut () {
    const userId = document.getElementById('userId').value;
    const attendanceId = document.getElementById('attendanceId').value;
    const timeOut = document.getElementById('time').textContent;

    showGoodbyeModal();

    $.post('./Modules/save_clockout.php', {
        userId: userId,
        attendanceId: attendanceId,
        timeOut: timeOut,
        timeOutStatus: checkTimeOutStatus([timeOut.split(' ')[0], timeOut.split(' ')[1]]),
        workClassification: 'R'
    }, function(response) {
        console.log('Server response:', response);
    });
    document.getElementById('tapIn').innerText = 'Tap In';
    document.getElementById('tapIn').classList.remove('btn-danger');
    document.getElementById('tapIn').classList.add('btn-success');
    document.getElementById('locationSelect').disabled = false; // Re-enable location select after tapping out
    document.getElementById('tapIn').removeEventListener('click', TapOut);
    document.getElementById('tapIn').addEventListener('click', TapIn);
}

function checkTimeInStatus(timeStr) {
    let [h1, m1] = timeStr[0].split(':').map(Number);
    if(h1 === 12 && timeStr[1] === 'AM') { 
        h1 = 0; // Convert 12 AM to 0 hours for easier comparison{
    };
    const timeinMinutes = timeStr[1] == 'PM' ? (h1 * 60 + m1) + 720 : h1 * 60 + m1; // Adjust for PM if needed
    return timeinMinutes <= 480 ? 'On-time' : 'Late'; // 480 minutes = 8 hours
};

function checkTimeOutStatus(timeStr) {
    let [h1, m1] = timeStr[0].split(':').map(Number);
    if(h1 === 12 && timeStr[1] === 'AM') { 
        h1 = 0; // Convert 12 AM to 0 hours for easier comparison{
    };
    const timeoutMinutes = timeStr[1] == 'PM' ? (h1 * 60 + m1) + 720 : h1 * 60 + m1; // Adjust for PM if needed
    return timeoutMinutes <= 18 * 60 ? 'Under-time' : 'Over-time'; // 18 * 60 minutes = 18 hours
};