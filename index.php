<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BrightBank - Modern Online Banking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom styles */
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1601597111158-2fceff292cdc?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
        }
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #0d6efd;
        }
        .bank-img {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
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
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light ms-2" href="register.php">Sign Up</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Banking Made Simple</h1>
            <p class="lead mb-4">Send, save, and manage money—all in one place.</p>
            <a href="register.php" class="btn btn-primary btn-lg px-4 me-2">Open an Account</a>
            <a href="#features" class="btn btn-outline-light btn-lg px-4">Learn More</a>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Why Choose BrightBank?</h2>
                <p class="lead text-muted">Fast, secure, and designed for you.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3>Instant Transfers</h3>
                    <p>Send money to anyone, anywhere in seconds.</p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3>Bank-Level Security</h3>
                    <p>Your data is encrypted and protected 24/7.</p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile-Friendly</h3>
                    <p>Bank on the go with our responsive app.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Us -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4">
                    <img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Banking Team" class="img-fluid bank-img">
                </div>
                <div class="col-lg-6">
                    <h2>About BrightBank</h2>
                    <p class="lead">We’re redefining digital banking.</p>
                    <p>Founded in 2023, BrightBank combines cutting-edge technology with personalized service. Our mission is to make financial management effortless for everyone.</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> No hidden fees</li>
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> 24/7 customer support</li>
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> FDIC-insured accounts</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="mb-4">Ready to Get Started?</h2>
            <a href="register.php" class="btn btn-light btn-lg px-4 me-2">Sign Up Free</a>
            <a href="login.php" class="btn btn-outline-light btn-lg px-4">Login</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 bg-dark text-white">
        <div class="container text-center">
            <p class="mb-0">&copy; 2023 BrightBank. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>