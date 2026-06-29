<?php
require_once '../../config/config.php';
$pageTitle = 'Registration Guide - PBO Kenya';
$currentPage = 'compliance';
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
    <link href="../../assets/css/compliance.css" rel="stylesheet">
</head>
<body>
<?php include '../../includes/navbar.php'; ?>

<section class="page-hero py-5 bg-success text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <span class="section-badge bg-white text-success">Guide</span>
                <h1 class="fw-bold display-5">PBO Registration Guide</h1>
                <p class="lead mb-0">Step-by-step guidance on registering your organization under the PBO Act 2013.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4" data-aos="fade-up">
                    <div class="card-body p-4">
                        <h4>Step 1: Determine Your Organization Type</h4>
                        <p>Identify whether your organization qualifies as a Public Benefit Organization under Section 5 of the PBO Act. Your organization must have objectives that benefit the public and be not-for-profit.</p>
                        <ul>
                            <li>NGOs (Non-Governmental Organizations)</li>
                            <li>Community Based Organizations (CBOs)</li>
                            <li>Faith Based Organizations (FBOs)</li>
                            <li>Foundations and Trusts</li>
                            <li>Other not-for-profit entities</li>
                        </ul>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4" data-aos="fade-up">
                    <div class="card-body p-4">
                        <h4>Step 2: Prepare Required Documents</h4>
                        <p>Gather the following documents before beginning your application:</p>
                        <div class="table-responsive">
                            <table class="table">
                                <thead><tr><th>Document</th><th>Details</th><th>Reference</th></tr></thead>
                                <tbody>
                                    <tr><td>Constitution / Memorandum of Association</td><td>Must include name, objectives, governance structure, dissolution clause</td><td>Section 12</td></tr>
                                    <tr><td>Founding Members List</td><td>Minimum 3 members with valid ID copies</td><td>Section 11</td></tr>
                                    <tr><td>Board Members Details</td><td>Full names, ID numbers, contact details</td><td>Section 15</td></tr>
                                    <tr><td>Public Benefit Statement</td><td>Clear explanation of how the organization benefits the public</td><td>Section 5</td></tr>
                                    <tr><td>Application Fee</td><td>Pay the prescribed registration fee</td><td>Section 14</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4" data-aos="fade-up">
                    <div class="card-body p-4">
                        <h4>Step 3: Complete Form PBO-1</h4>
                        <p>Fill out the official application form (Form PBO-1) available from the PBO Regulatory Authority. Ensure all sections are completed accurately.</p>
                        <p><strong>Key information required:</strong></p>
                        <ul>
                            <li>Organization name and registered address</li>
                            <li>Vision, mission, and objectives</li>
                            <li>Geographic area of operation</li>
                            <li>Governance structure details</li>
                            <li>Sources of funding</li>
                            <li>Target beneficiaries</li>
                        </ul>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4" data-aos="fade-up">
                    <div class="card-body p-4">
                        <h4>Step 4: Submit to PBO Regulatory Authority</h4>
                        <p>Submit your completed application package to the PBO Regulatory Authority. You can submit in person or through their official portal.</p>
                        <p><strong>The process typically takes 30–90 days</strong> for review and certificate issuance.</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4" data-aos="fade-up">
                    <div class="card-body p-4">
                        <h4>Step 5: Post-Registration Requirements</h4>
                        <p>After registration, you must:</p>
                        <ul>
                            <li>Display your PBO certificate prominently</li>
                            <li>Maintain proper books of accounts</li>
                            <li>Submit annual returns within 6 months of financial year end</li>
                            <li>Notify the authority of any changes in leadership or constitution within 30 days</li>
                            <li>Hold board meetings at least 4 times per year</li>
                            <li>Hold Annual General Meetings</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4" data-aos="fade-left">
                    <div class="card-body p-4">
                        <h5><i class="fas fa-file-pdf text-danger me-2"></i>Downloads</h5>
                        <hr>
                        <p class="small mb-2"><i class="fas fa-file-alt me-1"></i>Form PBO-1 Template</p>
                        <p class="small mb-2"><i class="fas fa-file-alt me-1"></i>Sample Constitution</p>
                        <p class="small mb-2"><i class="fas fa-file-alt me-1"></i>PBO Act 2013 Full Text</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm" data-aos="fade-left">
                    <div class="card-body p-4">
                        <h5><i class="fas fa-clipboard-list text-primary me-2"></i>Related Tools</h5>
                        <hr>
                        <a href="index.php" class="btn btn-outline-primary btn-sm w-100 mb-2">Compliance Checklist</a>
                        <a href="self-assessment.php" class="btn btn-outline-primary btn-sm w-100 mb-2">Self-Assessment</a>
                        <a href="templates.php" class="btn btn-outline-primary btn-sm w-100">Download Templates</a>
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
