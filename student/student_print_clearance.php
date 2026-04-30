<?php
require_once '../includes/session.php';
require_once '../config/database.php';

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

$control_no = "ID-" . date("Y") . "-" . str_pad($student_id, 5, "0", STR_PAD_LEFT);
$course = $student['course'] ?? $student['program'] ?? '';
$section = $student['section'] ?? $student['year_section'] ?? '';
$courseSection = trim($course . ($section ? ' - ' . $section : ''));
if ($courseSection === '') {
    $courseSection = 'BSIT';
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Clearance</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #e9eef5;
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
        }

        .page {
            width: 4.8in;
            min-height: 6.2in;
            margin: 24px auto;
            padding: 0.28in 0.35in;
            background: #fff;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.14);
        }

        .header {
            text-align: center;
            line-height: 1.25;
            font-weight: 700;
        }

        .seal {
            width: 64px;
            height: 64px;
            border: 2px solid #222;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
            font-size: 10px;
            font-weight: 700;
        }

        .republic {
            font-size: 11px;
        }

        .school {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0;
        }

        .campus,
        .college {
            font-size: 12px;
            font-weight: 700;
        }

        .form-title {
            margin: 10px 0 8px;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            text-decoration: underline;
        }

        .meta {
            display: grid;
            grid-template-columns: 1fr;
            gap: 5px;
            margin-bottom: 8px;
        }

        .field {
            display: grid;
            grid-template-columns: 96px 1fr;
            align-items: end;
            gap: 6px;
            min-height: 20px;
        }

        .label {
            font-weight: 700;
            white-space: nowrap;
        }

        .line {
            border-bottom: 1px solid #111;
            min-height: 18px;
            padding: 0 5px 1px;
        }

        .statement {
            margin: 8px 0 10px;
            text-align: justify;
            line-height: 1.35;
        }

        .signatures {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .signatures td {
            padding: 8px 6px 2px;
            vertical-align: bottom;
        }

        .signature-line {
            border-bottom: 1px solid #111;
            height: 20px;
        }

        .office {
            padding-top: 2px;
            text-align: center;
            font-weight: 700;
            font-size: 10px;
        }

        .footer {
            display: grid;
            grid-template-columns: 1fr;
            width: 60%;
            margin: 16px auto 0;
        }

        .print-actions {
            width: 4.8in;
            margin: 0 auto 24px;
            text-align: center;
        }

        .print-btn {
            border: 0;
            border-radius: 6px;
            background: #0d6efd;
            color: #fff;
            cursor: pointer;
            font-weight: 700;
            padding: 10px 18px;
        }

        @page {
            size: letter portrait;
            margin: 0.35in;
        }

        @media print {
            body {
                background: #fff;
            }

            .page {
                width: 4.8in;
                min-height: 6.2in;
                margin: 0;
                padding: 0.28in 0.35in;
                box-shadow: none;
            }

            .print-actions {
                display: none;
            }
        }
    </style>
</head>

<body>

<main class="page">
    <header class="header">
            <div class="republic">Student Clearance (1st Semester 2026-2026)</div>
    </header>

    <div class="form-title">STUDENT CLEARANCE</div>
    <p class="statement">
        This is to certify that the student named below has completed all assigned
        clearance requirements and is cleared by the offices listed below.
    </p>
    <section class="meta">
        <div class="field">
            <span class="label">Name:</span>
            <span class="line"><?php echo e($student['name'] ?? ''); ?></span>
        </div>
        <div class="field">
            <span class="label">ID No:</span>
            <span class="line"><?php echo e($control_no); ?></span>
        </div>
        <div class="field">
            <span class="label">Course & Section:</span>
            <span class="line"><?php echo e($courseSection); ?></span>
        </div>
        <div class="field">
            <span class="label">Date:</span>
            <span class="line"><?php echo date('F d, Y'); ?></span>
        </div>
        <div class="field">
            <span class="label">Purpose:</span>
            <span class="line">Clearance completion</span>
        </div>
        <div class="field">
            <span class="label">Status:</span>
            <span class="line">CLEARED</span>
        </div>
    </section>

    <table class="signatures">
        <tbody>
            <?php foreach ($requirements as $index => $req): ?>
                <?php if ($index % 2 == 0): ?>
                    <tr>
                <?php endif; ?>

                <td>
                    <div class="signature-line"></div>
                    <div class="office"><?php echo e($req['requirement_name']); ?></div>
                </td>

                <?php if ($index % 2 == 1): ?>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if ($total % 2 == 1): ?>
                <td></td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <section class="footer">
        <div>
            <div class="signature-line"></div>
            <div class="office">Student Signature</div>
        </div>
    </section>
</main>

<div class="print-actions">
    <button onclick="window.print()" class="print-btn">Print Clearance</button>
</div>

</body>
</html>
