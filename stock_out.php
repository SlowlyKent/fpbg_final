<?php
include ('connect.php');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch transactions from the database with brand information
$sql = "
    SELECT st.transaction_id, st.product_id, st.quantity, st.transaction_date, p.brand, t.amount_paid
    FROM stock_transactions st
    JOIN products p ON st.product_id = p.product_id
    JOIN transactions t ON st.transaction_id = t.transaction_id
";
$result = $conn->query($sql);

if ($result === false) {
    die("Query failed: " . $conn->error);
}

$transactions = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Stock Out</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: white;
      margin: 0;
      padding: 0;
    }

    .search-bar {
      background-color: #007bff;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .search-bar input[type="text"] {
      width: 500px;
      padding: 10px 20px;
      border: none;
      border-radius: 50px;
      outline: none;
    }

    .notification {
      background-color: white;
      border-radius: 50%;
      width: 35px;
      height: 35px;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      cursor: pointer;
    }

    .notification-icon {
      color: #007bff;
      font-size: 18px;
    }

    .badge {
      position: absolute;
      top: -5px;
      right: -5px;
      background-color: red;
      color: white;
      font-size: 10px;
      padding: 2px 5px;
      border-radius: 50%;
    }

    table {
      width: 90%;
      margin: 30px auto;
      border-collapse: collapse;
      background-color: white;
    }

    th, td {
      padding: 10px;
      text-align: center;
    }

    th {
      font-weight: bold;
      background: linear-gradient(to bottom, #e6e6e6, #ffffff);
    }

    @media screen and (max-width: 600px) {
      .search-bar input[type="text"] {
        width: 100%;
      }

      table {
        width: 100%;
        font-size: 12px;
      }
    }
  </style>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

  <div class="search-bar">
    <input type="text" placeholder="Search">
    <div class="notification">
      <i class="fas fa-bell notification-icon"></i>
      <span class="badge">3</span>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>TRANSACTION ID</th>
        <th>PRODUCT ID</th>
        <th>BRAND</th>
        <th>QUANTITY</th>
        <th>PAID AMOUNT</th>
        <th>TRANSACTION DATE</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($transactions)): ?>
          <tr><td colspan='6'>No transactions found</td></tr>
      <?php else: ?>
          <?php foreach ($transactions as $row): ?>
              <tr>
                  <td><?php echo htmlspecialchars($row['transaction_id']); ?></td>
                  <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                  <td><?php echo htmlspecialchars($row['brand']); ?></td>
                  <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                  <td><?php echo htmlspecialchars($row['amount_paid']); ?></td>
                  <td><?php echo htmlspecialchars($row['transaction_date']); ?></td>
              </tr>
          <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

</body>
</html>
