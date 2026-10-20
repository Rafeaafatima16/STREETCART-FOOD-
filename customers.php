<?php
session_start();
require_once __DIR__ . '/../connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get customers
$sql = "SELECT * FROM users WHERE role = 'buyer'";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Customers</title>

    <style>
        body {
            font-family: Arial;
            background: #F5EBDD;
            padding: 20px;
        }

        h1 {
            color: #3E1F0F;
        }

        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid brown;
            text-align: left;
        }

        th {
            background: #6F4E37;
            color: white;
        }
    </style>
</head>

<body>

    <h1>StreetCart Admin</h1>

    <a href="dashboard.php">Back to Dashboard</a>

    <h2>Registered Customers</h2>

    <?php
    if ($result && mysqli_num_rows($result) > 0) {
    ?>

        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
            </tr>

            <?php
            while ($customer = mysqli_fetch_assoc($result)) {
            ?>

                <tr>
                    <td><?php echo $customer['id']; ?></td>

                    <td><?php echo htmlspecialchars($customer['name']); ?></td>

                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                </tr>

            <?php
            }
            ?>

        </table>

    <?php
    } else {
        echo "<p>No customers found.</p>";
    }
    ?>

</body>

</html>