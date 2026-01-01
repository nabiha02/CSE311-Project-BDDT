<?php
session_start();
require_once(__DIR__ . '/../db.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /bddt/auth/auth.php?msg=Please login first");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch main user data
$stmt = $conn->prepare("SELECT * FROM users WHERE User_ID=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch phone numbers
$pstmt = $conn->prepare("SELECT User_Phone_Number FROM user_p_num WHERE User_ID=?");
$pstmt->bind_param("i", $user_id);
$pstmt->execute();
$phones = $pstmt->get_result();

// Convert phone numbers to comma string
$phoneList = [];
while ($p = $phones->fetch_assoc()) {
    $phoneList[] = $p['User_Phone_Number'];
}
$phoneString = implode(", ", $phoneList);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="profile.css">
    <script src="https://unpkg.com/feather-icons"></script>
</head>

<body>

<?php include_once(__DIR__ . '/../dashboard/header.php'); ?>

<div class="profile-container">

    <!-- ==================== SIDEBAR ==================== -->
    <div class="sidebar">
        <div class="avatar">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['F_Name']) ?>&background=5583f6&color=fff" alt="User Avatar">
            <h3><?= htmlspecialchars($user['F_Name']) ?></h3>
            <p><?= htmlspecialchars($user['Role']) ?></p>
        </div>

        <ul class="menu">
    <li class="active" data-section="overview"><i data-feather="user"></i> Profile Overview</li>
    <li data-section="edit"><i data-feather="edit"></i> Edit Profile</li>
    <li id="logout-btn"><i data-feather="log-out"></i> Logout</li>
</ul>

    </div>

    <!-- ==================== MAIN CONTENT ==================== -->
    <div class="profile-details">

        <h2 id="section-title">Profile Information</h2>

        <!-- ========= OVERVIEW SECTION ========= -->
        <div id="overview-section" class="info-card">

            <h3><i data-feather="info"></i> Personal Details</h3>

            <div class="info-row">
                <label>Full Name</label>
                <span><?= htmlspecialchars($user['F_Name']." ".$user['M_Name']." ".$user['L_Name']); ?></span>
            </div>

            <div class="info-row">
                <label>Email</label>
                <span><?= htmlspecialchars($user['Email']); ?></span>
            </div>

           <div class="info-row">
    <label>Role</label>
    <span>
        <?php 
            $role = strtolower($user['Role']);
            $color = "#777"; // default gray

            if ($role === "admin") $color = "#ff4d4d";              // Red
            if ($role === "citizen") $color = "#3b82f6";            // Blue
            if ($role === "govt employee" || $role === "government employee") 
                $color = "#22c55e";                                 // Green

            echo "<span style='
                background: {$color}20;  /* light background */
                color: {$color};
                padding: 6px 12px;
                border-radius: 8px;
                font-weight: 600;
                text-transform: capitalize;
            '>" . htmlspecialchars($user['Role']) . "</span>";
        ?>
    </span>
</div>


            <div class="info-row">
                <label>Address</label>
                <span><?= htmlspecialchars($user['Address']); ?></span>
            </div>

            <div class="info-row">
                <label>Phone Numbers</label>
                <span><?= nl2br(htmlspecialchars($phoneString)); ?></span>
            </div>

            <div class="info-row">
                <label>Created At</label>
                <span><?= htmlspecialchars(date("F d, Y h:i A", strtotime($user['Created_At']))); ?></span>
            </div>

        </div>

        <!-- ========= EDIT PROFILE SECTION ========= -->
        <div id="edit-section" class="info-card" style="display:none;">

            <h3><i data-feather="edit"></i> Edit Profile</h3>

            <form action="update_profile.php" method="POST">

                <label>First Name</label>
                <input type="text" name="F_Name" value="<?= htmlspecialchars($user['F_Name']); ?>" required>

                <label>Middle Name</label>
                <input type="text" name="M_Name" value="<?= htmlspecialchars($user['M_Name']); ?>">

                <label>Last Name</label>
                <input type="text" name="L_Name" value="<?= htmlspecialchars($user['L_Name']); ?>">

                <label>Address</label>
                <input type="text" name="Address" value="<?= htmlspecialchars($user['Address']); ?>">

                <label>Phone Numbers (comma separated)</label>
                <input type="text" name="Phone_Numbers" value="<?= htmlspecialchars($phoneString); ?>">

                <button type="submit" class="save-btn">Save Changes</button>
            </form>

        </div>

    </div>
</div>

<!-- ==================== JS ==================== -->
<script src="profile.js"></script>
<script>feather.replace();</script>

</body>
</html>
