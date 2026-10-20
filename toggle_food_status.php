
<?php
session_start();
require_once __DIR__ . '/../connection.php';

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'seller'
) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: foods.php");
    exit();
}

$seller_id = (int) $_SESSION['user_id'];
$food_id = (int) ($_POST['food_id'] ?? 0);
$new_status = $_POST['new_status'] ?? '';

if (
    $food_id <= 0 ||
    !in_array($new_status, ['available', 'unavailable'], true)
) {
    $_SESSION['food_message'] = "Invalid food status.";
    header("Location: foods.php");
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    "UPDATE foods
     INNER JOIN cafes ON foods.cafe_id = cafes.id
     SET foods.status = ?
     WHERE foods.id = ? AND cafes.owner_id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "sii",
    $new_status,
    $food_id,
    $seller_id
);

if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
    $_SESSION['food_message'] = "Food status updated successfully!";
} else {
    $_SESSION['food_message'] =
        "Status unchanged, or food item not found.";
}

mysqli_stmt_close($stmt);

header("Location: foods.php");
exit();
?>
