<?php
require_once 'config/config.php';
$pageTitle = 'About CRECO Kenya - PBO Kenya';
$currentPage = 'about';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <meta name="description" content="About CRECO Kenya - Constitution and Reform Education Consortium">
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
                <span class="section-badge bg-white text-primary">About</span>
                <h1 class="fw-bold display-5">About CRECO Kenya</h1>
                <p class="lead mb-0">Constitution and Reform Education Consortium — strengthening civil society through legal knowledge.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8" data-aos="fade-up">
                <h2>Our Mission</h2>
                <p>CRECO Kenya is a consortium of civil society organizations committed to promoting constitutionalism, democratic governance, and human rights in Kenya. We work to strengthen the capacity of civil society organizations (CSOs) to effectively participate in governance processes and advocate for their rights.</p>
                <p>This PBO Compliance Platform was developed to provide clear, accessible legal information and practical compliance tools for Public Benefit Organizations operating under the PBO Act 2013.</p>

                <h3 class="mt-4">What We Do</h3>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <div class="d-flex gap-3">
                            <i class="fas fa-gavel fa-2x text-primary"></i>
                            <div>
                                <h6>Legal Education</h6>
                                <p class="text-muted small">Providing plain-language legal summaries and resources for CSOs</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-3">
                            <i class="fas fa-clipboard-check fa-2x text-success"></i>
                            <div>
                                <h6>Compliance Support</h6>
                                <p class="text-muted small">Tools and guidance to help PBOs meet regulatory requirements</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-3">
                            <i class="fas fa-shield-alt fa-2x text-danger"></i>
                            <div>
                                <h6>Civic Space Monitoring</h6>
                                <p class="text-muted small">Tracking and reporting civic space conditions across Kenya</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-3">
                            <i class="fas fa-handshake fa-2x text-info"></i>
                            <div>
                                <h6>Advocacy</h6>
                                <p class="text-muted small">Advocating for a enabling environment for civil society</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-left">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5><i class="fas fa-info-circle text-primary me-2"></i>Quick Info</h5>
                        <hr>
                        <p><strong><i class="fas fa-globe me-2"></i>Website:</strong><br><a href="https://crecokenya.org">crecokenya.org</a></p>
                        <p><strong><i class="fas fa-envelope me-2"></i>Email:</strong><br><?= APP_EMAIL ?></p>
                        <p><strong><i class="fas fa-phone me-2"></i>Phone:</strong><br><?= APP_PHONE ?></p>
                        <p><strong><i class="fas fa-map-marker-alt me-2"></i>Location:</strong><br>Nairobi, Kenya</p>
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
