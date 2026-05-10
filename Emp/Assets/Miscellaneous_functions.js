// Sidebar toggle for mobile
const sidebarToggle = document.getElementById("sidebarToggle");
const sidebar = document.querySelector(".sidebar");
const contentArea = document.getElementById("content-area");

function toggleMenu() {
	document.getElementById("profileMenu").classList.toggle("active");
}

	// Open menu when avatar is clicked
document.querySelector(".avatar").addEventListener("click", function(e) {
	e.stopPropagation();
	toggleMenu();
});

// Close menu when clicking outside
document.addEventListener("click", function(e) {
	const menu = document.getElementById("profileMenu");
	const avatar = document.querySelector(".avatar");

	if (!avatar.contains(e.target) && !menu.contains(e.target)) {
		menu.classList.remove("active");
	}
});


if (sidebarToggle) {
	sidebarToggle.addEventListener("click", function() {
		sidebar.classList.toggle("active");
	});
}

// Close sidebar when clicking outside on mobile
document.addEventListener("click", function(e) {
	if (window.innerWidth <= 768) {
		if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
			sidebar.classList.remove("active");
		}
	}
});

// Close sidebar on content click (mobile)
if (contentArea) {
	contentArea.addEventListener("click", function() {
		if (window.innerWidth <= 768) {
			sidebar.classList.remove("active");
		}
	});
};

function showConfirmationModal(message) {
    return new Promise((resolve) => {
        const existingModal = document.getElementById('geofenceConfirmModal');
        if (existingModal) {
            existingModal.remove();
        }

        const modalWrapper = document.createElement('div');
        modalWrapper.id = 'geofenceConfirmModal';
        modalWrapper.style.position = 'fixed';
        modalWrapper.style.top = '0';
        modalWrapper.style.left = '0';
        modalWrapper.style.width = '100%';
        modalWrapper.style.height = '100%';
        modalWrapper.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
        modalWrapper.style.display = 'flex';
        modalWrapper.style.alignItems = 'center';
        modalWrapper.style.justifyContent = 'center';
        modalWrapper.style.zIndex = '9999';

        modalWrapper.innerHTML = `
            <div style="background: #fff; width: min(420px, 92vw); border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,.2); overflow: hidden;">
                <div style="padding: 14px 18px; border-bottom: 1px solid #eee; font-weight: 600;">Location Confirmation</div>
                <div style="padding: 16px 18px; line-height: 1.5; color: #333;">${message}</div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; padding: 14px 18px; border-top: 1px solid #eee;">
                    <button id="geofenceConfirmNo" type="button" style="padding: 8px 14px; border: 1px solid #bbb; background: #fff; border-radius: 6px; cursor: pointer;">Cancel</button>
                    <button id="geofenceConfirmYes" type="button" style="padding: 8px 14px; border: 1px solid #198754; background: #198754; color: #fff; border-radius: 6px; cursor: pointer;">Proceed</button>
                </div>
            </div>
        `;

        function cleanup() {
            modalWrapper.remove();
        }

        modalWrapper.querySelector('#geofenceConfirmNo').addEventListener('click', () => {
            cleanup();
            resolve(false);
        });

        modalWrapper.querySelector('#geofenceConfirmYes').addEventListener('click', () => {
            cleanup();
            resolve(true);
        });

        modalWrapper.addEventListener('click', (event) => {
            if (event.target === modalWrapper) {
                cleanup();
                resolve(false);
            }
        });

        document.body.appendChild(modalWrapper);
    });
}