<?php
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/database.php';

// Hanya Administrator yang bisa mengakses
if ($_SESSION['role'] != ROLE_ADMINISTRATOR) {
    die('Akses ditolak. Halaman ini hanya untuk Administrator.');
}

$pageTitle = "Manajemen User";

// Tambah user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Token CSRF tidak valid.';
    } else {
        $username = sanitize($_POST['username']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $role = intval($_POST['role']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, phone, role) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$username, $password, $email, $phone, $role])) {
            $success = 'User berhasil ditambahkan.';
        } else {
            $error = 'Gagal menambahkan user.';
        }
    }
}

// Hapus user
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success = 'User berhasil dihapus.';
        } else {
            $error = 'Gagal menghapus user.';
        }
    } else {
        $error = 'Tidak dapat menghapus akun sendiri.';
    }
}

// Ambil data users
$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-switch@3.4.0/dist/css/bootstrap3/bootstrap-switch.min.css">
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
                <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar User</h3>
                        <button type="button" class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#addUserModal">
                            <i class="fas fa-plus"></i> Tambah User
                        </button>
                    </div>
                    <div class="card-body">
                        <table id="usersTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>No. WhatsApp</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo $user['phone']; ?></td>
                                    <td>
                                        <?php 
                                        switch ($user['role']) {
                                            case ROLE_ADMINISTRATOR: echo 'Administrator'; break;
                                            case ROLE_TEKNISI: echo 'Teknisi'; break;
                                            case ROLE_KOLEKTOR: echo 'Kolektor'; break;
                                            case ROLE_OPERATOR: echo 'Operator'; break;
                                            default: echo 'Unknown';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="status" data-bootstrap-switch 
                                            data-on-text="Aktif" data-off-text="Nonaktif" 
                                            <?php echo $user['is_active'] ? 'checked' : ''; ?>
                                            data-user-id="<?php echo $user['id']; ?>">
                                    </td>
                                    <td>
                                        <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="users.php?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus user ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal Tambah User -->
    <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Tambah User Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Nomor WhatsApp</label>
                            <input type="text" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="<?php echo ROLE_ADMINISTRATOR; ?>">Administrator</option>
                                <option value="<?php echo ROLE_TEKNISI; ?>">Teknisi</option>
                                <option value="<?php echo ROLE_KOLEKTOR; ?>">Kolektor</option>
                                <option value="<?php echo ROLE_OPERATOR; ?>">Operator</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" name="add_user" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-switch@3.4.0/dist/js/bootstrap-switch.min.js"></script>
<script>
$(document).ready(function() {
    // Inisialisasi DataTables
    $('#usersTable').DataTable({
        "responsive": true,
        "autoWidth": false,
    });
    
    // Inisialisasi Bootstrap Switch
    $("input[data-bootstrap-switch]").each(function(){
        $(this).bootstrapSwitch();
    });
    
    // Event listener untuk toggle status user
    $('input[name="status"]').on('switchChange.bootstrapSwitch', function(event, state) {
        var userId = $(this).data('user-id');
        
        $.ajax({
            url: 'update-user-status.php',
            type: 'POST',
            data: {
                user_id: userId,
                status: state ? 1 : 0,
                csrf_token: '<?php echo generateCsrfToken(); ?>'
            },
            success: function(response) {
                var data = JSON.parse(response);
                if (!data.success) {
                    alert('Gagal mengupdate status user');
                    // Reset toggle jika gagal
                    $('input[data-user-id="' + userId + '"]').bootstrapSwitch('state', !state, true);
                }
            },
            error: function() {
                alert('Terjadi kesalahan saat mengupdate status user');
                // Reset toggle jika error
                $('input[data-user-id="' + userId + '"]').bootstrapSwitch('state', !state, true);
            }
        });
    });
});
</script>
</body>
</html>
