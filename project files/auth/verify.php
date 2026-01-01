<?php
session_start();
include '../db.php';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $code = $_POST['code'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE Email = ? AND verify_token = ?");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if ($user) {
        // Mark verified
        $stmt = $conn->prepare("UPDATE users SET verified = 1, verify_token = NULL WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        header("Location: auth.php?login_msg=" . urlencode("Email verified! Please login."));
        exit;
    } else {
        $msg = "Invalid verification code!";
    }
}

$email = $_GET['email'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>

    <style>
        body {
            background: #f8f8fb;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .verify-box {
            width: 380px;
            padding: 50px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .verify-box h2 {
            font-size: 22px;
            margin-bottom: 20px;
        }

        .verify-box input[type="text"] {
            width: 100%;
            padding: 14px;
            border: none;
            outline: none;
            background: #f4f6f9;
            border-radius: 50px;
            margin-bottom: 15px;
            font-size: 15px;
        }

        .verify-box button {
            width: 100%;
            padding: 12px;
            background: #1b2d63;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 15px;
            cursor: pointer;
            transition: 0.2s;
        }

        .verify-box button:hover {
            background: #12204b;
        }

        .msg {
            color: red;
            margin-bottom: 10px;
            font-size: 14px;
        }
    </style>

</head>
<body>

<div class="verify-box">

    <h2>Email Verification</h2>

    <?php if ($msg): ?>
        <div class="msg"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

        <input type="text" name="code" placeholder="Enter your verification code" required>

        <button type="submit">Verify</button>
    </form>
</div>

</body>
</html>
