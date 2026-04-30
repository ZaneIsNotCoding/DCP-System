<style>
.sidebar {
    width: 250px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: linear-gradient(180deg, #0d6efd, #0b5ed7);
    color: #fff;
    padding-top: 20px;
}

.sidebar h4 {
    color: #fff;
}

.sidebar a {
    color: #e6f0ff;
    display: block;
    padding: 12px 20px;
    text-decoration: none;
    transition: 0.2s;
}

.sidebar a:hover {
    background: rgba(255,255,255,0.15);
    color: #fff;
}

.sidebar .btn {
    margin-top: 10px;
}

.main {
    margin-left: 250px;
    padding: 20px;
}
</style>

<div class="sidebar">
    <h4 class="text-center mb-4">DCP System</h4>

    <a href="dashboard.php">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <a href="requirement.php">
        <i class="bi bi-list-check"></i> Requirements
    </a>

    <a href="student_print_clearance.php">
        <i class="bi bi-printer"></i> Print Clearance
    </a>

    <div class="px-3 mt-2">
        <?php if (isset($status) && $status == 'cleared'): ?>
            <a href="print_clearance_pdf.php" class="btn btn-primary w-100">
                📄 Download PDF Certificate
            </a>
        <?php else: ?>
            <button class="btn btn-secondary w-100" disabled>
                🔒 Certificate Locked
            </button>
        <?php endif; ?>
    </div>
            
    <a href="../actions/logout.php" class="mt-3">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>
