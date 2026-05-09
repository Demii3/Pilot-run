
const proceedButton = document.getElementById('proceedToAttendance');
const logoutButton = document.getElementById('logout');

if (proceedButton) {
    proceedButton.addEventListener('click', function() {
        const content = document.getElementById('content-area');
        // Remove raw PHP from JS file. If you need server-side values, inject them into the page
        // (e.g., data-user-id attribute or a hidden input) and read them here.
        const payload = { TEST: 'This is a string', USER_ID: '' };

        fetch('./Modules/test.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to load content');
            return response.text();
        })
        .then(html => { content.innerHTML = html; })
        .catch(error => {
            console.error(error);
            if (content) content.innerHTML = '<p>Unable to load content.</p>';
        });
    });
}
