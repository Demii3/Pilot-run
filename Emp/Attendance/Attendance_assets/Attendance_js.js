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

// function to determine if tap in is on time or late
function clockinStatus(timeStr) {
    const parts = String(timeStr).split(':');
    return parts[0] <= '08' ? 'On Time' : 'Late';
};
