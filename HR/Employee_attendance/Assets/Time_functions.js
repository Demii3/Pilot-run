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

function excludeLunchBreak(clockIn, clockOut) {
    console.log('Clock In Minutes:', clockIn, 'Clock Out Minutes:', clockOut);
    console.log('Clock In Minutes:', convert24HourTimetoMin(clockIn), 'Clock Out Minutes:', convert24HourTimetoMin(clockOut));

    const clockInMin = ((720 <= convert24HourTimetoMin(clockIn)) && (convert24HourTimetoMin(clockIn) <= 780)) ? 0 : convert24HourTimetoMin(clockIn);
    const clockOutMin = ((720 <= convert24HourTimetoMin(clockOut)) && (convert24HourTimetoMin(clockOut) <= 780)) ? 0 : convert24HourTimetoMin(clockOut);

    if (clockInMin === 0 && clockOutMin === 0) {
        return 0;
    };
    
    return (clockOutMin - clockInMin) - 60;
};