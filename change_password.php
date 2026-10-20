<?php
session_start();
require_once __DIR__ . '/../connection.php';

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'buyer'
) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (
        $current_password === '' ||
        $new_password === '' ||
        $confirm_password === ''
    ) {
        $message = "Please fill in all fields.";
        $message_type = "error";
    } elseif (strlen($new_password) < 8) {
        $message = "New password must be at least 8 characters.";
        $message_type = "error";
    } elseif ($new_password !== $confirm_password) {
        $message = "New passwords do not match.";
        $message_type = "error";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT password FROM users WHERE id = ? AND role = 'buyer'"
        );

        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$user || !password_verify($current_password, $user['password'])) {
            $message = "Current password is incorrect.";
            $message_type = "error";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE users SET password = ? WHERE id = ? AND role = 'buyer'"
            );

            mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);

            if (
                mysqli_stmt_execute($stmt) &&
                mysqli_stmt_affected_rows($stmt) === 1
            ) {
                $message = "Password changed successfully!";
                $message_type = "success";
            } else {
                $message = "Password was not changed. Please try again.";
                $message_type = "error";
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - StreetCart</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #263238;
        }

        .container {
            max-width: 500px;
            margin: 40px auto;
            padding: 28px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
        }

        .subtitle {
            color: #667085;
            line-height: 1.5;
        }

        label {
            display: block;
            margin: 18px 0 8px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccd3dd;
            border-radius: 7px;
            font-size: 15px;
        }

        button,
        .btn {
            display: inline-block;
            padding: 12px 16px;
            border: none;
            border-radius: 7px;
            background: #1677ff;
            color: white;
            text-decoration: none;
            font-size: 15px;
            cursor: pointer;
        }

        .message {
            padding: 12px;
            margin: 15px 0;
            border-radius: 7px;
        }

        .success {
            background: #e8f5e9;
            color: #176b36;
        }

        .error {
            background: #ffebee;
            color: #b42318;
        }

        .buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 24px;
        }

        .secondary {
            background: #263238;
        }
    </style>
</head>

<body>
    <div class="container">

        <h1>Change Password 🔐</h1>
        <p class="subtitle">
            Enter your current password and choose a new password.
        </p>

        <?php if ($message !== ''): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <label for="current_password">Current Password</label>
            <input
                type="password"
                id="current_password"
                name="current_password"
                autocomplete="current-password"
                required>

            <label for="new_password">New Password</label>
            <input
                type="password"
                id="new_password"
                name="new_password"
                minlength="8"
                autocomplete="new-password"
                required>

            <label for="confirm_password">Confirm New Password</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="8"
                autocomplete="new-password"
                required>

            <div class="buttons">
                <button type="submit">Update Password</button>
                <a class="btn secondary" href="customer_dash.php">
                    Dashboard
                </a>
            </div>

        </form>

    </div>
</body>

</html>