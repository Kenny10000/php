<?php 
include 'includes/config.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email, account_number, balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch last 5 transactions (example)
// $transactions = [];
// $txn_stmt = $conn->prepare("SELECT * FROM transactions WHERE sender_id = ? OR receiver_id = ? ORDER BY date DESC LIMIT 5");
// $txn_stmt->bind_param("ii", $user_id, $user_id);
// $txn_stmt->execute();
// $txn_result = $txn_stmt->get_result();
// while ($row = $txn_result->fetch_assoc()) {
//     $transactions[] = $row;
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | BrightBank</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card:hover {
            transform: translateY(-5px);
            transition: 0.3s;
        }
        .quick-action-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar (Collapsible on Mobile) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-piggy-bank me-2"></i>BrightBank
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transfer.php"><i class="fas fa-exchange-alt"></i> Transfer</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transactions.php"><i class="fas fa-history"></i> Transactions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-4">
        <div class="row">
            <!-- Sidebar (Hidden on Mobile) -->
            <div class="col-lg-3 d-none d-lg-block">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=0d6efd&color=fff" 
                             alt="Profile" class="rounded-circle mb-3" width="80">
                        <h5><?= htmlspecialchars($user['name']) ?></h5>
                        <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link active" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="transfer.php"><i class="fas fa-exchange-alt me-2"></i> Transfer Money</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="deposit.php"><i class="fas fa-plus-circle me-2"></i> Deposit</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="withdraw.php"><i class="fas fa-minus-circle me-2"></i> Withdraw</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="profile.php"><i class="fas fas fa-user me-2"></i> Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="settings.php"><i class="fas fa-cog me-2"></i> Settings</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="col-lg-9">
                <!-- Account Summary -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">Account Summary</h4>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Account Holder:</strong> <?= htmlspecialchars($user['name']) ?></p>
                                <p><strong>Account Number:</strong> <?= htmlspecialchars($user['account_number']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Available Balance:</strong></p>
                                <h2 class="text-primary">#<?= number_format($user['balance'], 2) ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions (Visible on All Devices) -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">Quick Actions</h4>
                        <hr>
                        <div class="row text-center">
                            <div class="col-4 col-md-3">
                                <a href="transfer.php" class="btn btn-primary quick-action-btn">
                                    <i class="fas fa-exchange-alt"></i>
                                </a>
                                <p>Transfer</p>
                            </div>
                            <div class="col-4 col-md-3">
                                <a href="deposit.php" class="btn btn-success quick-action-btn">
                                    <i class="fas fa-plus"></i>
                                </a>
                                <p>Deposit</p>
                            </div>
                            <div class="col-4 col-md-3">
                                <a href="withdraw.php" class="btn btn-warning quick-action-btn">
                                    <i class="fas fa-minus"></i>
                                </a>
                                <p>Withdraw</p>
                            </div>
                            <div class="col-4 col-md-3">
                                <a href="transactions.php" class="btn btn-info quick-action-btn">
                                    <i class="fas fa-history"></i>
                                </a>
                                <p>History</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">Recent Transactions</h4>
                        <hr>
                        <?php if (empty($transactions)): ?>
                            <p class="text-muted">No transactions yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transactions as $txn): ?>
                                            <tr>
                                                <td><?= date('M d, Y', strtotime($txn['date'])) ?></td>
                                                <td>
                                                    <?php 
                                                    if ($txn['sender_id'] == $user_id) {
                                                        echo "Sent to Acc: " . substr($txn['receiver_id'], -4);
                                                    } else {
                                                        echo "Received from Acc: " . substr($txn['sender_id'], -4);
                                                    }
                                                    ?>
                                                </td>
                                                <td class="<?= ($txn['sender_id'] == $user_id) ? 'text-danger' : 'text-success' ?>">
                                                    <?= ($txn['sender_id'] == $user_id) ? '-' : '+' ?> $<?= number_format($txn['amount'], 2) ?>
                                                </td>
                                                <td><span class="badge bg-success">Completed</span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <a href="transactions.php" class="btn btn-outline-primary">View All</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Spending Analytics (Chart) -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Spending Analytics</h4>
                        <hr>
                        <canvas id="spendingChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-3 bg-dark text-white mt-4">
        <div class="container text-center">
            <p class="mb-0">&copy; 2023 BrightBank. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sample Chart.js for Spending Analytics
        const ctx = document.getElementById('spendingChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Spending ($)',
                    data: [0, 0, 0, 0, 0, 0],
                    backgroundColor: 'rgba(13, 110, 253, 0.5)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>