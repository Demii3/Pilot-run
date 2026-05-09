const proceedButton = document.getElementById('proceedToAttendance');
proceedButton.addEventListener('click', function() {
    const content = document.getElementById('content-area');
    const payload = {'TEST': 'This is a string'}; // Add any necessary data to the payload
    fetch('./test.php',
        {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: JSON.stringify(payload)
        }
    )
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load content');
                }
                return response.text();
            })
            .then(html => {
                content.innerHTML = html;
            })
            .catch(error => {
                console.error(error);
                content.innerHTML = '<p>Unable to load content.</p>';
            });
});