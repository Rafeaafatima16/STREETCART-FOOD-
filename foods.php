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

$message = $_SESSION['food_message'] ?? "";
unset($_SESSION['food_message']);

// Get this seller's cafes
$cafe_stmt = mysqli_prepare(
    $conn,
    "SELECT id, cafe_name
     FROM cafes
     WHERE owner_id = ?
     ORDER BY cafe_name"
);

mysqli_stmt_bind_param($cafe_stmt, "i", $seller_id);
mysqli_stmt_execute($cafe_stmt);
$cafes_result = mysqli_stmt_get_result($cafe_stmt);

$cafes = [];
while ($cafe = mysqli_fetch_assoc($cafes_result)) {
    $cafes[] = $cafe;
}
mysqli_stmt_close($cafe_stmt);

// Add food item
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cafe_id = (int) ($_POST['cafe_id'] ?? 0);
    $food_name = trim($_POST['food_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);

    $check = mysqli_prepare(
        $conn,
        "SELECT id FROM cafes
         WHERE id = ? AND owner_id = ?"
    );

    mysqli_stmt_bind_param($check, "ii", $cafe_id, $seller_id);
    mysqli_stmt_execute($check);
    $owned_cafe = mysqli_stmt_get_result($check);

    if ($food_name === '' || $price <= 0) {
        $message = "Enter a food name and a price greater than zero.";
    } elseif (mysqli_num_rows($owned_cafe) === 0) {
        $message = "Please select your own cafe.";
    } else {
        $insert = mysqli_prepare(
            $conn,
            "INSERT INTO foods
                (cafe_id, food_name, description, price)
             VALUES (?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $insert,
            "issd",
            $cafe_id,
            $food_name,
            $description,
            $price
        );

        if (mysqli_stmt_execute($insert)) {
            $message = "Food item added successfully!";
        } else {
            $message = "Could not add food item.";
        }

        mysqli_stmt_close($insert);
    }

    mysqli_stmt_close($check);
}

// Get this seller's food items
$food_stmt = mysqli_prepare(
    $conn,
    "SELECT
        foods.id,
        foods.food_name,
        foods.description,
        foods.price,
        foods.status,
        cafes.cafe_name
     FROM foods
     INNER JOIN cafes ON foods.cafe_id = cafes.id
     WHERE cafes.owner_id = ?
     ORDER BY foods.id DESC"
);

mysqli_stmt_bind_param($food_stmt, "i", $seller_id);
mysqli_stmt_execute($food_stmt);
$foods_result = mysqli_stmt_get_result($food_stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Foods - StreetCart</title>

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

        .card,
        .food {
            background: white;
            padding: 20px;
            margin: 18px 0;
            border-radius: 12px;
            box-shadow: 0 2px 8px #ddd;
        }

        input,
        select,
        textarea {
            display: block;
            width: 100%;
            padding: 12px;
            margin: 8px 0 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            padding: 11px 16px;
            border: none;
            border-radius: 6px;
            background: #176b45;
            color: white;
            cursor: pointer;
        }

        .delete-btn {
            background: #c62828;
        }

        .message {
            padding: 12px;
            background: #e7f5ec;
            border-radius: 6px;
        }

        a {
            color: #075ed8;
        }
    </style>
</head>

<body>
    <div class="container">

        <h1>My Food Items 🍔</h1>
        <p><a href="dashboard.php">← Back to Dashboard</a></p>

        <?php if ($message !== '') { ?>
            <p class="message">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php } ?>

        <div class="card">
            <h2>Add New Food</h2>

            <?php if (count($cafes) > 0) { ?>
                <form method="POST" action="foods.php">

                    <label for="cafe_id">Select Your Cafe</label>
                    <select name="cafe_id" id="cafe_id" required>
                        <option value="">Choose your cafe</option>

                        <?php foreach ($cafes as $cafe) { ?>
                            <option value="<?= (int) $cafe['id'] ?>">
                                <?= htmlspecialchars($cafe['cafe_name']) ?>
                            </option>
                        <?php } ?>
                    </select>

                    <label for="food_name">Food Name</label>
                    <input
                        type="text"
                        name="food_name"
                        id="food_name"
                        placeholder="e.g. Cheese Pizza"
                        required>

                    <label for="description">Description</label>
                    <textarea
                        name="description"
                        id="description"
                        placeholder="Describe your food"></textarea>

                    <label for="price">Price (₹)</label>
                    <input
                        type="number"
                        name="price"
                        id="price"
                        min="0.01"
                        step="0.01"
                        placeholder="Enter price"
                        required>

                    <button type="submit">Add Food</button>
                </form>
            <?php } else { ?>
                <p>You haven't added a cafe yet.</p>
                <a href="cafes.php">Add Your Cafe First</a>
            <?php } ?>
        </div>

        <h2>My Food List</h2>

        <?php if (mysqli_num_rows($foods_result) > 0) { ?>

            <?php while ($food = mysqli_fetch_assoc($foods_result)) { ?>
                <div class="food">

                    <h3>
                        <?= htmlspecialchars($food['food_name']) ?>
                    </h3>

                    <p>
                        <strong>Cafe:</strong>
                        <?= htmlspecialchars($food['cafe_name']) ?>
                    </p>

                    <p>
                        <strong>Price:</strong>
                        ₹<?= number_format((float) $food['price'], 2) ?>
                    </p>

                    <p>
                        <strong>Description:</strong>
                        <?= htmlspecialchars($food['description'] ?? '') ?>
                    </p>

                    <p>
                        <strong>Status:</strong>
                        <?= htmlspecialchars($food['status']) ?>
                    </p>

                    <form
                        method="POST"
                        action="delete_food.php"
                        onsubmit="return confirm('Are you sure you want to delete this food?');">
                        <input
                            type="hidden"
                            name="food_id"
                            value="<?= (int) $food['id'] ?>">

                        <button type="submit" class="delete-btn">
                            Delete Food
                        </button>
                    </form>

                </div>
            <?php } ?>

        <?php } else { ?>
            <p>No food items added yet.</p>
        <?php } ?>

    </div>
</body>

</html>

<?php
mysqli_stmt_close($food_stmt);
?>