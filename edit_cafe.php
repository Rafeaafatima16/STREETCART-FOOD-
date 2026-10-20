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

$seller_id = (int) $_SESSION['user_id'];
$message = "";

$cafe_id = (int) ($_GET['id'] ?? $_POST['cafe_id'] ?? 0);

if ($cafe_id <= 0) {
    exit("Invalid cafe ID.");
}

// Load only this seller's cafe
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, cafe_name, address, phone
     FROM cafes
     WHERE id = ? AND owner_id = ?"
);
mysqli_stmt_bind_param($stmt, "ii", $cafe_id, $seller_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cafe = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$cafe) {
    exit("Cafe not found or access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cafe_name = trim($_POST['cafe_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($cafe_name === '') {
        $message = "Cafe name is required.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE cafes
             SET cafe_name = ?, address = ?, phone = ?
             WHERE id = ? AND owner_id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssii",
            $cafe_name,
            $address,
            $phone,
            $cafe_id,
            $seller_id
        );

        if (mysqli_stmt_execute($stmt)) {
            $message = "Cafe details updated successfully!";

            $cafe['cafe_name'] = $cafe_name;
            $cafe['address'] = $address;
            $cafe['phone'] = $phone;
        } else {
            $message = "Could not update cafe details.";
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
    <title>Edit Cafe - StreetCart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 20px;
        }

        .card {
            max-width: 500px;
            margin: 30px auto;
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px #ddd;
        }

        input,
        textarea {
            box-sizing: border-box;
            width: 100%;
            padding: 12px;
            margin: 8px 0 16px;
        }

        button {
            background: #176b45;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>Edit Cafe ☕</h1>

        <?php if ($message !== '') { ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php } ?>

        <form method="POST">
            <input type="hidden" name="cafe_id"
                value="<?= (int) $cafe['id'] ?>">

            <label>Cafe Name</label>
            <input type="text" name="cafe_name"
                value="<?= htmlspecialchars($cafe['cafe_name']) ?>"
                required>

            <label>Address</label>
            <textarea name="address"><?= htmlspecialchars($cafe['address'] ?? '') ?></textarea>

            <label>Phone</label>
            <input type="text" name="phone"
                value="<?= htmlspecialchars($cafe['phone'] ?? '') ?>">

            <button type="submit">Save Changes</button>
        </form>

        <p><a href="cafes.php">← Back to My Cafes</a></p>
        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</body>

</html>