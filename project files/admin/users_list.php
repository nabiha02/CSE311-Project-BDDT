<?php
session_start();

// Include DB connection
include_once(__DIR__ . "/../dashboard/header.php");
require_once __DIR__ . "/../db.php";

// Admin check
if (!isset($_SESSION['user_role'])) {
    die("Access Denied: No session role found.");
}

if ($_SESSION['user_role'] !== "Admin") {
    die("Access Denied: Admins only.");
}

// Fetch all users
$sql = "SELECT User_ID, Email, Role, F_Name, M_Name, L_Name, Address, Created_At 
        FROM users ORDER BY User_ID ASC";

$result = $conn->query($sql);

if (!$result) {
    echo "<pre>SQL ERROR: " . $conn->error . "</pre>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User List</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #333; color: white; }
        a { color: blue; }
    </style>
</head>
<body>

<h1>User List</h1>

<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Actions</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['User_ID'] ?></td>
            <td><?= $row['F_Name'] . " " . $row['M_Name'] . " " . $row['L_Name'] ?></td>
            <td><?= $row['Email'] ?></td>
            <td><?= $row['Role'] ?></td>
            <td>
                <a href="user_view.php?id=<?= $row['User_ID'] ?>">View</a> |
                <a href="user_edit.php?id=<?= $row['User_ID'] ?>">Edit</a> |
                <a href="delete_user.php?id=<?= $row['User_ID'] ?>" onclick="return confirm('Delete this user?')">Delete</a> |
                <?php if ($row['Role'] !== 'Admin'): ?>
                    <a href="make_admin.php?id=<?= $row['User_ID'] ?>">Make Admin</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
