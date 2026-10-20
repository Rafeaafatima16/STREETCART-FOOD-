<?php
session_start();
require_once __DIR__ . '/../connection.php';

if (
    !isset($_SESSION['user_id']) ||
    $_SESSION['role'] !== 'seller'
) {
    header("Location: ../login.php");
    exit();
}

$seller_id = (int) $_SESSION['user_id'];

$sql = "SELECT
            orders.id AS order_id,
            orders.status AS order_status,
            orders.payment_status,
            orders.created_at,
            foods.food_name,
            order_items.quantity,
            order_items.price,
            cafes.cafe_name
        FROM order_items
        INNER JOIN orders
            ON order_items.order_id = orders.id
        INNER JOIN foods
            ON order_items.food_id = foods.id
        INNER JOIN cafes
            ON foods.cafe_id = cafes.id
        WHERE cafes.owner_id = ?
        ORDER BY orders.id DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $seller_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Orders - StreetCart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 20px;
        }

        h1 {
            color: #222;
        }

        .order {
            background: white;
            padding: 15px;
            margin: 15px 0;
            border-radius: 10px;
            box-shadow: 0 2px 8px #ddd;
        }

        .status {
            color: #087f5b;
            font-weight: bold;
        }

        a {
            color: #075ed8;
        }
    </style>
</head>

<body>

    <h1>My Orders 🛒</h1>
    <p><a href="dashboard.php">← Back to Dashboard</a></p>

    <?php if (mysqli_num_rows($result) > 0) { ?>
        <?php while ($order = mysqli_fetch_assoc($result)) { ?>
            <div class="order">
                <h3>Order #<?= (int) $order['order_id'] ?></h3>
                <p><strong>Cafe:</strong>
                    <?= htmlspecialchars($order['cafe_name']) ?></p>
                <p><strong>Food:</strong>
                    <?= htmlspecialchars($order['food_name']) ?></p>
                <p><strong>Quantity:</strong>
                    <?= (int) $order['quantity'] ?></p>
                <p><strong>Price per item:</strong>
                    ₹<?= number_format((float) $order['price'], 2) ?></p>
                <p><strong>Item total:</strong>
                    ₹<?= number_format(
                            (float) $order['price'] * (int) $order['quantity'],
                            2
                        ) ?></p>
                <p><strong>Order status:</strong>
                    <span class="status">
                        <?= htmlspecialchars($order['order_status']) ?>
                    </span>
                </p>
                <p><strong>Payment:</strong>
                    <?= htmlspecialchars($order['payment_status']) ?></p>
                <p><strong>Date:</strong>
                    <?= htmlspecialchars($order['created_at']) ?></p>
            </div>
        <?php } ?>
    <?php } else { ?>
        <p>No orders found yet. Orders will appear here when items are ordered.</p>
    <?php } ?>

</body>

</html>
<?php mysqli_stmt_close($stmt); ?>