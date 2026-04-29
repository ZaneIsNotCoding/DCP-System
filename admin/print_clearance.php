<?php
require_once '../includes/session.php';
require_once '../config/database.php';
include 'layout.php';
// AUTH CHECK
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

$student_id = $_SESSION['user']['id'];

// =====================
// GET STUDENT INFO
// =====================
$userStmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$student_id]);
$student = $userStmt->fetch(PDO::FETCH_ASSOC);

// =====================
// GET REQUIREMENTS
// =====================
$stmt = $conn->prepare("SELECT * FROM requirements WHERE student_id = ?");
$stmt->execute([$student_id]);
$requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =====================
// CHECK CLEARANCE STATUS
// =====================
$total = count($requirements);
$cleared = 0;

foreach ($requirements as $req) {
    if ($req['status'] === 'cleared') {
        $cleared++;
    }
}

$is_cleared = ($total > 0 && $total == $cleared);
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
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
        }

        .header {
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .title {
            font-size: 28px;
            font-weight: bold;
            color: #0d6efd;
        }

        .status {
            font-size: 22px;
            font-weight: bold;
            margin-top: 20px;
        }

        .cleared {
            color: #0d6efd;
        }

        .not-cleared {
            color: #dc3545;
        }

        .print-btn {
            margin-top: 20px;
        }

        @media print {
            .print-btn {
                display: none;
            }

            body {
                background: white;
            }

            .certificate {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>

<body>

<div class="certificate">

    <!-- HEADER -->
    <div class="header">
        <h2>ISABELA STATE UNIVERSITY</h2>
        <h5>Digital Clearance Processing System</h5>
    </div>

    <!-- TITLE -->
    <div class="title">CLEARANCE CERTIFICATE</div>

    <!-- STUDENT INFO -->
    <p class="mt-4">
        This is to certify that
    </p>

    <h4 class="fw-bold">
        <?php echo htmlspecialchars($student['name']); ?>
    </h4>

    <p>
        has completed the required clearance requirements.
    </p>

    <!-- STATUS -->
    <div class="status <?php echo $is_cleared ? 'cleared' : 'not-cleared'; ?>">
        <?php if ($is_cleared): ?>
            ✓ CLEARED
        <?php else: ?>
            ✗ NOT CLEARED
        <?php endif; ?>
    </div>

    <!-- DETAILS -->
    <div class="mt-4 text-start">
        <p><strong>Total Requirements:</strong> <?php echo $total; ?></p>
        <p><strong>Cleared:</strong> <?php echo $cleared; ?></p>
        <p><strong>Pending:</strong> <?php echo $total - $cleared; ?></p>
    </div>

    <!-- SIGNATURE -->
    <div class="mt-5">
        <p>__________________________</p>
        <p>Registrar / Admin</p>
    </div>

    <!-- PRINT BUTTON -->
    <button onclick="window.print()" class="btn btn-primary print-btn">
        🖨 Print Certificate
    </button>

</div>

</body>
</html>