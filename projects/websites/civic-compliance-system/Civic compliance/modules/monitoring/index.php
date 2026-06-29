<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/auth.php';

$pageTitle = 'Civic Space Monitoring - PBO Compliance Hub';
$currentPage = 'monitoring';

$db = Database::getInstance()->getConnection();

// Get submission counts for stats
$stmt = $db->query("SELECT COUNT(*) FROM monitoring_reports WHERE status='verified'");
$approvedReports = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(DISTINCT county) FROM monitoring_reports");
$countiesCovered = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM incident_reports WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$recentIncidents = $stmt->fetchColumn();

// Kenya counties list
$counties = [
    'Mombasa','Kwale','Kilifi','Tana River','Lamu','Taita-Taveta',
    'Garissa','Wajir','Mandera','Marsabit','Isiolo','Meru',
    'Tharaka-Nithi','Embu','Kitui','Machakos','Makueni','Nyandarua',
    'Nyeri','Kirinyaga','Murang\'a','Kiambu','Turkana','West Pokot',
    'Samburu','Trans Nzoia','Uasin Gishu','Elgeyo-Marakwet','Nandi',
    'Baringo','Laikipia','Nakuru','Narok','Kajiado','Kericho','Bomet',
    'Kakamega','Vihiga','Bungoma','Busia','Siaya','Kisumu','Homa Bay',
    'Migori','Kisii','Nyamira','Nairobi'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/monitoring.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include '../../includes/navbar.php'; ?>

<!-- Hero -->
<section class="monitoring-hero">
    <div class="container">
        <div class="hero-content">
            <span class="hero-badge">
                <i class="fas fa-satellite-dish"></i> Civic Space Monitoring
            </span>
            <h1>Monitor & Report PBO Experiences</h1>
            <p>Help build the evidence base for civil society in Kenya. Submit your organization's registration experiences, compliance challenges, and civic space violations.</p>
            <div class="hero-buttons">
                <a href="#submit-report" class="btn btn-white">
                    <i class="fas fa-plus-circle"></i> Submit a Report
                </a>
                <a href="#report-incident" class="btn btn-outline-white">
                    <i class="fas fa-exclamation-triangle"></i> Report Incident
                </a>
            </div>
        </div>
        <div class="hero-stats">
            <div class="stat-card">
                <i class="fas fa-file-alt"></i>
                <span class="stat-number"><?php echo $approvedReports; ?></span>
                <span class="stat-label">Reports Submitted</span>
            </div>
            <div class="stat-card">
                <i class="fas fa-map-marker-alt"></i>
                <span class="stat-number"><?php echo $countiesCovered; ?></span>
                <span class="stat-label">Counties Covered</span>
            </div>
            <div class="stat-card">
                <i class="fas fa-bell"></i>
                <span class="stat-number"><?php echo $recentIncidents; ?></span>
                <span class="stat-label">Recent Incidents (30 days)</span>
            </div>
        </div>
    </div>
</section>

<!-- Report Type Selector -->
<section class="report-selector">
    <div class="container">
        <h2>What would you like to report?</h2>
        <div class="selector-grid">
            <div class="selector-card active" data-form="compliance" onclick="selectForm('compliance', this)">
                <div class="selector-icon bg-blue">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3>Compliance & Registration Experience</h3>
                <p>Share your organization's journey through PBO registration and ongoing compliance requirements.</p>
                <span class="selector-time"><i class="fas fa-clock"></i> ~10 minutes</span>
            </div>
            <div class="selector-card" data-form="barrier" onclick="selectForm('barrier', this)">
                <div class="selector-icon bg-orange">
                    <i class="fas fa-road-barrier"></i>
                </div>
                <h3>Administrative Barriers</h3>
                <p>Document delays, bureaucratic obstacles, and administrative challenges experienced with PBO Authority.</p>
                <span class="selector-time"><i class="fas fa-clock"></i> ~8 minutes</span>
            </div>
            <div class="selector-card" data-form="incident" onclick="selectForm('incident', this)">
                <div class="selector-icon bg-red">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3>Civic Space Violation</h3>
                <p>Report violations of civic space including harassment, intimidation, or unlawful restrictions on PBO activities.</p>
                <span class="selector-time"><i class="fas fa-clock"></i> ~12 minutes</span>
            </div>
            <div class="selector-card" data-form="enabling" onclick="selectForm('enabling', this)">
                <div class="selector-icon bg-green">
                    <i class="fas fa-thumbs-up"></i>
                </div>
                <h3>Enabling Practice</h3>
                <p>Share positive experiences of good practice, helpful officials, or effective support for PBO operations.</p>
                <span class="selector-time"><i class="fas fa-clock"></i> ~6 minutes</span>
            </div>
        </div>
    </div>
</section>

<!-- Forms Container -->
<section class="forms-container" id="submit-report">
    <div class="container">

        <!-- Privacy Notice -->
        <div class="privacy-notice">
            <div class="privacy-icon"><i class="fas fa-shield-alt"></i></div>
            <div class="privacy-text">
                <strong>Your Privacy is Protected</strong>
                <p>All submissions are securely encrypted. You may submit anonymously. Data is only used for research and advocacy purposes. See our <a href="/privacy.php">Privacy Policy</a>.</p>
            </div>
        </div>

        <!-- FORM 1: Compliance Experience -->
        <div class="report-form active" id="form-compliance">
            <div class="form-card">
                <div class="form-header">
                    <div class="form-icon bg-blue"><i class="fas fa-clipboard-list"></i></div>
                    <div>
                        <h2>Compliance & Registration Experience Report</h2>
                        <p>Share your organization's compliance and registration experience with the PBO Authority.</p>
                    </div>
                </div>

                <form id="complianceForm" enctype="multipart/form-data">
                    <input type="hidden" name="report_type" value="compliance">
                    <?php echo generateCSRFToken(); ?>

                    <!-- Section A: Organization Info -->
                    <div class="form-section">
                        <div class="section-label">
                            <span class="section-num">A</span>
                            <h3>Organization Information</h3>
                        </div>

                        <div class="anonymity-toggle">
                            <label class="toggle-label">
                                <input type="checkbox" id="anonymousToggle" onchange="toggleAnonymous(this)">
                                <span class="toggle-switch"></span>
                                <span>Submit Anonymously</span>
                            </label>
                            <p class="toggle-note">If anonymous, your organization name will not be stored.</p>
                        </div>

                        <div class="form-grid" id="orgDetailsSection">
                            <div class="form-group">
                                <label>Organization Name <span class="req">*</span></label>
                                <input type="text" name="org_name" required placeholder="Your organization's full name">
                            </div>
                            <div class="form-group">
                                <label>Organization Type <span class="req">*</span></label>
                                <select name="org_type" required>
                                    <option value="">-- Select --</option>
                                    <option>NGO</option>
                                    <option>CBO</option>
                                    <option>Trust</option>
                                    <option>Association</option>
                                    <option>Foundation</option>
                                    <option>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>County of Operation <span class="req">*</span></label>
                                <select name="submitter_county" required>
                                    <option value="">-- Select County --</option>
                                    <?php foreach($counties as $c): ?>
                                    <option><?php echo $c; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Contact Email (Optional)</label>
                                <input type="email" name="contact_email" placeholder="For follow-up if needed">
                            </div>
                        </div>
                    </div>

                    <!-- Section B: Registration Experience -->
                    <div class="form-section">
                        <div class="section-label">
                            <span class="section-num">B</span>
                            <h3>Registration Experience</h3>
                        </div>

                        <div class="form-group">
                            <label>Current Registration Status <span class="req">*</span></label>
                            <div class="radio-group">
                                <label class="radio-opt"><input type="radio" name="reg_status" value="registered" required> Fully Registered</label>
                                <label class="radio-opt"><input type="radio" name="reg_status" value="pending"> Registration Pending</label>
                                <label class="radio-opt"><input type="radio" name="reg_status" value="rejected"> Application Rejected</label>
                                <label class="radio-opt"><input type="radio" name="reg_status" value="not_registered"> Not Yet Applied</label>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label>Date of Application Submission</label>
                                <input type="date" name="application_date" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label>Date of Registration (if applicable)</label>
                                <input type="date" name="registration_date" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>How long did the registration process take?</label>
                            <select name="registration_duration">
                                <option value="">-- Select --</option>
                                <option value="under1month">Less than 1 month</option>
                                <option value="1-3months">1–3 months</option>
                                <option value="3-6months">3–6 months</option>
                                <option value="6-12months">6–12 months</option>
                                <option value="over1year">More than 1 year</option>
                                <option value="still_pending">Still pending</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Overall experience with the registration process <span class="req">*</span></label>
                            <div class="rating-group">
                                <?php for($i=1; $i<=5; $i++): ?>
                                <label class="rating-opt">
                                    <input type="radio" name="registration_rating" value="<?php echo $i; ?>" required>
                                    <span class="rating-star" data-val="<?php echo $i; ?>">
                                        <i class="fas fa-star"></i>
                                        <span><?php echo ['Very Poor','Poor','Fair','Good','Excellent'][$i-1]; ?></span>
                                    </span>
                                </label>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Describe your registration experience <span class="req">*</span></label>
                            <textarea name="registration_experience" rows="5" required
                                placeholder="Please describe what the registration process was like, any challenges or positive aspects, and the outcome..."></textarea>
                            <span class="char-count">0 / 2000</span>
                        </div>
                    </div>

                    <!-- Section C: Compliance Experience -->
                    <div class="form-section">
                        <div class="section-label">
                            <span class="section-num">C</span>
                            <h3>Ongoing Compliance Experience</h3>
                        </div>

                        <div class="form-group">
                            <label>Have you experienced challenges with ongoing compliance requirements?</label>
                            <div class="radio-group">
                                <label class="radio-opt"><input type="radio" name="compliance_challenges" value="yes"> Yes</label>
                                <label class="radio-opt"><input type="radio" name="compliance_challenges" value="no"> No</label>
                                <label class="radio-opt"><input type="radio" name="compliance_challenges" value="partial"> Partially</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Which compliance areas have been most challenging? (Select all that apply)</label>
                            <div class="checkbox-group">
                                <label class="checkbox-opt"><input type="checkbox" name="challenges[]" value="annual_returns"> Annual Returns Submission</label>
                                <label class="checkbox-opt"><input type="checkbox" name="challenges[]" value="financial_audit"> Financial Audit Requirements</label>
                                <label class="checkbox-opt"><input type="checkbox" name="challenges[]" value="board_requirements"> Board Composition Requirements</label>
                                <label class="checkbox-opt"><input type="checkbox" name="challenges[]" value="reporting"> Reporting Requirements</label>
                                <label class="checkbox-opt"><input type="checkbox" name="challenges[]" value="fees"> Registration/Annual Fees</label>
                                <label class="checkbox-opt"><input type="checkbox" name="challenges[]" value="foreign_funding"> Foreign Funding Restrictions</label>
                                <label class="checkbox-opt"><input type="checkbox" name="challenges[]" value="information_access"> Access to Information/Forms</label>
                                <label class="checkbox-opt"><input type="checkbox" name="challenges[]" value="other"> Other</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Additional details on compliance challenges</label>
                            <textarea name="compliance_details" rows="4"
                                placeholder="Provide any additional context or details about compliance challenges..."></textarea>
                        </div>
                    </div>

                    <!-- Section D: Quantitative Data -->
                    <div class="form-section">
                        <div class="section-label">
                            <span class="section-num">D</span>
                            <h3>Quantitative Information</h3>
                        </div>

                        <div class="form-grid form-grid-3">
                            <div class="form-group">
                                <label>Estimated cost of registration (KES)</label>
                                <input type="number" name="registration_cost" min="0" placeholder="e.g. 10000">
                            </div>
                            <div class="form-group">
                                <label>Number of follow-up visits required</label>
                                <input type="number" name="visits_count" min="0" placeholder="e.g. 3">
                            </div>
                            <div class="form-group">
                                <label>Number of documents requested</label>
                                <input type="number" name="documents_count" min="0" placeholder="e.g. 8">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Were any informal payments requested?</label>
                            <div class="radio-group">
                                <label class="radio-opt"><input type="radio" name="informal_payments" value="yes"> Yes</label>
                                <label class="radio-opt"><input type="radio" name="informal_payments" value="no"> No</label>
                                <label class="radio-opt"><input type="radio" name="informal_payments" value="prefer_not_say"> Prefer not to say</label>
                            </div>
                        </div>
                    </div>

                    <!-- Section E: Documents -->
                    <div class="form-section">
                        <div class="section-label">
                            <span class="section-num">E</span>
                            <h3>Supporting Documentation (Optional)</h3>
                        </div>

                        <div class="upload-area" id="uploadArea">
                            <div class="upload-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Drag & drop files here or <span class="upload-link">browse</span></p>
                                <small>Accepted: PDF, DOC, DOCX, JPG, PNG (Max 10MB each, up to 5 files)</small>
                            </div>
                            <input type="file" name="documents[]" id="fileInput" multiple
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" style="display:none">
                        </div>
                        <div id="fileList" class="file-list"></div>
                    </div>

                    <!-- Consent -->
                    <div class="form-section">
                        <div class="consent-box">
                            <label class="checkbox-opt">
                                <input type="checkbox" name="consent" id="consentCheck" required>
                                <span>I consent to CRECO Kenya processing this information for research and advocacy purposes. I understand my data will be handled as per the <a href="/privacy.php">Privacy Policy</a> and the Kenya Data Protection Act 2019.</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-submit">
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-paper-plane"></i> Submit Report
                        </button>
                        <p class="submit-note">Your submission will be reviewed by CRECO Kenya before publication.</p>
                    </div>
                </form>
            </div>
        </div>

        <!-- FORM 2: Administrative Barriers -->
        <div class="report-form" id="form-barrier">
            <div class="form-card">
                <div class="form-header">
                    <div class="form-icon bg-orange"><i class="fas fa-road-barrier"></i></div>
                    <div>
                        <h2>Administrative Barriers Report</h2>
                        <p>Document delays, bureaucratic obstacles, and administrative challenges.</p>
                    </div>
                </div>
                <form id="barrierForm" enctype="multipart/form-data">
                    <input type="hidden" name="report_type" value="barrier">
                    <?php echo generateCSRFToken(); ?>

                    <div class="form-section">
                        <div class="section-label">
                            <span class="section-num">A</span>
                            <h3>Organization Information</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Organization Name (Optional)</label>
                                <input type="text" name="org_name" placeholder="Or leave blank to submit anonymously">
                            </div>
                            <div class="form-group">
                                <label>County <span class="req">*</span></label>
                                <select name="submitter_county" required>
                                    <option value="">-- Select --</option>
                                    <?php foreach($counties as $c): ?>
                                    <option><?php echo $c; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-label">
                            <span class="section-num">B</span>
                            <h3>Barrier Details</h3>
                        </div>

                        <div class="form-group">
                            <label>Type of Barrier <span class="req">*</span></label>
                            <select name="barrier_type" required>
                                <option value="">-- Select Type --</option>
                                <option value="delay">Unreasonable Delays</option>
                                <option value="unclear_requirements">Unclear/Changing Requirements</option>
                                <option value="excessive_documentation">Excessive Documentation</option>
                                <option value="fee_related">Excessive Fees</option>
                                <option value="corruption">Corruption/Bribery</option>
                                <option value="rejection">Unexplained Rejection</option>
                                <option value="communication">Poor Communication</option>
                                <option value="digital_access">Digital Access Barriers</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Which PBO Authority office/department? <span class="req">*</span></label>
                            <input type="text" name="authority_office" required placeholder="e.g. Nairobi County Office, Registration Department">
                        </div>

                        <div class="form-group">
                            <label>Date barrier was experienced <span class="req">*</span></label>
                            <input type="date" name="barrier_date" required max="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group">
                            <label>Describe the barrier in detail <span class="req">*</span></label>
                            <textarea name="barrier_description" rows="6" required
                                placeholder="Please describe the barrier, how it affected your organization, steps taken to resolve it, and the outcome..."></textarea>
                            <span class="char-count">0 / 3000</span>
                        </div>

                        <div class="form-group">
                            <label>Impact on Organization</label>
                            <div class="checkbox-group">
                                <label class="checkbox-opt"><input type="checkbox" name="impact[]" value="delayed_programs"> Delayed programs/activities</label>
                                <label class="checkbox-opt"><input type="checkbox" name="impact[]" value="financial_loss"> Financial losses</label>
                                <label class="checkbox-opt"><input type="checkbox" name="impact[]" value="staff_time"> Excessive staff time consumed</label>
                                <label class="checkbox-opt"><input type="checkbox" name="impact[]" value="funder_relations"> Damaged funder relationships</label>
                                <label class="checkbox-opt"><input type="checkbox" name="impact[]" value="operations_halted"> Operations temporarily halted</label>
                                <label class="checkbox-opt"><input type="checkbox" name="impact[]" value="beneficiaries"> Harm to beneficiaries</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Has this been resolved? <span class="req">*</span></label>
                            <div class="radio-group">
                                <label class="radio-opt"><input type="radio" name="resolved" value="yes" required> Yes, fully resolved</label>
                                <label class="radio-opt"><input type="radio" name="resolved" value="partial"> Partially resolved</label>
                                <label class="radio-opt"><input type="radio" name="resolved" value="no"> No, still ongoing</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>If resolved, how was it resolved?</label>
                            <textarea name="resolution_details" rows="3"
                                placeholder="Describe how the barrier was resolved..."></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-label">
                            <span class="section-num">C</span>
                            <h3>Supporting Evidence (Optional)</h3>
                        </div>
                        <div class="upload-area" id="barrierUpload">
                            <div class="upload-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Upload correspondence, rejection letters, screenshots, etc.</p>
                                <small>PDF, DOC, JPG, PNG — Max 10MB each</small>
                            </div>
                            <input type="file" name="documents[]" multiple
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" style="display:none">
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="consent-box">
                            <label class="checkbox-opt">
                                <input type="checkbox" name="consent" required>
                                <span>I consent to this information being used by CRECO Kenya for research and advocacy purposes.</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-submit">
                        <button type="submit" class="btn btn-primary btn-large btn-orange">
                            <i class="fas fa-paper-plane"></i> Submit Barrier Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- FORM 3: Incident Report -->
        <div class="report-form" id="form-incident" id="report-incident">
            <div class="form-card">
                <div class="form-header">
                    <div class="form-icon bg-red"><i class="fas fa-exclamation-triangle"></i></div>
                    <div>
                        <h2>Civic Space Violation Incident Report</h2>
                        <p>Report violations of civic space, harassment, intimidation, or unlawful restrictions on PBO operations.</p>
                    </div>
                </div>

                <div class="urgency-banner">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <strong>Immediate Danger?</strong>
                        <p>If you or your organization is in immediate danger, please contact authorities or relevant protection agencies first. This form is for documentation purposes.</p>
                    </div>
                </div>

                <form id="incidentForm" enctype="multipart/form-data">
                    <input type="hidden" name="report_type" value="incident">
                    <?php echo generateCSRFToken(); ?>

                    <div class="form-section">
                        <div class="section-label">
                            <span class="section-num">A</span>
                            <h3>Reporter Information</h3>
                        </div>
                        <div class="form-group">
                            <label>Report as:</label>
                            <div class="radio-group">
                                <label class="radio-opt"><input type="radio" name="reporter_type" value="organization"> On behalf of an organization</label>
                                <label class="radio-opt"><input type="radio" name="reporter_type" value="individual"> As an individual</label>
                                <label class="radio-opt"><input type="radio" name="reporter_type" value="anonymous"> Anonymously</label>
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Organization/Name (Optional)</label>
                                <input type="text" name="org_name" placeholder="Leave blank if anonymous">
                            </div>
                            <div class="form-group">
                                <label>County where incident occurred <span class="req">*</span></label>
                                <select name="submitter_county" required>
                                    <option value="">-- Select --</option>
                                    <?php foreach($counties as $c): ?>
                                    <option><?php echo $c; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Secure Contact (Optional — for follow up)</label>
                            <input type="email" name="contact_email" placeholder="Your email or Signal number">
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-label">
                            <span class="section-num">B</span>
                            <h3>Incident Details</h3>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label>Type of Violation <span class="req">*</span></label>
                                <select name="violation_type" required>
                                    <option value="">-- Select --</option>
                                    <option value="harassment">Harassment of Staff/Members</option>
                                    <option value="intimidation">Intimidation/Threats</option>
                                    <option value="unlawful_closure">Unlawful Closure of Office</option>
                                    <option value="arrest">Unlawful Arrest/Detention</option>
                                    <option value="asset_seizure">Seizure of Assets</option>
                                    <option value="surveillance">Unlawful Surveillance</option>
                                    <option value="deregistration">Threat of Deregistration</option>
                                    <option value="funding_block">Blocking of Funding</option>
                                    <option value="assembly_restriction">Restriction of Assembly</option>
                                    <option value="smear_campaign">Smear/Defamation Campaign</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Severity Level <span class="req">*</span></label>
                                <select name="severity" required>
                                    <option value="">-- Select --</option>
                                    <option value="low">Low — Limited impact, no physical harm</option>
                                    <option value="medium">Medium — Significant disruption</option>
                                    <option value="high">High — Serious harm or threat</option>
                                    <option value="critical">Critical — Immediate safety risk</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label>Date of Incident <span class="req">*</span></label>
                                <input type="date" name="incident_date" required max="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label>Perpetrator/Actor</label>
                                <select name="perpetrator_type">
                                    <option value="">-- Select --</option>
                                    <option>Government Official</option>
                                    <option>PBO Authority</option>
                                    <option>Police/Security Forces</option>
                                    <option>County Government</option>
                                    <option>Unknown Actor</option>
                                    <option>Private Party</option>
                                    <option>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Detailed Description of Incident <span class="req">*</span></label>
                            <textarea name="incident_description" rows="8" required
                                placeholder="Please provide a detailed, factual account of what happened. Include: who was involved, what happened, when and where, witnesses, and immediate impact..."></textarea>
                            <span class="char-count">0 / 5000</span>
                        </div>

                        <div class="form-group">
                            <label>Number of People Affected</label>
                            <input type="number" name="people_affected" min="1" placeholder="Estimated number">
                        </div>

                        <div class="form-group">
                            <label>Has the incident been reported to authorities?</label>
                            <div class="radio-group">
                                <label class="radio-opt"><input type="radio" name="reported_to_authorities" value="yes"> Yes</label>
                                <label class="radio-opt"><input type="radio" name="reported_to_authorities" value="no"> No</label>
                                <label class="radio-opt"><input type="radio" name="reported_to_authorities" value="afraid"> No — afraid of retaliation</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>If reported, please provide reference number or details</label>
                            <input type="text" name="authority_reference" placeholder="e.g. Police OB number">
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-label">
                            <span class="section-num">C</span>
                            <h3>Evidence & Documentation</h3>
                        </div>
                        <div class="upload-area">
                            <div class="upload-content">
                                <i class="fas fa-paperclip"></i>
                                <p>Upload photos, videos, documents, or correspondence</p>
                                <small>Max 10MB per file. Files are stored securely.</small>
                            </div>
                            <input type="file" name="documents[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.mp4,.mov">
                        </div>
                        <div class="form-group" style="margin-top:12px">
                            <label>Witness Information (Optional)</label>
                            <textarea name="witness_info" rows="2"
                                placeholder="Names or contact details of witnesses (kept confidential)"></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="consent-box">
                            <label class="checkbox-opt">
                                <input type="checkbox" name="consent" required>
                                <span>I consent to CRECO Kenya using this information for human rights monitoring, documentation, and advocacy. I confirm the information provided is accurate to the best of my knowledge.</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-submit">
                        <button type="submit" class="btn btn-primary btn-large btn-red">
                            <i class="fas fa-paper-plane"></i> Submit Incident Report
                        </button>
                        <p class="submit-note"><i class="fas fa-lock"></i> This report is encrypted and stored securely.</p>
                    </div>
                </form>
            </div>
        </div>

        <!-- FORM 4: Enabling Practice -->
        <div class="report-form" id="form-enabling">
            <div class="form-card">
                <div class="form-header">
                    <div class="form-icon bg-green"><i class="fas fa-thumbs-up"></i></div>
                    <div>
                        <h2>Enabling Practice Report</h2>
                        <p>Share positive experiences that have helped PBOs operate effectively in Kenya.</p>
                    </div>
                </div>
                <form id="enablingForm" enctype="multipart/form-data">
                    <input type="hidden" name="report_type" value="enabling">
                    <?php echo generateCSRFToken(); ?>

                    <div class="form-section">
                        <div class="section-label">
                            <span class="section-num">A</span>
                            <h3>Organization</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Organization Name (Optional)</label>
                                <input type="text" name="org_name" placeholder="Optional">
                            </div>
                            <div class="form-group">
                                <label>County <span class="req">*</span></label>
                                <select name="submitter_county" required>
                                    <option value="">-- Select --</option>
                                    <?php foreach($counties as $c): ?>
                                    <option><?php echo $c; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-label">
                            <span class="section-num">B</span>
                            <h3>Positive Practice Details</h3>
                        </div>

                        <div class="form-group">
                            <label>Category of Enabling Practice <span class="req">*</span></label>
                            <select name="practice_category" required>
                                <option value="">-- Select --</option>
                                <option>Efficient Registration Process</option>
                                <option>Helpful PBO Authority Staff</option>
                                <option>Clear/Accessible Information</option>
                                <option>Online Services/Digital Tools</option>
                                <option>Supportive County Government</option>
                                <option>Effective Appeals Mechanism</option>
                                <option>Training/Capacity Support</option>
                                <option>Other Positive Practice</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Describe the enabling practice <span class="req">*</span></label>
                            <textarea name="practice_description" rows="6" required
                                placeholder="Please describe the positive practice in detail — what happened, who was involved, and why it was helpful to your organization..."></textarea>
                            <span class="char-count">0 / 2000</span>
                        </div>

                        <div class="form-group">
                            <label>Overall rating of this experience</label>
                            <div class="rating-group">
                                <?php for($i=1; $i<=5; $i++): ?>
                                <label class="rating-opt">
                                    <input type="radio" name="practice_rating" value="<?php echo $i; ?>">
                                    <span class="rating-star">
                                        <i class="fas fa-star"></i>
                                        <span><?php echo ['Poor','Fair','Good','Very Good','Excellent'][$i-1]; ?></span>
                                    </span>
                                </label>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Would you recommend this practice be replicated elsewhere?</label>
                            <div class="radio-group">
                                <label class="radio-opt"><input type="radio" name="recommend_replication" value="yes"> Yes, definitely</label>
                                <label class="radio-opt"><input type="radio" name="recommend_replication" value="maybe"> Maybe, with modifications</label>
                                <label class="radio-opt"><input type="radio" name="recommend_replication" value="no"> No</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="consent-box">
                            <label class="checkbox-opt">
                                <input type="checkbox" name="consent" required>
                                <span>I consent to CRECO Kenya publishing this positive practice (anonymized if requested) to promote good governance.</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-submit">
                        <button type="submit" class="btn btn-primary btn-large btn-green">
                            <i class="fas fa-paper-plane"></i> Submit Practice Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</section>

<?php include '../../includes/footer.php'; ?>

<script>
// ===================== FORM SELECTOR =====================
function selectForm(formId, card) {
    document.querySelectorAll('.selector-card').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.report-form').forEach(f => f.classList.remove('active'));
    card.classList.add('active');
    document.getElementById('form-' + formId).classList.add('active');
    document.getElementById('submit-report').scrollIntoView({ behavior: 'smooth' });
}

// ===================== ANONYMOUS TOGGLE =====================
function toggleAnonymous(checkbox) {
    const orgSection = document.getElementById('orgDetailsSection');
    orgSection.style.opacity = checkbox.checked ? '0.4' : '1';
    orgSection.style.pointerEvents = checkbox.checked ? 'none' : 'auto';
    document.querySelectorAll('#orgDetailsSection [required]').forEach(el => {
        el.required = !checkbox.checked;
    });
}

// ===================== CHAR COUNTER =====================
document.querySelectorAll('textarea').forEach(ta => {
    const counter = ta.nextElementSibling;
    if(counter && counter.classList.contains('char-count')) {
        const max = parseInt(counter.textContent.split('/')[1]) || 2000;
        ta.addEventListener('input', () => {
            counter.textContent = ta.value.length + ' / ' + max;
            counter.style.color = ta.value.length > max * 0.9 ? '#ef4444' : '#9ca3af';
        });
    }
});

// ===================== FILE UPLOAD =====================
const uploadArea = document.getElementById('uploadArea');
const fileInput  = document.getElementById('fileInput');

if(uploadArea) {
    uploadArea.addEventListener('click', () => fileInput && fileInput.click());
    uploadArea.addEventListener('dragover', e => { e.preventDefault(); uploadArea.classList.add('drag-over'); });
    uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('drag-over'));
    uploadArea.addEventListener('drop', e => {
        e.preventDefault();
        uploadArea.classList.remove('drag-over');
        handleFiles(e.dataTransfer.files);
    });
}

