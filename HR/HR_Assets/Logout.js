const modal = document.getElementById("notificationModal");
const notification = document.getElementById("notificationMessage");

function logout() {
    // Clear session data
    fetch('../Modules/logout_process.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              notification.textContent = 'Logout successful. Redirecting to login page...';
              const bsModal = new bootstrap.Modal(modal);
              bsModal.show();
              modal.addEventListener('hidden.bs.modal', function() {
                  window.location.href = '../';
              });
          }
          else {
              notification.textContent = 'Logout failed. Please try again.';
              const bsModal = new bootstrap.Modal(modal);
              bsModal.show();
          }
      });
}