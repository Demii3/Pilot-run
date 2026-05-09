function saveInfotoDatabase () {
    const userId = document.getElementById('userId').value;
    const date = new Date().toISOString().split('T')[0]; // Get current date in YYYY-MM-DD format
    const location = document.getElementById('locationSelect').value;
    const coordinates = document.getElementById('locationSelect').options[document.getElementById('locationSelect').selectedIndex].getAttribute('data-coordinates');
    const timeIn = document.getElementById('time').textContent;
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
}

function checkTimeInStatus(timeStr) {
    let [h1, m1] = timeStr[0].split(':').map(Number);
    if(h1 === 12 && timeStr[1] === 'AM') { 
        h1 = 0; // Convert 12 AM to 0 hours for easier comparison{
    };
    const timeinMinutes = timeStr[1] == 'PM' ? (h1 * 60 + m1) + 720 : h1 * 60 + m1; // Adjust for PM if needed
    return timeinMinutes <= 480 ? 'On Time' : 'Late'; // 480 minutes = 8 hours
};

function checkClockoutStatus(timeStr) {
    let [h1, m1] = timeStr[0].split(':').map(Number);
    if(h1 === 12 && timeStr[1] === 'AM') { 
        h1 = 0; // Convert 12 AM to 0 hours for easier comparison{
    };
    const timeinMinutes = timeStr[1] == 'PM' ? (h1 * 60 + m1) + 720 : h1 * 60 + m1; // Adjust for PM if needed
    return timeinMinutes <= 18 * 60 ? 'Under-time' : 'Over-time'; // 18 * 60 minutes = 18 hours
};