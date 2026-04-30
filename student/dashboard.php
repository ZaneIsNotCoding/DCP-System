<?php
require_once '../includes/session.php';
require_once '../config/database.php';
include 'partials/sidebar.php';
// AUTH CHECK
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'student') {
    header("Location: ../index.php");
    exit;
}

$user = $_SESSION['user'];
$student_id = $user['id'];

// =====================
// GET CLEARANCE STATUS
// =====================
$statusStmt = $conn->prepare("SELECT clearance_status FROM users WHERE id = ?");
$statusStmt->execute([$student_id]);
$status = $statusStmt->fetchColumn();


$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(status='cleared') as cleared,
        SUM(status!='cleared') as pending
    FROM requirements
    WHERE student_id = ?
");
$stmt->execute([$student_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

$total = $data['total'];
$cleared = $data['cleared'];
$pending = $data['pending'];

$percent = ($total > 0) ? ($cleared / $total) * 100 : 0;
$is_cleared = (strtolower(trim($status)) === 'cleared');
?>
s
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>

    <!-- Optional Auto Refresh -->
    <!-- <meta http-equiv="refresh" content="10"> -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #eef5ff;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            background: linear-gradient(180deg, #0d6efd, #0b5ed7);
            color: #fff;
            padding-top: 20px;
        }

        .sidebar h4 {
            color: #fff;
        }

        .sidebar a {
            color: #e6f0ff;
            display: block;
            padding: 12px 20px;
            text-decoration: none;
            transition: 0.2s;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }

        .main {
            margin-left: 250px;
            padding: 20px;
        }

        .card-box {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(13,110,253,0.15);
            border: none;
        }

        .topbar {
            background: #fff;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(13,110,253,0.08);
        }

        .badge.bg-success {
            background-color: #0d6efd !important;
        }

        .badge.bg-warning {
            background-color: #6ea8fe !important;
            color: #fff;
        }
    </style>
</head>

<body>

<!-- MAIN -->
<div class="main">

    <!-- TOPBAR -->
    <div class="topbar d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Student Dashboard</h5>
        <div>Welcome, <strong><?php echo htmlspecialchars($user['name']); ?></strong></div>
    </div>

    <!-- STATUS ALERT -->
    <?php if ($status == 'cleared'): ?>
        <div class="alert alert-primary fw-bold">
            ✅ You are CLEARED. You can now download your certificate.
        </div>
    <?php else: ?>
        <div class="alert alert-warning fw-bold">
            ⏳ Clearance Pending. Please complete all requirements.
        </div>
    <?php endif; ?>

    <!-- CARDS -->
    <div class="row">
        <div class="col-md-4">
            <div class="card card-box p-3">
                <h6>Total Requirements</h6>
                <h3><?php echo $total; ?></h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-box p-3">
                <h6>Cleared</h6>
                <h3><?php echo $cleared; ?></h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-box p-3">
                <h6>Pending</h6>
                <h3><?php echo $pending; ?></h3>
            </div>
        </div>
    </div>
    <div class="mt-4">
    <?php if ($is_cleared): ?>
        <a href="student_print_clearance.php" class="btn btn-success btn-lg">
            🖨 Print Clearance Certificate
        </a>
    <?php else: ?>
        <button class="btn btn-secondary btn-lg" disabled>
            🖨 Clearance Not Available
        </button>
    <?php endif; ?>
</div>
</div>

</body>
</html>