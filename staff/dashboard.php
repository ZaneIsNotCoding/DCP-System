<?php
require_once '../includes/session.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'staff') {
    header("Location: ../index.php");
    exit;
}

echo "Welcome Staff: " . $_SESSION['user']['name'];
?>