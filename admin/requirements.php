<?php
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../config/database.php';

// AUTH CHECK
requireRole('admin');

$selectedStudentId = filter_input(INPUT_GET, 'student_id', FILTER_VALIDATE_INT);

// FETCH STUDENTS
$students = $conn->query("
    SELECT
        u.id,
        u.name,
        u.email,
        u.clearance_status,
        COUNT(r.id) AS requirement_count,
        COALESCE(SUM(r.status = 'cleared'), 0) AS cleared_count
    FROM users u
    LEFT JOIN requirements r ON r.student_id = u.id
    WHERE u.role = 'student'
    GROUP BY u.id, u.name, u.email, u.clearance_status
    ORDER BY u.name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$selectedStudent = null;
$requirements = [];

if ($selectedStudentId) {
    $stmt = $conn->prepare("
        SELECT id, name, email, clearance_status
        FROM users
        WHERE id = ? AND role = 'student'
    ");
    $stmt->execute([$selectedStudentId]);
    $selectedStudent = $stmt->fetch();

    if ($selectedStudent) {
        $stmt = $conn->prepare("
            SELECT id, requirement_name, status, remarks, updated_at
            FROM requirements
            WHERE student_id = ?
            ORDER BY requirement_name ASC
        ");
        $stmt->execute([$selectedStudentId]);
        $requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

include 'layout.php';
?>

<div class="container-fluid">

    <h3 class="fw-bold text-primary mb-4">Clearance Approval System</h3>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            Requirement updated successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            Unable to update the requirement.
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <div class="col-lg-4">
            <div class="card card-custom p-3">
                <h6 class="fw-bold text-primary mb-3">Students</h6>

                <div class="list-group">
                    <?php foreach ($students as $student): ?>
                        <?php $isActive = ((int) $student['id'] === (int) $selectedStudentId); ?>
                        <a
                            href="requirements.php?student_id=<?php echo (int) $student['id']; ?>"
                            class="list-group-item list-group-item-action <?php echo $isActive ? 'active' : ''; ?>"
                        >
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <strong><?php echo htmlspecialchars($student['name']); ?></strong>
                                <?php if ($student['clearance_status'] === 'cleared'): ?>
                                    <span class="badge bg-light text-primary">Cleared</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php endif; ?>
                            </div>
                            <small>
                                <?php echo (int) $student['cleared_count']; ?> /
                                <?php echo (int) $student['requirement_count']; ?> cleared
                            </small>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-custom p-4">

                <?php if (!$selectedStudent): ?>
                    <div class="text-center text-muted py-5">
                        Select a student name to approve or reject requirements.
                    </div>
                <?php else: ?>

                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">
                                <?php echo htmlspecialchars($selectedStudent['name']); ?>
                            </h5>
                            <div class="text-muted">
                                <?php echo htmlspecialchars($selectedStudent['email']); ?>
                            </div>
                        </div>

                        <?php if ($selectedStudent['clearance_status'] === 'cleared'): ?>
                            <span class="badge bg-primary fs-6">Cleared</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark fs-6">Pending</span>
                        <?php endif; ?>
                    </div>

                    <table class="table table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>Requirement</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th width="180">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (count($requirements) === 0): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        No requirements assigned yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($requirements as $req): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($req['requirement_name']); ?></td>

                                    <td>
                                        <?php if ($req['status'] == 'cleared'): ?>
                                            <span class="badge bg-primary">Cleared</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php endif; ?>
                                    </td>

                                    <td><?php echo htmlspecialchars($req['remarks'] ?? 'N/A'); ?></td>

                                    <td>
                                        <form method="POST" action="../actions/approve.php" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?php echo (int) $req['id']; ?>">
                                            <input type="hidden" name="redirect_student_id" value="<?php echo (int) $selectedStudent['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-primary">Approve</button>
                                        </form>

                                        <form method="POST" action="../actions/reject.php" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?php echo (int) $req['id']; ?>">
                                            <input type="hidden" name="redirect_student_id" value="<?php echo (int) $selectedStudent['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                <?php endif; ?>

            </div>
        </div>

    </div>

</div>
