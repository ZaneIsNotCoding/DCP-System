<?php
require_once __DIR__ . '/session.php';

function currentUser()
{
    return $_SESSION['user'] ?? null;
}

function isLoggedIn()
{
    return currentUser() !== null;
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit;
    }
}

function requireRole($role)
{
    requireLogin();

    if (($_SESSION['user']['role'] ?? '') !== $role) {
        header('Location: ../index.php');
        exit;
    }
}
