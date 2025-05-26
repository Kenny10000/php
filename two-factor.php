<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$user = get_user_account($conn, $user_id);


require_once __DIR__ . '/includes/TwoFactorAuth.php';
$tfa = new TwoFactorAuth('SecureBank');

// Generate secret if not already set
if (empty($user['tfa_secret'])) {
    $secret = $tfa->createSecret();
    $stmt = $conn->prepare("UPDATE users SET tfa_secret = ? WHERE id = ?");
    $stmt->bind_param("si", $secret, $user_id);
    $stmt->execute();
    $user['tfa_secret'] = $secret;
}

// Enable/disable 2FA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['enable_2fa'])) {
        $code = $_POST['verification_code'];
        
        if ($tfa->verifyCode($user['tfa_secret'], $code)) {
            $stmt = $conn->prepare("UPDATE users SET tfa_enabled = 1 WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                $success = "Two-factor authentication enabled successfully";
                log_activity($conn, $user_id, '2fa_enabled');
            } else {
                $error = "Failed to enable 2FA";
            }
        } else {
            $error = "Invalid verification code";
        }
    } elseif (isset($_POST['disable_2fa'])) {
        $stmt = $conn->prepare("UPDATE users SET tfa_enabled = 0 WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $success = "Two-factor authentication disabled";
            log_activity($conn, $user_id, '2fa_disabled');
        } else {
            $error = "Failed to disable 2FA";
        }
    }
}

// Get updated user data
$user = get_user_account($conn, $user_id);
$qrCodeUrl = $tfa->getQRCodeImageAsDataUri($user['email'], $user['tfa_secret']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Auth | SecureBank</title>
    <!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-white">
                        <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Two-Factor Authentication</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <?php if ($user['tfa_enabled']): ?>
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle me-2"></i>Two-Factor Authentication is Enabled</h5>
                                <p class="mb-0">Your account is protected with an extra layer of security.</p>
                            </div>
                            
                            <form method="POST">
                                <input type="hidden" name="disable_2fa" value="1">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-danger">Disable Two-Factor Authentication</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <h5><i class="fas fa-exclamation-triangle me-2"></i>Two-Factor Authentication is Disabled</h5>
                                <p>Enable 2FA for enhanced account security.</p>
                            </div>
                            
                            <h5 class="mt-4">Setup Instructions</h5>
                            <ol>
                                <li>Install an authenticator app like Google Authenticator or Authy</li>
                                <li>Scan the QR code below or enter the secret key manually</li>
                                <li>Enter the 6-digit code from your app to verify</li>
                            </ol>
                            
                            <div class="text-center my-4">
                                <img src="<?= $qrCodeUrl ?>" alt="QR Code" class="img-fluid" style="max-width: 200px;">
                                <div class="mt-3">
                                    <small class="text-muted">Or enter this secret key manually:</small>
                                    <div class="input-group mt-1">
                                        <input type="text" class="form-control text-center" value="<?= chunk_split($user['tfa_secret'], 4, ' ') ?>" readonly>
                                        <button class="btn btn-outline-secondary" onclick="copyToClipboard(this)">Copy</button>
                                    </div>
                                </div>
                            </div>
                            
                            <form method="POST">
                                <input type="hidden" name="enable_2fa" value="1">
                                <div class="mb-3">
                                    <label for="verification_code" class="form-label">Verification Code</label>
                                    <input type="text" class="form-control" id="verification_code" name="verification_code" 
                                           placeholder="Enter 6-digit code" maxlength="6" required>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Enable Two-Factor Authentication</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard(button) {
            const input = button.parentElement.querySelector('input');
            input.select();
            document.execCommand('copy');
            button.textContent = 'Copied!';
            setTimeout(() => button.textContent = 'Copy', 2000);
        }
    </script>
</body>
</html>