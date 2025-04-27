document.addEventListener("DOMContentLoaded", function () {
    let pieCtx = document.getElementById("pieChart").getContext("2d");
    let barCtx = document.getElementById("barChart").getContext("2d");
    let generalCtx = document.getElementById("myChart").getContext("2d");

    // Pie Chart
    new Chart(pieCtx, {
        type: "pie",
        data: {
            labels: ["Product A", "Product B", "Product C"],
            datasets: [{
                label: "Sales Distribution",
                data: [40, 30, 30], // Percentage values
                backgroundColor: ["blue", "green", "red"]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "top",
                }
            }
        }
    });

    // Bar Chart
    new Chart(barCtx, {
        type: "bar",
        data: {
            labels: ["January", "February", "March", "April"],
            datasets: [{
                label: "Sales",
                data: [12000, 8000, 10000, 15000],
                backgroundColor: "#69a2ff",
                borderColor: "#003366",
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // General Chart
    new Chart(generalCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar'],
            datasets: [{
                label: 'Sales',
                data: [10000, 15000, 20000],
                backgroundColor: 'blue'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Clickable Cashiering Button
    document.getElementById("cashieringButton").addEventListener("click", function() {
        window.location.href = "cashiering.php"; // Redirect to cashiering.php
    });

    // Password Toggle
    const eyeIcon = document.getElementById("eye");
    const passwordField = document.getElementById("password");

    if (eyeIcon && passwordField) {
        eyeIcon.addEventListener("click", () => {
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.toggle("fa-eye-slash");
                eyeIcon.classList.toggle("fa-eye");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.toggle("fa-eye");
                eyeIcon.classList.toggle("fa-eye-slash");
            }
        });
    }
});