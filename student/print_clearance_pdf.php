<?php
require_once '../includes/session.php';
require_once '../config/database.php';

require_once '../vendor/autoload.php';

use Dompdf\Dompdf;

// AUTH CHECK
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

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

// =====================
// BUILD HTML
// =====================
$html = '
<h2 style="text-align:center; color:#0d6efd;">ISABELA STATE UNIVERSITY</h2>
<h4 style="text-align:center;">Digital Clearance Processing System</h4>
<hr>

<h2 style="text-align:center;">CLEARANCE CERTIFICATE</h2>

<p style="text-align:center;">This is to certify that</p>

<h3 style="text-align:center;">'.$student['name'].'</h3>

<p style="text-align:center;">
has completed the required clearance process.
</p>

<h2 style="text-align:center; color:'.($is_cleared ? 'green' : 'red').'">
'.($is_cleared ? 'CLEARED' : 'NOT CLEARED').'
</h2>

<br>

<p>Total Requirements: '.$total.'</p>
<p>Cleared: '.$cleared.'</p>
<p>Pending: '.($total - $cleared).'</p>

<br><br>

<p style="text-align:center;">_____________________</p>
<p style="text-align:center;">Registrar / Admin</p>
';

// =====================
// GENERATE PDF
// =====================
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("clearance_certificate.pdf", ["Attachment" => true]);
exit;
?>