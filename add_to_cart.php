
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: foods.php");
    exit();
}

$food_id = (int) ($_POST['food_id'] ?? 0);
$quantity = (int) ($_POST['quantity'] ?? 1);

if ($food_id <= 0 || $quantity < 1 || $quantity > 20) {
    $_SESSION['cart_message'] = "Please select a valid food and quantity.";
    header("Location: foods.php");
    exit();
}

// Check that food belongs to an approved cafe and is available
$stmt = mysqli_prepare(
    $conn,
    "SELECT foods.id
     FROM foods
     INNER JOIN cafes ON foods.cafe_id = cafes.id
     WHERE foods.id = ?
       AND foods.status = 'available'
       AND cafes.status = 'approved'"
);

mysqli_stmt_bind_param($stmt, "i", $food_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    $_SESSION['cart_message'] = "This food is not currently available.";
    mysqli_stmt_close($stmt);
    header("Location: foods.php");
    exit();
}

mysqli_stmt_close($stmt);

// Add quantity to cart
$current_quantity = (int) ($_SESSION['cart'][$food_id] ?? 0);

if ($current_quantity + $quantity > 20) {
    $_SESSION['cart_message'] = "Maximum quantity per food is 20.";
} else {
    $_SESSION['cart'][$food_id] = $current_quantity + $quantity;
    $_SESSION['cart_message'] = "Food added to cart successfully!";
}

header("Location: foods.php");
exit();
?>
