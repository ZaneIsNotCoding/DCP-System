<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

$message = "";

// HANDLE FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf_token();

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($password)) {
        $message = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters";
    } else {

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch()) {
            $message = "Email already exists";
        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $role = "student";

            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");

            if ($stmt->execute([$name, $email, $hashedPassword, $role])) {
                $message = "success";
            } else {
                $message = "Registration failed";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>DCP System Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            height: 100vh;
            margin: 0;
            background: #f4f6f9;
        }

        .login-wrapper {
            height: 100vh;
        }

        .glass {
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(10px);
            border-radius: 18px;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .branding {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            color: white;
        }

        .form-control {
            border-radius: 10px;
        }

        .btn-primary {
            border-radius: 10px;
            padding: 10px;
        }
    </style>
</head>

<body>

<div class="container d-flex align-items-center justify-content-center login-wrapper">

    <div class="row glass w-100" style="max-width: 900px; overflow: hidden;">

        <!-- LEFT SIDE (same as login) -->
        <div class="col-md-6 p-5 branding d-flex flex-column justify-content-center">
            <h1>DCP System</h1>
            <p>Digital Clearance & Personnel System</p>
            <hr class="text-white">
            <p class="small">
                Create an account to access attendance, employee management, and clearance modules.
            </p>
        </div>

        <!-- RIGHT SIDE (REGISTER FORM) -->
        <div class="col-md-6 p-5 bg-white">

            <h3 class="text-center mb-4">Register</h3>

            <?php if ($message == "success"): ?>
                <div class="alert alert-success text-center">
                    Account created successfully!
                </div>
            <?php elseif ($message): ?>
                <div class="alert alert-danger text-center">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?php echo csrf_field(); ?>

                <div class="mb-3">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button class="btn btn-primary w-100">Register</button>

                <div class="text-center mt-3">
                    <small>
                        Already have an account?
                        <a href="../index.php">Login</a>
                    </small>
                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>
