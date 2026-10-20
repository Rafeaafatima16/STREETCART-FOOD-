<?php
session_start();
require_once __DIR__ . '/../connection.php';

// Only buyers can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../login.php");
    exit;
}

$buyer_id = (int) $_SESSION['user_id'];
$order_id = isset($_SESSION['last_order_id'])
    ? (int) $_SESSION['last_order_id']
    : 0;

$order = null;
$items = [];

if ($order_id > 0) {
    // Get this buyer's order only
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, total_amount, status, payment_status,
                delivery_address, created_at
         FROM orders
         WHERE id = ? AND buyer_id = ?"
    );

    mysqli_stmt_bind_param($stmt, "ii", $order_id, $buyer_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($order) {
        // Get ordered food items
        $stmt = mysqli_prepare(
            $conn,
            "SELECT f.food_name, oi.quantity, oi.price
             FROM order_items oi
             JOIN foods f ON oi.food_id = f.id
             WHERE oi.order_id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        while ($item = mysqli_fetch_assoc($result)) {
            $items[] = $item;
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - StreetCart</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 25px;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #263238;
        }

        .container {
            max-width: 700px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
        }

        .success {
            text-align: center;
            color: #16834a;
        }

        .success .check {
            font-size: 55px;
        }

        h1 {
            margin-bottom: 8px;
        }

        .details {
            margin-top: 25px;
            padding: 18px;
            background: #f7f9fc;
            border-radius: 10px;
            line-height: 1.8;
        }

        h2 {
            margin-top: 25px;
            font-size: 20px;
        }

        .item {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .total {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
        }

        .buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 7px;
            text-decoration: none;
            background: #1677ff;
            color: white;
        }

        .secondary {
            background: #263238;
        }

        .empty {
            text-align: center;
            padding: 25px 0;
        }

        @media (max-width: 500px) {
            body {
                padding: 12px;
            }

            .container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="container">

        <?php if ($order): ?>

            <div class="success">
                <div class="check">✓</div>
                <h1>Order Placed Successfully!</h1>
                <p>Thank you for ordering from StreetCart.</p>
            </div>

            <div class="details">
                <strong>Order ID:</strong>
                #<?php echo (int) $order['id']; ?><br>

                <strong>Order Status:</strong>
                <?php echo htmlspecialchars($order['status']); ?><br>

                <strong>Payment Status:</strong>
                <?php echo htmlspecialchars($order['payment_status']); ?><br>

                <strong>Delivery Address:</strong>
                <?php echo htmlspecialchars($order['delivery_address']); ?><br>

                <strong>Order Date:</strong>
                <?php echo htmlspecialchars($order['created_at']); ?>
            </div>

            <h2>Ordered Items</h2>

            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <div class="item">
                        <div>
                            <strong>
                                <?php echo htmlspecialchars($item['food_name']); ?>
                            </strong>
                            <br>
                            <small>
                                Quantity:
                                <?php echo (int) $item['quantity']; ?>
                            </small>
                        </div>

                        <div>
                            ₹<?php
                                echo number_format(
                                    (float) $item['price'] * (int) $item['quantity'],
                                    2
                                );
                                ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Order item details are unavailable.</p>
            <?php endif; ?>

            <div class="total">
                Total: ₹<?php
                        echo number_format((float) $order['total_amount'], 2);
                        ?>
            </div>

            <div class="buttons">
                <a class="btn" href="foods.php">Order More Food</a>
                <a class="btn secondary" href="customer_dash.php">
                    Dashboard
                </a>
            </div>

        <?php else: ?>

            <div class="empty">
                <h1>No Recent Order Found</h1>
                <p>Place an order first to see your confirmation here.</p>

                <div class="buttons">
                    <a class="btn" href="foods.php">Browse Food</a>
                    <a class="btn secondary" href="customer_dash.php">
                        Dashboard
                    </a>
                </div>
            </div>

        <?php endif; ?>

    </div>

</body>

</html>