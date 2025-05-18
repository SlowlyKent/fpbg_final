import React, { useState, useEffect } from 'react';
import axios from 'axios';

const NotificationDropdown = () => {
  const [notifications, setNotifications] = useState([]);
  const [isOpen, setIsOpen] = useState(false);

  useEffect(() => {
    const fetchNotifications = async () => {
      try {
        const response = await axios.get('http://localhost:3001/api/notifications');
        setNotifications(response.data);
      } catch (error) {
        console.error('Error fetching notifications:', error);
      }
    };

    fetchNotifications();
  }, []);

  return (
    <div className="notification-dropdown">
      <button onClick={() => setIsOpen(!isOpen)}>
        Notifications ({notifications.length})
      </button>
      {isOpen && (
        <div className="dropdown-content">
          {notifications.length === 0 ? (
            <p>No notifications</p>
          ) : (
            notifications.map((notification, index) => (
              <div key={index} className="notification">
                <p>{notification.message}</p>
                <small>{notification.timestamp}</small>
              </div>
            ))
          )}
        </div>
      )}
    </div>
  );
};

export default NotificationDropdown;
