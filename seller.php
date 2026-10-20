<?php
session_start();
require_once __DIR__ . '/../connection.php';

// Check admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get all sellers
$sql = "SELECT * FROM users WHERE role = 'seller' ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Sellers - StreetCart</title>

    <style>
        body {
            font-family: Arial;
            background: #F5EBDD;
            color: #3E1F0F;
            padding: 20px;
        }

        h1 {
            background: #3E1F0F;
            color: white;
            padding: 15px;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #C19A7B;
            text-align: left;
        }

        th {
            background: #6F4E37;
            color: white;
        }

        a {
            color: #6F4E37;
        }
    </style>
</head>

<body>

    <h1>StreetCart Admin</h1>

    <p><a href="dashboard.php">Back to Dashboard</a></p>

    <div class="container">

        <h2>Registered Sellers</h2>

        <?php
        if ($result) {
            echo "<p>Total Sellers: " . mysqli_num_rows($result) . "</p>";
        ?>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                </tr>

                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($seller = mysqli_fetch_assoc($result)) {
                ?>

                        <tr>
                            <td><?php echo (int)$seller['id']; ?></td>
                            <td><?php echo htmlspecialchars($seller['name']); ?></td>
                            <td><?php echo htmlspecialchars($seller['email']); ?></td>
                        </tr>

                <?php
                    }
                } else {
                    echo "<tr><td colspan='3'>No sellers registered yet.</td></tr>";
                }
                ?>

            </table>

        <?php
        } else {
            echo "Unable to load sellers.";
        }
        ?>

    </div>

</body>

</html>