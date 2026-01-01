<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/../db.php';

$unread_count = 0;
if(isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM notifications WHERE User_ID = ? AND is_read = 0");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $unread_count = $result['cnt'];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BDDT Dashboard</title>

    <!-- CSS -->
    <link rel="stylesheet" href="/bddt/dashboard/dashboard.css">

    <!-- JS -->
    <script src="/bddt/dashboard/dashboard.js" defer></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <!-- GLOBAL DARK MODE -->
<link rel="stylesheet" href="/bddt/assets/darkmode.css">     <!-- 🔵 -->
<script src="/bddt/assets/darkmode.js" defer></script>       <!-- 🔵 -->

</head>
<body>

<header class="navbar">
    <nav class="nav-container">
        
        <!-- LOGO -->
        
        <div class="logo">
    <img src="../Untitled_design-removebg-preview.png" alt="BDDT Logo" class="logo-img" />
    <a href="/bddt/dashboard/index.php">BDDT</a>
</div>


        <!-- NAV LINKS -->
        <ul class="nav-links">

            <li><a href="/bddt/dashboard/index.php">Homepage</a></li>

            <!-- PROJECT -->
            <li class="dropdown">
                <a href="/bddt/project/index.php">Project <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <a href="/bddt/project/index.php">All Project</a>
                    <a href="/bddt/project/index.php?status=On Track">On Track</a>
                    <a href="/bddt/project/index.php?status=Active">Active</a>
                    <a href="/bddt/project/index.php?status=Completed">Completed</a>
                    <a href="/bddt/project/index.php?status=Cancelled">Cancelled</a>
                    <a href="/bddt/project/add.php">Add a new project</a>
                </div>
            </li>

            <!-- RESEARCH -->
            <li class="dropdown">
                <a href="/bddt/research/index.php">Research <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <a href="/bddt/research/index.php">All Research</a>
                    <a href="/bddt/research/index.php?status=On Track">On Track</a>
                    <a href="/bddt/research/index.php?status=Active">Active</a>
                    <a href="/bddt/research/index.php?status=Completed">Completed</a>
                    <a href="/bddt/research/index.php?status=Cancelled">Cancelled</a>
                    <a href="/bddt/research/add.php">Add a new research</a>
                </div>
            </li>

            <!-- INNOVATION -->
            <li class="dropdown">
                <a href="/bddt/innovation/index.php">Innovation <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <a href="/bddt/innovation/index.php">All Innovation</a>
                    <a href="/bddt/innovation/index.php?status=On Track">On Track</a>
                    <a href="/bddt/innovation/index.php?status=Active">Active</a>
                    <a href="/bddt/innovation/index.php?status=Completed">Completed</a>
                    <a href="/bddt/innovation/index.php?status=Cancelled">Cancelled</a>
                   <a href="/bddt/innovation/add.php">Add a new innovation</a>
                </div>
            </li>

          
            <li class="dropdown">
                <a href="/bddt/milestone/index.php">Milestone <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <a href="/bddt/milestone/index.php">All Milestone</a>
                   <a href="/bddt/milestone/add.php">Add a new milestone</a>  </div>
            </li>
             <li class="dropdown">
            <a href="/bddt/news/index.php">News<i class="fas fa-caret-down"></i></a>
            <div class="dropdown-content">
               <a href="/bddt/news/index.php">All News</a>
              <a href="/bddt/news/add.php">Add a news</a>  </div>
            </li>
            <!-- SECTOR -->
            <li class="dropdown">
                <a href="#">Sector <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">

                    <a href="/bddt/sector/index.php?S_Name=Airports">Airports</a>
                    <a href="/bddt/sector/index.php?S_Name=Bridges">Bridges</a>
                    <a href="/bddt/sector/index.php?S_Name=Road and highways">Road and highways</a>
                    <a href="/bddt/sector/index.php?S_Name=Railways">Railways</a>
                    <a href="/bddt/sector/index.php?S_Name=Energy Plants">Energy Plants</a>
                    <a href="/bddt/sector/index.php?S_Name=Ports">Ports</a>
                    <a href="/bddt/sector/index.php?S_Name=Defense">Defense</a>
                    <a href="/bddt/sector/index.php?S_Name=Buildings and housing">Buildings and housing</a>
                    <a href="/bddt/sector/index.php?S_Name=Sports">Sports</a>
                    <a href="/bddt/sector/index.php?S_Name=Barrages">Barrages</a>
                    <a href="/bddt/sector/index.php?S_Name=Delta Plan">Delta Plan</a>
                    <a href="/bddt/sector/index.php?S_Name=Satellites">Satellites</a>
                    <a href="/bddt/sector/index.php?S_Name=Economic Zone">Economic Zone</a>
                    <a href="/bddt/sector/index.php?S_Name=Technology">Technology</a>
                    <a href="/bddt/sector/index.php?S_Name=Field and Water">Field and Water</a>
                    <a href="/bddt/sector/index.php?S_Name=Education">Education</a>
                    <a href="/bddt/sector/index.php?S_Name=Business">Business</a>
                    <a href="/bddt/sector/index.php?S_Name=Public Health">Public Health</a>
                    <a href="/bddt/sector/index.php?S_Name=Land">Land</a>
                    <a href="/bddt/sector/index.php?S_Name=Tax">Tax</a>
                    <a href="/bddt/sector/index.php?S_Name=Agriculture">Agriculture</a>
                    <a href="/bddt/sector/index.php?S_Name=Business & Industry">Business & Industry</a>
                    <a href="/bddt/sector/index.php?S_Name=Environment & Forest">Environment & Forest</a>
                    <a href="/bddt/sector/index.php?S_Name=Urban Development">Urban Development</a>
                    <a href="/bddt/sector/index.php?S_Name=Electricity">Electricity</a>
                    <a href="/bddt/sector/index.php?S_Name=Gas & Petroleum">Gas & Petroleum</a>

                </div>
            </li>

            <!-- EXTRAS -->
            <li class="dropdown">
                <a href="#">Extras <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">

                    <a href="/bddt/document/index.php">Document</a>
                    <a href="/bddt/feedback/my_feedbacks.php">Feedbacks</a>
                    <a href="#">Emergency's</a>
                    <a href="#">About us</a>
                    

                    <!-- ADMIN ONLY -->
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin'): ?>

                        <a href="/bddt/admin/users_list.php">
                            <i class="fas fa-users"></i> Users List
                        </a>

                        <a href="/bddt/admin/feedback_list.php">
                            <i class="fas fa-users"></i> Feedbacks List
                        </a>

                    <?php endif; ?>

                </div>
            </li>

        </ul>

        <!-- RIGHT SIDE: SEARCH + PROFILE -->
        <div class="nav-actions">

           <form class="search-form" action="../search/search.php" method="GET">
    <input type="text" name="q" placeholder="Search anything..." autocomplete="off">
    <button type="submit"><i class="fas fa-search"></i></button>
    <div id="liveResults" class="live-results"></div>
</form>

            

            <div class="toggleWrapper">
    <input type="checkbox" id="darkModeToggle">

    <label class="toggle" for="darkModeToggle">
        <span class="toggle__handler">
            <span class="crater crater--1"></span>
            <span class="crater crater--2"></span>
            <span class="crater crater--3"></span>
        </span>

        <span class="star star--1"></span>
        <span class="star star--2"></span>
        <span class="star star--3"></span>
        <span class="star star--4"></span>
        <span class="star star--5"></span>
        <span class="star star--6"></span>
    </label>
</div>



            <div class="profile-dropdown">
                <a href="javascript:void(0);" class="user-icon">
                    <i class="fas fa-user-circle fa-2x"></i>
                </a>
                <div class="profile-dropdown-content">
                    <a href="/bddt/auth/auth.php"><i class="fas fa-sign-in-alt"></i> Sign In / Sign Up</a>
                    <a href="/bddt/profile/profile.php"><i class="fas fa-user"></i> Your Profile</a>
                    <a href="/bddt/notifications/notifications.php" class="notif-link">
    <i class="fas fa-bell"></i> Notifications
    <?php if($unread_count > 0): ?>
        <span class="notif-badge"><?= $unread_count ?></span>
    <?php endif; ?>
</a>

                </div>
            </div>

        </div>

        

    </nav>
</header>
