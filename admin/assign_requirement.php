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
    "Library Clearance",
    "SSC Clearance",
    "Guidance Clearance",
    "Dean Clearance",
    "Department Clearance"
];

// =====================
// HANDLE FORM
// =====================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $student_id = $_POST['student_id'];

    // =====================
    // AUTO ASSIGN (SAFE VERSION)
    // =====================
    if (isset($_POST['auto_assign'])) {

        foreach ($defaultRequirements as $req) {

            // ❗ CHECK IF ALREADY EXISTS (PREVENT DUPLICATES)
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

    } 
    // =====================
    // MANUAL ASSIGN
    // =====================
    else {

        $requirements = $_POST['requirements'];

        foreach ($requirements as $req) {

            if (!empty($req)) {

                // prevent duplicates here too
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
        }
    }

    header("Location: assign_requirement.php?success=1");
    exit;
}

include 'layout.php';
?>

<!-- ===================== UI ===================== -->

<div class="container p-4">

<h3 class="text-primary fw-bold mb-4">📌 Assign Requirements</h3>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        Requirements assigned successfully!
    </div>
<?php endif; ?>

<div class="card p-4">

    <!-- AUTO ASSIGN -->
    <form method="POST">

        <input type="hidden" name="auto_assign" value="1">

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
            ⚡ Auto Assign Default Requirements
        </button>

    </form>

    <hr class="my-4">

    <!-- MANUAL ASSIGN -->
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

        <div class="mb-3">
            <label class="form-label">Custom Requirements</label>

            <input type="text" name="requirements[]" class="form-control mb-2" placeholder="Requirement 1">
            <input type="text" name="requirements[]" class="form-control mb-2" placeholder="Requirement 2">
            <input type="text" name="requirements[]" class="form-control mb-2" placeholder="Requirement 3">
        </div>

        <button class="btn btn-primary w-100">
            ➕ Assign Custom Requirements
        </button>

    </form>

</div>

</div>