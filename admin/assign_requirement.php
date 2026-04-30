<?php
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/clearance_engine.php';

// AUTH CHECK
requireRole('admin');

// FETCH STUDENTS
$students = $conn->query("SELECT id, name FROM users WHERE role='student' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// DEFAULT REQUIREMENTS
$defaultRequirements = [
    "Accounting Office",
    "Library",
    "SSC President",
    "College Dean"
];

// =====================
// HANDLE FORM
// =====================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf_token();

    $student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);

    if (!$student_id) {
        header("Location: assign_requirement.php?error=invalid");
        exit;
    }

    foreach ($defaultRequirements as $req) {

        // Prevent duplicate requirements for the same student.
        $check = $conn->prepare("
            SELECT id FROM requirements
            WHERE student_id = ? AND requirement_name = ?
        ");
        $check->execute([$student_id, $req]);

        if ($check->rowCount() == 0) {

            $stmt = $conn->prepare("
                INSERT INTO requirements (student_id, requirement_name, status)
                VALUES (?, ?, 'pending')
            ");

            $stmt->execute([$student_id, $req]);
        }
    }

    syncClearanceStatus($student_id, $conn);

    header("Location: assign_requirement.php?success=1");
    exit;
}

include 'layout.php';
?>

<div class="container p-4">

<h3 class="text-primary fw-bold mb-4">Assign Requirements</h3>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        Requirements assigned successfully!
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        Unable to assign requirements.
    </div>
<?php endif; ?>

<div class="card p-4">

    <form method="POST">
        <?php echo csrf_field(); ?>

        <div class="mb-3">
            <label class="form-label">Select Student</label>
            <select name="student_id" class="form-select" required>
                <option value="">Choose student</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?php echo (int) $s['id']; ?>">
                        <?php echo htmlspecialchars($s['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button class="btn btn-success w-100">
            Auto Assign Default Requirements
        </button>

    </form>

</div>

</div>
