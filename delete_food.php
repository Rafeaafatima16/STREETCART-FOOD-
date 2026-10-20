
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: foods.php");
    exit();
}

$food_id = (int) ($_POST['food_id'] ?? 0);

if ($food_id <= 0) {
    header("Location: foods.php");
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    "DELETE foods FROM foods
     INNER JOIN cafes ON foods.cafe_id = cafes.id
     WHERE foods.id = ? AND cafes.owner_id = ?"
);

mysqli_stmt_bind_param($stmt, "ii", $food_id, $seller_id);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) > 0) {
    $_SESSION['food_message'] = "Food item deleted successfully!";
} else {
    $_SESSION['food_message'] = "Food item not found or you cannot delete it.";
}

mysqli_stmt_close($stmt);

header("Location: foods.php");
exit();
?>
