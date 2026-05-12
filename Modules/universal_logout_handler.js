/**
 * Universal Logout Handler
 * Works from any page/tab by using absolute paths
 * Prevents back button access and clears all session data
 */

// Determine if attendance is currently active
const isAttendanceActive = typeof window.isAttendanceActive !== 'undefined' ? window.isAttendanceActive : false;

/**
 * Handle logout with confirmation if attendance is active
 * @param {Event} event - The click event
 * @returns {boolean} - Whether to proceed with logout
 */
function handleLogout(event) {
    if (event) {
        event.preventDefault();
    }

    if (isAttendanceActive) {
        const shouldLogout = window.confirm('You are currently tapped in. Logging out will automatically tap you out first. Continue?');
        if (!shouldLogout) {
            return false;
        }
    }

    // Clear any client-side storage to prevent back button issues
    localStorage.clear();
    sessionStorage.clear();

    // Use absolute path for logout
    window.location.href = '/Pilot-run/Modules/logout_process.php';
    return false;
}

/**
 * Handle AJAX logout (for use with Fetch API)
 * Useful if you want to logout without full page reload
 * @returns {Promise} - Promise that resolves when logout is complete
 */
function logoutAjax() {
    return fetch('/Pilot-run/Modules/logout_process.php?ajax=1', {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => {
        if (response.ok) {
            // Redirect after successful logout
            window.location.href = '/Pilot-run/?nocache=' + Date.now();
        }
        return response;
    })
    .catch(error => {
        console.error('Logout error:', error);
        // Fallback to direct logout
        window.location.href = '/Pilot-run/Modules/logout_process.php';
    });
}

// Add event listener for logout buttons with data-logout attribute
document.addEventListener('DOMContentLoaded', function() {
    const logoutButtons = document.querySelectorAll('[data-logout]');
    logoutButtons.forEach(button => {
        button.addEventListener('click', handleLogout);
    });
});
