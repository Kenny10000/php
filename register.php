<?php include 'includes/config.php';
require_once __DIR__ . '/includes/functions.php';
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <title>BrightBank - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'includes/navbar.php'?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Create Account</h2>
                        <?php
                        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                            $name = trim($_POST['name']);
                            $email = trim($_POST['email']);
                            $password = $_POST['password'];
                            
                            // Generate random account number
                            $account_number = str_pad(mt_rand(1, 9999999999), 10, '0', STR_PAD_LEFT);
                            
                            // Hash password
                            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                            
                            // Insert into DB
                            $stmt = $conn->prepare("INSERT INTO users (name, email, password, account_number) VALUES (?, ?, ?, ?)");
                            $stmt->bind_param("ssss", $name, $email, $hashed_password, $account_number);
                            
                            if ($stmt->execute()) {
                                echo '<div class="alert alert-success">Account created! <a href="login.php">Login here</a></div>';
                            } else {
                                echo '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
                            }
                        }
                        ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </form>
                        <p class="mt-3 text-center">Already have an account? <a href="login.php">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>