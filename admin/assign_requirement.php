<?php
require_once '../includes/session.php';
require_once '../config/database.php';

// AUTH CHECK
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

// FETCH STUDENTS
$students = $conn->query("SELECT id, name FROM users WHERE role='student'")->fetchAll(PDO::FETCH_ASSOC);

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

    $student_id = $_POST['student_id'];

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

    header("Location: assign_requirement.php?success=1");
    exit;
}

include 'layout.php';
?>

<!-- ===================== UI ===================== -->

<div class="container p-4">

<h3 class="text-primary fw-bold mb-4">Assign Requirements</h3>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        Requirements assigned successfully!
    </div>
<?php endif; ?>

<div class="card p-4">

    <form method="POST">

        <div class="mb-3">
            <label class="form-label">Select Student</label>
            <select name="student_id" class="form-select" required>
                <option value="">Choose student</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?php echo $s['id']; ?>">
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