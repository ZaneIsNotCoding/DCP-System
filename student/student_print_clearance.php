<?php
require_once '../includes/session.php';
require_once '../config/database.php';
include 'partials/sidebar.php';
// check login
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

// check role (must be student)
if ($_SESSION['user']['role'] !== 'student') {
    header("Location: ../index.php");
    exit;
}

$student_id = $_SESSION['user']['id'];

// get student info
$userStmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$student_id]);
$student = $userStmt->fetch(PDO::FETCH_ASSOC);

// get requirements
$stmt = $conn->prepare("SELECT * FROM requirements WHERE student_id = ?");
$stmt->execute([$student_id]);
$requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = count($requirements);
$cleared = 0;

foreach ($requirements as $req) {
    if ($req['status'] === 'cleared') {
        $cleared++;
    }
}

$is_cleared = ($total > 0 && $total == $cleared);

if (!$is_cleared) {
    header("Location: dashboard.php?error=not_cleared");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Clearance Certificate</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #eef5ff;
        }

        .certificate {
            width: 800px;
            margin: 30px auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            text-align: center;
        }

        .title {
            font-size: 28px;
            font-weight: bold;
            color: #0d6efd;
        }

        @media print {
            .print-btn {
                display: none;
            }

            body {
                background: white;
            }
        }
    </style>
</head>

<body>

<div class="certificate" <?php
$control_no = "CLR-" . date("Y") . "-" . str_pad($student_id, 5, "0", STR_PAD_LEFT);
?>

<p><strong>Control No:</strong> <?php echo $control_no; ?></p>
<p><strong>Date Issued:</strong> <?php echo date('F d, Y'); ?></p>>

    <h2>ISABELA STATE UNIVERSITY</h2>
    <h5>Student Clearance System</h5>

    <div class="title">CLEARANCE CERTIFICATE</div>

    <p class="mt-4">This is to certify that</p>

    <h4 class="fw-bold">
        <?php echo htmlspecialchars($student['name']); ?>
    </h4>

    <p>has successfully completed all clearance requirements.</p>

    <h3 class="text-success">✓ CLEARED</h3>
    <div class="mt-5">
        <p>__________________________</p>
        <p>Registrar / Admin</p>
    </div>

    <button onclick="window.print()" class="btn btn-primary print-btn">
        🖨 Print Certificate
    </button>

</div>

</body>
</html>