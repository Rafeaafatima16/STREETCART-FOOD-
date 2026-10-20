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

// Add a new cafe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cafe_name = trim($_POST['cafe_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($cafe_name === '') {
        $message = "Please enter a cafe name.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO cafes (cafe_name, owner_id, address, phone)
             VALUES (?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "siss",
            $cafe_name,
            $seller_id,
            $address,
            $phone
        );

        if (mysqli_stmt_execute($stmt)) {
            $message = "Cafe added successfully! It is pending admin approval.";
        } else {
            $message = "Could not add cafe. Please try again.";
        }

        mysqli_stmt_close($stmt);
    }
}

// Get this seller's cafes
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, cafe_name, address, phone, status, created_at
     FROM cafes
     WHERE owner_id = ?
     ORDER BY id DESC"
);

mysqli_stmt_bind_param($stmt, "i", $seller_id);
mysqli_stmt_execute($stmt);
$cafes_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cafes - StreetCart</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #222;
        }

        .container {
            max-width: 900px;
            margin: auto;
        }

        .card {
            background: white;
            padding: 22px;
            margin: 20px 0;
            border-radius: 12px;
            box-shadow: 0 2px 8px #ddd;
        }

        input,
        textarea {
            display: block;
            width: 100%;
            padding: 12px;
            margin: 8px 0 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            padding: 12px 18px;
            background: #176b45;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .edit-link {
            display: inline-block;
            padding: 9px 14px;
            background: #075ed8;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .message {
            padding: 12px;
            background: #e7f5ec;
            border-radius: 6px;
        }

        .status {
            font-weight: bold;
            text-transform: capitalize;
        }

        a.back {
            color: #075ed8;
        }
    </style>
</head>

<body>
    <div class="container">

        <h1>My Cafes ☕</h1>
        <p><a class="back" href="dashboard.php">← Back to Dashboard</a></p>

        <?php if ($message !== '') { ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php } ?>

        <div class="card">
            <h2>Add New Cafe</h2>

            <form method="POST" action="cafes.php">
                <label for="cafe_name">Cafe Name</label>
                <input
                    type="text"
                    id="cafe_name"
                    name="cafe_name"
                    placeholder="Enter cafe name"
                    required>

                <label for="address">Cafe Address</label>
                <textarea
                    id="address"
                    name="address"
                    placeholder="Enter cafe address"></textarea>

                <label for="phone">Phone Number</label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    placeholder="Enter phone number">

                <button type="submit">Add Cafe</button>
            </form>
        </div>

        <h2>My Cafe List</h2>

        <?php if (mysqli_num_rows($cafes_result) > 0) { ?>

            <?php while ($cafe = mysqli_fetch_assoc($cafes_result)) { ?>
                <div class="card">

                    <h3><?= htmlspecialchars($cafe['cafe_name']) ?></h3>

                    <p>
                        <strong>Cafe ID:</strong>
                        <?= (int) $cafe['id'] ?>
                    </p>

                    <p>
                        <strong>Address:</strong>
                        <?= htmlspecialchars($cafe['address'] ?? '') ?>
                    </p>

                    <p>
                        <strong>Phone:</strong>
                        <?= htmlspecialchars($cafe['phone'] ?? '') ?>
                    </p>

                    <p>
                        <strong>Approval Status:</strong>
                        <span class="status">
                            <?= htmlspecialchars($cafe['status']) ?>
                        </span>
                    </p>

                    <p>
                        <strong>Created:</strong>
                        <?= htmlspecialchars($cafe['created_at']) ?>
                    </p>

                    <a
                        class="edit-link"
                        href="edit_cafe.php?id=<?= (int) $cafe['id'] ?>">
                        Edit Cafe ✏️
                    </a>

                </div>
            <?php } ?>

        <?php } else { ?>
            <p>You haven't added any cafes yet.</p>
        <?php } ?>

    </div>
</body>

</html>

<?php
mysqli_stmt_close($stmt);
?>