<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/database.php';

/**
 * REAL-TIME CLEARANCE SYNC ENGINE
 * Automatically computes and updates student clearance status
 */

function syncClearanceStatus($student_id, $conn)
{
    // Get all requirements of student
    $stmt = $conn->prepare("
        SELECT status 
        FROM requirements 
        WHERE student_id = ?
    ");
    $stmt->execute([$student_id]);
    $requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = count($requirements);

    if ($total == 0) {
        // No requirements yet
        $status = 'pending';
    } else {

        $cleared = 0;

        foreach ($requirements as $req) {
            if ($req['status'] === 'cleared') {
                $cleared++;
            }
        }

        // FINAL RULE
        $status = ($total === $cleared) ? 'cleared' : 'pending';
    }

    // Update USERS table instantly
    $update = $conn->prepare("
        UPDATE users 
        SET clearance_status = ?
        WHERE id = ?
    ");
    $update->execute([$status, $student_id]);

    return $status;
}
?>
