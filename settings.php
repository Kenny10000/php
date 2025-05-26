<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user data
$user = get_user_account($conn, $user_id);
if (!$user) {
    redirect('logout.php');
}

// Process notification settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_notifications'])) {
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
        
        $stmt = $conn->prepare("UPDATE users SET email_notifications = ?, sms_notifications = ? WHERE id = ?");
        $stmt->bind_param("iii", $email_notifications, $sms_notifications, $user_id);
        
        if ($stmt->execute()) {
            $success = 'Notification settings updated';
            log_activity($conn, $user_id, 'notification_settings_update');
        } else {
            $error = 'Failed to update settings';
        }
    }
    
    // Get updated user data
    $user = get_user_account($conn, $user_id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings | BrightBank</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .settings-card {
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .settings-section {
            border-bottom: 1px solid #eee;
            padding: 1.5rem 0;
        }
        .settings-section:last-child {
            border-bottom: none;
        }
        .form-check-input:checked {
            background-color: #4a6baf;
            border-color: #4a6baf;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card settings-card mb-4">
                    <div class="card-header bg-white">
                        <h3 class=" text-center mb-0"><i class="fas fa-cog me-2"></i>Account Settings</h3>
                    </div>
                    
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <div class="settings-section">
                            <h5 class="mb-4"><i class="fas fa-bell me-2"></i>Notification Preferences</h5>
                            <form method="POST">
                                <input type="hidden" name="update_notifications" value="1">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="emailNotifications" 
                                               name="email_notifications" 
                                               <?= ($user['email_notifications'] ?? 1) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="emailNotifications">
                                            Email Notifications
                                        </label>
                                    </div>
                                    <small class="text-muted">Receive account activity alerts via email</small>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="smsNotifications" 
                                               name="sms_notifications" 
                                               <?= ($user['sms_notifications'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="smsNotifications">
                                            SMS Notifications
                                        </label>
                                    </div>
                                    <small class="text-muted">Receive important alerts via text message</small>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                        
                        <div class="settings-section">
                            <h5 class="mb-4"><i class="fas fa-lock me-2"></i>Security Settings</h5>
                            <div class="list-group">
                                <a href="change-password.php" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">Change Password</h6>
                                            <small class="text-muted">Update your account password</small>
                                        </div>
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </a>
                                <a href="two-factor.php" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">Two-Factor Authentication</h6>
                                            <small class="text-muted">Add an extra layer of security</small>
                                        </div>
                                        <span class="badge bg-success">Enabled</span>
                                    </div>
                                </a>
                                <a href="linked-devices.php" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">Linked Devices</h6>
                                            <small class="text-muted">Manage your trusted devices</small>
                                        </div>
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                        
                        <div class="settings-section">
                            <h5 class="mb-4"><i class="fas fa-sliders-h me-2"></i>Preferences</h5>
                            <div class="mb-3">
                                <label class="form-label">Default Currency</label>
                                <select class="form-select" Enabled>
                                    <option>Nigerian Naira (NGN)</option>
                                    <option>US Dollar (USD)</option>
                                    <option>Euro (EUR)</option>
                                    <option>British Pound (GBP)</option>
                                </select>
                                <small class="text-muted">Currency preferences coming soon</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Language</label>
                                <select class="form-select" Enabled>
                                    <option>English</option>
                                    <option>Spanish</option>
                                    <option>French</option>
                                </select>
                                <!-- <small class="text-muted">Language preferences coming soon</small> -->
                            </div>
                        </div>
                        
                        <div class="settings-section">
                            <h5 class="mb-4 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h5>
                            <div class="card border-danger">
                                <div class="card-body">
                                    <h6 class="card-title">Close Account</h6>
                                    <p class="card-text text-muted">Permanently delete your account and all associated data.</p>
                                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#closeAccountModal">
                                        Close Account
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Close Account Modal -->
    <div class="modal fade" id="closeAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Close Your Account?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>This action cannot be undone. All your account data including transaction history will be permanently deleted.</p>
                    <p class="fw-bold">Please confirm your password to continue:</p>
                    <input type="password" class="form-control" placeholder="Enter your password">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger">Permanently Close Account</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>