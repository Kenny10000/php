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
    redirect('logout.php'); // Invalid user session
}

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize_input($_POST['name']);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    // Validate inputs
    if (empty($name)) {
        $error = 'Name is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } else {
        try {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $email, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['user_name'] = $name;
                $success = 'Profile updated successfully';
                $user = get_user_account($conn, $user_id); // Refresh user data
                log_activity($conn, $user_id, 'profile_update');
            } else {
                $error = 'Failed to update profile';
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = 'Email already exists';
            } else {
                $error = 'Database error: ' . $e->getMessage();
                error_log("Profile Update Error: " . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | SecureBank</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-card {
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .profile-header {
            background: linear-gradient(135deg, #4a6baf 0%, #2c3e50 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .profile-pic {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 5px solid white;
        }
        .account-badge {
            background-color: #e9f0f9;
            border-left: 4px solid #4a6baf;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card profile-card mb-4">
                    <div class="profile-header text-center p-4">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=ffffff&color=4a6baf&size=120" 
                             alt="Profile" class="profile-pic rounded-circle mb-3">
                        <h3><?= htmlspecialchars($user['name']) ?></h3>
                        <p class="mb-0">Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h4 class="mb-4"><i class="fas fa-user-circle me-2"></i>Profile Information</h4>
                                <form method="POST">
                                    <input type="hidden" name="update_profile" value="1">
                                    <div class="mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="name" class="form-control" 
                                               value="<?= htmlspecialchars($user['name']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" name="email" class="form-control" 
                                               value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                </form>
                            </div>
                            
                            <div class="col-md-6">
                                <h4 class="mb-4"><i class="fas fa-wallet me-2"></i>Account Details</h4>
                                <div class="p-3 mb-3 account-badge rounded">
                                    <small class="text-muted">Account Number</small>
                                    <h5 class="mb-0"><?= htmlspecialchars($user['account_number']) ?></h5>
                                </div>
                                <div class="p-3 mb-3 account-badge rounded">
                                    <small class="text-muted">Account Type</small>
                                    <h5 class="mb-0">Personal Checking</h5>
                                </div>
                                <div class="p-3 account-badge rounded">
                                    <small class="text-muted">Current Balance</small>
                                    <h5 class="mb-0"><?= format_currency($user['balance']) ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card profile-card">
                    <div class="card-body p-4">
                        <h4 class="mb-4"><i class="fas fa-shield-alt me-2"></i>Security</h4>
                        <div class="list-group">
                            <a href="change-password.php" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-key me-2"></i> Change Password</span>
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                            <a href="two-factor.php" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-mobile-alt me-2"></i> Two-Factor Authentication</span>
                                    <span class="badge bg-success">Enabled</span>
                                </div>
                            </a>
                            <a href="login-history.php" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-history me-2"></i> Login History</span>
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>