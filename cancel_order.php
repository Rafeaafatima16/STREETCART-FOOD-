
<?php
session_start();
require_once __DIR__ . '/../connection.php';

// Only logged-in buyers can cancel orders
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'buyer'
) {
    header("Location: ../login.php");
    exit;
}

// Accept POST requests only
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

// Cancel only this buyer's pending order
$sql = "UPDATE orders
        SET status = 'cancelled'
        WHERE id = ?
          AND buyer_id = ?
          AND status = 'pending'";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $buyer_id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) === 1) {
        $_SESSION['order_message'] =
            "Order #" . $order_id . " cancelled successfully.";
    } else {
        $_SESSION['order_message'] =
            "Order could not be cancelled. It may not exist, may belong to another buyer, or may no longer be pending.";
    }
} else {
    $_SESSION['order_message'] =
        "Something went wrong. Please try again.";
}

mysqli_stmt_close($stmt);

header("Location: orders.php");
exit;
?>
