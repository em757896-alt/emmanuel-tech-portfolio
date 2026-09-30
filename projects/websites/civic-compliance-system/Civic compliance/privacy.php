<?php
require_once 'config/config.php';
$pageTitle = 'Privacy Policy';
$currentPage = 'privacy';
$lastUpdated = date('F d, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= APP_NAME ?></title>
    <meta name="description" content="Privacy Policy for <?= APP_NAME ?>. Learn how we collect, use, protect, and manage your personal data in accordance with the Kenya Data Protection Act, 2019.">
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
                <p class="lead mb-0">How we collect, use, protect, and manage your personal data. Last updated: <?= htmlspecialchars($lastUpdated) ?>.</p>
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

                        <h4>1. Introduction</h4>
                        <p>Elevate Media Productions ("we," "our," or "us") operates <?= APP_NAME ?> (the "Platform"). This Privacy Policy explains how we collect, use, disclose, store, and safeguard personal information when you visit or use the Platform, including through the Platform's registration, login, compliance assessment, incident reporting, monitoring, and dashboard features.</p>
                        <p>We are committed to protecting your privacy and handling your personal data lawfully, fairly, and transparently in accordance with the <strong>Data Protection Act, 2019</strong> of Kenya, the <strong>Constitution of Kenya</strong>, and other applicable data protection laws.</p>

                        <h4>2. Information We Collect</h4>
                        <p><strong>Information you provide directly:</strong> When you register, log in, or interact with the Platform's features, we may collect your name, email address, phone number, organization details, county location, account credentials (username and password), and any information you choose to submit through compliance checklists, self-assessments, incident reports, and monitoring submissions. If you attach supporting documents, we collect those files as well.</p>
                        <p><strong>Usage and device data:</strong> When you use the Platform, we may automatically collect pages visited, time spent, browser type and version, device information, operating system, Internet Protocol (IP) address, and referring URLs for analytics, security, and improvement purposes.</p>
                        <p><strong>Cookies and similar technologies:</strong> The Platform uses cookies and similar technologies to maintain your session, remember your preferences, improve performance, and gather aggregated analytics. See Section 6 below.</p>

                        <h4>3. How We Use Your Information</h4>
                        <ul>
                            <li>To create and manage your account and authenticate your identity</li>
                            <li>To provide and maintain the Platform's features and services</li>
                            <li>To process compliance assessments, incident reports, and monitoring submissions</li>
                            <li>To communicate important updates, service notices, and regulatory information</li>
                            <li>To improve Platform functionality, security, and user experience</li>
                            <li>To generate anonymized, aggregated statistical reports on civic space conditions</li>
                            <li>To detect, prevent, and respond to fraud, abuse, or security incidents</li>
                            <li>To comply with our legal and regulatory obligations</li>
                        </ul>

                        <h4>4. Legal Basis for Processing</h4>
                        <p>We process personal data on the following lawful bases under the Data Protection Act, 2019:</p>
                        <ul>
                            <li><strong>Consent:</strong> where you have freely given specific, informed, and unambiguous consent</li>
                            <li><strong>Performance of a contract:</strong> where processing is necessary to provide services you have requested</li>
                            <li><strong>Legal obligation:</strong> where processing is required to comply with applicable law</li>
                            <li><strong>Legitimate interests:</strong> where processing is necessary for our legitimate interests (or those of a third party), provided your rights and freedoms are not overridden</li>
                        </ul>

                        <h4>5. How We Share Your Information</h4>
                        <p>We do <strong>not sell, rent, or trade</strong> your personal information. We may share information in the following limited circumstances:</p>
                        <ul>
                            <li>With service providers who help us operate the Platform (e.g., hosting, analytics, and support), under appropriate confidentiality and security safeguards</li>
                            <li>With partner organizations working on civic space protection where you have given consent</li>
                            <li>With government authorities or law enforcement where required by law or to protect legal rights</li>
                            <li>As anonymized or aggregated data for research and advocacy purposes that does not identify you personally</li>
                        </ul>

                        <h4>6. Cookies and Similar Technologies</h4>
                        <p>The Platform uses cookies to improve your experience. These include essential session cookies required for login and security, preference cookies that remember choices, and analytics cookies that help us understand usage. You can control or delete cookies through your browser settings; however, disabling essential cookies may prevent you from logging in or using certain features.</p>
                        <p><strong>Bot protection (Cloudflare Turnstile).</strong> Our sign-in and registration forms use Cloudflare Turnstile, a CAPTCHA service, to prevent automated abuse, bots and credential-stuffing attacks. Turnstile may place a cookie named <code>cf-turnstile-response</code> and may process your IP address and browser characteristics in order to determine whether you are human. This processing is carried out by Cloudflare, Inc. and is subject to Cloudflare's own privacy policy. If you do not complete the verification, you will not be able to sign in or create an account.</p>

                        <h4>7. Data Security</h4>
                        <p>We implement appropriate technical and organizational measures to safeguard your personal data, including encryption in transit (HTTPS), secure password hashing, access controls, session management, input validation, and regular security testing. While no method of transmission over the Internet or electronic storage is completely secure, we strive to protect your information and to comply with the standards required under the Data Protection Act, 2019.</p>

                        <h4>8. Data Retention</h4>
                        <p>We retain personal data only for as long as necessary to fulfill the purposes described in this Policy, to comply with our legal obligations, to resolve disputes, and to enforce our agreements. Where monitoring reports are retained for statistical and advocacy purposes, personal identifiers are removed or anonymized wherever possible.</p>

                        <h4>9. Your Rights</h4>
                        <p>Under the Data Protection Act, 2019, you have the right to:</p>
                        <ul>
                            <li><strong>Access</strong> your personal data processed by us</li>
                            <li><strong>Correct</strong> inaccurate or incomplete data</li>
                            <li><strong>Delete</strong> your data, subject to applicable legal obligations</li>
                            <li><strong>Object</strong> to processing of your data in certain circumstances</li>
                            <li><strong>Restrict</strong> processing in certain circumstances</li>
                            <li><strong>Data portability</strong> — receive your data in a structured, commonly used format</li>
                            <li><strong>Withdraw consent</strong> at any time where processing is based on consent</li>
                            <li><strong>Lodge a complaint</strong> with the Office of the Data Protection Commissioner (ODPC) of Kenya</li>
                        </ul>
                        <p>To exercise any of these rights, contact us using the details in Section 11. We will respond within the timeframes required by law.</p>

                        <h4>10. Changes to This Privacy Policy</h4>
                        <p>We may update this Privacy Policy from time to time. Any changes will be posted on this page with an updated "Last updated" date. Where changes are significant, we may also notify you by email or by a notice on the Platform. Continued use of the Platform after changes take effect constitutes acceptance of the updated Policy.</p>

                        <h4>11. Contact Us</h4>
                        <p>For questions, concerns, or requests relating to this Privacy Policy or your personal data, please contact:</p>
                        <ul>
                            <li><strong>Data Controller:</strong> Elevate Media Productions</li>
                            <li><strong>Email:</strong> <?= htmlspecialchars(APP_EMAIL) ?></li>
                            <li><strong>Phone:</strong> <?= htmlspecialchars(APP_PHONE) ?></li>
                            <li><strong>Complaints:</strong> You may also contact the Office of the Data Protection Commissioner (ODPC), Kenya at <a href="https://www.odpc.go.ke" target="_blank" rel="noopener">www.odpc.go.ke</a></li>
                        </ul>

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