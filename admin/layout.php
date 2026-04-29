<?php
require_once '../includes/session.php';

// AUTH CHECK
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #eef5ff;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #0d6efd, #0b5ed7);
            height: 100vh;
            position: fixed;
            color: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.08);
        }

        .sidebar-head {
            padding: 22px;
            text-align: center;
            font-weight: bold;
            letter-spacing: 1px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .sidebar a {
            color: #e6f0ff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            transition: 0.2s;
            font-weight: 500;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,0.15);
            padding-left: 25px;
        }

        .sidebar i {
            color: #ffffff;
        }

        /* CONTENT */
        .content {
            margin-left: 260px;
            width: calc(100% - 260px);
        }

        /* TOPBAR */
        .topbar {
            background: #ffffff;
            padding: 15px 25px;
            box-shadow: 0 2px 8px rgba(13,110,253,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 0 0 12px 12px;
        }

        .topbar b {
            color: #0d6efd;
        }

        /* CARD */
        .card-custom {
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(13,110,253,0.15);
            transition: 0.3s;
        }

        .card-custom:hover {
            transform: translateY(-4px);
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="sidebar-head">
        ⚙️ ADMIN PANEL
    </div>

    <div class="mt-3">

        <a href="dashboard.php">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <a href="add_user.php">
            <i class="bi bi-person-plus"></i> Add User
        </a>
        <a href="print_clearance.php">
            <i class="bi bi-printer"></i> Clearance Certificate
        </a>
        <a href="reports.php">
            <i class="bi bi-bar-chart"></i> Reports
        </a>
        <a href="users.php">
            <i class="bi bi-people"></i> Users
        </a>

        <a href="requirements.php">
            <i class="bi bi-list-check"></i> Requirements
        </a>

        <a href="../index.php">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>

    </div>
</div>

<!-- CONTENT -->
<div class="content">

    <!-- TOPBAR -->
    <div class="topbar">

        <div>
            👋 Welcome,
            <b><?php echo htmlspecialchars($user['name']); ?></b>
        </div>

        <div>
            <i class="bi bi-person-circle fs-4 text-primary"></i>
        </div>

    </div>

    <!-- PAGE CONTENT -->
    <div class="p-4">

        <!-- THIS IS WHERE ALL PAGES LOAD -->