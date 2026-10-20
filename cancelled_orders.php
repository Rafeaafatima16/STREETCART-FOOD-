<?php
session_start();
require_once __DIR__ . '/../connection.php';

// Check admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get cancelled orders
$sql = "SELECT orders.*, users.name, users.email
        FROM orders
        INNER JOIN users ON orders.buyer_id = users.id
        WHERE orders.status = 'cancelled'";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Cancelled Orders</title>

    <style>
        body {
            font-family: Arial;
            background: #f5f1eb;
            padding: 20px;
        }

        h1 {
            color: brown;
        }

        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid gray;
            padding: 10px;
            text-align: left;
        }

        th {
            background: brown;
            color: white;
        }
    </style>
</head>

<body>

    <h1>Cancelled Orders</h1>

    <a href="dashboard.php">Back to Dashboard</a>

    <table>
        <tr>
            <th>Order ID</th>
            <th>Buyer Name</th>
            <th>Email</th>
            <th>Amount</th>
            <th>Payment Status</th>
            <th>Order Date</th>
        </tr>

        <?php
        if ($result && mysqli_num_rows($result) > 0) {

            while ($row = mysqli_fetch_assoc($result)) {
        ?>

                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>Rs. <?php echo $row['total_amount']; ?></td>
                    <td><?php echo htmlspecialchars($row['payment_status']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                </tr>

        <?php
            }
        } else {
            echo "<tr><td colspan='6'>No cancelled orders found.</td></tr>";
        }
        ?>

    </table>

</body>

</html>