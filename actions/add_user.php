<?php
require_once '../config/database.php';
require_once '../includes/session.php';

if ($_SESSION['user']['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

$name = $_POST['name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role = $_POST['role'];

$sql = "INSERT INTO users (name, email, password, role)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->execute([$name, $email, $password, $role]);

header("Location: ../admin/dashboard.php?success=1");
exit;
?>