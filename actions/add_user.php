<?php
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requireRole('admin');
verify_csrf_token();

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = $_POST['role'] ?? '';

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, ['admin', 'student'], true) || strlen($_POST['password'] ?? '') < 8) {
    header("Location: ../admin/add_user.php?error=invalid");
    exit;
}

$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$sql = "INSERT INTO users (name, email, password, role)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->execute([$name, $email, $password, $role]);

header("Location: ../admin/dashboard.php?success=1");
exit;
?>
