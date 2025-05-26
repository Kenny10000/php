<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session and include config
// session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize variables
$error = '';
$success = '';
$current_balance = 0;
$user_id = $_SESSION['user_id'];

// Get current balance
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$current_balance = $user['balance'];

// Process deposit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));
    
    // Validate amount
    if ($amount <= 0) {
        $error = "Deposit amount must be greater than zero.";
    } elseif ($amount > 1000000) { // Set reasonable deposit limit
        $error = "Maximum deposit amount is $1,000,000 per transaction.";
    } else {
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Update user balance
            $update = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $update->bind_param("di", $amount, $user_id);
            $update->execute();
            
            // Record transaction (sender_id 0 represents the bank)
            $transaction = $conn->prepare("INSERT INTO transactions 
                                         (sender_id, receiver_id, amount, notes, status) 
                                         VALUES (0, ?, ?, ?, 'completed')");
            $transaction->bind_param("ids", $user_id, $amount, $description);
            $transaction->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Update local balance
            $current_balance += $amount;
            $success = "Successfully deposited $" . number_format($amount, 2);
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Transaction failed: " . $e->getMessage();
            error_log("Deposit Error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make a Deposit | SecureBank</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        .deposit-card {
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .deposit-card:hover {
            transform: translateY(-5px);
        }
        .balance-display {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
        }
        .form-control:focus {
            border-color: #4a6baf;
            box-shadow: 0 0 0 0.25rem rgba(74, 107, 175, 0.25);
        }
        .btn-deposit {
            background-color: #4a6baf;
            border: none;
            padding: 10px 25px;
            font-weight: 600;
        }
        .btn-deposit:hover {
            background-color: #3a5a9f;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card deposit-card mb-4">
                    <div class="card-header bg-white border-0 pt-4">
                        <h2 class="text-center"><i class="fas fa-coins me-2"></i>Make a Deposit</h2>
                    </div>
                    <div class="card-body p-4">
                        <!-- Success/Error Messages -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Current Balance -->
                        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded">
                            <span class="text-muted">Current Balance:</span>
                            <span class="balance-display">#<?php echo number_format($current_balance, 2); ?></span>
                        </div>
                        
                        <!-- Deposit Form -->
                        <form method="POST" id="depositForm">
                            <div class="mb-4">
                                <label for="amount" class="form-label fw-bold">Deposit Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">#</span>
                                    <input type="number" 
                                           class="form-control form-control-lg" 
                                           id="amount" 
                                           name="amount" 
                                           step="0.01" 
                                           min="0.01" 
                                           max="1000000" 
                                           required
                                           placeholder="0.00">
                                </div>
                                <small class="text-muted">Minimum deposit: #100</small>
                            </div>
                            
                            <div class="mb-4">
                                <label for="description" class="form-label fw-bold">Description (Optional)</label>
                                <textarea class="form-control" 
                                          id="description" 
                                          name="description" 
                                          rows="2"
                                          placeholder="e.g., Cash deposit, check deposit, etc."></textarea>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-deposit btn-lg text-white">
                                    <i class="fas fa-plus-circle me-2"></i>Complete Deposit
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Deposit Information -->
                    <div class="card-footer bg-white border-0">
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <h5><i class="fas fa-info-circle text-primary me-2"></i>Deposit Info</h5>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Instant credit</li>
                                    <li><i class="fas fa-check text-success me-2"></i>No fees</li>
                                    <li><i class="fas fa-check text-success me-2"></i>FDIC insured</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="fas fa-clock text-primary me-2"></i>Processing Times</h5>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-circle text-info me-2 small"></i>Cash: Immediate</li>
                                    <li><i class="fas fa-circle text-info me-2 small"></i>Checks: 1 business day</li>
                                    <li><i class="fas fa-circle text-info me-2 small"></i>Wire transfers: Same day</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Deposits (Only shown if there are deposits) -->
                <?php
                $recent_stmt = $conn->prepare("SELECT amount, date, notes FROM transactions 
                                             WHERE receiver_id = ? AND sender_id = 0 
                                             ORDER BY date DESC LIMIT 5");
                $recent_stmt->bind_param("i", $user_id);
                $recent_stmt->execute();
                $recent_deposits = $recent_stmt->get_result();
                
                if ($recent_deposits->num_rows > 0): ?>
                <div class="card deposit-card">
                    <div class="card-header bg-white border-0">
                        <h5><i class="fas fa-history me-2"></i>Recent Deposits</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($deposit = $recent_deposits->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y g:i A', strtotime($deposit['date'])); ?></td>
                                        <td class="text-success fw-bold">+$<?php echo number_format($deposit['amount'], 2); ?></td>
                                        <td><?php echo !empty($deposit['notes']) ? htmlspecialchars($deposit['notes']) : 'Deposit'; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript for enhanced UX -->
    <script>
        // Client-side validation
        document.getElementById('depositForm').addEventListener('submit', function(e) {
            const amountInput = document.getElementById('amount');
            const amount = parseFloat(amountInput.value);
            
            if (amount <= 0) {
                e.preventDefault();
                alert('Please enter a valid deposit amount.');
                amountInput.focus();
            }
        });
        
        // Format amount as user types
        document.getElementById('amount').addEventListener('blur', function(e) {
            const value = parseFloat(e.target.value);
            if (!isNaN(value)) {
                e.target.value = value.toFixed(2);
            }
        });
    </script>
</body>
</html>