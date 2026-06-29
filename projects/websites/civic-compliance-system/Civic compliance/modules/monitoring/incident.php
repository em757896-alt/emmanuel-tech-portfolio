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
    $incidentType = sanitizeInput($_POST['incident_type'] ?? '');
    $incidentDate = $_POST['incident_date'] ?? '';
    $location = sanitizeInput($_POST['location'] ?? '');
    $county = sanitizeInput($_POST['county'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $perpetratorType = sanitizeInput($_POST['perpetrator_type'] ?? '');
    $urgencyLevel = sanitizeInput($_POST['urgency_level'] ?? 'medium');
    $consent = isset($_POST['consent']);

    if (empty($incidentType) || empty($incidentDate) || empty($location) || empty($county) || empty($description) || !$consent) {
        $error = 'Please fill in all required fields and provide consent.';
    } else {
        try {
            $allowedTypes = ['harassment','intimidation','arbitrary_arrest','deregistration','funding_block','assembly_denial','other'];
            $db->insert('incident_reports', [
                'incident_number' => generateReportNumber('INC'),
                'reporter_id' => $_SESSION['user_id'] ?? 0,
                'incident_type' => in_array($incidentType, $allowedTypes) ? $incidentType : 'other',
                'incident_date' => $incidentDate,
                'location' => $location,
                'county' => $county,
                'description' => $description,
                'perpetrator_type' => in_array($perpetratorType, ['government_agency','police','county_government','private_actor','unknown']) ? $perpetratorType : null,
                'urgency_level' => $urgencyLevel,
                'status' => 'reported',
                'is_confidential' => isset($_POST['is_confidential']) ? 1 : 0,
            ]);
            $success = 'Your incident report has been submitted. It will be reviewed by the CRECO Kenya team.';
        } catch (Exception $e) {
            $error = 'Submission failed. Please try again.';
            error_log('Incident submission error: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Report Incident - PBO Kenya';
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
                <span class="section-badge bg-white text-danger">Incident</span>
                <h1 class="fw-bold display-5">Report a Civic Space Incident</h1>
                <p class="lead mb-0">Report harassment, intimidation, arbitrary arrests, or other civic space violations.</p>
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
                        <form method="POST" action="">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Incident Type <span class="text-danger">*</span></label>
                                    <select name="incident_type" class="form-select form-select-lg" required>
                                        <option value="">Select type...</option>
                                        <option value="harassment">Harassment</option>
                                        <option value="intimidation">Intimidation</option>
                                        <option value="arbitrary_arrest">Arbitrary Arrest</option>
                                        <option value="deregistration">Unfair Deregistration</option>
                                        <option value="funding_block">Funding Block</option>
                                        <option value="assembly_denial">Assembly Denial</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Incident Date <span class="text-danger">*</span></label>
                                    <input type="date" name="incident_date" class="form-control form-control-lg" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Location <span class="text-danger">*</span></label>
                                    <input type="text" name="location" class="form-control form-control-lg" placeholder="Specific location" required>
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
                                    <label class="form-label">Perpetrator Type</label>
                                    <select name="perpetrator_type" class="form-select form-select-lg">
                                        <option value="">Unknown / Prefer not to say</option>
                                        <option value="government_agency">Government Agency</option>
                                        <option value="police">Police</option>
                                        <option value="county_government">County Government</option>
                                        <option value="private_actor">Private Actor</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Urgency Level</label>
                                    <select name="urgency_level" class="form-select form-select-lg">
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea name="description" rows="7" class="form-control form-control-lg" placeholder="Describe what happened in detail. Include any witnesses, evidence, or actions taken." required></textarea>
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="checkbox" id="is_confidential" name="is_confidential" class="form-check-input" value="1">
                                        <label for="is_confidential" class="form-check-label">Keep this report confidential (not for publication)</label>
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
                                        <i class="fas fa-flag me-2"></i>Submit Incident Report
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