if(fileInput) {
    fileInput.addEventListener('change', () => handleFiles(fileInput.files));
}

function handleFiles(files) {
    const fileList = document.getElementById('fileList');
    if(!fileList) return;
    Array.from(files).forEach(file => {
        const item = document.createElement('div');
        item.className = 'file-item';
        item.innerHTML = `
            <i class="fas fa-file"></i>
            <span>${file.name}</span>
            <span class="file-size">${(file.size/1024/1024).toFixed(2)} MB</span>
            <button type="button" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
        `;
        fileList.appendChild(item);
    });
}

// ===================== FORM SUBMISSIONS =====================
['complianceForm','barrierForm','incidentForm','enablingForm'].forEach(formId => {
    const form = document.getElementById(formId);
    if(!form) return;
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const origText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        btn.disabled = true;

        try {
            const formData = new FormData(this);
            const resp = await fetch('../../api/monitoring.php', {
                method: 'POST',
                body: formData
            });
            const data = await resp.json();

            if(data.success) {
                showSuccessMessage(data.message || 'Report submitted successfully! Thank you.');
                this.reset();
            } else {
                showErrorMessage(data.error || 'Submission failed. Please try again.');
            }
        } catch(err) {
            showErrorMessage('Network error. Please check your connection and try again.');
        } finally {
            btn.innerHTML = origText;
            btn.disabled = false;
        }
    });
});

function showSuccessMessage(msg) {
    const el = document.createElement('div');
    el.className = 'form-success-msg';
    el.innerHTML = `<i class="fas fa-check-circle"></i><div><strong>Success!</strong><p>${msg}</p></div>`;
    document.querySelector('.report-form.active .form-card').prepend(el);
    el.scrollIntoView({ behavior: 'smooth' });
    setTimeout(() => el.remove(), 8000);
}

function showErrorMessage(msg) {
    const el = document.createElement('div');
    el.className = 'form-error-msg';
    el.innerHTML = `<i class="fas fa-times-circle"></i><div><strong>Error</strong><p>${msg}</p></div>`;
    document.querySelector('.report-form.active .form-card').prepend(el);
    el.scrollIntoView({ behavior: 'smooth' });
    setTimeout(() => el.remove(), 6000);
}
</script>
</body>
</html>