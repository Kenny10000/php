<?php
/**
 * Banking Application - Core Functions
 * Includes database utilities, security functions, and banking operations
 */

/**
 * Redirect to another page
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Sanitize user input
 * @param string $data Input to sanitize
 * @return string Sanitized output
 */
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a random account number
 * @return string 12-digit account number
 */
function generate_account_number() {
    return str_pad(mt_rand(1, 9999999999), 10, '0', STR_PAD_LEFT);
}

/**
 * Format currency with 2 decimal places
 * @param float $amount Amount to format
 * @return string Formatted currency
 */
function format_currency($amount) {
    return '#' . number_format($amount, 2);
}

/**
 * Log transaction activity
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @param string $action Action performed
 * @param string $details Additional details
 */
function log_activity($conn, $user_id, $action, $details = '') {
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) 
                           VALUES (?, ?, ?, ?)");
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt->bind_param("isss", $user_id, $action, $details, $ip);
    $stmt->execute();
}

/**
 * Verify if user has sufficient balance
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @param float $amount Amount to check
 * @return bool True if sufficient balance
 */
function has_sufficient_balance($conn, $user_id, $amount) {
    $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    return ($user['balance'] >= $amount);
}

/**
 * Process money transfer between accounts
 * @param mysqli $conn Database connection
 * @param int $sender_id Sender user ID
 * @param int $receiver_id Receiver user ID
 * @param float $amount Amount to transfer
 * @param string $notes Transaction notes
 * @return array [success: bool, message: string]
 */
function transfer_money($conn, $sender_id, $receiver_id, $amount, $notes = '') {
    if ($amount <= 0) {
        return ['success' => false, 'message' => 'Amount must be positive'];
    }

    if (!has_sufficient_balance($conn, $sender_id, $amount)) {
        return ['success' => false, 'message' => 'Insufficient balance'];
    }

    $conn->begin_transaction(); 
    try {
        // Deduct from sender
        $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmt->bind_param("di", $amount, $sender_id);
        $stmt->execute();

        // Add to receiver
        $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->bind_param("di", $amount, $receiver_id);
        $stmt->execute();

        // Record transaction
        $stmt = $conn->prepare("INSERT INTO transactions 
                              (sender_id, receiver_id, amount, notes, status) 
                              VALUES (?, ?, ?, ?, 'completed')");
        $stmt->bind_param("iids", $sender_id, $receiver_id, $amount, $notes);
        $stmt->execute();

        $conn->commit();
        log_activity($conn, $sender_id, 'money_transfer', "Sent $amount to $receiver_id");
        return ['success' => true, 'message' => 'Transfer successful'];
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Transfer Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Transfer failed'];
    }
}

/**
 * Process deposit
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @param float $amount Amount to deposit
 * @param string $notes Deposit notes
 * @return array [success: bool, message: string]
 */
function process_deposit($conn, $user_id, $amount, $notes = '') {
    if ($amount <= 0) {
        return ['success' => false, 'message' => 'Amount must be positive'];
    }

    $conn->begin_transaction();
    try {
        // Add to user balance (sender_id 0 represents the bank)
        $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->bind_param("di", $amount, $user_id);
        $stmt->execute();

        // Record transaction
        $stmt = $conn->prepare("INSERT INTO transactions 
                              (sender_id, receiver_id, amount, notes, status) 
                              VALUES (0, ?, ?, ?, 'completed')");
        $stmt->bind_param("ids", $user_id, $amount, $notes);
        $stmt->execute();

        $conn->commit();
        log_activity($conn, $user_id, 'deposit', "Deposited $amount");
        return ['success' => true, 'message' => 'Deposit successful'];
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Deposit Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Deposit failed'];
    }
}

/**
 * Process withdrawal
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @param float $amount Amount to withdraw
 * @param string $notes Withdrawal notes
 * @return array [success: bool, message: string]
 */
function process_withdrawal($conn, $user_id, $amount, $notes = '') {
    if ($amount <= 0) {
        return ['success' => false, 'message' => 'Amount must be positive'];
    }

    if (!has_sufficient_balance($conn, $user_id, $amount)) {
        return ['success' => false, 'message' => 'Insufficient balance'];
    }

    $conn->begin_transaction();
    try {
        // Deduct from user balance (receiver_id 0 represents the bank)
        $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmt->bind_param("di", $amount, $user_id);
        $stmt->execute();

        // Record transaction
        $stmt = $conn->prepare("INSERT INTO transactions 
                              (sender_id, receiver_id, amount, notes, status) 
                              VALUES (?, 0, ?, ?, 'completed')");
        $stmt->bind_param("ids", $user_id, $amount, $notes);
        $stmt->execute();

        $conn->commit();
        log_activity($conn, $user_id, 'withdrawal', "Withdrew $amount");
        return ['success' => true, 'message' => 'Withdrawal successful'];
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Withdrawal Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Withdrawal failed'];
    }
}

/**
 * Get user transactions with pagination
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @param int $page Page number
 * @param int $per_page Items per page
 * @return array [transactions: array, total: int]
 */
function get_user_transactions($conn, $user_id, $page = 1, $per_page = 10) {
    $offset = ($page - 1) * $per_page;
    
    // Get transactions
    $stmt = $conn->prepare("SELECT t.*, 
                           u1.name as sender_name, u1.account_number as sender_account,
                           u2.name as receiver_name, u2.account_number as receiver_account
                           FROM transactions t
                           LEFT JOIN users u1 ON t.sender_id = u1.id
                           LEFT JOIN users u2 ON t.receiver_id = u2.id
                           WHERE t.sender_id = ? OR t.receiver_id = ?
                           ORDER BY t.date DESC
                           LIMIT ? OFFSET ?");
    $stmt->bind_param("iiii", $user_id, $user_id, $per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $transactions = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get total count
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total 
                                FROM transactions 
                                WHERE sender_id = ? OR receiver_id = ?");
    $count_stmt->bind_param("ii", $user_id, $user_id);
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'];
    
    return [
        'transactions' => $transactions,
        'total' => $total
    ];
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if valid
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get user account details
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @return array|null User data or null if not found
 */
function get_user_account($conn, $user_id) {
    $stmt = $conn->prepare("SELECT id, name, email, account_number, balance, created_at 
                           FROM users 
                           WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function get_device_name($user_agent) {
    if (strpos($user_agent, 'Windows') !== false) return 'Windows PC';
    if (strpos($user_agent, 'Macintosh') !== false) return 'Mac Computer';
    if (strpos($user_agent, 'Linux') !== false) return 'Linux PC';
    if (strpos($user_agent, 'iPhone') !== false) return 'iPhone';
    if (strpos($user_agent, 'iPad') !== false) return 'iPad';
    if (strpos($user_agent, 'Android') !== false) return 'Android Device';
    return 'Unknown Device';
}

function send_email($to, $subject, $message) {
    // Set content-type header for HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

    // Additional headers
    $headers .= "From: BrightBank<no-reply@yourdomain.com>" . "\r\n";
    $headers .= "Reply-To: support@yourdomain.com" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Word-wrap the message for better email client compatibility
    $message = wordwrap($message, 70);

    // Send the email
    return mail($to, $subject, $message, $headers);
}
