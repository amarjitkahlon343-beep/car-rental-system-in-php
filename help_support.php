<?php
session_start();
include('connect.php');

// Optional: Handle a support form submission
if (isset($_POST['send_message'])) {
    $user_id = $_SESSION['user_id'] ?? 0; // 0 if guest
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // You would typically have a 'support_tickets' table for this
    // For now, let's just simulate a success message
    $success = "Your message has been sent! We will get back to you soon.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Help Center | MyProject</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .hero-section { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: white; padding: 60px 0; }
        .search-box { max-width: 600px; margin: -30px auto 0; }
        .accordion-button:not(.collapsed) { background-color: #f8f9fa; color: #0d6efd; }
        .support-card { transition: transform 0.2s; border: none; }
        .support-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body class="bg-light">

<div class="hero-section text-center">
    <div class="container">
        <h1 class="fw-bold">How can we help you?</h1>
        <p class="lead">Search our knowledge base or get in touch with our experts.</p>
    </div>
</div>

<div class="container search-box mb-5">
    <div class="card shadow-sm border-0 rounded-pill p-2">
        <div class="input-group">
            <span class="input-group-text bg-transparent border-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" class="form-control border-0 shadow-none" placeholder="Search for articles, help, and more...">
            <button class="btn btn-primary rounded-pill px-4">Search</button>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row g-4">
        
        <div class="col-lg-7">
            <h4 class="fw-bold mb-4">Frequently Asked Questions</h4>
            <div class="accordion" id="helpAccordion">
                
                <div class="accordion-item shadow-sm mb-3 border-0">
                    <h2 class="accordion-header">
                        <button class="accordion-button rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            <i class="bi bi-file-earmark-pdf me-2 text-primary"></i> How do I download my payment receipt?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#helpAccordion">
                        <div class="accordion-body text-muted">
                            Go to your <strong>Transaction History</strong> in the dashboard. Click the "View Receipt" button next to your successful payment to generate a PDF instantly.
                        </div>
                    </div>
                </div>

                <div class="accordion-item shadow-sm mb-3 border-0">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            <i class="bi bi-exclamation-triangle me-2 text-warning"></i> What should I do if my payment fails?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                        <div class="accordion-body text-muted">
                            If your payment was declined, check your internet and card details. You can retry the payment from the <strong>Pending Bookings</strong> section in your profile.
                        </div>
                    </div>
                </div>

                <div class="accordion-item shadow-sm mb-3 border-0">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            <i class="bi bi-person-gear me-2 text-success"></i> How can I change my profile details?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                        <div class="accordion-body text-muted">
                            Navigate to <strong>Profile Settings</strong>. You can update your phone number, address, and password there. Note that email addresses are fixed for security.
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Send us a Message</h5>
                    <?php if (isset($success))
                        echo "<div class='alert alert-success small'>$success</div>"; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Subject</label>
                            <select name="subject" class="form-select bg-light" required>
                                <option value="">Select a topic</option>
                                <option value="Payment Issue">Payment Issue</option>
                                <option value="Booking Error">Booking Error</option>
                                <option value="Account Access">Account Access</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">How can we help?</label>
                            <textarea name="message" class="form-control bg-light" rows="4" placeholder="Describe your issue..." required></textarea>
                        </div>
                        <button type="submit" name="send_message" class="btn btn-primary w-100 fw-bold">Submit Ticket</button>
                    </form>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-6">
                    <a href="mailto:support@myproject.com" class="text-decoration-none">
                        <div class="card support-card text-center p-3 bg-white">
                            <i class="bi bi-envelope-at fs-3 text-primary mb-2"></i>
                            <span class="small fw-bold text-dark d-block">Email Us</span>
                        </div>
                    </a>
                </div>
                <div class="col-6">
                    <a href="tel:+919876543210" class="text-decoration-none">
                        <div class="card support-card text-center p-3 bg-white">
                            <i class="bi bi-telephone-outbound fs-3 text-success mb-2"></i>
                            <span class="small fw-bold text-dark d-block">Call Support</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>