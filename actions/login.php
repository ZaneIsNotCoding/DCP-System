<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../includes/session.php';

verify_csrf_token();

$auth = new AuthController();

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$role = $auth->login($email, $password);

if ($role) {

    switch ($role) {
        case 'admin':
            header("Location: ../admin/dashboard.php");
            break;
        case 'staff':
            header("Location: ../staff/dashboard.php");
            break;
        case 'student':
            header("Location: ../student/dashboard.php");
            break;
    }

} else {
    header("Location: ../index.php?error=1");
}

exit;
?>
