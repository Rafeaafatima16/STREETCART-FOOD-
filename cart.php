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

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$message = $_SESSION['cart_message'] ?? '';
unset($_SESSION['cart_message']);

// Remove an item from cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $food_id = (int) ($_POST['food_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'remove' && $food_id > 0) {
        unset($_SESSION['cart'][$food_id]);
        $_SESSION['cart_message'] = "Item removed from cart.";
        header("Location: cart.php");
        exit();
    }

    if ($action === 'update' && $food_id > 0) {
        $quantity = (int) ($_POST['quantity'] ?? 1);

        if ($quantity < 1 || $quantity > 20) {
            $_SESSION['cart_message'] = "Quantity must be between 1 and 20.";
        } else {
            $_SESSION['cart'][$food_id] = $quantity;
            $_SESSION['cart_message'] = "Cart updated.";
        }

        header("Location: cart.php");
        exit();
    }
}

$cart = $_SESSION['cart'];
$items = [];
$total = 0.0;

if (!empty($cart)) {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT foods.id, foods.food_name, foods.price,
                cafes.cafe_name
         FROM foods
         INNER JOIN cafes ON foods.cafe_id = cafes.id
         WHERE cafes.status = 'approved'
           AND foods.status = 'available'
           AND foods.id = ?"
    );

    foreach ($cart as $food_id => $quantity) {
        $food_id = (int) $food_id;
        $quantity = (int) $quantity;

        mysqli_stmt_bind_param($stmt, "i", $food_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $food = mysqli_fetch_assoc($result);

        if (!$food) {
            unset($_SESSION['cart'][$food_id]);
            continue;
        }

        $food['quantity'] = $quantity;
        $food['item_total'] = (float) $food['price'] * $quantity;

        $items[] = $food;
        $total += $food['item_total'];
    }

    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - StreetCart</title>
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

        .container {
            max-width: 850px;
            margin: auto;
        }

        .item,
        .summary {
            background: white;
            padding: 18px;
            margin: 15px 0;
            border-radius: 12px;
            box-shadow: 0 2px 8px #ddd;
        }

        input {
            padding: 9px;
            width: 85px;
            margin: 6px;
        }

        button {
            padding: 9px 13px;
            border: 0;
            border-radius: 6px;
            background: #176b45;
            color: white;
            cursor: pointer;
        }

        .remove {
            background: #c62828;
        }

        .total {
            font-size: 22px;
            font-weight: bold;
        }

        a {
            color: #075ed8;
        }
    </style>
</head>

<body>
    <div class="container">

        <h1>My Shopping Cart 🛒</h1>
        <p><a href="foods.php">← Continue Shopping</a></p>
        <p><a href="customer_dash.php">Back to Dashboard</a></p>

        <?php if ($message !== '') { ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php } ?>

        <?php if (!empty($items)) { ?>

            <?php foreach ($items as $item) { ?>
                <div class="item">
                    <h3><?= htmlspecialchars($item['food_name']) ?></h3>
                    <p>Cafe: <?= htmlspecialchars($item['cafe_name']) ?></p>
                    <p>Price: ₹<?= number_format((float) $item['price'], 2) ?></p>
                    <p>Quantity: <?= (int) $item['quantity'] ?></p>
                    <p>
                        Item Total:
                        ₹<?= number_format((float) $item['item_total'], 2) ?>
                    </p>

                    <form method="POST" action="cart.php">
                        <input type="hidden" name="food_id"
                            value="<?= (int) $item['id'] ?>">
                        <input type="hidden" name="action" value="update">
                        <label>Change quantity:</label>
                        <input type="number" name="quantity"
                            min="1" max="20"
                            value="<?= (int) $item['quantity'] ?>" required>
                        <button type="submit">Update</button>
                    </form>

                    <form method="POST" action="cart.php">
                        <input type="hidden" name="food_id"
                            value="<?= (int) $item['id'] ?>">
                        <input type="hidden" name="action" value="remove">
                        <button class="remove" type="submit">Remove Item</button>
                    </form>
                </div>
            <?php } ?>

            <div class="summary">
                <p class="total">
                    Cart Total: ₹<?= number_format($total, 2) ?>
                </p>
                <p>Delivery charges, if any, are not included.</p>
                <a href="checkout.php">Proceed to Checkout →</a>
            </div>

        <?php } else { ?>
            <div class="summary">
                <h3>Your cart is empty.</h3>
                <p>Add some food from the menu first!</p>
                <a href="foods.php">Browse Food Menu</a>
            </div>
        <?php } ?>

    </div>
</body>

</html>