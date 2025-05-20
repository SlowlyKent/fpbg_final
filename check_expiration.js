document.addEventListener('DOMContentLoaded', function() {
    // Initialize notification system
    const notificationIcon = document.querySelector('.notification-icon');
    const notificationDropdown = document.getElementById('notifDropdown');

    notificationIcon.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationDropdown.style.display = notificationDropdown.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', function(e) {
        if (!notificationDropdown.contains(e.target) && !notificationIcon.contains(e.target)) {
            notificationDropdown.style.display = 'none';
        }
    });
});

function checkExpirations() {
    fetch('check_expiration_ajax.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload(); // Reload the page to show updated data
            } else {
                alert('Error checking expirations: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to check expirations. Please try again.');
        });
}
