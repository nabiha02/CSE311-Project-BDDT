<?php
session_start();
require_once(__DIR__ . '/../db.php');

// User must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /bddt/auth/auth.php?msg=Please login first");
    exit;
}

$user_id = $_SESSION['user_id'];

// Receive form data
$fname   = trim($_POST['F_Name']);
$mname   = trim($_POST['M_Name']);
$lname   = trim($_POST['L_Name']);
$address = trim($_POST['Address']);
$phones  = trim($_POST['Phone_Numbers']); // comma separated

// ============ UPDATE MAIN USER TABLE ============ //
$update = $conn->prepare("
    UPDATE users 
    SET F_Name=?, M_Name=?, L_Name=?, Address=? 
    WHERE User_ID=?
");
$update->bind_param("ssssi", $fname, $mname, $lname, $address, $user_id);
$update->execute();

// ============ UPDATE PHONE NUMBERS ============ //

// delete old phone numbers
$del = $conn->prepare("DELETE FROM user_p_num WHERE User_ID=?");
$del->bind_param("i", $user_id);
$del->execute();

// insert new phone numbers
$phoneArray = array_filter(array_map('trim', explode(",", $phones)));

if (!empty($phoneArray)) {
    $insert = $conn->prepare("
        INSERT INTO user_p_num (User_ID, User_Phone_Number)
        VALUES (?, ?)
    ");

    foreach ($phoneArray as $p) {
        if ($p !== "") {
            $insert->bind_param("is", $user_id, $p);
            $insert->execute();
        }
    }
}

// Redirect back to profile
header("Location: profile.php?msg=Profile updated successfully");
exit;
?>
