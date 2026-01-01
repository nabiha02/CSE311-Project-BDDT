<?php
session_start();
require_once("../db.php");

// Access control
if (!isset($_SESSION['user_role']) || 
   !in_array($_SESSION['user_role'], ["Admin", "Govt Employee"])) {
    die("Access Denied");
}

// Safe output helper (prevents warnings)
function h($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

// Fetch dropdown data
$sectors   = $conn->query("SELECT Sector_ID, S_Name FROM sector ORDER BY S_Name");

// Form Submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title  = $_POST['M_Title'];
    $desc   = $_POST['M_Description'];
    $budget = $_POST['M_Budget'] ?: NULL;
    $cost   = $_POST['M_Cost']   ?: NULL;
    $date   = $_POST['M_Start_Date'];
    $date   = $_POST['M_Target_Date'];
    $sector = $_POST['Sector_ID'];

    // Insert Project
    $sql = $conn->prepare("
        INSERT INTO milestone (M_Title, M_Description, M_Budget, M_Cost, M_Start_Date,  M_Target_Date, Sector_ID) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $sql->bind_param("sssssss", $title, $desc, $budget, $cost, $date, $date,  $sector);
    $sql->execute();

    $milestone_id = $sql->insert_id;

    header("Location: index.php");
    exit;
}

include("../dashboard/header.php");
?>

<style>
.form-wrapper {
    display: flex;
    justify-content: center;
    padding-top: 150px;
}
.form-box {
    width: 700px;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    font-family: Inter, sans-serif;
}
.form-box h2 {
    text-align: center;
    margin-bottom: 20px;
}
.form-box label {
    font-weight: 600;
    margin-top: 12px;
    display: block;
}
.form-box input, 
.form-box select, 
.form-box textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    margin-top: 5px;
}
.form-box button {
    width: 100%;
    padding: 12px;
    margin-top: 25px;
    font-size: 16px;
    background: #333;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
.form-box button:hover {
    background: black;
}
</style>

<div class="form-wrapper">
    <div class="form-box">

        <h2>Add New Milestone</h2>

        <form method="POST">

            <label>Milestone Title</label>
            <input type="text" name="M_Title" required>

            <label>Description</label>
            <textarea name="M_Description" rows="4"></textarea>

            <label>Budget</label>
            <input type="text"    name="M_Budget">

            <label>Cost</label>
            <input type="text" name="M_Cost">

            
            <label>Start Date</label>
            <input type="date" name="M_Start_Date">
            
            <label>Targetted Date</label>
            <input type="date" name="M_Target_Date">

            
            <label>Sector</label>
            <select name="Sector_ID" required>
                <option value="">Select Sector</option>
                <?php while ($s = $sectors->fetch_assoc()): ?>
                    <option value="<?= $s['Sector_ID'] ?>"><?= h($s['S_Name']) ?></option>
                <?php endwhile; ?>
            </select>

           
            </select>
           <label>Milestone Status:</label><br>
    <select name="R_Status" required>
        <option value="Active">Active</option>
        <option value="On Track">On Track</option>
        <option value="Completed">Completed</option>
        <option value="Cancelled">Cancelled</option>
    </select>
    <br><br>

            <button type="submit">Submit Milestone</button>

        
        </form>

    </div>
</div>
