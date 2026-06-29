<?php
require_once 'config/config.php';
$pageTitle = 'Accessibility - PBO Kenya';
$currentPage = 'accessibility';
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
                <span class="section-badge bg-white text-primary">Accessibility</span>
                <h1 class="fw-bold display-5">Accessibility Statement</h1>
                <p class="lead mb-0">Our commitment to making the platform accessible to all users.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10" data-aos="fade-up">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <h4>Our Commitment</h4>
                        <p>The PBO Kenya Platform is committed to ensuring digital accessibility for people with disabilities. We continuously improve the user experience for everyone and apply the relevant accessibility standards.</p>

                        <h4>Standards</h4>
                        <p>We aim to conform to WCAG 2.1 Level AA guidelines. Our platform features:</p>
                        <ul>
                            <li>Clear, consistent navigation structure</li>
                            <li>Proper heading hierarchy</li>
                            <li>Alt text on images</li>
                            <li>Sufficient color contrast ratios</li>
                            <li>Keyboard navigable interfaces</li>
                            <li>Screen reader compatible content</li>
                            <li>Responsive design for various devices</li>
                        </ul>

                        <h4>Limitations</h4>
                        <p>Some third-party content (embedded videos, PDF documents) may not be fully accessible. We are working with partners to improve this.</p>

                        <h4>Contact Us</h4>
                        <p>If you encounter accessibility barriers, please contact us at <strong><?= APP_EMAIL ?></strong>. We will make every reasonable effort to make the content accessible.</p>
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
