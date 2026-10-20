<?php
session_start();
require_once __DIR__ . '/../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}

$name = $_SESSION['name'] ?? 'Seller';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StreetCart - Seller Dashboard</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f1eb;
            margin: 0;
            padding: 25px;
        }

        h1 {
            color: #6b3e26;
        }

        .container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px;
            margin-top: 25px;
        }

        .card {
            background: white;
            padding: 22px;
            border-radius: 12px;
            text-decoration: none;
            color: #6b3e26;
            box-shadow: 0 2px 8px #ddd;
        }

        .card h3 {
            margin-top: 0;
        }

        .logout {
            display: inline-block;
            margin-top: 25px;
            color: #6b3e26;
        }
    </style>
</head>

<body>

    <h1>Welcome, <?= htmlspecialchars($name) ?>!</h1>
    <p>Manage your StreetCart cafe and food items here.</p>

    <div class="container">
        <a class="card" href="cafes.php">
            <h3>My Cafe</h3>
            <p>Manage cafe details</p>
        </a>

        <a class="card" href="foods.php">
            <h3>My Foods</h3>
            <p>Manage food items</p>
        </a>

        <a class="card" href="orders.php">
            <h3>My Orders</h3>
            <p>View customer orders</p>
        </a>
    </div>

    <a class="logout" href="../logout.php">Logout</a>

</body>

</html>