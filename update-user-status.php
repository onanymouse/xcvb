<?php
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if ($_SESSION['role'] != ROLE_ADMINISTRATOR) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid.']);
        exit;
    }
    
    $userId = intval($_POST['user_id']);
    $status = intval($_POST['status']);
    
    $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
    if ($stmt->execute([$status, $userId])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupdate status user.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
}
?>
