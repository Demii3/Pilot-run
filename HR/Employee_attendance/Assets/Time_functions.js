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
    if (!clockIn || !clockOut || clockIn === '--:--' || clockOut === '--:--') {
        return 0;
    };

    const IncludeLunchBreak = document.getElementById('Include-lunchbreak').checked;
    const allowOvertime = document.getElementById('allowOvertime').checked;
    console.log('IncludeLunchBreak:', IncludeLunchBreak);
    console.log('allowOvertime:', allowOvertime);
    /* const clockInMin = ((720 <= convert24HourTimetoMin(clockIn)) && (convert24HourTimetoMin(clockIn) <= 780)) ? 0 : convert24HourTimetoMin(clockIn);
    const clockOutMin = ((720 <= convert24HourTimetoMin(clockOut)) && (convert24HourTimetoMin(clockOut) <= 780)) ? 0 : convert24HourTimetoMin(clockOut); */
    let clockInMin = 0;
    let clockOutMin = 0;
    let result = 0;

    if (allowOvertime) {
        clockOutMin = convert24HourTimetoMin(clockOut);
        clockInMin = convert24HourTimetoMin(clockIn);
        console.log('With overtime:', clockOutMin);
    } else {
        if (convert24HourTimetoMin(clockOut) > 1020) {
            clockOutMin = convert24HourTimetoMin('17:00');
        } else {
            clockOutMin = convert24HourTimetoMin(clockOut);
        };
        clockInMin = convert24HourTimetoMin(clockIn);
        console.log('Without overtime:', clockOutMin)
    };


    if (IncludeLunchBreak) {
        result = clockOutMin - clockInMin;
        console.log('With lunch break result:', result);
    } else {
        const newClockInMin = (720 <= clockInMin && clockInMin <= 780) ? 0 : clockInMin;
        const newClockOutMin = (720 <= clockOutMin && clockOutMin <= 780) ? 0 : clockOutMin;
        result = newClockOutMin - newClockInMin - 60;
        console.log('No lunch break result:', result);
    };

    if (result < 0) {
        result += 24 * 60;
    };

    return result;
};