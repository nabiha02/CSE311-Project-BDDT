

<?php
$msg = $_GET['msg'] ?? "Registration complete! Please check your email to verify.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Success</title>
    <style>
        /* General body */
        body {
            font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #f6f5f7;
            margin: 0;
        }

        /* Container box */
        .container {
            text-align: center;
            background: #fff;
            padding: 50px 60px;
            border-radius: 15px;
            box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
            max-width: 500px;
        }

        h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 30px;
        }

        /* Button styling */
        a {
            text-decoration: none;
            color: #fff;
            background: linear-gradient(to right, #5583f6, #6387e4);
            padding: 14px 30px;
            border-radius: 30px;
            font-weight: bold;
            transition: all 0.3s;
            display: inline-block;
        }

        a:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        /* Optional responsive */
        @media(max-width: 600px) {
            .container { padding: 40px 30px; }
            h2 { font-size: 20px; }
            a { padding: 12px 25px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($msg); ?></h2>
        <a href="auth.php">Go to Login</a>
    </div>
</body>
</html>
