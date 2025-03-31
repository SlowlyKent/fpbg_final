<?php
include 'connect.php'; // Ensure database connection

$sql = "SELECT user_id, username FROM users"; 
$result = $conn->query($sql);

if (!$result) {
    die("SQL Error: " . $conn->error); // Debugging
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List</title>
    <link rel="stylesheet" href="read.css">
</head>
<body>
    <h1 class="logo">FPBG<br> STOCK</h1>    
    <h2>Registered Users</h2>
    <table>
        <tr>
            <th>User ID</th>
            <th>Username</th>
            <th>Actions</th>
        </tr>

        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['user_id']}</td>
                        <td>{$row['username']}</td>
                        <td>
                            <a href='update.php?user_id={$row['user_id']}'>Edit</a> | 
                            <a href='delete.php?user_id={$row['user_id']}' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No users found!</td></tr>";
        }
        ?>
    </table>
</body>
</html>

<?php
$conn->close();
?>
