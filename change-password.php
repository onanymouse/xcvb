<?php
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/database.php';

$pageTitle = "Ganti Password";
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Token CSRF tidak valid.';
    } else {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if ($newPassword !== $confirmPassword) {
            $error = 'Password baru dan konfirmasi password tidak cocok.';
        } else {
            // Verifikasi password saat ini
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($currentPassword, $user['password'])) {
                // Update password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                
                if ($updateStmt->execute([$hashedPassword, $_SESSION['user_id']])) {
                    $success = 'Password berhasil diubah.';
                } else {
                    $error = 'Gagal mengubah password.';
                }
            } else {
                $error = 'Password saat ini salah.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1><?php echo $pageTitle; ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active"><?php echo $pageTitle; ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Form Ganti Password</h3>
                            </div>
                            <form method="post" action="">
                                <div class="card-body">
                                    <?php if (!empty($error)): ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($success)): ?>
                                    <div class="alert alert-success"><?php echo $success; ?></div>
                                    <?php endif; ?>
                                    
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                    
                                    <div class="form-group">
                                        <label for="current_password">Password Saat Ini</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="new_password">Password Baru</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="confirm_password">Konfirmasi Password Baru</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Ganti Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
