document.addEventListener("DOMContentLoaded", function () {

    const logoutBtn = document.getElementById("logout-btn");

    if (logoutBtn) {
        logoutBtn.addEventListener("click", function () {
            if (confirm("Are you sure you want to logout?")) {
                window.location.href = "../logout/logout.php";
            }
        });
    }

    const menuItems = document.querySelectorAll(".menu li[data-section]");
    const overviewSection = document.getElementById("overview-section");
    const editSection = document.getElementById("edit-section");
    const sectionTitle = document.getElementById("section-title");

    menuItems.forEach(item => {
        item.addEventListener("click", function () {

            // Remove active from all
            menuItems.forEach(li => li.classList.remove("active"));
            this.classList.add("active");

            const section = this.getAttribute("data-section");

            if (section === "overview") {
                sectionTitle.innerText = "Profile Information";
                overviewSection.style.display = "block";
                editSection.style.display = "none";
            } 
            else if (section === "edit") {
                sectionTitle.innerText = "Edit Profile";
                overviewSection.style.display = "none";
                editSection.style.display = "block";
            }
        });
    });

});
