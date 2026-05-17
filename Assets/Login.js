const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
const notification = document.getElementById('notificationMessage');

function login() {
    let proceeed = true;
    const username = document.getElementById('userName');
    const password = document.getElementById('passWord');
    
    if (username.value.length === 0 || password.value.length === 0) {
        proceeed = false;
        notification.textContent = 'Please enter both username and password.';
        modal.show();
        if (username.value.length === 0) {
            username.classList.add('input-error');
        }
        if (password.value.length === 0) {
            password.classList.add('input-error');
        }
    }

    if (!proceeed) {
        return;
    }

    const payload = {
        username: username.value,
        password: password.value
    };

    fetch('./Modules/login_process.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        notification.textContent = data.message;
        modal.show();
        if(data.success) {
            modal._element.addEventListener('hidden.bs.modal', function() {
                window.location.href = data.empType === 'HR' ? './HR' : './Emp';
            });
        };
    })
}
