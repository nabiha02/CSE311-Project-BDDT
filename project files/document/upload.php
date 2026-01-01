<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/auth.php");
    exit;
}
include_once(__DIR__ . "/../dashboard/header.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Upload Document</title>
    <link rel="stylesheet" href="index.css">
    <script src="upload.js" defer></script>
</head>
<body>

<div class="doc-container">
    <h2>Upload Document</h2>

    <p class="hint">Select category, choose the entity from the dropdown, add a description, and upload a file.</p>

    <form id="uploadForm" action="upload_process.php" method="POST" enctype="multipart/form-data" class="upload-form">
        <label>Category</label>
<select name="category" id="categorySelect" required>
    <option value="">-- Select category --</option>
    <option value="Project">Project</option>
    <option value="Research">Research</option>
    <option value="Innovation">Innovation</option>
    <option value="Milestone">Milestone</option>
</select>

<label>Select from below:</label>
<select name="entity_id" id="entitySelect" required>
    <option value="">-- Select --</option>
</select>

        <label>Description</label>
        <textarea name="description" rows="4" placeholder="Short description (optional)"></textarea>

        <label>Choose file</label>
        <input type="file" name="file" id="fileInput" required>

        <button type="submit">Upload</button>
    </form>
</div>

<script>
document.getElementById('categorySelect').addEventListener('change', function() {
    const category = this.value;
    const entitySelect = document.getElementById('entitySelect');

    if (!category) {
        entitySelect.innerHTML = '<option value="">-- Select entity --</option>';
        return;
    }

    fetch(`fetch_entities.php?category=${category}`)
        .then(res => res.json())
        .then(data => {
            entitySelect.innerHTML = '<option value="">-- Select entity --</option>';
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.title;
                entitySelect.appendChild(option);
            });
        });
});
</script>

</body>
</html>
