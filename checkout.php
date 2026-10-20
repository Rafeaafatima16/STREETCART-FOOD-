<?php
session_start();
require_once __DIR__ . '/../connection.php';

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'buyer'
) {
    header("Location: ../login.php");
    exit();
}

$buyer_id = (int) $_SESSION['user_id'];
$message = "";

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['delivery_address'] ?? '');
    $cart = $_SESSION['cart'] ?? [];

    if ($address === '') {
        $message = "Please enter your delivery address.";
    } elseif (empty($cart)) {
        $message = "Your cart is empty.";
    } else {
        mysqli_begin_transaction($conn);

        try {
            $items = [];
            $total = 0.0;

            // Validate every cart item against current database data
            $food_stmt = mysqli_prepare(
                $conn,
                "SELECT foods.id, foods.price
                 FROM foods
                 INNER JOIN cafes ON foods.cafe_id = cafes.id
                 WHERE foods.id = ?
                   AND foods.status = 'available'
                   AND cafes.status = 'approved'"
            );

            foreach ($cart as $food_id => $quantity) {
                $food_id = (int) $food_id;
                $quantity = (int) $quantity;

                if ($food_id <= 0 || $quantity < 1 || $quantity > 20) {
                    throw new Exception("Invalid cart item or quantity.");
                }

                mysqli_stmt_bind_param($food_stmt, "i", $food_id);
                mysqli_stmt_execute($food_stmt);
                $food_result = mysqli_stmt_get_result($food_stmt);
                $food = mysqli_fetch_assoc($food_result);

                if (!$food) {
                    throw new Exception(
                        "A food item is no longer available. Please review your cart."
                    );
                }

                $price = (float) $food['price'];
                $items[] = [
                    'food_id' => $food_id,
                    'quantity' => $quantity,
                    'price' => $price
                ];

                $total += $price * $quantity;
            }

            mysqli_stmt_close($food_stmt);

            if ($total <= 0) {
                throw new Exception("Invalid order total.");
            }

            // Create order
            $order_stmt = mysqli_prepare(
                $conn,
                "INSERT INTO orders
                    (buyer_id, total_amount, delivery_address)
                 VALUES (?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $order_stmt,
                "ids",
                $buyer_id,
                $total,
                $address
            );

            if (!mysqli_stmt_execute($order_stmt)) {
                throw new Exception("Could not create the order.");
            }

            $order_id = mysqli_insert_id($conn);
            mysqli_stmt_close($order_stmt);

            // Add items to the order
            $item_stmt = mysqli_prepare(
                $conn,
                "INSERT INTO order_items
                    (order_id, food_id, quantity, price)
                 VALUES (?, ?, ?, ?)"
            );

            foreach ($items as $item) {
                mysqli_stmt_bind_param(
                    $item_stmt,
                    "iiid",
                    $order_id,
                    $item['food_id'],
                    $item['quantity'],
                    $item['price']
                );

                if (!mysqli_stmt_execute($item_stmt)) {
                    throw new Exception("Could not save order items.");
                }
            }

            mysqli_stmt_close($item_stmt);

            mysqli_commit($conn);

            unset($_SESSION['cart']);
            $_SESSION['last_order_id'] = $order_id;

            header("Location: order_success.php");
            exit();
        } catch (Throwable $e) {
            mysqli_rollback($conn);
            $message = $e->getMessage();
        }
    }
}

// Show current cart total
$total = 0.0;
$cart = $_SESSION['cart'] ?? [];

if (!empty($cart)) {
    $price_stmt = mysqli_prepare(
        $conn,
        "SELECT price FROM foods WHERE id = ?"
    );

    foreach ($cart as $food_id => $quantity) {
        $food_id = (int) $food_id;
        $quantity = (int) $quantity;

        mysqli_stmt_bind_param($price_stmt, "i", $food_id);
        mysqli_stmt_execute($price_stmt);
        $price_result = mysqli_stmt_get_result($price_stmt);
        $food = mysqli_fetch_assoc($price_result);

        if ($food && $quantity > 0) {
            $total += (float) $food['price'] * $quantity;
        }
    }

    mysqli_stmt_close($price_stmt);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - StreetCart</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 20px;
            color: #222;
        }

        .card {
            max-width: 600px;
            margin: 25px auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px #ddd;
        }

        textarea {
            box-sizing: border-box;
            width: 100%;
            padding: 12px;
            margin: 10px 0 18px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            background: #176b45;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 18px;
            cursor: pointer;
        }

        .total {
            font-size: 22px;
            font-weight: bold;
        }

        .message {
            color: #b42318;
        }

        a {
            color: #075ed8;
        }
    </style>
</head>

<body>

    <div class="card">
        <h1>Checkout 🛒</h1>

        <p><a href="cart.php">← Back to Cart</a></p>

        <?php if ($message !== '') { ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php } ?>

        <?php if (empty($cart)) { ?>
            <p>Your cart is empty. <a href="foods.php">Browse food</a></p>
        <?php } else { ?>

            <p class="total">
                Order Total: ₹<?= number_format($total, 2) ?>
            </p>

            <form method="POST" action="checkout.php">
                <label for="delivery_address">
                    Enter Delivery Address
                </label>

                <textarea
                    id="delivery_address"
                    name="delivery_address"
                    rows="4"
                    placeholder="House number, street, area, city"
                    required></textarea>

                <button type="submit">Place Order</button>
            </form>

        <?php } ?>
    </div>

</body>

</html>