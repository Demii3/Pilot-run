let saveInfoquery = 0;
let proceed = true;

function showNotification(message) {
    const modalElement = document.getElementById('notification');
    const modalBody = document.querySelector('#notification .modal-body');

    if (modalBody) {
        modalBody.textContent = message;
    }

    // Create a non-blocking modal (no backdrop) so the page remains interactive
    if (modalElement && window.bootstrap && bootstrap.Modal) {
        // Ensure any existing backdrop is not used by creating a new instance with backdrop: false
        const modal = new bootstrap.Modal(modalElement, { backdrop: false, keyboard: true });
        modal.show();

        // Attach a one-time handler so when the modal is closed it reloads the employee info
        if (!modalElement.dataset.onHiddenAttached) {
            modalElement.addEventListener('hidden.bs.modal', () => {
                if (typeof loadEmpInfoContent === 'function') {
                    try { loadEmpInfoContent(); } catch (e) { console.error(e); }
                }
            });
            modalElement.dataset.onHiddenAttached = 'true';
        }
    }
}

function updateSaveButtonState(email, username, saveButtonContainer) {
    if (email.value.trim() === '' || username.value.trim() === '') {
        saveButtonContainer.disabled = true;
    } else {
        saveButtonContainer.disabled = false;
    }
}

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

    if (!email.dataset.listenerAttached) {
        email.addEventListener('input', () => {
            if (email.value.trim() === '') {
                email.classList.add('input-error');
                proceed = false;
            } else {
                email.classList.remove('input-error');
                proceed = true;
            }
            updateSaveButtonState(email, username, saveButtonContainer);
        });
        email.dataset.listenerAttached = 'true';
    }

    if (!username.dataset.listenerAttached) {
        username.addEventListener('input', () => {
            if (username.value.trim() === '') {
                username.classList.add('input-error');
                proceed = false;
            } else {
                username.classList.remove('input-error');
                proceed = true;
            }
            updateSaveButtonState(email, username, saveButtonContainer);
        });
        username.dataset.listenerAttached = 'true';
    }
};

function checkPasswordMatch(newPassword, confirmPassword, passwordError) {
    if (newPassword.value !== confirmPassword.value && confirmPassword.value !== '') {
        passwordError.classList.remove('d-none');
    } else {
        passwordError.classList.add('d-none');
    }
}

function changePassword() {
    const passwordContainer = document.getElementById('passwordContainer');
    if (saveInfoquery) {
        passwordContainer.classList.add('d-none');
        saveInfoquery = !saveInfoquery;
        return;
    }

    passwordContainer.classList.remove('d-none');
    saveInfoquery = !saveInfoquery;

    const currentPassword = document.getElementById('currentPassword');
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const passwordError = document.getElementById('passwordError');

    if (!currentPassword.dataset.listenerAttached) {
        currentPassword.addEventListener('input', () => {
            if (currentPassword.value.trim() === '') {
                currentPassword.classList.add('input-error');
            } else {
                currentPassword.classList.remove('input-error');
            }
        });
        currentPassword.dataset.listenerAttached = 'true';
    }

    if (!newPassword.dataset.listenerAttached) {
        newPassword.addEventListener('input', () => {
            if (newPassword.value.trim() === '') {
                newPassword.classList.add('input-error');
            } else {
                newPassword.classList.remove('input-error');
            }
            checkPasswordMatch(newPassword, confirmPassword, passwordError);
        });
        newPassword.dataset.listenerAttached = 'true';
    }

    if (!confirmPassword.dataset.listenerAttached) {
        confirmPassword.addEventListener('input', () => {
            if (confirmPassword.value.trim() === '') {
                confirmPassword.classList.add('input-error');
            } else {
                confirmPassword.classList.remove('input-error');
            }
            checkPasswordMatch(newPassword, confirmPassword, passwordError);
        });
        confirmPassword.dataset.listenerAttached = 'true';
    }
};

function saveInfo() {
    const saveButtonContainer = document.getElementById('saveButtonContainer');
    saveButtonContainer.disabled = true; // Disable the save button to prevent multiple clicks

    const email = document.getElementById('email');
    const username = document.getElementById('Username');
    const currentPassword = document.getElementById('currentPassword');
    const newPassword = document.getElementById('newPassword');
    const confirmNewPassword = document.getElementById('confirmPassword');

    if (currentPassword.value.trim().length == 0 && saveInfoquery) {
        Proceed = false;
        currentPassword.classList.add('input-error');
    };

    if(newPassword.value.trim().length == 0 && saveInfoquery) {
        proceed = false;
        newPassword.classList.add('input-error');
    };

    if(confirmNewPassword.value.trim().length == 0 && saveInfoquery) {
        proceed = false;
        confirmNewPassword.classList.add('input-error');
    };


    const payload = {
        USER_ID: document.getElementById('userId').value, // Assuming USER_ID is available globally
        purpose: saveInfoquery ? 'updateInfoAndPassword' : 'updateInfo',
        email: email.value,
        username: username.value,
        currentPassword: currentPassword.value,
        confirmNewPassword: confirmNewPassword.value
    };

    if (proceed) {
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
            showNotification(data.testingvar || 'Saved successfully');
            
        })
    } else {
        console.log('Please fill in all required fields correctly.');
        showNotification('Please fill in all required fields correctly.');
        saveButtonContainer.disabled = false;
    }

    console.log(proceed);
    proceed = true;
    console.log(proceed);
};
