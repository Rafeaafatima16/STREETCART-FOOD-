<?php
include("connection.php");

$message = "";

if (isset($_POST['register'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($name == "" || $email == "" || $password == "") {
        $message = "Please fill all fields!";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters!";
    } elseif (!in_array($role, ['admin', 'buyer', 'seller'], true)) {
        $message = "Invalid account type!";
    } else {

        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $message = "Email already registered!";
        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $sql = "INSERT INTO users
                    (name, email, password, role)
                    VALUES (?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "ssss",
                $name,
                $email,
                $hashedPassword,
                $role
            );

            if (mysqli_stmt_execute($stmt)) {
                $message = "Registration successful! Please login.";
            } else {
                $message = "Registration failed. Please try again.";
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
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>StreetCart - Register</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            text-align: center;
            font-family: Georgia, serif;
            background-color: #3E1F0F;
            color: #E8D5C4;
        }

        h1 {
            margin-top: 40px;
            font-size: 40px;
        }

        .register-title {
            font-size: 30px;
        }

        .box {
            width: 400px;
            max-width: 90%;
            margin: 30px auto;
            padding: 30px;
            background-color: #6F4E37;
            border: 2px solid #C19A7B;
            border-radius: 25px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
            animation: appear 0.8s;
        }

        label {
            display: block;
            text-align: left;
            margin-top: 15px;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            margin-top: 7px;
            border: 2px solid #C19A7B;
            border-radius: 12px;
            background-color: #E8D5C4;
            color: #3E1F0F;
            font-family: Georgia, serif;
            font-size: 15px;
        }

        button {
            width: 100%;
            padding: 13px;
            margin-top: 22px;
            background-color: #3E1F0F;
            border: 2px solid #C19A7B;
            border-radius: 25px;
            color: #E8D5C4;
            font-family: Georgia, serif;
            font-size: 18px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background-color: #E8D5C4;
            color: #3E1F0F;
            transform: scale(1.03);
        }

        .message {
            overflow-wrap: anywhere;
            font-size: 15px;
        }

        a {
            color: #F3D5B5;
        }

        footer {
            margin: 25px;
        }

        @keyframes appear {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <h1>Street Cart</h1>
    <h2 class="register-title">Create Account</h2>

    <div class="box">

        <h2>Join StreetCart</h2>

        <?php if ($message != "") { ?>
            <p class="message">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php } ?>

        <form method="POST" action="">

            <label>Full Name</label>
            <input
                type="text"
                name="name"
                placeholder="Enter your name"
                maxlength="100"
                required>

            <label>Email Address</label>
            <input
                type="email"
                name="email"
                placeholder="Enter your email"
                maxlength="100"
                required>

            <label>Password</label>
            <input
                type="password"
                name="password"
                placeholder="Create password"
                minlength="8"
                required>

            <label>Account Type</label>
            <select name="role" required>
                <option value="">Select User</option>
                <option value="admin">Admin</option>
                <option value="buyer">Buyer</option>
                <option value="seller">Seller</option>

            </select>

            <button type="submit" name="register">
                Register
            </button>

        </form>

        <p>Already have an account?</p>
        <a href="login.php">Login Here</a>

    </div>

    <footer>
        <p>Dil ka Rasta Street Cart se </p>
    </footer>

</body>

</html>