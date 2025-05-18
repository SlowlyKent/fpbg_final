document.addEventListener('DOMContentLoaded', () => {
    // Show notification container
    const notificationContainer = document.getElementById('notificationContainer');
    if (notificationContainer) {
        notificationContainer.style.display = 'flex';
    }

    // Toggle notifications dropdown
    function toggleNotifications() {
        const notifDropdown = document.getElementById('notifDropdown');
        if (!notifDropdown) return;

        notifDropdown.style.display = (notifDropdown.style.display === 'block') ? 'none' : 'block';
    }

    // Attach toggleNotifications to bell icon
    const notifIcon = document.querySelector('.notification-icon');
    if (notifIcon) {
        notifIcon.addEventListener('click', toggleNotifications);
    }

    // Close notification dropdown if clicked outside
    document.addEventListener('click', (event) => {
        const notifDropdown = document.getElementById('notifDropdown');
        const notifIcon = document.querySelector('.notification-icon');
        if (!notifDropdown || !notifIcon) return;

        if (!notifDropdown.contains(event.target) && !notifIcon.contains(event.target)) {
            notifDropdown.style.display = 'none';
        }
    });

    // Function to update notification as read
    function updateNotification(notificationId) {
        fetch('update_notification.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ notification_id: notificationId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            } else {
                console.error('Failed to update notification:', data.message);
            }
        })
        .catch(err => {
            console.error('Error updating notification:', err);
        });
    }

    // Function to load notifications
    function loadNotifications() {
        fetch('get_notifications.php')
            .then(response => response.json())
            .then(data => {
                const notifList = document.getElementById('notifList');
                const notifBadge = document.getElementById('notifBadge');
                notifList.innerHTML = '';

                if (!data || data.length === 0) {
                    notifList.innerHTML = '<li style="padding: 10px; border-bottom: 1px solid #eee;">No new notifications</li>';
                    notifBadge.style.display = 'none';
                    return;
                }

                notifBadge.style.display = 'flex';
                notifBadge.textContent = data.length;

                data.forEach(notif => {
                    const li = document.createElement('li');
                    li.textContent = notif.message;
                    li.style.padding = '10px';
                    li.style.cursor = 'pointer';
                    li.onclick = () => updateNotification(notif.id);
                    notifList.appendChild(li);
                });
            })
            .catch(err => {
                console.error('Error fetching notifications:', err);
                const notifList = document.getElementById('notifList');
                notifList.innerHTML = '<li style="padding: 10px; border-bottom: 1px solid #eee; color: red;">Error loading notifications</li>';
            });
    }

    // Initial load of notifications
    loadNotifications();

    // Listen for custom newNotification events to add notifications dynamically
    const notifList = document.getElementById('notifList');
    const notifBadge = document.getElementById('notifBadge');
    if (notifList && notifBadge) {
        document.addEventListener('newNotification', (e) => {
            const message = e.detail;
            const newNotif = document.createElement('li');
            newNotif.textContent = message;
            newNotif.style.padding = '10px';
            newNotif.style.cursor = 'pointer';
            newNotif.onclick = () => updateNotification(newNotif.id);
            notifList.appendChild(newNotif);

            // Update badge count
            let count = parseInt(notifBadge.textContent) || 0;
            notifBadge.textContent = count + 1;
            notifBadge.style.display = 'inline-block';
        });

// Edit product function
function editProduct(productId) {
    window.location.href = `edit_product.php?id=${productId}`;
}

// Delete product function
function deleteProduct(productId) {
    if (confirm("Are you sure you want to delete this product?")) {
        fetch('delete_product.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${encodeURIComponent(productId)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Product deleted successfully");
                window.location.reload();
            } else {
                alert("Error deleting product: " + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}
