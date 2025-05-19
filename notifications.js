// Notification polling and management
let notificationPollingInterval;

function initializeNotifications() {
    // Start polling for notifications
    pollNotifications();
    notificationPollingInterval = setInterval(pollNotifications, 30000); // Poll every 30 seconds

    // Add click event listener to notification icon
    const notifIcon = document.querySelector('.notification-icon');
    if (notifIcon) {
        notifIcon.addEventListener('click', toggleNotifications);
    }

    // Add click event listener to close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('notifDropdown');
        const icon = document.querySelector('.notification-icon');
        if (dropdown && icon && !dropdown.contains(event.target) && !icon.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });
}

function pollNotifications() {
    fetch('notifications_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ action: 'get' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateNotificationUI(data.notifications);
        } else {
            console.error('Error fetching notifications:', data.error);
        }
    })
    .catch(error => console.error('Error polling notifications:', error));
}

function getNotificationTypeClass(type) {
    switch (type) {
        case 'danger':
            return 'notification-danger';
        case 'warning':
            return 'notification-warning';
        case 'success':
            return 'notification-success';
        default:
            return 'notification-info';
    }
}

function updateNotificationUI(notifications) {
    const notifList = document.getElementById('notifList');
    const notifBadge = document.getElementById('notifBadge');
    
    if (!notifList || !notifBadge) return;

    notifList.innerHTML = '';
    
    if (!notifications || notifications.length === 0) {
        notifList.innerHTML = '<li class="notification-item">No new notifications</li>';
        notifBadge.style.display = 'none';
        return;
    }

    notifBadge.style.display = 'flex';
    notifBadge.textContent = notifications.length;

    notifications.forEach(notification => {
        const li = document.createElement('li');
        li.className = `notification-item ${getNotificationTypeClass(notification.type)}`;
        li.innerHTML = `
            <div class="notification-content">
                <p>${notification.message}</p>
                <small>${new Date(notification.created_at).toLocaleString()}</small>
            </div>
            <button onclick="markNotificationAsRead(${notification.id})" class="mark-read-btn">
                <i class="fas fa-check"></i>
            </button>
        `;
        notifList.appendChild(li);
    });

    // Add notification type styles if not already present
    if (!document.getElementById('notification-styles')) {
        const styles = document.createElement('style');
        styles.id = 'notification-styles';
        styles.textContent = `
            .notification-danger {
                border-left: 4px solid #dc3545;
                background-color: #fff5f5;
            }
            .notification-warning {
                border-left: 4px solid #ffc107;
                background-color: #fff9e6;
            }
            .notification-success {
                border-left: 4px solid #28a745;
                background-color: #f0fff4;
            }
            .notification-info {
                border-left: 4px solid #17a2b8;
                background-color: #f0f8ff;
            }
        `;
        document.head.appendChild(styles);
    }
}

function markNotificationAsRead(notificationId) {
    fetch('notifications_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'markAsRead',
            notification_id: notificationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            pollNotifications(); // Refresh notifications
        } else {
            console.error('Error marking notification as read:', data.error);
        }
    })
    .catch(error => console.error('Error marking notification as read:', error));
}

function toggleNotifications() {
    const dropdown = document.getElementById('notifDropdown');
    if (!dropdown) return;
    
    const isVisible = dropdown.style.display === 'block';
    dropdown.style.display = isVisible ? 'none' : 'block';
    
    if (!isVisible) {
        pollNotifications(); // Refresh notifications when opening
    }
}

// Initialize notifications when the DOM is loaded
document.addEventListener('DOMContentLoaded', initializeNotifications); 