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

    const defaultShiftStart = 8 * 60;  // 8:00 AM
    const defaultShiftEnd = 17 * 60;   // 5:00 PM

    const manualModeToggle = document.getElementById('Manual-modify');
    const allowOvertimeToggle = document.getElementById('allowOvertime') || document.getElementById('newAllowOvertime');
    const includeLunchBreakToggle = document.getElementById('Include-lunchbreak');
    const shiftStartInput = document.getElementById('setTimeIn');
    const shiftEndInput = document.getElementById('setTimeOut');

    const isManualMode = !!(manualModeToggle && manualModeToggle.checked);
    const allowOvertime = !!(allowOvertimeToggle && allowOvertimeToggle.checked);
    const includeLunchBreak = !!(includeLunchBreakToggle && includeLunchBreakToggle.checked);

    const shiftStartMin = (shiftStartInput && shiftStartInput.value)
        ? convert24HourTimetoMin(shiftStartInput.value)
        : defaultShiftStart;
    const shiftEndMin = (shiftEndInput && shiftEndInput.value)
        ? convert24HourTimetoMin(shiftEndInput.value)
        : defaultShiftEnd;
    const isNightShift = shiftEndMin <= shiftStartMin;

    let startMin = convert24HourTimetoMin(startTime);
    let endMin = convert24HourTimetoMin(endTime);

    function normalizeToShiftTimeline(minuteValue) {
        if (isNightShift && minuteValue < shiftStartMin) {
            return minuteValue + 24 * 60;
        }
        return minuteValue;
    }

    let startNorm = normalizeToShiftTimeline(startMin);
    let endNorm = normalizeToShiftTimeline(endMin);
    const shiftStartNorm = shiftStartMin;
    const shiftEndNorm = isNightShift ? shiftEndMin + 24 * 60 : shiftEndMin;

    if (endNorm < startNorm) {
        endNorm += 24 * 60;
    }

    // Enforce regular shift start unless manual mode is enabled.
    if (!isManualMode && startNorm < shiftStartNorm) {
        startNorm = shiftStartNorm;
    }

    // Cap to regular shift end unless overtime is explicitly allowed.
    if (!allowOvertime && endNorm > shiftEndNorm) {
        endNorm = shiftEndNorm;
    }

    let durationMin = endNorm - startNorm;

    if (durationMin < 0) {
        durationMin += 24 * 60; // Adjust for overnight shifts
    }

    const NOON_MIN = 12 * 60;
    const ONE_PM_MIN = 13 * 60;
    const spansLunchWindow = startMin < NOON_MIN && endMin > ONE_PM_MIN;

    if (!includeLunchBreak && spansLunchWindow && durationMin > 0) {
        durationMin -= 60;
    }

    return durationMin;
}