<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/auth.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? "User";

// Validate POST
$category   = $_POST['category'] ?? "";
$entity_id  = $_POST['entity_id'] ?? "";
$description = trim($_POST['description'] ?? "");

if ($category === "" || $entity_id === "") {
    die("Missing required fields.");
}

// Fetch entity_name based on category and entity_id
$entity_name = '';
switch ($category) {
    case 'Project':
        $stmt = $conn->prepare("SELECT P_Title FROM project WHERE Project_ID = ? LIMIT 1");
        break;
    case 'Research':
        $stmt = $conn->prepare("SELECT R_Title FROM research WHERE Research_ID = ? LIMIT 1");
        break;
    case 'Innovation':
        $stmt = $conn->prepare("SELECT I_Title FROM innovation WHERE Innovation_ID = ? LIMIT 1");
        break;
    case 'Milestone':
        $stmt = $conn->prepare("SELECT M_Title FROM milestone WHERE Milestone_ID = ? LIMIT 1");
        break;
}

if (isset($stmt)) {
    $stmt->bind_param("i", $entity_id);
    $stmt->execute();
    $stmt->bind_result($entity_name);
    $stmt->fetch();
    $stmt->close();
}

// FILE CHECK
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== 0) {
    die("File upload error.");
}

$file = $_FILES['file'];
$fileName = time() . "_" . basename($file['name']);
$fileType = $file['type'];
$fileTmp  = $file['tmp_name'];

$uploadDir = __DIR__ . "/files/";
$relativePath = "files/" . $fileName;

// Move uploaded file
if (!move_uploaded_file($fileTmp, $uploadDir . $fileName)) {
    die("Failed to upload file.");
}

// INSERT: document
$stmt = $conn->prepare("
    INSERT INTO document (
        User_ID, 
        D_Description, 
        D_Uploaded_At, 
        File_Type, 
        File_Path, 
        File_Name, 
        Status
    ) VALUES (?, ?, NOW(), ?, ?, ?, 'Pending')
");

$stmt->bind_param(
    "issss",
    $user_id,
    $description,
    $fileType,
    $relativePath,
    $fileName
);

$stmt->execute();
$docId = $stmt->insert_id;
$stmt->close();

// INSERT: document_relation
$rel = $conn->prepare("
    INSERT INTO document_relation (document_id, category, entity_id, entity_name)
    VALUES (?, ?, ?, ?)
");

$rel->bind_param(
    "isis",
    $docId,
    $category,
    $entity_id,
    $entity_name
);

$rel->execute();
$rel->close();

// NOTIFICATIONS
// Notify all admins
$adminNotify = $conn->prepare("
    INSERT INTO notifications (User_ID, Message, Created_At, is_read)
    SELECT User_ID, ?, NOW(), 0 
    FROM users 
    WHERE Role = 'Admin'
");

$adminMsg = "A new document has been uploaded and is waiting for approval.";
$adminNotify->bind_param("s", $adminMsg);
$adminNotify->execute();

// Notify govt employee (info)
if ($role === "Govt Employee") {

    $empMessage = "Hello {$_SESSION['f_name']}, 
Thank you for your contribution to BDDT. Your submission has been sent for processing.";

    $empNotify = $conn->prepare("
        INSERT INTO notifications (User_ID, Message, Created_At, is_read)
        VALUES (?, ?, NOW(), 0)
    ");

    $empNotify->bind_param("is", $user_id, $empMessage);
    $empNotify->execute();
}

// Redirect after success
header("Location: index.php?success=1");
exit;
?>
