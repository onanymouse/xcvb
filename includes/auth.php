<?php
require_once 'database.php';
require_once 'functions.php';

if (!isLoggedIn() && basename($_SERVER['PHP_SELF']) != 'login.php') {
    redirect('login.php');
}

// Cek akses role
$allowedRoles = [];
$currentPage = basename($_SERVER['PHP_SELF']);

switch ($currentPage) {
    case 'users.php':
        $allowedRoles = [ROLE_ADMINISTRATOR];
        break;
    case 'dashboard.php':
        $allowedRoles = [ROLE_ADMINISTRATOR, ROLE_TEKNISI, ROLE_KOLEKTOR, ROLE_OPERATOR];
        break;
    // Tambahkan halaman lainnya sesuai kebutuhan
}

if (!empty($allowedRoles) && isLoggedIn() && !in_array($_SESSION['role'], $allowedRoles)) {
    die('Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
}
?>
