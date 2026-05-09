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