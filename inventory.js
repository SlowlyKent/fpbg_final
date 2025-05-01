document.addEventListener("DOMContentLoaded", function () {
    document.getElementById('notificationContainer').style.display = 'flex';
});

function toggleNotifications() {
    var dropdown = document.getElementById("notifDropdown");
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

document.addEventListener("click", function (event) {
    var dropdown = document.getElementById("notifDropdown");
    var icon = document.querySelector(".notification-icon");
    if (dropdown && icon && !dropdown.contains(event.target) && !icon.contains(event.target)) {
        dropdown.style.display = "none";
    }
});
