<?php
include 'includes/config.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

// Fetch user balance
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$current_balance = $user['balance'];

// Process transfer
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient_account = trim($_POST['recipient_account']);
    $amount = floatval($_POST['amount']);
    $notes = trim($_POST['notes']);

    // Validate
    if ($amount <= 0) {
        $error = "Amount must be positive";
    } elseif ($amount > $current_balance) {
        $error = "Insufficient balance";
    } else {
        // Check if recipient exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE account_number = ?");
        $stmt->bind_param("s", $recipient_account);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $recipient = $result->fetch_assoc();
            $recipient_id = $recipient['id'];

            // Begin transaction
            $conn->begin_transaction();
            try {
                // Deduct from sender
                $conn->query("UPDATE users SET balance = balance - $amount WHERE id = $user_id");
                // Add to recipient
                $conn->query("UPDATE users SET balance = balance + $amount WHERE id = $recipient_id");
                // Record transaction
                $stmt = $conn->prepare("INSERT INTO transactions (sender_id, receiver_id, amount, notes) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iids", $user_id, $recipient_id, $amount, $notes);
                $stmt->execute();

                $conn->commit();
                $success = "Transfer successful!";
                $current_balance -= $amount; // Update local balance
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Transfer failed: " . $e->getMessage();
            }
        } else {
            $error = "Recipient account not found";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Money | BrightBank</title>
  <!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Bootstrap JS Bundle (for dropdowns) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Transfer Money</h2>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Recipient Account Number</label>
                                <input type="text" name="recipient_account" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Amount (#)</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes (Optional)</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <p class="fw-bold">Current Balance: $<?= number_format($current_balance, 2) ?></p>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Confirm Transfer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>