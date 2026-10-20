<?php
session_start();
require_once __DIR__ . '/../connection.php';

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'buyer'
) {
    header("Location: ../login.php");
    exit;
}

$buyer_id = (int) $_SESSION['user_id'];
$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$order = null;
$items = [];

if ($order_id && $order_id > 0) {
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

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$status_steps = ['pending', 'confirmed', 'preparing', 'delivered'];
$current_status = $order['status'] ?? '';

$step_index = array_search($current_status, $status_steps, true);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order - StreetCart</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #263238;
        }

        .container {
            max-width: 750px;
            margin: 30px auto;
            background: white;
            padding: 28px;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .07);
        }

        h1 {
            margin-top: 0;
        }

        .muted {
            color: #667085;
        }

        .status-box {
            margin: 22px 0;
            padding: 18px;
            border-radius: 10px;
            background: #f1f6ff;
        }

        .status {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            background: #dce9ff;
            color: #174ea6;
            text-transform: capitalize;
            font-weight: bold;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-top: 22px;
        }

        .step {
            text-align: center;
            color: #98a2b3;
            font-size: 13px;
        }

        .circle {
            width: 32px;
            height: 32px;
            margin: 0 auto 8px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e5e7eb;
            color: #475467;
            font-weight: bold;
        }

        .step.active {
            color: #1677ff;
        }

        .step.active .circle {
            background: #1677ff;
            color: white;
        }

        .detail {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .item {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .total {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            margin-top: 22px;
            padding: 12px 17px;
            border-radius: 7px;
            background: #1677ff;
            color: white;
            text-decoration: none;
        }

        .cancelled {
            background: #fff0f0;
            color: #b42318;
        }

        .empty {
            text-align: center;
            padding: 20px 0;
        }

        @media (max-width: 500px) {
            .container {
                padding: 18px;
            }

            .steps {
                gap: 4px;
            }

            .step {
                font-size: 11px;
            }
        }
    </style>
</head>

<body>
    <div class="container">

        <?php if ($order): ?>

            <h1>Track Your Order 📦</h1>
            <p class="muted">
                Order #<?php echo (int) $order['id']; ?>
            </p>

            <?php if ($current_status === 'cancelled'): ?>

                <div class="status-box cancelled">
                    <strong>Order Cancelled</strong>
                    <p>This order has been cancelled.</p>
                </div>

            <?php else: ?>

                <div class="status-box">
                    <p>Current order status</p>
                    <span class="status"><?php echo h($current_status); ?></span>

                    <div class="steps">
                        <?php foreach ($status_steps as $index => $step): ?>
                            <div class="step <?php
                                                echo ($step_index !== false && $index <= $step_index)
                                                    ? 'active' : '';
                                                ?>">
                                <div class="circle">
                                    <?php
                                    echo ($step_index !== false && $index <= $step_index)
                                        ? '✓' : ($index + 1);
                                    ?>
                                </div>
                                <?php echo h(ucfirst($step)); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            <?php endif; ?>

            <h2>Order Details</h2>

            <div class="detail">
                <strong>Payment Status:</strong>
                <?php echo h($order['payment_status']); ?>
            </div>

            <div class="detail">
                <strong>Delivery Address:</strong>
                <?php echo h($order['delivery_address']); ?>
            </div>

            <div class="detail">
                <strong>Order Date:</strong>
                <?php echo h($order['created_at']); ?>
            </div>

            <h2>Food Items</h2>

            <?php foreach ($items as $item): ?>
                <div class="item">
                    <div>
                        <strong><?php echo h($item['food_name']); ?></strong>
                        <br>
                        <small>Quantity: <?php echo (int) $item['quantity']; ?></small>
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

            <div class="total">
                Total: ₹<?php echo number_format((float) $order['total_amount'], 2); ?>
            </div>

        <?php else: ?>

            <div class="empty">
                <h1>Order Not Found</h1>
                <p class="muted">
                    This order doesn't exist or doesn't belong to your account.
                </p>
            </div>

        <?php endif; ?>

        <a class="btn" href="orders.php">Back to My Orders</a>
        <a class="btn" href="customer_dash.php">Dashboard</a>

    </div>
</body>

</html>