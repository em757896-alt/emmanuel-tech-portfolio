<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/auth.php';

$auth = new Auth();
$db = Database::getInstance();
$counties = KENYA_COUNTIES;
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportType = sanitizeInput($_POST['report_type'] ?? '');
    $orgName = sanitizeInput($_POST['organization_name'] ?? '');
    $orgType = sanitizeInput($_POST['organization_type'] ?? '');
    $county = sanitizeInput($_POST['county'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $severity = sanitizeInput($_POST['severity'] ?? 'medium');
    $consent = isset($_POST['consent']);

    if (empty($county) || empty($description) || !$consent) {
        $error = 'Please fill in all required fields and provide consent.';
    } else {
        try {
            $db->insert('monitoring_reports', [
                'report_number' => generateReportNumber(),
                'user_id' => $_SESSION['user_id'] ?? 0,
                'organization_name' => $orgName ?: 'Anonymous',
                'organization_type' => $orgType,
                'county' => $county,
                'report_type' => in_array($reportType, ['registration','compliance','barrier','delay','violation','enabling_practice','other']) ? $reportType : 'other',
                'title' => sanitizeInput($_POST['title'] ?? 'Report'),
                'description' => $description,
                'severity' => $severity,
                'status' => 'submitted',
                'is_anonymous' => isset($_POST['is_anonymous']) ? 1 : 0,
                'consent_to_publish' => isset($_POST['consent_to_publish']) ? 1 : 0,
            ]);
            $success = 'Your report has been submitted successfully. Thank you for contributing to civic space monitoring.';
        } catch (Exception $e) {
            $error = 'Submission failed. Please try again.';
            error_log('Report submission error: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Submit Report - PBO Kenya';
$currentPage = 'monitoring';
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
    <link href="../../assets/css/style.css" rel="stylesheet">
    <link href="../../assets/css/monitoring.css" rel="stylesheet">
</head>
<body>
<?php include '../../includes/navbar.php'; ?>

<section class="page-hero py-5 bg-danger text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <span class="section-badge bg-white text-danger">Report</span>
                <h1 class="fw-bold display-5">Submit a Monitoring Report</h1>
                <p class="lead mb-0">Share your experience with PBO registration, compliance, or civic space conditions.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

                <div class="card border-0 shadow-sm" data-aos="fade-up">
                    <div class="card-body p-4">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Report Type <span class="text-danger">*</span></label>
                                    <select name="report_type" class="form-select form-select-lg" required>
                                        <option value="">Select type...</option>
                                        <option value="registration">Registration Experience</option>
                                        <option value="compliance">Compliance Challenge</option>
                                        <option value="barrier">Administrative Barrier</option>
                                        <option value="delay">Registration Delay</option>
                                        <option value="violation">Civic Space Violation</option>
                                        <option value="enabling_practice">Enabling Practice</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Organization Name</label>
                                    <input type="text" name="organization_name" class="form-control form-control-lg" placeholder="Your organization name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Organization Type</label>
                                    <input type="text" name="organization_type" class="form-control form-control-lg" placeholder="e.g., NGO, CBO, FBO">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">County <span class="text-danger">*</span></label>
                                    <select name="county" class="form-select form-select-lg" required>
                                        <option value="">Select County</option>
                                        <?php foreach ($counties as $c): ?>
                                        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Severity Level</label>
                                    <select name="severity" class="form-select form-select-lg">
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                        <option value="critical">Critical</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control form-control-lg" placeholder="Brief title for your report" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea name="description" rows="6" class="form-control form-control-lg" placeholder="Please describe your experience in detail..." required></textarea>
                                    <small class="text-muted">Include dates, locations, and any relevant details.</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Supporting Documents (optional)</label>
                                    <input type="file" name="documents[]" class="form-control" multiple>
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="checkbox" id="is_anonymous" name="is_anonymous" class="form-check-input" value="1">
                                        <label for="is_anonymous" class="form-check-label">Submit anonymously</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" id="consent_to_publish" name="consent_to_publish" class="form-check-input" value="1">
                                        <label for="consent_to_publish" class="form-check-label">I consent to publishing anonymized data from this report</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" id="consent" name="consent" class="form-check-input" value="1" required>
                                        <label for="consent" class="form-check-label">
                                            I confirm that the information provided is accurate to the best of my knowledge <span class="text-danger">*</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-danger btn-lg w-100">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Report
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({duration:700,once:true});</script>
</body>
</html>
