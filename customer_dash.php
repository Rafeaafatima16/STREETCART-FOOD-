<?php
session_start();
require_once __DIR__ . '/../connection.php';

// Only buyers can access this dashboard
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'buyer'
) {
    header("Location: ../login.php");
    exit;
}

$name = $_SESSION['name'] ?? 'Buyer';
$user_id = (int) $_SESSION['user_id'];

// Count this buyer's orders
$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total_orders FROM orders WHERE buyer_id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order_data = mysqli_fetch_assoc($result);
$total_orders = (int) $order_data['total_orders'];
mysqli_stmt_close($stmt);

// Count items currently in the cart
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $quantity) {
        $cart_count += max(0, (int) $quantity);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard - StreetCart</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #263238;
        }

        header {
            background: #1677ff;
            color: white;
            padding: 22px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        header h1 {
            margin: 0;
            font-size: 26px;
        }

        header a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .container {
            max-width: 1100px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .welcome {
            background: white;
            padding: 28px;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }

        .welcome h2 {
            margin-top: 0;
            font-size: 28px;
        }

        .welcome p {
            color: #667085;
            line-height: 1.6;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px;
            margin-bottom: 30px;
        }

        .stat {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.05);
        }

        .stat h3 {
            margin: 0 0 10px;
            color: #667085;
            font-size: 15px;
        }

        .stat p {
            font-size: 28px;
            font-weight: bold;
            margin: 0;
            color: #1677ff;
        }

        .section-title {
            margin-bottom: 18px;
        }

        .menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }

        .menu-card {
            background: white;
            padding: 23px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.05);
        }

        .menu-card .emoji {
            font-size: 30px;
        }

        .menu-card h3 {
            margin: 12px 0 8px;
        }

        .menu-card p {
            color: #667085;
            line-height: 1.5;
            min-height: 42px;
        }

        .btn {
            display: inline-block;
            margin-top: 10px;
            padding: 11px 15px;
            background: #1677ff;
            color: white;
            text-decoration: none;
            border-radius: 7px;
        }

        .logout {
            background: #dc3545;
        }

        footer {
            text-align: center;
            padding: 25px;
            color: #667085;
        }

        @media (max-width: 500px) {
            .welcome h2 {
                font-size: 23px;
            }

            header h1 {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>

    <header>
        <h1>StreetCart</h1>
        <a href="../logout.php">Logout</a>
    </header>

    <div class="container">

        <section class="welcome">
            <h2>
                Welcome, <?php echo htmlspecialchars($name); ?>! 👋
            </h2>
            <p>
                Discover delicious food, manage your cart, and track your
                orders — all in one place.
            </p>

            <a class="btn" href="foods.php">Explore Food</a>
        </section>

        <section class="stats">
            <div class="stat">
                <h3>Total Orders</h3>
                <p><?php echo $total_orders; ?></p>
            </div>

            <div class="stat">
                <h3>Items in Cart</h3>
                <p><?php echo $cart_count; ?></p>
            </div>
        </section>

        <h2 class="section-title">Quick Actions</h2>

        <section class="menu">

            <div class="menu-card">
                <div class="emoji">🍔</div>
                <h3>Browse Food</h3>
                <p>Explore food available from approved cafes.</p>
                <a class="btn" href="foods.php">View Food</a>
            </div>

            <div class="menu-card">
                <div class="emoji">🛒</div>
                <h3>My Cart</h3>
                <p>Review your selected food before checkout.</p>
                <a class="btn" href="cart.php">Open Cart</a>
            </div>

            <div class="menu-card">
                <div class="emoji">📦</div>
                <h3>My Orders</h3>
                <p>Check your order history and current status.</p>
                <a class="btn" href="orders.php">View Orders</a>
            </div>

            <div class="menu-card">
                <div class="emoji">👤</div>
                <h3>My Profile</h3>
                <p>View your account information.</p>
                <a class="btn" href="profile.php">View Profile</a>
            </div>

            <div class="menu-card">
                <div class="emoji">✏️</div>
                <h3>Edit Profile</h3>
                <p>Update your name and email address.</p>
                <a class="btn" href="edit_profile.php">Edit Details</a>
            </div>

            <div class="menu-card">
                <div class="emoji">🚪</div>
                <h3>Logout</h3>
                <p>Securely sign out of your StreetCart account.</p>
                <a class="btn logout" href="../logout.php">Logout</a>
            </div>

        </section>

    </div>

    <div class="menu-card">
        <div class="emoji">🔐</div>
        <h3>Change Password</h3>
        <p>Keep your account password up to date.</p>
        <a class="btn" href="change_password.php">Change Password</a>
    </div>

    <footer>
        © <?php echo date('Y'); ?> StreetCart. All rights reserved.
    </footer>

</body>

</html>