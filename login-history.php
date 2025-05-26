<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get login history
$stmt = $conn->prepare("SELECT * FROM login_history 
                       WHERE user_id = ? 
                       ORDER BY login_time DESC 
                       LIMIT ? OFFSET ?");
$stmt->bind_param("iii", $user_id, $per_page, $offset);
$stmt->execute();
$history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get total count
$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM login_history WHERE user_id = ?");
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login History | BrightBank</title>
    <!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .device-icon {
            font-size: 1.5rem;
            margin-right: 10px;
        }
        .location-flag {
            width: 20px;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow">
                    <div class="card-header bg-white">
                        <h4 class="mb-0"><i class="fas fa-history me-2"></i>Login History</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>This shows your recent account login activity.
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Device</th>
                                        <th>Location</th>
                                        <th>IP Address</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history as $entry): ?>
                                        <tr>
                                            <td><?= date('M j, Y g:i A', strtotime($entry['login_time'])) ?></td>
                                            <td>
                                                <?php if (strpos($entry['user_agent'], 'Mobile') !== false): ?>
                                                    <i class="fas fa-mobile-alt device-icon"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-laptop device-icon"></i>
                                                <?php endif; ?>
                                                <?= get_device_name($entry['user_agent']) ?>
                                            </td>
                                            <td>
                                                <img 
                                                    src="https://flagcdn.com/16x12/<?= strtolower($entry['country_code']) ?>.png" 
                                                    alt="<?= $entry['country_code'] ?>" 
                                                    class="location-flag">
                                                <?= $entry['city'] ? $entry['city'] . ', ' : '' ?>
                                                <?= $entry['country'] ?>
                                            </td>

                                            <td><?= $entry['ip_address'] ?></td>
                                            <td>
                                                <span class="badge bg-<?= $entry['success'] ? 'success' : 'danger' ?>">
                                                    <?= $entry['success'] ? 'Successful' : 'Failed' ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>