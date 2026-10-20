<?php
session_start();
require_once __DIR__ . '/../connection.php';

// Check admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get payment details
$sql = "SELECT orders.id AS order_id, users.name,
        orders.total_amount, orders.payment_status,
        orders.created_at
        FROM orders
        INNER JOIN users ON orders.buyer_id = users.id";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>StreetCart Payments</title>

    <style>
        body {
            font-family: Arial;
            background: #f5f1eb;
            padding: 20px;
        }

        h1 {
            color: #6b3e26;
        }

        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid gray;
            text-align: left;
        }

        th {
            background: #6b3e26;
            color: white;
        }
    </style>
</head>

<body>

    <h1>StreetCart - Payments</h1>

    <a href="dashboard.php">Back to Dashboard</a>

    <table>
        <tr>
            <th>Order ID</th>
            <th>Buyer</th>
            <th>Amount</th>
            <th>Payment Status</th>
            <th>Order Date</th>
        </tr>

        <?php
        if ($result && mysqli_num_rows($result) > 0) {

            while ($payment = mysqli_fetch_assoc($result)) {
        ?>

                <tr>
                    <td><?php echo $payment['order_id']; ?></td>

                    <td><?php echo htmlspecialchars($payment['name']); ?></td>

                    <td>Rs. <?php echo $payment['total_amount']; ?></td>

                    <td><?php echo htmlspecialchars($payment['payment_status']); ?></td>

                    <td><?php echo htmlspecialchars($payment['created_at']); ?></td>
                </tr>

        <?php
            }
        } else {
            echo "<tr><td colspan='5'>No payment records found.</td></tr>";
        }
        ?>

    </table>

</body>

</html>