<?php
include 'includes/config.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Fetch transactions
$stmt = $conn->prepare("SELECT t.*, 
                       u1.name as sender_name, u1.account_number as sender_account,
                       u2.name as receiver_name, u2.account_number as receiver_account
                       FROM transactions t
                       LEFT JOIN users u1 ON t.sender_id = u1.id
                       LEFT JOIN users u2 ON t.receiver_id = u2.id
                       WHERE t.sender_id = ? OR t.receiver_id = ?
                       ORDER BY t.date DESC
                       LIMIT ? OFFSET ?");
$stmt->bind_param("iiii", $user_id, $user_id, $limit, $offset);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Count total transactions for pagination
$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM transactions WHERE sender_id = ? OR receiver_id = ?");
$count_stmt->bind_param("ii", $user_id, $user_id);
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History | BrightBank</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-5">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="mb-4">Transaction History</h2>
                
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
                                    <td><?= date('M d, Y h:i A', strtotime($txn['date'])) ?></td>
                                    <td>
                                        <?php if ($txn['sender_id'] == $user_id): ?>
                                            Sent to <?= $txn['receiver_name'] ?> (Acc: <?= substr($txn['receiver_account'], -4) ?>)
                                        <?php else: ?>
                                            Received from <?= $txn['sender_name'] ?> (Acc: <?= substr($txn['sender_account'], -4) ?>)
                                        <?php endif; ?>
                                        <?php if (!empty($txn['notes'])): ?>
                                            <br><small class="text-muted"><?= $txn['notes'] ?></small>
                                        <?php endif; ?>
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

                <!-- Pagination -->
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a></li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">Next</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>