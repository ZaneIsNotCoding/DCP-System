<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/clearance_engine.php'; // FIXED
include 'layout.php';
// AUTH CHECK
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

// =====================
// HANDLE APPROVE / REJECT
// =====================
if (isset($_GET['action']) && isset($_GET['id'])) {

    $id = $_GET['id'];
    $action = $_GET['action'];

    if ($action == 'approve') {
        $stmt = $conn->prepare("UPDATE requirements SET status = 'cleared', remarks = 'Approved by admin' WHERE id = ?");
        $stmt->execute([$id]);
    }

    if ($action == 'reject') {
        $stmt = $conn->prepare("UPDATE requirements SET status = 'pending', remarks = 'Rejected by admin' WHERE id = ?");
        $stmt->execute([$id]);
    }

    header("Location: requirements.php");
    exit;
}

// =====================
// FETCH DATA
// =====================
$stmt = $conn->prepare("
    SELECT r.*, u.name AS student_name
    FROM requirements r
    JOIN users u ON u.id = r.student_id
    ORDER BY r.id DESC
");
$stmt->execute();
$requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- ===================== -->
<!-- PAGE UI -->
<!-- ===================== -->

<div class="p-4">

    <h3 class="fw-bold text-primary mb-4">📋 Clearance Approval System</h3>

    <div class="card card-custom p-3">

        <table class="table table-hover align-middle">

            <thead class="table-primary">
                <tr>
                    <th>Student</th>
                    <th>Requirement</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th width="200">Action</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($requirements as $req): ?>
                <tr>

                    <td>
                        <strong><?php echo htmlspecialchars($req['student_name']); ?></strong>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($req['requirement_name']); ?>
                    </td>

                    <td>
                        <?php if ($req['status'] == 'cleared'): ?>
                            <span class="badge bg-primary">Cleared</span>
                        <?php else: ?>
                            <span class="badge bg-warning">Pending</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($req['remarks'] ?? 'N/A'); ?>
                    </td>

                    <td>

                        <!-- APPROVE -->
                        <a href="?action=approve&id=<?php echo $req['id']; ?>"
                           class="btn btn-sm btn-primary">
                           ✔ Approve
                        </a>

                        <!-- REJECT -->
                        <a href="?action=reject&id=<?php echo $req['id']; ?>"
                           class="btn btn-sm btn-outline-primary">
                           ✖ Reject
                        </a>

                    </td>

                </tr>
                <?php endforeach; ?>

            </tbody>

        </table>

    </div>
</div>