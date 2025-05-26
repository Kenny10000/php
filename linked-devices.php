<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle device removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_device'])) {
    $device_id = $_POST['device_id'];
    
    $stmt = $conn->prepare("DELETE FROM trusted_devices WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $device_id, $user_id);
    
    if ($stmt->execute()) {
        $success = "Device removed successfully";
        log_activity($conn, $user_id, 'device_removed', "Device ID: $device_id");
    } else {
        $error = "Failed to remove device";
    }
}

// Get trusted devices
$stmt = $conn->prepare("SELECT * FROM trusted_devices WHERE user_id = ? ORDER BY last_used DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$devices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Linked Devices | SecureBank</title>
    <!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .device-card {
            border-left: 4px solid #4a6baf;
            transition: transform 0.2s;
        }
        .device-card:hover {
            transform: translateY(-3px);
        }
        .current-device {
            background-color: rgba(74, 107, 175, 0.05);
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-white">
                        <h4 class="mb-0"><i class="fas fa-laptop me-2"></i>Linked Devices</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Trusted devices don't require full authentication when logging in from recognized locations.
                        </div>
                        
                        <?php if (empty($devices)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-laptop-slash fa-3x text-muted mb-3"></i>
                                <h5>No Trusted Devices</h5>
                                <p class="text-muted">Your trusted devices will appear here when you enable "Remember this device" during login.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($devices as $device): ?>
                                    <div class="list-group-item p-3 mb-2 device-card <?= ($device['device_token'] === ($_COOKIE['device_token'] ?? '')) ? 'current-device' : '' ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">
                                                    <?php if (strpos($device['user_agent'], 'Mobile') !== false): ?>
                                                        <i class="fas fa-mobile-alt me-2"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-laptop me-2"></i>
                                                    <?php endif; ?>
                                                    <?= get_device_name($device['user_agent']) ?>
                                                </h6>
                                                <small class="text-muted">
                                                        Last used: <?= date('M j, Y g:i A', strtotime($device['last_used'])) ?>
                                                        <?php if ($device['ip_address']): ?>
                                                            • <?= $device['ip_address'] ?>
                                                        <?php endif; ?>
                                                </small>

                                            </div>
                                            <?php if ($device['device_token'] !== ($_COOKIE['device_token'] ?? '')): ?>
                                                <form method="POST">
                                                    <input type="hidden" name="device_id" value="<?= $device['id'] ?>">
                                                    <button type="submit" name="remove_device" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="badge bg-success">This Device</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>