<?php
session_start();
include '../db.php';

if (!isset($_SESSION['reset_user'])) {
    die("Invalid session. Start again.");
}

$userid = $_SESSION['reset_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newpass = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE Users SET Password=?, verify_token=NULL WHERE User_ID=?");
    $stmt->bind_param("si", $newpass, $userid);

    if ($stmt->execute()) {
        unset($_SESSION['reset_user']);
        echo "<p style='font-family:Arial;font-size:18px;'>Password reset successful! <a href='auth.php'>Login</a></p>";
        exit();
    } else {
        $msg = "Error resetting password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reset Password</title>

<style>
body {
    margin:0; padding:0;
    background:#f5f7fa; font-family:Arial;
    height:100vh; display:flex;
    justify-content:center; align-items:center;
}

.container {
    width:360px; padding:30px; background:white;
    border-radius:12px; box-shadow:0 6px 20px rgba(0,0,0,0.15);
    text-align:center;
}

input {
    width:100%; padding:12px;
    margin-bottom:15px; border-radius:8px;
    border:1px solid #ccc;
}

button {
    width:100%; padding:12px;
    background:#4a90e2; color:white;
    border:none; border-radius:8px;
    cursor:pointer;
}

button:hover { background:#3c7cc3; }

.msg {
    margin-top: 10px;
    background: #f9e0e0;
    color: #b33a3a;
    padding: 10px; border-left: 4px solid #b33a3a;
    border-radius: 6px;
}
</style>

</head>
<body>

<div class="container">
    <h2>Reset Password</h2>

    <form method="POST">
        <input type="password" name="password" placeholder="New Password" required>
        <button type="submit">Reset Password</button>
    </form>

    <?php if (!empty($msg)): ?>
        <div class="msg"><?php echo $msg; ?></div>
    <?php endif; ?>
</div>

</body>
</html>
