<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/clearance_engine.php';

requireRole('admin');
verify_csrf_token();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$redirectStudentId = filter_input(INPUT_POST, 'redirect_student_id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: ../admin/requirements.php?error=invalid');
    exit;
}

$stmt = $conn->prepare('SELECT student_id FROM requirements WHERE id = ?');
$stmt->execute([$id]);
$studentId = $stmt->fetchColumn();

if (!$studentId) {
    header('Location: ../admin/requirements.php?error=missing');
    exit;
}

$stmt = $conn->prepare("
    UPDATE requirements
    SET status = 'cleared', remarks = 'Approved by admin'
    WHERE id = ?
");
$stmt->execute([$id]);

syncClearanceStatus($studentId, $conn);

$redirectStudentId = $redirectStudentId ?: $studentId;
header("Location: ../admin/requirements.php?student_id={$redirectStudentId}&success=approved");
exit;
