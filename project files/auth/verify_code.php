<?php
session_start();
include '../db.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];

    $stmt = $conn->prepare("SELECT User_ID FROM Users WHERE verify_token=?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $_SESSION['reset_user'] = $user['User_ID'];
        header("Location: reset_password.php");
        exit();
    } else {
        $msg = "Invalid code!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Verify Code</title>

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
    <h2>Enter Reset Code</h2>

    <form method="POST">
        <input type="text" name="code" placeholder="Enter 6-digit code" required>
        <button type="submit">Verify</button>
    </form>

    <?php if ($msg): ?>
        <div class="msg"><?php echo $msg; ?></div>
    <?php endif; ?>
</div>

</body>
</html>
