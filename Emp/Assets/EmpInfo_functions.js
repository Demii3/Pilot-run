let saveInfoquery = 0;
let proceed = true;

function editEmpInfo() {
    const email = document.getElementById('email');
    const username = document.getElementById('Username');
    const changePasswordButton = document.getElementById('changePasswordButton');
    const cancelButton = document.getElementById('cancelButton');
    const saveButtonContainer = document.getElementById('saveButtonContainer');

    email.readOnly = false;
    username.readOnly = false;
    changePasswordButton.classList.remove('d-none');
    cancelButton.classList.remove('d-none');
    saveButtonContainer.classList.remove('d-none');

    email.addEventListener('input', () => {
        console.log(email.value.length);
    });

    username.addEventListener('input', () => {
        console.log(username.value.length);
        if (email.value.trim() === '' || username.value.trim() === '') {
            saveButtonContainer.disabled = true;
        } else {
            saveButtonContainer.disabled = false;
        }
    });
};

function changePassword() {
    const passwordContainer = document.getElementById('passwordContainer');
    if (saveInfoquery) {
        passwordContainer.classList.add('d-none');
        saveInfoquery = !saveInfoquery;
        return;
    }

    passwordContainer.classList.remove('d-none');
    saveInfoquery = !saveInfoquery;
};

function saveInfo() {
    const saveButtonContainer = document.getElementById('saveButtonContainer');
    saveButtonContainer.disabled = true; // Disable the save button to prevent multiple clicks

    const email = document.getElementById('email').value;
    const username = document.getElementById('Username').value;
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmNewPassword = document.getElementById('confirmPassword').value;

    const payload = {
        USER_ID: document.getElementById('userId').value, // Assuming USER_ID is available globally
        purpose: saveInfoquery ? 'updateInfoAndPassword' : 'updateInfo',
        email: email,
        username: username,
        currentPassword: currentPassword,
        confirmNewPassword: confirmNewPassword
    };

    fetch('./Modules/UpdateEmpInfo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        console.log(data);
        saveButtonContainer.disabled = false; // Re-enable the save button
    })
};