<?php
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;

requireRole('admin');

$students = $conn->query("
    SELECT
        u.id,
        u.name,
        u.email,
        u.clearance_status,
        COUNT(r.id) AS total_requirements,
        COALESCE(SUM(r.status = 'cleared'), 0) AS cleared_requirements
    FROM users u
    LEFT JOIN requirements r ON r.student_id = u.id
    WHERE u.role = 'student'
    GROUP BY u.id, u.name, u.email, u.clearance_status
    ORDER BY u.name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$requirementsStmt = $conn->prepare("
    SELECT requirement_name, status, remarks, updated_at
    FROM requirements
    WHERE student_id = ?
    ORDER BY requirement_name ASC
");

$totalStudents = count($students);
$clearedStudents = 0;

foreach ($students as $student) {
    if ($student['clearance_status'] === 'cleared') {
        $clearedStudents++;
    }
}

$pendingStudents = $totalStudents - $clearedStudents;
$generatedAt = date('F d, Y h:i A');

$studentSections = '';

foreach ($students as $index => $student) {
    $requirementsStmt->execute([$student['id']]);
    $requirements = $requirementsStmt->fetchAll(PDO::FETCH_ASSOC);

    $status = ($student['clearance_status'] === 'cleared') ? 'CLEARED' : 'PENDING';
    $statusClass = ($student['clearance_status'] === 'cleared') ? 'cleared' : 'pending';
    $pendingRequirements = (int) $student['total_requirements'] - (int) $student['cleared_requirements'];

    $requirementRows = '';

    foreach ($requirements as $req) {
        $reqStatus = ($req['status'] === 'cleared') ? 'CLEARED' : 'PENDING';
        $reqStatusClass = ($req['status'] === 'cleared') ? 'cleared' : 'pending';
        $remarks = $req['remarks'] ?: 'N/A';
        $updatedAt = $req['updated_at'] ?: 'N/A';

        $requirementRows .= '
            <tr>
                <td>' . htmlspecialchars($req['requirement_name'], ENT_QUOTES, 'UTF-8') . '</td>
                <td class="' . $reqStatusClass . '">' . $reqStatus . '</td>
                <td>' . htmlspecialchars($remarks, ENT_QUOTES, 'UTF-8') . '</td>
                <td>' . htmlspecialchars($updatedAt, ENT_QUOTES, 'UTF-8') . '</td>
            </tr>
        ';
    }

    if ($requirementRows === '') {
        $requirementRows = '
            <tr>
                <td colspan="4" class="empty">No requirements assigned yet.</td>
            </tr>
        ';
    }

    $studentSections .= '
        <div class="student-block">
            <div class="student-header">
                <div>
                    <h3>' . ($index + 1) . '. ' . htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') . '</h3>
                    <p>' . htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') . '</p>
                </div>
                <div class="student-status ' . $statusClass . '">' . $status . '</div>
            </div>

            <table class="mini-summary">
                <tr>
                    <td><strong>' . (int) $student['total_requirements'] . '</strong><br>Total Requirements</td>
                    <td><strong>' . (int) $student['cleared_requirements'] . '</strong><br>Cleared</td>
                    <td><strong>' . $pendingRequirements . '</strong><br>Pending</td>
                </tr>
            </table>

            <table class="requirements">
                <thead>
                    <tr>
                        <th width="30%">Requirement</th>
                        <th width="15%">Status</th>
                        <th width="35%">Remarks</th>
                        <th width="20%">Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    ' . $requirementRows . '
                </tbody>
            </table>
        </div>
    ';
}

if ($studentSections === '') {
    $studentSections = '<div class="empty">No student records found.</div>';
}

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 11px;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .header h2 {
            margin: 0;
            color: #0d6efd;
            font-size: 20px;
        }

        .header p {
            margin: 4px 0 0;
        }

        .summary {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: collapse;
        }

        .summary td,
        .mini-summary td {
            border: 1px solid #bfdbfe;
            padding: 8px;
            text-align: center;
        }

        .summary .value {
            display: block;
            font-size: 18px;
            font-weight: bold;
            color: #0d6efd;
        }

        .student-block {
            page-break-inside: avoid;
            border: 1px solid #dbeafe;
            margin-bottom: 16px;
            padding: 10px;
        }

        .student-header {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .student-header > div {
            display: table-cell;
            vertical-align: top;
        }

        .student-header h3 {
            margin: 0;
            color: #0d6efd;
            font-size: 14px;
        }

        .student-header p {
            margin: 3px 0 0;
            color: #4b5563;
        }

        .student-status {
            text-align: right;
            font-weight: bold;
        }

        .mini-summary {
            width: 100%;
            margin-bottom: 8px;
            border-collapse: collapse;
        }

        table.requirements {
            width: 100%;
            border-collapse: collapse;
        }

        table.requirements th {
            background: #0d6efd;
            color: #ffffff;
            padding: 7px;
            text-align: left;
        }

        table.requirements td {
            border: 1px solid #dbeafe;
            padding: 7px;
        }

        .cleared {
            color: #0d6efd;
            font-weight: bold;
        }

        .pending {
            color: #b45309;
            font-weight: bold;
        }

        .empty {
            text-align: center;
            color: #6b7280;
            padding: 12px;
        }

        .footer {
            margin-top: 18px;
            font-size: 10px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Digital Clearance Processing System</h2>
        <p>Student Clearance Reports</p>
        <p>Generated: ' . htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8') . '</p>
    </div>

    <table class="summary">
        <tr>
            <td><span class="value">' . $totalStudents . '</span>Total Students</td>
            <td><span class="value">' . $clearedStudents . '</span>Cleared Students</td>
            <td><span class="value">' . $pendingStudents . '</span>Pending Students</td>
        </tr>
    </table>

    ' . $studentSections . '

    <div class="footer">
        Prepared by the administrator through the DCP System.
    </div>
</body>
</html>
';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('student_clearance_reports.pdf', ['Attachment' => true]);
exit;
