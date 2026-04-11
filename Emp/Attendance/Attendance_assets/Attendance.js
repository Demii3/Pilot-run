function isPointInPolygon(point, polygon) {
    let x = point[0], y = point[1];
    let inside = false;
    for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
        let xi = polygon[i][0], yi = polygon[i][1];
        let xj = polygon[j][0], yj = polygon[j][1];
        if ((yi > y) !== (yj > y) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi)) {
            inside = !inside;
        }
    }
    return inside;
};


// function to convert time to 24-hour format (can be used for comparing and operations)
function to24HourTime(dateObj) {
    return dateObj.toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });
};

function subtractTime(timeStr1, timeStr2) {
    const [h1, m1, s1] = timeStr1[0].split(':').map(Number);
    const [h2, m2, s2] = timeStr2[0].split(':').map(Number);

    const minutes1 = timeStr1[1] == 'PM' ? (h1 * 60 + m1) + 720 : h1 * 60 + m1; // Adjust for PM if needed
    const minutes2 = timeStr2[1] == 'PM' ? (h2 * 60 + m2) + 720 : h2 * 60 + m2; // Adjust for PM if needed;

    return minutes1 - minutes2;
};

function formatDateToYYYYMMDD(dateStr) {
    const date = new Date(dateStr);

    if (isNaN(date.getTime())) {
        return '';
    }

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;   
};

function checkClockinStatus(timeStr) {
    let [h1, m1, s1] = timeStr[0].split(':').map(Number);
    if(h1 === 12 && timeStr[1] === 'AM') { 
        h1 = 0; // Convert 12 AM to 0 hours for easier comparison{
    };
    const timeinMinutes = timeStr[1] == 'PM' ? (h1 * 60 + m1) + 720 : h1 * 60 + m1; // Adjust for PM if needed
    return timeinMinutes <= 480 ? 'On Time' : 'Late'; // 480 minutes = 8 hours
};


