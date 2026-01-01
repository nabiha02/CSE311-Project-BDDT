<?php
session_start();
require_once("../db.php");

// ---------------------------------------------
// INSERT NEWS + RELATION
// ---------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $_POST["title"];
    $date = $_POST["date"];
    $content = $_POST["content"];
    $category = $_POST["category"];
    $entity_id = $_POST["entity_id"];
    $entity_name = $_POST["entity_name"];

    // Insert News
    $stmt = $conn->prepare("INSERT INTO news (N_Title, N_Publish_Date, N_Content) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $date, $content);
    if (!$stmt->execute()) { die("News insert failed: " . $stmt->error); }
    $news_id = $stmt->insert_id;
    $stmt->close();

    // Insert News Relation
    $stmt2 = $conn->prepare("INSERT INTO news_relation (news_id, category, entity_id, entity_name)
                             VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("isis", $news_id, $category, $entity_id, $entity_name);
    if (!$stmt2->execute()) { die("Relation insert failed: " . $stmt2->error); }
    $stmt2->close();

    header("Location: index.php");
    exit;
}

include("../dashboard/header.php");
?>

<style>

/* PAGE BACKGROUND */
body {
    background: linear-gradient(#eef1f5);
    transition: .35s ease-out;
    font-family: Inter, sans-serif;
}

/* FORM WRAPPER & BOX */
.form-wrapper {
    display: flex;
    justify-content: center;
    padding-top: 100px;
}

.form-box {
    width: 750px;
    background: rgba(255, 255, 255, 0.95);
    padding: 30px;
    border-radius: 14px;
    box-shadow: 
        0 4px 20px rgba(0,0,0,0.10),
        0 0 0 1px rgba(255,255,255,0.6) inset;
    backdrop-filter: blur(6px);
    transition: .35s;
}

.form-box h2 {
    text-align: center;
    margin-bottom: 20px;
    font-weight: 700;
    background: linear-gradient(90deg, #0051ff, #00d0ff);
    color: transparent;
    background-clip: text;
-webkit-background-clip: text;

}

/* INPUTS */
.form-box label {
    font-weight: 600;
    margin-top: 12px;
    display: block;
    color: #2c2c2c;
}

.form-box input,
.form-box select,
.form-box textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #c9ced8;
    border-radius: 6px;
    margin-top: 5px;
    font-size: 14px;
    transition: .25s;
    background: #fafafa;
}

.form-box input:focus,
.form-box select:focus,
.form-box textarea:focus {
    border-color: #008cff;
    box-shadow: 0 0 8px rgba(0,140,255,0.35);
    background: #fff;
}

/* BUTTON */
.form-box button {
    padding: 12px 20px;
    font-size: 15px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 22px;
    width: 100%;
    background: linear-gradient(90deg, #0048ff, #00c8ff);
    color: white;
    font-weight: 600;
    letter-spacing: 0.3px;
    transition: .3s;
    box-shadow: 0 4px 12px rgba(0,140,255,0.3);
}

.form-box button:hover {
    transform: translateY(-1px);
    box-shadow: 0 0 12px rgba(0,170,255,0.6);
}

/* TOGGLE */
.theme-toggle {
    position: fixed;
    right: 20px;
    top: 90px;
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 999999;
}

.toggle-text {
    color: #444;
    font-size: 14px;
}

/* Switch */
.switch {
    width: 52px;
    height: 28px;
    position: relative;
    cursor: pointer;
}

.switch input { display: none; }

.slider {
    position: absolute;
    inset: 0;
    background: #bfc5d0;
    border-radius: 30px;
    transition: .3s;
}

.slider::before {
    content: "";
    position: absolute;
    height: 24px;
    width: 24px;
    left: 2px;
    top: 2px;
    background: white;
    border-radius: 50%;
    transition: .3s;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

input:checked + .slider {
    background: linear-gradient(90deg, #007bff, #00e0ff);
    box-shadow: 0 0 10px rgba(0,200,255,0.7);
}

input:checked + .slider::before {
    transform: translateX(24px);
    background: #eaffff;
}

/* -----------------------------------------
   FIXED DARK MODE (clean + no leaking)
------------------------------------------*/
body.dark-mode {
    background: linear-gradient(135deg, #06080d, #0e1522) !important;
}

body.dark-mode .form-box {
    background: rgba(20, 22, 30, 0.88);
    color: #eee;
    box-shadow:
        0 4px 18px rgba(0,0,0,0.6),
        0 0 0 1px rgba(255,255,255,0.06) inset;
}

/* Labels */
body.dark-mode label {
    color: #cfd6e4;
}

/* Inputs */
body.dark-mode input,
body.dark-mode select,
body.dark-mode textarea {
    background: #141820;
    color: #eaeaea;
    border: 1px solid #2b3141;
}

body.dark-mode input:focus,
body.dark-mode select:focus,
body.dark-mode textarea:focus {
    border-color: #00c8ff;
    box-shadow: 0 0 10px rgba(0,200,255,0.45);
}

/* Button */
body.dark-mode button {
    background: linear-gradient(90deg, #007bff, #00d4ff);
    color: #fff;
    box-shadow: 0 0 10px rgba(0,150,255,0.5);
}

/* Toggle text */
body.dark-mode .toggle-text {
    color: #eaeaea;
}

/* Smooth transitions */
* {
    transition: background .25s ease, color .25s ease, border-color .25s ease, box-shadow .25s ease;
}

/* -----------------------------------------
   FINAL FIX: NO FORCED DARK BACKGROUND FOR LIGHT MODE
------------------------------------------*/
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    background: none !important;   /* ← FIXED */
}

</style>


<!DOCTYPE html>
<html>
<head>
    <title>Add News</title>
</head>
<body>

<!-- Dark Mode Toggle -->
<div class="theme-toggle">
    <label class="switch">
        <input type="checkbox" id="darkModeToggle">
        <span class="slider"></span>
    </label>
    <span class="toggle-text">Dark Mode</span>
</div>


<div class="form-wrapper">
<div class="form-box">

<h2>Add News</h2>

<form method="POST">

    <label>News Title:</label>
    <input type="text" name="title" required>

    <label>Publish Date:</label>
    <input type="date" name="date" required>

    <label>Content:</label>
    <textarea name="content" required></textarea>

    <label>Category:</label>
    <select name="category" id="category" required onchange="fetchEntities()">
        <option value="">-- Select Category --</option>
        <option value="project">Project</option>
        <option value="research">Research</option>
        <option value="innovation">Innovation</option>
        <option value="milestone">Milestone</option>
    </select>

    <label>Entity Name:</label>
    <select name="entity_id" id="entityDropdown" required></select>

    <input type="hidden" name="entity_name" id="entityNameField">

    <button type="submit">Add News</button>

</form>

</div>
</div>

<script>
// Load Entities
function fetchEntities() {
    let cat = document.getElementById("category").value;
    let dropdown = document.getElementById("entityDropdown");

    dropdown.innerHTML = "<option>Loading...</option>";

    fetch("fetch_entities.php?cat=" + cat)
    .then(response => response.json())
    .then(data => {
        dropdown.innerHTML = "";
        data.forEach(row => {
            let opt = document.createElement("option");
            opt.value = row.id;
            opt.textContent = row.title;
            opt.dataset.name = row.title;
            dropdown.appendChild(opt);
        });

        if (data.length > 0) {
            dropdown.value = data[0].id;
            document.getElementById("entityNameField").value = data[0].title;
        }
    });
}

// Sync Name
document.addEventListener("change", function (e) {
    if (e.target.id === "entityDropdown") {
        let name = e.target.options[e.target.selectedIndex].dataset.name;
        document.getElementById("entityNameField").value = name;
    }
});
</script>

<script>
// DARK MODE LOGIC
const toggle = document.getElementById("darkModeToggle");

// Load existing state
if (localStorage.getItem("darkmode") === "enabled") {
    document.body.classList.add("dark-mode");
    toggle.checked = true;
}

toggle.addEventListener("change", function () {
    if (this.checked) {
        document.body.classList.add("dark-mode");
        localStorage.setItem("darkmode", "enabled");
    } else {
        document.body.classList.remove("dark-mode");
        localStorage.setItem("darkmode", "disabled");
    }
});
</script>

</body>
</html>
