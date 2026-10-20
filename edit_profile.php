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
$message = "";

$stmt = mysqli_prepare(
    $conn,
    "SELECT name, email FROM users WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid name and email.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE users SET name = ?, email = ? WHERE id = ?"
        );
        mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['name'] = $name;
            $message = "Profile updated successfully!";

            $user['name'] = $name;
            $user['email'] = $email;
        } else {
            if (mysqli_stmt_errno($stmt) === 1062) {
                $message = "This email is already registered.";
            } else {
                $message = "Could not update profile. Please try again.";
            }
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
    <title>Edit Profile - StreetCart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 25px;
        }

        .card {
            max-width: 450px;
            margin: 30px auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px #ddd;
        }

        input {
            box-sizing: border-box;
            width: 100%;
            padding: 12px;
            margin: 8px 0 16px;
        }

        button {
            padding: 12px 18px;
            background: #176b45;
            color: white;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>Edit Profile</h1>

        <p><?= htmlspecialchars($message) ?></p>

        <?php if ($user) { ?>
            <form method="POST">
                <label>Name</label>
                <input
                    type="text"
                    name="name"
                    value="<?= htmlspecialchars($user['name']) ?>"
                    required>

                <label>Email</label>
                <input
                    type="email"
                    name="email"
                    value="<?= htmlspecialchars($user['email']) ?>"
                    required>

                <button type="submit">Save Changes</button>
            </form>
        <?php } else { ?>
            <p>Profile not found.</p>
        <?php } ?>

        <p><a href="profile.php">View Profile</a></p>
        <p><a href="dashboard.php">← Back to Dashboard</a></p>
    </div>
</body>

</html>