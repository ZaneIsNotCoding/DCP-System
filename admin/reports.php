<?php
require_once '../includes/session.php';
require_once '../config/database.php';
include 'layout.php';

// AUTH CHECK
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

// =====================
// FETCH STUDENTS + STATUS
// =====================
$students = $conn->query("
    SELECT id, name, email, clearance_status
    FROM users
    WHERE role = 'student'
    ORDER BY name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Reports</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #eef5ff;
        }

        .page-title {
            color: #0d6efd;
        }

        .card-custom {
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(13,110,253,0.12);
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                background: white;
            }
        }
    </style>
</head>

<body>

<div class="p-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <h4 class="page-title fw-bold">📊 Clearance Reports</h4>

        <button onclick="window.print()" class="btn btn-primary">
            🖨 Print Report
        </button>
    </div>

    <!-- REPORT TABLE -->
    <div class="card card-custom p-3">

        <table class="table table-hover align-middle">

            <thead class="table-primary">
                <tr>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($students as $s): ?>
                <tr>

                    <td>
                        <strong><?php echo htmlspecialchars($s['name']); ?></strong>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($s['email']); ?>
                    </td>

                    <td>
                        <?php if ($s['clearance_status'] == 'cleared'): ?>
                            <span class="badge bg-primary">CLEARED</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">PENDING</span>
                        <?php endif; ?>
                    </td>

                </tr>
                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>