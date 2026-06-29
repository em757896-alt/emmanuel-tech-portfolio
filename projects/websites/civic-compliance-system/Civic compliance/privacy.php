<?php
require_once 'config/config.php';
$pageTitle = 'Privacy Policy - PBO Kenya';
$currentPage = 'privacy';
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
                <h1 class="fw-bold display-5">Privacy Policy</h1>
                <p class="lead mb-0">How we collect, use, and protect your personal data.</p>
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

                        <h4>1. Introduction</h4>
                        <p>CRECO Kenya ("we," "our," or "us") operates the PBO Kenya Platform. This policy explains how we collect, use, disclose, and safeguard your information when you visit our platform.</p>

                        <h4>2. Information We Collect</h4>
                        <p><strong>Personal Information:</strong> Name, email address, phone number, organization details, and county location when you register or submit reports.</p>
                        <p><strong>Usage Data:</strong> Pages visited, time spent, browser type, device information, and IP address for analytics.</p>

                        <h4>3. How We Use Your Information</h4>
                        <ul>
                            <li>To provide and maintain our platform services</li>
                            <li>To process monitoring reports and compliance assessments</li>
                            <li>To communicate important updates and regulatory changes</li>
                            <li>To improve platform functionality and user experience</li>
                            <li>To generate anonymized statistical reports on civic space conditions</li>
                        </ul>

                        <h4>4. Data Protection</h4>
                        <p>We implement appropriate technical and organizational measures to protect your personal data in accordance with Kenya's Data Protection Act 2019. This includes encryption, access controls, and regular security audits.</p>

                        <h4>5. Data Retention</h4>
                        <p>We retain your personal data only as long as necessary for the purposes outlined in this policy. Monitoring reports may be retained longer for statistical and advocacy purposes, with personal identifiers removed.</p>

                        <h4>6. Your Rights</h4>
                        <p>Under Kenya's Data Protection Act 2019, you have the right to:</p>
                        <ul>
                            <li>Access your personal data</li>
                            <li>Correct inaccurate data</li>
                            <li>Delete your data (subject to legal obligations)</li>
                            <li>Object to processing of your data</li>
                            <li>Data portability</li>
                        </ul>

                        <h4>7. Third-Party Sharing</h4>
                        <p>We do not sell your personal information. We may share anonymized, aggregated data for research and advocacy purposes. Reports submitted through the monitoring module may be shared with partner organizations working on civic space protection, with your consent.</p>

                        <h4>8. Contact Us</h4>
                        <p>For privacy-related inquiries, contact us at <strong><?= APP_EMAIL ?></strong> or write to CRECO Kenya, Nairobi, Kenya.</p>
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
