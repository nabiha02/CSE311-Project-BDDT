<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . "/../dashboard/header.php");
require_once(__DIR__ . "/../db.php");

//all documents
$sql = "SELECT d.*, u.Full_Name 
        FROM documents d
        JOIN users u ON d.User_ID = u.User_ID
        ORDER BY d.D_Uploaded_At DESC";

$result = $conn->query($sql);
?>

<div class="container mt-4">
    <h2>Document Approval Panel</h2>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Description</th>
                <th>File</th>
                <th>Uploaded</th>
                <th>Status</th>
                <th width="200px">Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['Document_ID'] ?></td>
                    <td><?= htmlspecialchars($row['Full_Name']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['D_Description'])) ?></td>

                    <td>
                        <a href="../<?= $row['File_Path'] ?>" target="_blank">
                            <?= htmlspecialchars($row['File_Name']) ?>
                        </a>
                    </td>

                    <td><?= $row['D_Uploaded_At'] ?></td>

                    <td>
                        <?php 
                        if ($row['Status'] == "Pending") echo "<span class='badge bg-warning'>Pending</span>";
                        if ($row['Status'] == "Approved") echo "<span class='badge bg-success'>Approved</span>";
                        if ($row['Status'] == "Rejected") echo "<span class='badge bg-danger'>Rejected</span>";
                        ?>
                    </td>

                    <td>
                        <?php if ($row['Status'] == "Pending"): ?>
                            <a href="approve_document.php?id=<?= $row['Document_ID'] ?>" class="btn btn-success btn-sm">
                                Approve
                            </a>

                            
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal<?= $row['Document_ID'] ?>">
                                Reject
                            </button>

                            
                            <div class="modal fade" id="rejectModal<?= $row['Document_ID'] ?>">
                                <div class="modal-dialog">
                                    <form method="POST" action="reject_document.php?id=<?= $row['Document_ID'] ?>">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reject Document</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                <textarea class="form-control" name="reason" placeholder="Reason for rejection" required></textarea>
                                            </div>

                                            <div class="modal-footer">
                                                <button class="btn btn-danger">Confirm Reject</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        <?php else: ?>
                            <i>No action available</i>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>

    </table>
</div>
