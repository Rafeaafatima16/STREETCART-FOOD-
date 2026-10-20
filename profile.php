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

$user_id = (int) $_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT name, email, role FROM users WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$seller = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - StreetCart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 30px;
        }

        .profile {
            max-width: 450px;
            margin: 30px auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px #ddd;
        }

        h1 {
            color: #222;
        }

        p {
            padding: 10px;
            background: #f7f7f7;
            border-radius: 6px;
        }

        a {
            color: #075ed8;
        }
    </style>
</head>

<body>

    <div class="profile">
        <h1>My Profile 👤</h1>

        <?php if ($seller) { ?>
            <p><strong>Name:</strong>
                <?= htmlspecialchars($seller['name']) ?></p>

            <p><strong>Email:</strong>
                <?= htmlspecialchars($seller['email']) ?></p>

            <p><strong>Role:</strong>
                <?= htmlspecialchars($seller['role']) ?></p>
        <?php } else { ?>
            <p>Profile not found.</p>
        <?php } ?>

        <a href="dashboard.php">← Back to Dashboard</a>
    </div>

</body>

</html>
<?php mysqli_stmt_close($stmt); ?>