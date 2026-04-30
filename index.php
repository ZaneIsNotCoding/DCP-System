<?php
require_once 'includes/session.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>DCP System Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            height: 100vh;
            margin: 0;
            background: #f4f6f9; /* CLEAN WHITE BACKGROUND */
        }

        .login-wrapper {
            height: 100vh;
        }

        /* SOFT GLASS CARD (ADAPTED FOR WHITE UI) */
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .branding {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            color: white;
        }

        .system-title {
            font-size: 32px;
            font-weight: 600;
        }

        .subtitle {
            font-size: 14px;
            opacity: 0.85;
        }

        .form-control {
            border-radius: 10px;
        }

        .btn-primary {
            border-radius: 10px;
            padding: 10px;
        }

        label {
            font-size: 14px;
        }
    </style>
</head>

<body>

<div class="container d-flex align-items-center justify-content-center login-wrapper">

    <div class="row glass w-100" style="max-width: 900px; overflow: hidden;">

        <!-- LEFT SIDE -->
        <div class="col-md-6 p-5 branding d-flex flex-column justify-content-center">
            <h1 class="system-title">DCP System</h1>
            <p class="subtitle">Digital Clearance & Personnel System</p>
            <hr class="text-white">
            <p class="small">
                Manage attendance, employees, and clearance processing in one modern system.
            </p>
        </div>

        <!-- RIGHT SIDE -->
        <div class="col-md-6 p-5 bg-white">

            <h3 class="text-center mb-4">Sign In</h3>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger text-center">
                    Invalid email or password
                </div>
            <?php endif; ?>

            <form method="POST" action="actions/login.php">
                <?php echo csrf_field(); ?>

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>

                <button class="btn btn-primary w-100">Login</button>

                <div class="text-center mt-3">
                    <small>
                        Don't have an account?
                        <a href="actions/register.php">Register</a>
                    </small>
                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>
