<?php
require_once '../includes/session.php';
require_once '../config/database.php';
include 'layout.php';

// AUTH CHECK
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

$message = "";

// =====================
// HANDLE FORM SUBMIT
// =====================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    // Basic validation
    if (empty($name) || empty($email) || empty($role) || empty($password)) {
        $message = "All fields are required.";
    } else {

        try {
            // Check duplicate email
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);

            if ($check->rowCount() > 0) {
                $message = "Email already exists.";
            } else {

                // Insert user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("
                    INSERT INTO users (name, email, role, password)
                    VALUES (?, ?, ?, ?)
                ");

                $stmt->execute([
                    $name,
                    $email,
                    $role,
                    $hashedPassword
                ]);

                header("Location: add_user.php?success=1");
                exit;
            }

        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}
?>

<!-- ===================== -->
<!-- PAGE UI -->
<!-- ===================== -->

<div class="p-4">

    <h3 class="fw-bold mb-4 text-primary">➕ Add User</h3>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-primary shadow-sm">
            ✅ User created successfully!
        </div>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
        <div class="alert alert-danger shadow-sm">
            ❌ <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="card card-custom p-4">

        <form method="POST">

            <!-- NAME -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <!-- EMAIL -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <!-- ROLE -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Role</label>
                <select name="role" class="form-select" required>
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                    <option value="student">Student</option>
                </select>
            </div>

            <!-- PASSWORD -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <!-- BUTTON -->
            <button type="submit" class="btn btn-primary w-100 fw-semibold">
                💾 Create User
            </button>

        </form>

    </div>
</div>