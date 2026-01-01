<?php
include '../dashboard/header.php';
include '../db.php';
require '../vendor/autoload.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    
    $stmt = $conn->prepare("SELECT User_ID FROM Users WHERE Email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Generate 6 digit code
        $code = random_int(100000, 999999);

        // Store code in database
        $stmt = $conn->prepare("UPDATE Users SET verify_token=? WHERE User_ID=?");
        $stmt->bind_param("si", $code, $user['User_ID']);
        $stmt->execute();

        // Send email
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'nabihachaiti02@gmail.com';
            $mail->Password = 'loav dbqi wgap tpfq';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('nabihachaiti02@gmail.com', 'BDDT');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Your Password Reset Code';
            $mail->Body = "Your reset code is: <b>$code</b>";

            $mail->send();
            $msg = "A reset code has been sent to your email.";
        } catch (Exception $e) {
            $msg = "Mailer Error: {$mail->ErrorInfo}";
        }

    } else {
        $msg = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: #fff;
            padding: 35px;
            width: 360px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        h2 {
            margin-bottom: 15px;
        }

        form input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        form button {
            width: 100%;
            padding: 12px;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        form button:hover {
            background: #155fc0;
        }

        .msg {
            margin-top: 15px;
            padding: 10px;
            border-radius: 6px;
            background: #e7f3ff;
            color: #0b53c6;
        }

        a {
            display: block;
            margin-top: 15px;
            color: #1a73e8;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Forgot Password</h2>

        <form method="POST">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Send Code</button>
        </form>

        <?php if($msg): ?>
            <div class="msg"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <a href="verify_code.php">Already have a code?</a>
    </div>
</body>
</html>
