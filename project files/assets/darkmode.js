document.addEventListener("DOMContentLoaded", () => {
    const toggle = document.getElementById("darkModeToggle");
    if (!toggle) return;

    const isDark = localStorage.getItem("darkmode") === "enabled";

    // Apply saved mode
    document.body.classList.toggle("dark-mode", isDark);
    toggle.checked = isDark;

    toggle.addEventListener("change", () => {
        if (toggle.checked) {
            document.body.classList.add("dark-mode");
            localStorage.setItem("darkmode", "enabled");
        } else {
            document.body.classList.remove("dark-mode");
            localStorage.removeItem("darkmode");
        }
    });
});
