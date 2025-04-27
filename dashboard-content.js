document.addEventListener("DOMContentLoaded", function () {
    loadPage('dashboard-content.php'); // Load default dashboard content
});

// Function to load pages dynamically via AJAX
function loadPage(page, event = null) {
    if (event) {
        event.preventDefault(); // Prevent default link behavior
    }

    var contentContainer = document.getElementById("contentContainer");
    if (!contentContainer) {
        console.error("Error: contentContainer not found!");
        return;
    }

    contentContainer.innerHTML = "<p>Loading...</p>"; // Show loading text

    var xhr = new XMLHttpRequest();
    xhr.open("GET", page, true);
    
    xhr.onload = function () {
        if (xhr.status === 200) {
            contentContainer.innerHTML = xhr.responseText; // Load page content
        } else if (xhr.status === 404) {
            console.error("Error: Page not found (" + page + ")");
            contentContainer.innerHTML = "<p style='color: red;'>Error: Page not found (404). Please check the filename.</p>";
        } else {
            console.error("Error loading " + page + ": " + xhr.statusText);
            contentContainer.innerHTML = "<p style='color: red;'>Error loading page. Please try again.</p>";
        }
    };

    xhr.onerror = function () {
        console.error("Network error while loading " + page);
        contentContainer.innerHTML = "<p style='color: red;'>Network error. Please check your connection.</p>";
    };

    xhr.send();
}

// Notification Dropdown Toggle
function toggleNotifications() {
    var dropdown = document.getElementById("notifDropdown");
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

// Hide dropdown when clicking outside
document.addEventListener("click", function (event) {
    var dropdown = document.getElementById("notifDropdown");
    var icon = document.querySelector(".notification-icon");

    if (dropdown && icon && !dropdown.contains(event.target) && !icon.contains(event.target)) {
        dropdown.style.display = "none";
    }
});

// Load Charts After Page Load
document.addEventListener("DOMContentLoaded", function() {
    const pieCanvas = document.getElementById('pieChart');
    const barCanvas = document.getElementById('barChart');

    if (pieCanvas && barCanvas) {
        const pieCtx = pieCanvas.getContext('2d');
        const barCtx = barCanvas.getContext('2d');

        // Pie Chart with Title
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Cost', 'Revenue'],
                datasets: [{
                    data: [4895, 7850],
                    backgroundColor: ['#1D2B53', '#7FDBFF']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Cost vs. Revenue',
                        color: '#000000', // Black title color
                        font: { size: 16 }
                    },
                    legend: {
                        labels: {
                            color: '#000000' // Black legend labels
                        }
                    }
                }
            }
        });

        // Bar Chart with Title
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: ['January', 'February', 'March', 'April'],
                datasets: [{
                    label: 'Sales',
                    data: [5000, 7000, 8000, 10000],
                    backgroundColor: '#4A90E2'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Sales Report',
                        color: '#000000', // Black title color
                        font: { size: 16 }
                    },
                    legend: {
                        labels: {
                            color: '#000000' // Black legend labels
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#000000' // Black X-axis labels
                        }
                    },
                    y: {
                        ticks: {
                            color: '#000000' // Black Y-axis labels
                        }
                    }
                }
            }
        });
    }
});
</script>

</body>
</html>