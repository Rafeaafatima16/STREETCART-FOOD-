<?php
session_start();
require_once __DIR__ . '/../connection.php';


// Check admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get sales details
$sql = "SELECT * FROM orders";
$result = mysqli_query($conn, $sql);

$total_orders = 0;
$total_sales = 0;
$delivered_orders = 0;
$pending_orders = 0;

// Count orders and calculate sales
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $total_orders++;

        if ($row['status'] == 'delivered') {
            $total_sales = $total_sales + $row['total_amount'];
            $delivered_orders++;
        }

        if ($row['status'] == 'pending') {
            $pending_orders++;
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>StreetCart Sales Report</title>

    <style>
        body {
            font-family: Arial;
            background: #f5f1eb;
            padding: 25px;
        }

        h1 {
            color: #6b3e26;
        }

        .card {
            background: white;
            padding: 20px;
            margin: 15px 0;
            border-radius: 10px;
            max-width: 350px;
        }

        .number {
            color: #6b3e26;
            font-size: 25px;
            font-weight: bold;
        }

        a {
            color: #6b3e26;
        }
    </style>
</head>

<body>

    <h1>StreetCart - Sales Report</h1>

    <p><a href="dashboard.php">Back to Dashboard</a></p>

    <div class="card">
        <h3>Total Orders</h3>
        <div class="number">
            <?php echo $total_orders; ?>
        </div>
    </div>

    <div class="card">
        <h3>Total Sales (Delivered Orders)</h3>
        <div class="number">
            ₹<?php echo number_format($total_sales, 2); ?>
        </div>
    </div>

    <div class="card">
        <h3>Delivered Orders</h3>
        <div class="number">
            <?php echo $delivered_orders; ?>
        </div>
    </div>

    <div class="card">
        <h3>Pending Orders</h3>
        <div class="number">
            <?php echo $pending_orders; ?>
        </div>
    </div>

</body>

</html>