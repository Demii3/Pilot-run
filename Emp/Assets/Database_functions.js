function saveInfotoDatabase () {
    $.post('save_info.php', {
        userId: document.getElementById('userId').value,
        username: document.getElementById('username').value,
        location: document.getElementById('locationSelect').value,
        empLocationLat: document.getElementById('empLocationLat').value,
        empLocationLng: document.getElementById('empLocationLng').value
    }, function(response) {
        console.log('Server response:', response);
    });
}

function checkClockinStatus(timeStr) {
    let [h1, m1, s1] = timeStr[0].split(':').map(Number);
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