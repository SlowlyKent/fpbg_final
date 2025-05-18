document.addEventListener("DOMContentLoaded", function () {
    document.getElementById('notificationContainer').style.display = 'flex';
    fetchNotifications(); // Fetch notifications when the page loads
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

async function fetchNotifications() {
    try {
        const userId = currentUserId; // Use dynamic user ID from PHP session
        if (!userId) {
            console.warn("User ID not set. Cannot fetch notifications.");
            return;
        }
        const response = await fetch(`/api/notifications/${userId}`);
        const notifications = await response.json();

        const dropdown = document.getElementById("notifDropdown");
        const notificationList = document.getElementById("notificationList");
        const notificationDot = document.querySelector(".notification-dot");

        // Clear existing notifications
        notificationList.innerHTML = '';

        // Add new notifications
        notifications.forEach(notification => {
            const notificationItem = document.createElement("div");
            notificationItem.className = "notification-item";
            notificationItem.textContent = notification.message;
            notificationList.appendChild(notificationItem);
        });

        // Update notification count
        if (notifications.length > 0) {
            notificationDot.textContent = notifications.length;
            notificationDot.style.display = "flex";
        } else {
            notificationDot.style.display = "none";
        }
    } catch (error) {
        console.error("Error fetching notifications:", error);
    }
}

async function addNotification(message) {
    try {
        const userId = currentUserId; // Use dynamic user ID from PHP session
        if (!userId) {
            console.warn("User ID not set. Cannot add notification.");
            return;
        }
        await fetch('/api/notifications', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_id: userId, message }),
        });
        fetchNotifications(); // Refresh the notification list
    } catch (error) {
        console.error("Error adding notification:", error);
    }
}

// Example function to add a new stock
async function addNewStock(productName) {
    try {
        // Here you would typically have code to add the stock to your database
        // For example:
        // await fetch('/api/stocks', { method: 'POST', body: JSON.stringify({ productName }) });

        // After successfully adding the stock, add a notification
        const message = `A new stock was added: ${productName}`;
        await addNotification(message);

        console.log("Stock added and notification sent:", productName);
    } catch (error) {
        console.error("Error adding stock:", error);
    }
}


document.addEventListener("DOMContentLoaded", function () {
    document.getElementById('notificationContainer').style.display = 'flex';
    fetchNotifications(); // Fetch notifications when the page loads

    // Check if newStockProductName is set and add notification
    if (typeof newStockProductName !== 'undefined' && newStockProductName) {
        addNewStock(newStockProductName);
    }
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

async function fetchNotifications() {
    try {
        const userId = 1; // Replace with the actual user ID
        const response = await fetch(`/api/notifications/${userId}`);
        const notifications = await response.json();

        const dropdown = document.getElementById("notifDropdown");
        const notificationList = document.getElementById("notificationList");
        const notificationDot = document.querySelector(".notification-dot");

        // Clear existing notifications
        notificationList.innerHTML = '';

        // Add new notifications
        notifications.forEach(notification => {
            const notificationItem = document.createElement("div");
            notificationItem.className = "notification-item";
            notificationItem.textContent = notification.message;
            notificationList.appendChild(notificationItem);
        });

        // Update notification count
        if (notifications.length > 0) {
            notificationDot.textContent = notifications.length;
            notificationDot.style.display = "flex";
        } else {
            notificationDot.style.display = "none";
        }
    } catch (error) {
        console.error("Error fetching notifications:", error);
    }
}

async function addNotification(message) {
    try {
        const userId = 1; // Replace with the actual user ID
        await fetch('/api/notifications', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_id: userId, message }),
        });
        fetchNotifications(); // Refresh the notification list
    } catch (error) {
        console.error("Error adding notification:", error);
    }
}

// Example function to add a new stock
async function addNewStock(productName) {
    try {
        // Here you would typically have code to add the stock to your database
        // For example:
        // await fetch('/api/stocks', { method: 'POST', body: JSON.stringify({ productName }) });

        // After successfully adding the stock, add a notification
        const message = `A new stock was added: ${productName}`;
        await addNotification(message);

        console.log("Stock added and notification sent:", productName);
    } catch (error) {
        console.error("Error adding stock:", error);
    }
}
