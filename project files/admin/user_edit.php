<?php
session_start();
require_once("../db.php");

// Access control
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== "Admin") {
    die("Access Denied");
}

$id = intval($_GET['id']);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $f = $_POST['F_Name'];
    $m = $_POST['M_Name'];
    $l = $_POST['L_Name'];
    $email = $_POST['Email'];
    $address = $_POST['Address'];
    $role = $_POST['Role'];

    $q = $conn->prepare("UPDATE user SET F_Name=?, M_Name=?, L_Name=?, Email=?, Address=?, Role=? WHERE User_ID=?");
    $q->bind_param("ssssssi", $f, $m, $l, $email, $address, $role, $id);
    $q->execute();

    header("Location: users_list.php?msg=updated");
    exit;
}

// Fetch user info
$sql = "SELECT * FROM users WHERE User_ID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

include("../dashboard/header.php");
?>

<style>
.edit-wrapper {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding-top: 140px;
}

.edit-box {
    width: 550px;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    font-family: Inter, sans-serif;
}

.edit-box h2 {
    margin-bottom: 15px;
}

.edit-box label {
    font-weight: 600;
}

.edit-box input, .edit-box select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    margin-top: 5px;
}

.edit-box button {
    margin-top: 15px;
    padding: 12px 20px;
    background: #333;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 15px;
}

.edit-box button:hover {
    background: #000;
}

.back-link {
    display: inline-block;
    margin-top: 15px;
    text-decoration: none;
    font-size: 15px;
}
</style>

<div class="edit-wrapper">
    <div class="edit-box">

        <h2>Edit User</h2>

        <form method="POST">
            <label>First Name:</label>
            <input type="text" name="F_Name" value="<?= $data['F_Name'] ?>">

            <label>Middle Name:</label>
            <input type="text" name="M_Name" value="<?= $data['M_Name'] ?>">

            <label>Last Name:</label>
            <input type="text" name="L_Name" value="<?= $data['L_Name'] ?>">

            <label>Email:</label>
            <input type="email" name="Email" value="<?= $data['Email'] ?>">

            <label>Address:</label>
            <input type="text" name="Address" value="<?= $data['Address'] ?>">

            <label>Role:</label>
            <select name="Role">
                <option <?= $data['Role']=="Citizen" ? "selected" : "" ?>>Citizen</option>
                <option <?= $data['Role']=="Govt. Employee" ? "selected" : "" ?>>Govt. Employee</option>
                <option <?= $data['Role']=="Admin" ? "selected" : "" ?>>Admin</option>
            </select>

            <button type="submit">Update User</button>
        </form>

        <a class="back-link" href="users_list.php">← Back to User List</a>

    </div>
</div>
