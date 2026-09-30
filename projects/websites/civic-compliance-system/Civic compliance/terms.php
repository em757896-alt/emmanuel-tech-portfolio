<?php
require_once 'config/config.php';
$pageTitle = 'Terms of Use';
$currentPage = 'terms';
$lastUpdated = date('F d, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= APP_NAME ?></title>
    <meta name="description" content="Terms of Use for <?= APP_NAME ?>. The conditions governing your access to and use of the Platform.">
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
                <p class="lead mb-0">Conditions governing your access to and use of <?= APP_NAME ?>. Last updated: <?= htmlspecialchars($lastUpdated) ?>.</p>
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
                        <p class="text-muted">Last updated: <?= htmlspecialchars($lastUpdated) ?></p>

                        <h4>1. Acceptance of Terms</h4>
                        <p>By accessing or using <?= APP_NAME ?> (the "Platform"), you agree to be bound by these Terms of Use and our <a href="/privacy.php">Privacy Policy</a>. If you do not agree with any part of these Terms, you may not access or use the Platform.</p>

                        <h4>2. Description of the Platform</h4>
                        <p>The Platform provides legal awareness resources, compliance guidance and tools, civic space monitoring, incident reporting, and educational content relating to the PBO (Public Benefit Organizations) Act, 2013 of Kenya. The Platform also offers password-protected user, organizational, and administrative dashboards.</p>

                        <h4>3. No Legal Advice</h4>
                        <p>The content on the Platform is for informational and educational purposes only and does <strong>not</strong> constitute legal advice. PBO Act summaries are plain-language interpretations, not authoritative legal text. Always refer to the official PBO Act 2013 (or other applicable legislation) for authoritative provisions, and consult a qualified legal professional for advice specific to your circumstances.</p>

                        <h4>4. User Accounts</h4>
                        <p>Certain features require an account. You are responsible for maintaining the confidentiality of your account credentials and for all activity that occurs under your account. You must provide accurate and current information during registration and keep it updated. Notify us immediately of any unauthorized use of your account.</p>

                        <h4>5. Acceptable Use</h4>
                        <p>You agree not to:</p>
                        <ul>
                            <li>Use the Platform for any unlawful purpose or in violation of applicable laws or regulations</li>
                            <li>Attempt to gain unauthorized access to any part of the Platform, other accounts, or connected systems</li>
                            <li>Interfere with or disrupt the Platform, its servers, or networks</li>
                            <li>Upload malicious code, viruses, or any harmful or disruptive files</li>
                            <li>Impersonate any person or entity, or misrepresent your affiliation with any organization</li>
                            <li>Submit content that is unlawful, defamatory, abusive, harassing, or that infringes the rights of others</li>
                            <li>Scrape, harvest, or collect data about other users without consent</li>
                        </ul>

                        <h4>6. Report Accuracy</h4>
                        <p>When submitting compliance assessments, incident reports, or monitoring submissions, you agree to provide truthful and accurate information. Knowingly submitting false or misleading information may result in account suspension or termination. Where you submit information about third parties, you confirm that you are authorized to do so.</p>

                        <h4>7. Intellectual Property</h4>
                        <p>All content, trademarks, logos, and materials on the Platform (including text, graphics, forms, and educational resources) are owned by or licensed to Elevate Media Productions, except where otherwise noted. You may download resources for personal or organizational use, but you may not reproduce, redistribute, modify, or commercially exploit them without prior written permission and appropriate attribution.</p>

                        <h4>8. Disclaimer of Warranties</h4>
                        <p>The Platform and all content, tools, and features are provided on an "as is" and "as available" basis without warranties of any kind, whether express or implied, including but not limited to implied warranties of merchantability, fitness for a particular purpose, accuracy, and non-infringement. We do not warrant that the Platform will be uninterrupted, error-free, or free of harmful components.</p>

                        <h4>9. Limitation of Liability</h4>
                        <p>To the maximum extent permitted by law, Elevate Media Productions shall not be liable for any indirect, incidental, special, consequential, or punitive damages, or any loss of profits, data, or goodwill, arising from or related to your use of, or inability to use, the Platform. Your sole and exclusive remedy for dissatisfaction with the Platform is to stop using it.</p>

                        <h4>10. Termination</h4>
                        <p>We reserve the right to suspend or terminate your access to the Platform, in whole or in part, at any time and without notice, where you violate these Terms or where such action is necessary to protect the Platform, its users, or the law. Upon termination, any rights granted to you under these Terms cease immediately.</p>

                        <h4>11. Changes to These Terms</h4>
                        <p>We reserve the right to modify these Terms at any time. Changes will be posted on this page with an updated "Last updated" date. Continued use of the Platform after changes take effect constitutes acceptance of the updated Terms.</p>

                        <h4>12. Governing Law</h4>
                        <p>These Terms shall be governed by and construed in accordance with the laws of the Republic of Kenya. Any disputes arising under or in connection with these Terms shall be subject to the exclusive jurisdiction of the courts of Kenya.</p>

                        <h4>13. Contact</h4>
                        <p>For questions about these Terms, contact us at <strong><?= htmlspecialchars(APP_EMAIL) ?></strong> or by phone at <strong><?= htmlspecialchars(APP_PHONE) ?></strong>.</p>

                        <div class="mt-4 p-3 rounded" style="background:rgba(255,0,0,.06);font-size:.8rem;color:#6c757d;border:1px solid rgba(255,0,0,.15);">
                            <strong>DISCLAIMER:</strong> This website is a practice / portfolio project created for educational purposes only. It is <strong>NOT affiliated with</strong> any real organization or government body. Nothing on this site constitutes legal advice.
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