<?php
session_start();
require_once("../db.php");

// Admin check
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== "Admin") {
    die("Access Denied");
}

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid User ID");
}

$id = intval($_GET['id']);

// Correct table: users
$sql = "SELECT User_ID, Email, Role, F_Name, M_Name, L_Name, Address, Created_At 
        FROM users WHERE User_ID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$data = $result->fetch_assoc();

if (!$data) {
    die("User not found.");
}

include_once("../dashboard/header.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Details</title>
    <style>
        .user-box {
            max-width: 600px;
            margin: 30px auto;
            padding: 50px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            font-family: Arial;
        }
        .user-box h2 {
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .user-box p {
            font-size: 16px;
            margin: 8px 0;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 14px;
            background: #333;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
        }
        .center-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    padding-top: 120px; /* pushes box below top menu */
}
        .back-btn:hover {
            background: #555;
        }
    </style>
</head>
<body>

<div class="center-wrapper">
    <div class="user-box">
        <h2>User Details</h2>

        <p><strong>ID:</strong> <?= $data['User_ID'] ?></p>
        <p><strong>Name:</strong> <?= $data['F_Name']." ".$data['M_Name']." ".$data['L_Name'] ?></p>
        <p><strong>Email:</strong> <?= $data['Email'] ?></p>
        <p><strong>Role:</strong> <?= $data['Role'] ?></p>
        <p><strong>Address:</strong> <?= $data['Address'] ?></p>
        <p><strong>Created At:</strong> <?= $data['Created_At'] ?></p>

        <a class="back-btn" href="users_list.php">← Back to User List</a>
    </div>
</div>

</body>

</html>
