<?php
// config.php
session_start();
define('SITE_NAME', 'Admin Dashboard');
define('BASE_URL', 'http://localhost/admin-dashboard/');

// Role constants
define('ROLE_ADMINISTRATOR', 1);
define('ROLE_TEKNISI', 2);
define('ROLE_KOLEKTOR', 3);
define('ROLE_OPERATOR', 4);

// Set timezone
date_default_timezone_set('Asia/Jakarta');
?>
