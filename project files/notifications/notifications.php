<?php
session_start();
require '../db.php';

// If user not logged in, redirect
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/auth.php");
    exit;
}

$uid = $_SESSION['user_id'];

// Fetch all notifications for this user
$stmt = $conn->prepare("
    SELECT Notification_ID, Message, Created_At, is_read
    FROM notifications
    WHERE User_ID = ?
    ORDER BY Created_At DESC
");
$stmt->bind_param("i", $uid);
$stmt->execute();
$notifications = $stmt->get_result();

// Mark all as read
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <link rel="stylesheet" href="notifications.css">
</head>
<body>

<?php include("../dashboard/header.php"); ?>

<h2 class="ntitle">Notifications</h2>

<div class="notification-list">
<?php if ($notifications->num_rows > 0): ?>
    <?php while ($n = $notifications->fetch_assoc()): ?>
        <div class="notif-card <?= $n['is_read'] ? 'read' : 'unread' ?>" 
             data-id="<?= $n['Notification_ID'] ?>">
            <p class="message"><?= nl2br(htmlspecialchars($n['Message'])) ?></p>
            <span class="time"><?= htmlspecialchars($n['Created_At']) ?></span>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="empty-state">
        <p>No notifications yet.</p>
    </div>
<?php endif; ?>
</div>
<script>
document.querySelectorAll('.notif-card.unread, .notif-card.read').forEach(card => {
    card.addEventListener('click', () => {
        const id = card.dataset.id;

        // Send AJAX request to mark as read
        fetch('mark_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + id
        }).then(res => res.json())
          .then(data => {
              if(data.success) {
                  card.classList.remove('unread');
                  card.classList.add('read');

                  // Redirect after marking as read
                  window.location.href = '/bddt/document/index.php';
              } else {
                  // In case of failure, still redirect
                  window.location.href = '/bddt/document/index.php';
              }
          }).catch(() => {
              // On error, still redirect
              window.location.href = '/bddt/document/index.php';
          });
    });
});
</script>


</body>
</html>
