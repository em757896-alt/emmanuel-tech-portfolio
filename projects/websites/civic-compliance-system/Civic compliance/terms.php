<?php
require_once 'config/config.php';
$pageTitle = 'Terms of Use - PBO Kenya';
$currentPage = 'terms';
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
                <span class="section-badge bg-white text-primary">Legal</span>
                <h1 class="fw-bold display-5">Terms of Use</h1>
                <p class="lead mb-0">Conditions governing your use of the PBO Kenya Platform.</p>
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
                        <p class="text-muted">Last updated: <?= date('F Y') ?></p>

                        <h4>1. Acceptance of Terms</h4>
                        <p>By accessing or using the PBO Kenya Platform, you agree to be bound by these Terms of Use. If you disagree with any part, you may not access the platform.</p>

                        <h4>2. No Legal Advice</h4>
                        <p>The content on this platform is for informational purposes only and does not constitute legal advice. The PBO Act summaries are plain-language interpretations. Always refer to the official PBO Act 2013 for authoritative legal text. Consult a qualified advocate for specific legal guidance.</p>

                        <h4>3. User Accounts</h4>
                        <p>You are responsible for maintaining the confidentiality of your account credentials. You must provide accurate, current information during registration. Notify us immediately of any unauthorized use.</p>

                        <h4>4. Acceptable Use</h4>
                        <p>You agree not to use the platform for any unlawful purpose or to violate any applicable laws. You may not attempt to gain unauthorized access to any part of the platform.</p>

                        <h4>5. Report Accuracy</h4>
                        <p>When submitting monitoring reports, you agree to provide truthful and accurate information. Knowingly submitting false information may result in account suspension.</p>

                        <h4>6. Intellectual Property</h4>
                        <p>All content, trademarks, and materials on this platform are owned by or licensed to CRECO Kenya. You may download resources for personal or organizational use but may not redistribute without attribution.</p>

                        <h4>7. Limitation of Liability</h4>
                        <p>CRECO Kenya shall not be liable for any indirect, incidental, or consequential damages arising from your use of the platform. The platform is provided "as is" without warranties of any kind.</p>

                        <h4>8. Changes to Terms</h4>
                        <p>We reserve the right to modify these terms at any time. Changes will be posted on this page with an updated date.</p>

                        <h4>9. Contact</h4>
                        <p>For questions about these terms, contact <strong><?= APP_EMAIL ?></strong>.</p>
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
