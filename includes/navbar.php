<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="fas fa-piggy-bank me-2"></i>SecureBank
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <!-- Dashboard Link -->
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                        <i class="fas fa-home me-1"></i> Dashboard
                    </a>
                </li>

                <!-- Transfer Link -->
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'transfer.php' ? 'active' : '' ?>" href="transfer.php">
                        <i class="fas fa-exchange-alt me-1"></i> Transfer
                    </a>
                </li>

                <!-- Withdraw Link -->
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'withdraw.php' ? 'active' : '' ?>" href="withdraw.php">
                        <i class="fas fa-exchange-alt me-1"></i> Withdraw
                    </a>
                </li>

                
                <!-- Transactions Link -->
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : '' ?>" href="transactions.php">
                        <i class="fas fa-history me-1"></i> Transactions
                    </a>
                </li> 
            </ul>

            <!-- User Dropdown (Visible when logged in) -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> 
                            <?= htmlspecialchars($_SESSION['user_name'] ?? 'Account') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            <?php else: ?>
                <!-- Login/Register Buttons (Visible when logged out) -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-1"></i> Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light ms-2" href="register.php"><i class="fas fa-user-plus me-1"></i> Register</a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>