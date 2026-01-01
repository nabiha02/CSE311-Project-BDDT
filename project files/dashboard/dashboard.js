// Profile dropdown toggle
document.addEventListener('DOMContentLoaded', function() {
    const profileIcon = document.querySelector('.profile-dropdown .user-icon');
    const dropdown = document.querySelector('.profile-dropdown-content');

    profileIcon.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('show');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function() {
        dropdown.classList.remove('show');
    });
});


document.addEventListener("DOMContentLoaded", () => {
    const notifyArea = document.querySelector(".notification-dropdown");

    if (notifyArea) {
        notifyArea.addEventListener("mouseover", () => {
            fetch("/bddt/notifications/mark_read.php");
        });
    }
});


