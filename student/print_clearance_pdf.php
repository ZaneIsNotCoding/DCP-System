<?php
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../config/database.php';

require_once '../vendor/autoload.php';

use Dompdf\Dompdf;

// AUTH CHECK
requireRole('student');

$student_id = $_SESSION['user']['id'];

// =====================
// GET STUDENT DATA
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
// CHECK STATUS
// =====================
$total = count($requirements);
$cleared = 0;

foreach ($requirements as $req) {
    if ($req['status'] === 'cleared') {
        $cleared++;
    }
}

$is_cleared = ($total > 0 && $total === $cleared);

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

// =====================
// BUILD HTML
// =====================
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0.5in;
        }

        body {
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            margin: 0;
        }

        .page {
            width: 4.8in;
            min-height: 6.2in;
            margin: 0 auto;
            padding: 0.28in 0.35in;
        }

        .header {
            text-align: center;
            line-height: 1.25;
            font-weight: 700;
        }

        .republic {
            font-size: 11px;
        }

        .form-title {
            margin: 10px 0 8px;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            text-decoration: underline;
        }

        .statement {
            margin: 8px 0 14px;
            text-align: justify;
            line-height: 1.35;
        }

        .field {
            margin-bottom: 5px;
            min-height: 20px;
            width: 100%;
        }

        .label {
            display: inline-block;
            font-weight: 700;
            width: 96px;
        }

        .line {
            border-bottom: 1px solid #111;
            display: inline-block;
            min-height: 18px;
            padding: 0 5px 1px;
            width: 250px;
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
            margin: 34px auto 0;
            width: 60%;
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
            clearance requirements and is officially cleared.
        </p>

        <section class="meta">
            <div class="field">
                <span class="label">Name:</span>
                <span class="line">'.e($student['name'] ?? '').'</span>
            </div>
            <div class="field">
                <span class="label">ID No:</span>
                <span class="line">'.e($control_no).'</span>
            </div>
            <div class="field">
                <span class="label">Course &amp; Section:</span>
                <span class="line">'.e($courseSection).'</span>
            </div>
            <div class="field">
                <span class="label">Date:</span>
                <span class="line">'.date('F d, Y').'</span>
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

        <section class="footer">
            <div>
                <div class="signature-line"></div>
                <div class="office">Student Signature</div>
            </div>
        </section>
    </main>
</body>
</html>
';

// =====================
// GENERATE PDF
// =====================
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();
$dompdf->stream("student_clearance_" . $student_id . ".pdf", ["Attachment" => true]);
exit;
?>
