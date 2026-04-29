<?php
require_once '../includes/session.php';
require_once '../config/database.php';

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

// =====================
// FETCH REQUIREMENTS
// =====================
$stmt = $conn->prepare("SELECT * FROM requirements WHERE student_id = ?");
$stmt->execute([$student_id]);
$requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = count($requirements);
$cleared = 0;
$pending = 0;

foreach ($requirements as $req) {
    if ($req['status'] === 'cleared') {
        $cleared++;
    } else {
        $pending++;
    }
}

// =====================
// PROGRESS CALCULATION
// =====================
$percent = ($total > 0) ? ($cleared / $total) * 100 : 0;
?>

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

<!-- SIDEBAR -->
<div class="sidebar">
    <h4 class="text-center mb-4">DCP System</h4>

    <a href="#"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="requirements.php"><i class="bi bi-list-check"></i> Requirements</a>
    <a href="print.php"><i class="bi bi-printer"></i> Print Clearance</a>

    <!-- LOCKED / UNLOCKED PDF -->
    <div class="px-3 mt-2">
        <?php if ($status == 'cleared'): ?>
            <a href="print_clearance_pdf.php" class="btn btn-primary w-100">
                📄 Download PDF Certificate
            </a>
        <?php else: ?>
            <button class="btn btn-secondary w-100" disabled>
                🔒 Certificate Locked
            </button>
        <?php endif; ?>
    </div>

    <a href="../logout.php" class="mt-3">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

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

    <!-- PROGRESS -->
    <div class="card card-box mt-4 p-3">
        <h6>Progress</h6>
        <div class="progress">
            <div class="progress-bar bg-primary" style="width: <?php echo $percent; ?>%">
                <?php echo round($percent); ?>%
            </div>
        </div>
    </div>

    <!-- REQUIREMENTS TABLE -->
    <div class="card card-box mt-4 p-3">
        <h6>Clearance Status</h6>

        <table class="table table-striped mt-2">
            <thead>
                <tr>
                    <th>Requirement</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($total == 0): ?>
                <tr>
                    <td colspan="3" class="text-center text-muted">
                        No requirements assigned yet.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($requirements as $req): ?>
                <tr>
                    <td><?php echo htmlspecialchars($req['requirement_name']); ?></td>
                    <td>
                        <?php if ($req['status'] == 'cleared'): ?>
                            <span class="badge bg-success">Cleared</span>
                        <?php else: ?>
                            <span class="badge bg-warning">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($req['remarks'] ?? 'N/A'); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>

        </table>
    </div>

</div>

</body>
</html>