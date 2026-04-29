<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/clearance_engine.php';
include 'layout.php';

// =====================
// AUTH CHECK
// =====================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

// =====================
// COUNTS
// =====================

// Users
$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Admins
$totalAdmins = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();

// Students
$totalStudents = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();

// Requirements (safe check)
$totalRequirements = 0;
$clearedRequirements = 0;
$pendingRequirements = 0;

try {
    $totalRequirements = $conn->query("SELECT COUNT(*) FROM requirements")->fetchColumn();
    $clearedRequirements = $conn->query("SELECT COUNT(*) FROM requirements WHERE status = 'cleared'")->fetchColumn();
    $pendingRequirements = $conn->query("SELECT COUNT(*) FROM requirements WHERE status = 'pending'")->fetchColumn();
} catch (Exception $e) {
    // ignore if table not ready
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #eef5ff;
        }

        .top-header {
            background: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(13,110,253,0.08);
        }

        .page-title {
            color: #0d6efd;
        }

        .card-custom {
            border-radius: 14px;
            border: none;
            box-shadow: 0 4px 12px rgba(13,110,253,0.15);
            transition: 0.2s;
        }

        .card-custom:hover {
            transform: translateY(-4px);
        }

        .icon-box {
            font-size: 2rem;
            color: #0d6efd;
        }
    </style>
</head>

<body>

<div class="p-4">

    <!-- HEADER -->
    <div class="top-header d-flex justify-content-between align-items-center">
        <h4 class="page-title fw-bold mb-0">Admin Dashboard</h4>

        <div>
            Welcome, 
            <strong><?php echo htmlspecialchars($_SESSION['user']['name']); ?></strong>
        </div>
    </div>

    <!-- CARDS -->
    <div class="row g-4">

        <!-- USERS -->
        <div class="col-md-4">
            <div class="card card-custom p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Total Users</h6>
                        <h3 class="fw-bold text-primary"><?php echo $totalUsers; ?></h3>
                    </div>
                    <i class="bi bi-people icon-box"></i>
                </div>
            </div>
        </div>

        <!-- ADMINS -->
        <div class="col-md-4">
            <div class="card card-custom p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Admins</h6>
                        <h3 class="fw-bold text-primary"><?php echo $totalAdmins; ?></h3>
                    </div>
                    <i class="bi bi-shield-lock icon-box"></i>
                </div>
            </div>
        </div>

        <!-- STUDENTS -->
        <div class="col-md-4">
            <div class="card card-custom p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Students</h6>
                        <h3 class="fw-bold text-primary"><?php echo $totalStudents; ?></h3>
                    </div>
                    <i class="bi bi-mortarboard icon-box"></i>
                </div>
            </div>
        </div>

        <!-- REQUIREMENTS -->
        <div class="col-md-4">
            <div class="card card-custom p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Requirements</h6>
                        <h3 class="fw-bold text-primary"><?php echo $totalRequirements; ?></h3>
                    </div>
                    <i class="bi bi-file-text icon-box"></i>
                </div>
            </div>
        </div>

        <!-- CLEARED -->
        <div class="col-md-4">
            <div class="card card-custom p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Cleared</h6>
                        <h3 class="fw-bold text-primary"><?php echo $clearedRequirements; ?></h3>
                    </div>
                    <i class="bi bi-check-circle icon-box"></i>
                </div>
            </div>
        </div>

        <!-- PENDING -->
        <div class="col-md-4">
            <div class="card card-custom p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Pending</h6>
                        <h3 class="fw-bold text-primary"><?php echo $pendingRequirements; ?></h3>
                    </div>
                    <i class="bi bi-hourglass-split icon-box"></i>
                </div>
            </div>
        </div>

    </div>

</div>

</body>
</html>