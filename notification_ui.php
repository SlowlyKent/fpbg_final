<!-- Notification UI -->
<link rel="stylesheet" href="css/fontawesome/css/all.min.css">

<div class="notification-container" id="notificationContainer">
    <div class="notification-icon" onclick="toggleNotifications()">
        <i class="fa-solid fa-bell"></i>
        <span class="notification-badge" id="notifBadge" style="display:none;">0</span>
    </div>
    <div class="notification-dropdown" id="notifDropdown" style="display:none;">
        <div style="padding: 15px; border-bottom: 1px solid #eee;">
            <h4 style="margin: 0; color: #003366;">Notifications</h4>
        </div>
        <ul id="notifList" style="list-style: none; margin: 0; padding: 0; max-height: 300px; overflow-y: auto;">
            <li style="padding: 15px; border-bottom: 1px solid #eee; color: #666;">No new notifications</li>
        </ul>
    </div>
</div>

<script>
function toggleNotifications() {
    const dropdown = document.getElementById('notifDropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    if (dropdown.style.display === 'block') {
        loadNotifications();
    }
}

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

function loadNotifications() {
    fetch('get_notifications.php')
        .then(response => response.json())
        .then(data => {
            const notifList = document.getElementById('notifList');
            const notifBadge = document.getElementById('notifBadge');
            notifList.innerHTML = '';

            if (!data || data.length === 0) {
                notifList.innerHTML = '<li style="padding: 15px; border-bottom: 1px solid #eee; color: #666;">No new notifications</li>';
                notifBadge.style.display = 'none';
                return;
            }

            notifBadge.style.display = 'flex';
            notifBadge.textContent = data.length;

            data.forEach(notif => {
                const li = document.createElement('li');
                li.style.padding = '15px';
                li.style.borderBottom = '1px solid #eee';
                li.style.color = '#333';
                li.style.cursor = 'pointer';
                li.style.transition = 'background-color 0.2s';
                
                const message = document.createElement('div');
                message.textContent = notif.message;
                message.style.marginBottom = '5px';
                
                const timestamp = document.createElement('div');
                timestamp.textContent = new Date(notif.created_at).toLocaleString();
                timestamp.style.fontSize = '12px';
                timestamp.style.color = '#666';
                
                li.appendChild(message);
                li.appendChild(timestamp);
                
                li.onmouseover = () => {
                    li.style.backgroundColor = '#f5f5f5';
                };
                li.onmouseout = () => {
                    li.style.backgroundColor = 'white';
                };
                li.onclick = () => updateNotification(notif.id);
                
                notifList.appendChild(li);
            });
        })
        .catch(err => {
            console.error('Error fetching notifications:', err);
            const notifList = document.getElementById('notifList');
            notifList.innerHTML = '<li style="padding: 15px; border-bottom: 1px solid #eee; color: #dc3545;">Error loading notifications</li>';
        });
}

// Initial notification badge update and polling for real-time updates
function pollNotifications() {
    fetch('get_notifications.php')
        .then(response => response.json())
        .then(data => {
            const notifBadge = document.getElementById('notifBadge');
            if (!data || data.length === 0) {
                notifBadge.style.display = 'none';
            } else {
                notifBadge.style.display = 'flex';
                notifBadge.textContent = data.length;
            }
        })
        .catch(err => {
            console.error('Error polling notifications:', err);
        });
}

// Poll every 10 seconds
setInterval(pollNotifications, 10000);

// Close dropdown when clicking outside
document.addEventListener('click', (event) => {
    const dropdown = document.getElementById('notifDropdown');
    const icon = document.querySelector('.notification-icon');
    if (dropdown && icon && !dropdown.contains(event.target) && !icon.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});

document.addEventListener('DOMContentLoaded', () => {
    pollNotifications();
});
</script>
