<?php
$pageTitle = 'Waves Technology & Services - Home';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<section class="hero-section text-center">
    <div class="container">
        <h1><i class="fas fa-headset me-3"></i>Waves Technology & Services</h1>
        <p class="lead mt-3">Smart Customer Support & Ticket Management System</p>
        <p class="mt-2">AI-Powered ticket classification, sentiment analysis, and chatbot support</p>
        <div class="mt-4">
            <a href="login.php" class="btn btn-light btn-lg me-2"><i class="fas fa-sign-in-alt me-2"></i>Login</a>
            <a href="register.php" class="btn btn-outline-light btn-lg me-2"><i class="fas fa-user-plus me-2"></i>Register</a>
        </div>
    </div>
</section>

<section class="container my-5">
    <h2 class="text-center mb-4">Our Features</h2>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card feature-card h-100">
                <i class="fas fa-ticket-alt"></i>
                <h5>Ticket Management</h5>
                <p>Submit, track, and manage support tickets with real-time status updates.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card feature-card h-100">
                <i class="fas fa-brain"></i>
                <h5>AI Classification</h5>
                <p>Automatic ticket categorization and priority assignment using AI.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card feature-card h-100">
                <i class="fas fa-smile"></i>
                <h5>Sentiment Analysis</h5>
                <p>Detect customer urgency and sentiment to prioritize critical issues.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card feature-card h-100">
                <i class="fas fa-robot"></i>
                <h5>Chatbot Support</h5>
                <p>Get instant answers to common questions with our AI chatbot.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card feature-card h-100">
                <i class="fas fa-chart-bar"></i>
                <h5>Reports & Analytics</h5>
                <p>Comprehensive dashboards and reports for performance monitoring.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card feature-card h-100">
                <i class="fas fa-shield-alt"></i>
                <h5>Secure System</h5>
                <p>Role-based access control with encrypted passwords and secure sessions.</p>
            </div>
        </div>
    </div>
</section>

<section class="bg-light py-5">
    <div class="container text-center">
        <h2 class="mb-4">About Our System</h2>
        <p class="lead">Waves Technology & Services provides a complete AI-powered support ticket management solution. Our system helps businesses efficiently manage customer inquiries with intelligent automation, real-time tracking, and comprehensive analytics.</p>
    </div>
</section>

<section class="container my-5">
    <h2 class="text-center mb-4">Contact Us</h2>
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <p><i class="fas fa-envelope me-2 text-primary"></i>support@waves-tech.com</p>
            <p><i class="fas fa-phone me-2 text-primary"></i>+1 234 567 8900</p>
            <p><i class="fas fa-map-marker-alt me-2 text-primary"></i>123 Tech Street, Innovation City</p>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>