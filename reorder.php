
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: orders.php");
    exit;
}

$buyer_id = (int) $_SESSION['user_id'];
$order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);

if (!$order_id || $order_id < 1) {
    $_SESSION['order_message'] = "Invalid order ID.";
    header("Location: orders.php");
    exit;
}

// Verify that this order belongs to the logged-in buyer
$stmt = mysqli_prepare(
    $conn,
    "SELECT id FROM orders WHERE id = ? AND buyer_id = ?"
);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $buyer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$owned_order = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$owned_order) {
    $_SESSION['order_message'] = "Order not found.";
    header("Location: orders.php");
    exit;
}

// Get the old order's food items that are still available
$stmt = mysqli_prepare(
    $conn,
    "SELECT oi.food_id
     FROM order_items oi
     JOIN foods f ON f.id = oi.food_id
     JOIN cafes c ON c.id = f.cafe_id
     WHERE oi.order_id = ?
       AND f.status = 'available'
       AND c.status = 'approved'"
);

mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$added = 0;
$skipped = 0;

while ($item = mysqli_fetch_assoc($result)) {
    $food_id = (int) $item['food_id'];

    if (isset($_SESSION['cart'][$food_id])) {
        // Keep quantity within the cart's maximum of 20 per food
        $current_quantity = (int) $_SESSION['cart'][$food_id];

        if ($current_quantity < 20) {
            $_SESSION['cart'][$food_id] = min(20, $current_quantity + 1);
            $added++;
        } else {
            $skipped++;
        }
    } else {
        $_SESSION['cart'][$food_id] = 1;
        $added++;
    }
}

mysqli_stmt_close($stmt);

if ($added > 0) {
    $_SESSION['cart_message'] =
        $added . " item(s) added to your cart."
        . ($skipped > 0 ? " Some items were skipped because the cart limit was reached." : "");

    header("Location: cart.php");
    exit;
}

$_SESSION['order_message'] =
    "No items could be added. They may be unavailable or your cart may be full.";

header("Location: orders.php");
exit;
?>
