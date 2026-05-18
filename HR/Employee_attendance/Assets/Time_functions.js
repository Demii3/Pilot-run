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

function convert12HourTimetoMin(time) {
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

function convert24HourTimetoMin(time) {
    const [hours, minutes] = time.split(':').map(Number);
    return hours * 60 + minutes;
};

/* function adjustTime(time){
    return time + 24*60;
}  */

function durationCalculation(startTime, endTime) {
    if (!startTime || !endTime || startTime === '--:-- --' || endTime === '--:-- --') {
        return '--:--';
    }

    const startMin = convert24HourTimetoMin(startTime);
    const endMin = convert24HourTimetoMin(endTime);
    const durationMin = endMin - startMin;

    if (durationMin < 0) {
        durationMin += 24 * 60; // Adjust for overnight shifts
    }
}