<?php
ob_start();
session_start();

include '../dashboard/header.php';
include '../db.php';
require '../vendor/autoload.php'; // PHPMailer

$register_msg = "";
$login_msg = "";


// ------------------- HANDLE REGISTRATION -------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'register') {

    $email = $_POST['email'];
    $password = $_POST['password'];
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $address = $_POST['address'];
    $role = $_POST['role'];
    $phones = $_POST['phones'];

    // Check duplicate email
    $stmt = $conn->prepare("SELECT * FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        header("Location: auth.php?register_msg=" . urlencode("Email already exists!"));
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Generate 6-digit verify code
    $verify_code = rand(100000, 999999);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (Email, Role, F_Name, M_Name, L_Name, Address, Password, verified, verify_token, Created_At)
                            VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, NOW())");
    $stmt->bind_param("ssssssss", $email, $role, $fname, $mname, $lname, $address, $hashedPassword, $verify_code);
    $stmt->execute();

    $userId = $conn->insert_id;

    // Insert phone numbers
    $phoneArray = explode(',', $phones);
    $stmtPhone = $conn->prepare("INSERT INTO user_p_num (User_ID, User_Phone_Number) VALUES (?, ?)");
    foreach($phoneArray as $phone) {
        $phone = trim($phone);
        $stmtPhone->bind_param("is", $userId, $phone);
        $stmtPhone->execute();
    }

    // Send verification code
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'nabihachaiti02@gmail.com';
    $mail->Password = 'loav dbqi wgap tpfq';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('nabihachaiti02@gmail.com', 'Bangladesh Development Tracker');
    $mail->addAddress($email);
    $mail->isHTML(true);

    $mail->Subject = 'Your Verification Code';
    $mail->Body = "
        Hi $fname,<br><br>
        Your verification code is:<br>
        <h2>$verify_code</h2>
        Enter it here:<br>
        <a href='verify.php'>Verify Account</a>
    ";

    $mail->send();

    header("Location: verify.php?email=" . urlencode($email));
    exit;
}

// ------------------- HANDLE LOGIN -------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {

    $email = $_POST['login_email'];
    $password = $_POST['login_password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if ($user) {
        if ($user['verified'] == 0) {
            $login_msg = "Please verify your email before logging in!";
        } elseif (password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['User_ID'];
            $_SESSION['user_name'] = $user['F_Name'];
            $_SESSION['user_role'] = $user['Role'];
            header("Location: ../dashboard/index.php");
            exit;
        } else {
            $login_msg = "Invalid email or password!";
        }
    } else {
        $login_msg = "Invalid email or password!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login / Register</title>
    <link rel="stylesheet" href="auth.css">
</head>

<body>
<div class="container" id="container">

    <!-- Sign Up -->
    <div class="form-container sign-up-container">
        <form method="POST">
            <h1>Create Account</h1>

            <p class="msg"><?= htmlspecialchars($register_msg) ?></p>

            <input type="text" name="fname" placeholder="First Name" required>
            <input type="text" name="mname" placeholder="Middle Name">
            <input type="text" name="lname" placeholder="Last Name" required>
            <input type="text" name="address" placeholder="Address" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="phones" placeholder="Phone numbers (comma separated)" required>

            <select name="role" required>
                <option value="">-----</option>
                <option value="Citizen">Citizen</option>
                <option value="Admin">Admin</option>
                <option value="Govt Employee">Govt Employee</option>
            </select>

            <input type="password" name="password" placeholder="Password" required>

            <button type="submit" name="action" value="register">Sign Up</button>
        </form>
    </div>

    <!-- Sign In -->
    <div class="form-container sign-in-container">
        <form method="POST">
            <h1>Sign In</h1>

            <p class="msg"><?= htmlspecialchars($login_msg) ?></p>

            <input type="email" name="login_email" placeholder="Email" required>
            <input type="password" name="login_password" placeholder="Password" required>

            <button type="submit" name="action" value="login">Sign In</button>

            <a href="forgot_password.php">Forgot Password?</a>
        </form>
    </div>

    <!-- Overlay -->
    <div class="overlay-container">
        <div class="overlay">
            <div class="overlay-panel overlay-left">
                <h1>Welcome Back!</h1>
                <p>If you already have an account, please login</p>
                <button class="ghost" id="signIn">Sign In</button>
            </div>
            <div class="overlay-panel overlay-right">
                <h1>Welcome to BDDT!</h1>
                <p>Enter your details to start your journey</p>
                <button class="ghost" id="signUp">Sign Up</button>
            </div>
        </div>
    </div>

</div>

<script src="auth.js"></script>

</body>
</html>


<?php ob_end_flush(); ?>
