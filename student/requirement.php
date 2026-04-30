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

// FETCH REQUIREMENTS
$stmt = $conn->prepare("SELECT * FROM requirements WHERE student_id = ?");
$stmt->execute([$student_id]);
$requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// STATUS (FOR SIDEBAR)
$statusStmt = $conn->prepare("SELECT clearance_status FROM users WHERE id = ?");
$statusStmt->execute([$student_id]);
$status = $statusStmt->fetchColumn();

// COUNTS
$total = count($requirements);
$cleared = 0;
$pending = 0;

foreach ($requirements as $req) {
    if ($req['status'] === 'cleared') $cleared++;
    else $pending++;
}

$percent = ($total > 0) ? ($cleared / $total) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Requirements</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #eef5ff;
        }

        .card-box {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(13,110,253,0.15);
            border: none;
        }

        /* ONLY layout positioning */
        .main {
            margin-left: 250px;
            padding: 20px;
        }
    </style>
</head>

<body>

<!-- SIDEBAR (NOW CORRECT PLACE) -->
<?php include 'partials/sidebar.php'; ?>

<!-- MAIN CONTENT -->
<div class="main">

    <h3 class="text-primary mb-4">📋 My Requirements</h3>

    <!-- SUMMARY -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card card-box p-3">
                <h6>Total</h6>
                <h3><?= $total ?></h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-box p-3">
                <h6>Cleared</h6>
                <h3><?= $cleared ?></h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-box p-3">
                <h6>Pending</h6>
                <h3><?= $pending ?></h3>
            </div>
        </div>
    </div>

    <!-- PROGRESS -->
    <div class="card card-box p-3 mb-4">
        <h6>Progress</h6>
        <div class="progress">
            <div class="progress-bar" style="width: <?= $percent ?>%">
                <?= round($percent) ?>%
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card card-box p-3">

        <h6>Requirement List</h6>

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
                    <td><?= htmlspecialchars($req['requirement_name']) ?></td>
                    <td><?= ucfirst($req['status']) ?></td>
                    <td><?= htmlspecialchars($req['remarks'] ?? 'N/A') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>

        </table>

    </div>

</div>

</body>
</html>