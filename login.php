<?php
session_start();
include("connection.php");

$message = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    $sql = "SELECT * FROM users WHERE email = ? AND role = ?";
    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "ss", $email, $role);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($role == "admin") {
            header("Location: admin/dashboard.php");
            exit();
        } elseif ($role == "seller") {
            header("Location: seller/dashboard.php");
            exit();
        } elseif ($role == "buyer") {
            header("Location: buyer/customer_dash.php");
            exit();
        }
    } else {
        $message = "Invalid email, password or user type!";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Street Cart - Login</title>

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
            margin-top: 50px;
            animation: name 1s;
            color: #E8D5C4;
            font-size: 40px;
        }

        .login-title {
            margin-top: 30px;
            font-size: 32px;
        }

        p {
            color: #E8D5C4;
            font-size: 16px;
        }

        form {
            width: 400px;
            max-width: 90%;
            margin: 30px auto;
            padding: 35px;
            background-color: #6F4E37;
            border: 2px solid #C19A7B;
            border-radius: 25px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
            animation: right 1s;
        }

        table {
            width: 100%;
        }

        td {
            padding: 12px 5px;
            color: #E8D5C4;
            font-size: 16px;
        }

        td:first-child {
            text-align: left;
            width: 35%;
        }

        input,
        select {
            width: 100%;
            padding: 11px;
            border: 2px solid #C19A7B;
            border-radius: 12px;
            background-color: #E8D5C4;
            color: #3E1F0F;
            font-family: Georgia, serif;
            font-size: 15px;
        }

        input:focus,
        select:focus {
            border-color: #F3D5B5;
            outline: none;
        }

        input[type="submit"] {
            padding: 13px;
            margin-top: 10px;
            background-color: #3E1F0F;
            border: 2px solid #C19A7B;
            border-radius: 25px;
            color: #E8D5C4;
            font-size: 18px;
            cursor: pointer;
            transition: 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #E8D5C4;
            color: #3E1F0F;
            transform: scale(1.03);
        }

        .message {
            max-width: 350px;
            margin: 15px auto;
            overflow-wrap: anywhere;
        }

        a {
            color: #F3D5B5;
        }

        footer {
            margin-top: 30px;
        }

        @keyframes name {
            from {
                transform: translateY(-100px);
            }

            to {
                transform: translateY(0);
            }
        }

        @keyframes right {
            from {
                transform: translateX(100px);
            }

            to {
                transform: translateX(0);
            }
        }

        @media (max-width: 500px) {
            form {
                padding: 20px;
            }

            h1 {
                font-size: 32px;
            }

            .login-title {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>

    <h1>Street Cart</h1>
    <h1 class="login-title">Login User</h1>

    <?php if ($message != "") { ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <form method="POST" action="">

        <table>

            <tr>
                <td>Email:</td>
                <td>
                    <input type="email" name="email" required>
                </td>
            </tr>

            <tr>
                <td>Password:</td>
                <td>
                    <input type="password" name="password" required>
                </td>
            </tr>

            <tr>
                <td>User Type:</td>
                <td>
                    <select name="role" required>
                        <option value="">Select User</option>
                        <option value="admin">Admin</option>
                        <option value="seller">Seller</option>
                        <option value="buyer">Buyer</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td></td>
                <td>
                    <input type="submit" name="login" value="Login">
                </td>
            </tr>

        </table>

        <p>Don't have an account?</p>
        <a href="register.php">Register Here</a>

    </form>

    <footer>
        <p>Dil ka Rasta Street Cart se</p>
    </footer>

</body>

</html>