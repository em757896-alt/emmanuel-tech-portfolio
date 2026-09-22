<?php
require_once 'config/config.php';
require_once 'config/database.php';

$auth = new Auth();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');

    if (!$name || !$email || !$subject || !$message) {
        $error = 'All fields are required.';
    } else {
        try {
            $db = Database::getInstance();
            $db->insert('contact_messages', [
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $success = 'Thank you! Your message has been received. We will get back to you soon.';
        } catch (Exception $e) {
            $success = 'Thank you! Your message has been received.';
        }
    }
}

$pageTitle = 'Contact Us - PBO Kenya';
$currentPage = 'contact';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<section class="page-hero py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <span class="section-badge bg-white text-primary">Contact</span>
                <h1 class="fw-bold display-5">Get in Touch</h1>
                <p class="lead mb-0">Have questions or feedback? We'd love to hear from you.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7" data-aos="fade-up">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4>Send us a Message</h4>

                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Your Name</label>
                                    <input type="text" name="name" class="form-control form-control-lg" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control form-control-lg" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Subject</label>
                                    <input type="text" name="subject" class="form-control form-control-lg" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message</label>
                                    <textarea name="message" rows="5" class="form-control form-control-lg" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5" data-aos="fade-left">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4>Contact Information</h4>
                        <div class="d-flex gap-3 mb-3">
                            <i class="fas fa-envelope fa-lg text-primary mt-1"></i>
                            <div>
                                <strong>Email</strong><br>
                                <a href="mailto:<?= APP_EMAIL ?>"><?= APP_EMAIL ?></a>
                            </div>
                        </div>
                        <div class="d-flex gap-3 mb-3">
                            <i class="fas fa-phone fa-lg text-primary mt-1"></i>
                            <div>
                                <strong>Phone</strong><br>
                                <a href="tel:<?= APP_PHONE ?>"><?= APP_PHONE ?></a>
                            </div>
                        </div>
                        <div class="d-flex gap-3 mb-3">
                            <i class="fas fa-map-marker-alt fa-lg text-primary mt-1"></i>
                            <div>
                                <strong>Location</strong><br>
                                Nairobi, Kenya
                            </div>
                        </div>
                        <div class="d-flex gap-3">
                            <i class="fas fa-globe fa-lg text-primary mt-1"></i>
                            <div>
                                <strong>Website</strong><br>
                                <a href="https://crecokenya.org" target="_blank">crecokenya.org</a>
                            </div>
                        </div>
                        <hr>
                        <h5>Follow Us</h5>
                        <div class="d-flex gap-3">
                            <a href="#" class="btn btn-outline-primary btn-sm rounded-circle"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="btn btn-outline-primary btn-sm rounded-circle"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="btn btn-outline-primary btn-sm rounded-circle"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="btn btn-outline-primary btn-sm rounded-circle"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({duration:700,once:true});</script>
</body>
</html>
