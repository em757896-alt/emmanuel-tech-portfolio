<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/auth.php';

$pageTitle = 'Compliance Tools - PBO Compliance Hub';
$currentPage = 'compliance';

// Get compliance categories (fallback to empty if table missing)
$categories = [];
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT DISTINCT category FROM compliance_checklists WHERE is_active=1 ORDER BY category ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Compliance categories query failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/compliance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include '../../includes/navbar.php'; ?>

<!-- Hero Section -->
<section class="compliance-hero">
    <div class="container">
        <div class="hero-content">
            <span class="badge badge-green">
                <i class="fas fa-shield-alt"></i> Compliance Support
            </span>
            <h1>PBO Compliance Tools</h1>
            <p>Interactive tools to help your organization assess, track, and achieve full compliance with the Public Benefit Organizations Act.</p>
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number" id="checklistCount">24</span>
                    <span class="stat-label">Checklist Items</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="toolsCount">6</span>
                    <span class="stat-label">Interactive Tools</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="templatesCount">12</span>
                    <span class="stat-label">Templates</span>
                </div>
            </div>
        </div>
        <div class="hero-visual">
            <div class="compliance-meter">
                <svg viewBox="0 0 200 200" class="meter-svg">
                    <circle cx="100" cy="100" r="80" fill="none" stroke="#e5e7eb" stroke-width="20"/>
                    <circle cx="100" cy="100" r="80" fill="none" stroke="#10b981" stroke-width="20"
                            stroke-dasharray="502" stroke-dashoffset="502" id="meterCircle"
                            transform="rotate(-90 100 100)"/>
                </svg>
                <div class="meter-center">
                    <span class="meter-value" id="meterValue">0%</span>
                    <span class="meter-label">Compliance</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Tools Navigation -->
<section class="tools-nav">
    <div class="container">
        <div class="tools-tabs">
            <button class="tab-btn active" data-tab="checklist">
                <i class="fas fa-tasks"></i>
                <span>Compliance Checklist</span>
            </button>
            <button class="tab-btn" data-tab="assessment">
                <i class="fas fa-clipboard-check"></i>
                <span>Self-Assessment</span>
            </button>
            <button class="tab-btn" data-tab="registration">
                <i class="fas fa-registered"></i>
                <span>Registration Guide</span>
            </button>
            <button class="tab-btn" data-tab="templates">
                <i class="fas fa-file-alt"></i>
                <span>Templates</span>
            </button>
        </div>
    </div>
</section>

<!-- Tab Content -->
<section class="tools-content">
    <div class="container">

        <!-- COMPLIANCE CHECKLIST TAB -->
        <div class="tab-pane active" id="tab-checklist">
            <div class="section-header">
                <h2>Organizational Compliance Checklist</h2>
                <p>Track your organization's compliance status across all PBO Act requirements. Check each item as you complete it.</p>
                <div class="checklist-actions">
                    <button class="btn btn-outline" onclick="resetChecklist()">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                    <button class="btn btn-outline" onclick="saveChecklist()">
                        <i class="fas fa-save"></i> Save Progress
                    </button>
                    <button class="btn btn-primary" onclick="downloadChecklistPDF()">
                        <i class="fas fa-download"></i> Download PDF Report
                    </button>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="checklist-progress">
                <div class="progress-header">
                    <span>Overall Compliance Progress</span>
                    <span id="progressText">0 of 24 completed</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar" id="checklistProgressBar" style="width: 0%"></div>
                </div>
                <div class="compliance-status" id="complianceStatus">
                    <span class="status-badge status-low">Not Started</span>
                </div>
            </div>

            <!-- Checklist Categories -->
            <div class="checklist-container">

                <!-- Category 1: Registration Requirements -->
                <div class="checklist-category" data-category="registration">
                    <div class="category-header" onclick="toggleCategory(this)">
                        <div class="category-info">
                            <div class="category-icon bg-blue">
                                <i class="fas fa-file-signature"></i>
                            </div>
                            <div>
                                <h3>1. Registration Requirements</h3>
                                <p>Core registration requirements under the PBO Act</p>
                            </div>
                        </div>
                        <div class="category-meta">
                            <span class="category-progress" id="cat-progress-registration">0/6</span>
                            <i class="fas fa-chevron-down category-arrow"></i>
                        </div>
                    </div>
                    <div class="category-items">
                        <div class="checklist-item" data-item="1">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Certificate of Registration obtained</strong>
                                    <p>Valid certificate issued by the PBO Authority</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">PBO Act §10</span>
                                <button class="item-info-btn" onclick="showItemInfo('reg1')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="checklist-item" data-item="2">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Constitution/Memorandum of Association filed</strong>
                                    <p>Current governing document on file with PBO Authority</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">PBO Act §12</span>
                                <button class="item-info-btn" onclick="showItemInfo('reg2')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="checklist-item" data-item="3">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Annual returns submitted on time</strong>
                                    <p>Returns filed within prescribed deadline each year</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">PBO Act §34</span>
                                <button class="item-info-btn" onclick="showItemInfo('reg3')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="checklist-item" data-item="4">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Registered office address maintained</strong>
                                    <p>Physical address registered and kept current</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">PBO Act §15</span>
                                <button class="item-info-btn" onclick="showItemInfo('reg4')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="checklist-item" data-item="5">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Board of Directors properly constituted</strong>
                                    <p>Minimum required members and compliance with composition rules</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">PBO Act §20</span>
                                <button class="item-info-btn" onclick="showItemInfo('reg5')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="checklist-item" data-item="6">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Name and logo comply with PBO regulations</strong>
                                    <p>Organization name approved and in use as registered</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">PBO Act §8</span>
                                <button class="item-info-btn" onclick="showItemInfo('reg6')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category 2: Governance -->
                <div class="checklist-category" data-category="governance">
                    <div class="category-header" onclick="toggleCategory(this)">
                        <div class="category-info">
                            <div class="category-icon bg-purple">
                                <i class="fas fa-sitemap"></i>
                            </div>
                            <div>
                                <h3>2. Governance & Management</h3>
                                <p>Organizational governance structures and practices</p>
                            </div>
                        </div>
                        <div class="category-meta">
                            <span class="category-progress" id="cat-progress-governance">0/5</span>
                            <i class="fas fa-chevron-down category-arrow"></i>
                        </div>
                    </div>
                    <div class="category-items">
                        <div class="checklist-item" data-item="7">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Annual General Meeting (AGM) held</strong>
                                    <p>AGM conducted as required by the constitution</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">PBO Act §22</span>
                                <button class="item-info-btn" onclick="showItemInfo('gov1')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="checklist-item" data-item="8">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Board meeting minutes properly recorded</strong>
                                    <p>Minutes maintained for all board and committee meetings</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">PBO Act §24</span>
                                <button class="item-info-btn" onclick="showItemInfo('gov2')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="checklist-item" data-item="9">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Conflict of interest policy in place</strong>
                                    <p>Written policy with disclosure procedures adopted</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">PBO Act §26</span>
                                <button class="item-info-btn" onclick="showItemInfo('gov3')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="checklist-item" data-item="10">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Strategic plan documented and approved</strong>
                                    <p>Current strategic plan adopted by the board</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">Best Practice</span>
                                <button class="item-info-btn" onclick="showItemInfo('gov4')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="checklist-item" data-item="11">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>HR policies and procedures documented</strong>
                                    <p>Staff handbook covering key employment matters</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">Best Practice</span>
                                <button class="item-info-btn" onclick="showItemInfo('gov5')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category 3: Financial Compliance -->
                <div class="checklist-category" data-category="financial">
                    <div class="category-header" onclick="toggleCategory(this)">
                        <div class="category-info">
                            <div class="category-icon bg-green">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div>
                                <h3>3. Financial Compliance</h3>
                                <p>Financial management and reporting obligations</p>
                            </div>
                        </div>
                        <div class="category-meta">
                            <span class="category-progress" id="cat-progress-financial">0/5</span>
                            <i class="fas fa-chevron-down category-arrow"></i>
                        </div>
                    </div>
                    <div class="category-items">
                        <div class="checklist-item" data-item="12">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Annual audited accounts submitted</strong>
                                    <p>Audited financial statements submitted to PBO Authority</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">PBO Act §35</span>
                                <button class="item-info-btn" onclick="showItemInfo('fin1')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="checklist-item" data-item="13">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Budget approved by board annually</strong>
                                    <p>Annual budget formally adopted before fiscal year start</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">PBO Act §33</span>
                                <button class="item-info-btn" onclick="showItemInfo('fin2')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="checklist-item" data-item="14">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Separate bank account maintained</strong>
                                    <p>Dedicated organizational bank account in use</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">PBO Act §36</span>
                                <button class="item-info-btn" onclick="showItemInfo('fin3')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="checklist-item" data-item="15">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Financial controls and procurement policy</strong>
                                    <p>Documented financial controls and procurement procedures</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">Best Practice</span>
                                <button class="item-info-btn" onclick="showItemInfo('fin4')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="checklist-item" data-item="16">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>KRA PIN and tax compliance current</strong>
                                    <p>Tax obligations met and KRA PIN certificate valid</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">Tax Law</span>
                                <button class="item-info-btn" onclick="showItemInfo('fin5')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category 4: Reporting -->
                <div class="checklist-category" data-category="reporting">
                    <div class="category-header" onclick="toggleCategory(this)">
                        <div class="category-info">
                            <div class="category-icon bg-orange">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div>
                                <h3>4. Reporting & Transparency</h3>
                                <p>Reporting obligations and transparency requirements</p>
                            </div>
                        </div>
                        <div class="category-meta">
                            <span class="category-progress" id="cat-progress-reporting">0/4</span>
                            <i class="fas fa-chevron-down category-arrow"></i>
                        </div>
                    </div>
                    <div class="category-items">
                        <div class="checklist-item" data-item="17">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Annual report published</strong>
                                    <p>Annual narrative report on activities and finances available</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">PBO Act §37</span>
                                <button class="item-info-btn" onclick="showItemInfo('rep1')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="checklist-item" data-item="18">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Donor/funding disclosures made</strong>
                                    <p>Funding sources disclosed as required by law</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">PBO Act §38</span>
                                <button class="item-info-btn" onclick="showItemInfo('rep2')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="checklist-item" data-item="19">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Register of members maintained</strong>
                                    <p>Up-to-date member register kept at registered office</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">PBO Act §28</span>
                                <button class="item-info-btn" onclick="showItemInfo('rep3')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="checklist-item" data-item="20">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Material changes reported to PBO Authority</strong>
                                    <p>Changes in officers, address, activities reported promptly</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">PBO Act §16</span>
                                <button class="item-info-btn" onclick="showItemInfo('rep4')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category 5: Data & Safety -->
                <div class="checklist-category" data-category="datasafety">
                    <div class="category-header" onclick="toggleCategory(this)">
                        <div class="category-info">
                            <div class="category-icon bg-red">
                                <i class="fas fa-lock"></i>
                            </div>
                            <div>
                                <h3>5. Data Protection & Safety</h3>
                                <p>Data protection, safeguarding and security obligations</p>
                            </div>
                        </div>
                        <div class="category-meta">
                            <span class="category-progress" id="cat-progress-datasafety">0/4</span>
                            <i class="fas fa-chevron-down category-arrow"></i>
                        </div>
                    </div>
                    <div class="category-items">
                        <div class="checklist-item" data-item="21">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Data Protection Policy in place</strong>
                                    <p>Written policy compliant with Kenya Data Protection Act 2019</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">DPA 2019</span>
                                <button class="item-info-btn" onclick="showItemInfo('data1')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="checklist-item" data-item="22">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Safeguarding policy adopted</strong>
                                    <p>Child and vulnerable adult safeguarding policy in force</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">Best Practice</span>
                                <button class="item-info-btn" onclick="showItemInfo('data2')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="checklist-item" data-item="23">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Anti-Money Laundering (AML) compliance</strong>
                                    <p>AML risk assessment conducted and controls implemented</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">POCAMLA</span>
                                <button class="item-info-btn" onclick="showItemInfo('data3')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="checklist-item" data-item="24">
                            <label class="item-label">
                                <input type="checkbox" class="item-checkbox" onchange="updateChecklist()">
                                <span class="checkmark"></span>
                                <div class="item-content">
                                    <strong>Whistleblower/complaints mechanism</strong>
                                    <p>Safe, confidential reporting channel for staff and beneficiaries</p>
                                </div>
                            </label>
                            <div class="item-meta">
                                <span class="item-ref">Best Practice</span>
                                <button class="item-info-btn" onclick="showItemInfo('data4')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /.checklist-container -->

            <!-- Compliance Summary -->
            <div class="compliance-summary" id="complianceSummary">
                <h3><i class="fas fa-chart-pie"></i> Compliance Summary</h3>
                <div class="summary-grid">
                    <div class="summary-card summary-compliant">
                        <span class="summary-number" id="compliantCount">0</span>
                        <span class="summary-label">Compliant</span>
                    </div>
                    <div class="summary-card summary-pending">
                        <span class="summary-number" id="pendingCount">24</span>
                        <span class="summary-label">Pending</span>
                    </div>
                    <div class="summary-card summary-total">
                        <span class="summary-number">24</span>
                        <span class="summary-label">Total Items</span>
                    </div>
                </div>
                <div class="summary-recommendation" id="summaryRecommendation">
                    <i class="fas fa-lightbulb"></i>
                    <p>Begin by reviewing your registration documents and ensure your certificate of registration is current.</p>
                </div>
            </div>

        </div><!-- /#tab-checklist -->

        <!-- SELF-ASSESSMENT TAB -->
        <div class="tab-pane" id="tab-assessment">
            <div class="section-header">
                <h2>Compliance Self-Assessment Tool</h2>
                <p>Answer the questions below to receive an automated compliance score and personalized recommendations.</p>
            </div>

            <div class="assessment-container">
                <div class="assessment-progress">
                    <div class="step-indicators" id="stepIndicators">
                        <div class="step active" data-step="1">1</div>
                        <div class="step-line"></div>
                        <div class="step" data-step="2">2</div>
                        <div class="step-line"></div>
                        <div class="step" data-step="3">3</div>
                        <div class="step-line"></div>
                        <div class="step" data-step="4">4</div>
                        <div class="step-line"></div>
                        <div class="step" data-step="5">5</div>
                    </div>
                    <div class="step-labels">
                        <span>Organization Info</span>
                        <span>Registration</span>
                        <span>Governance</span>
                        <span>Finance</span>
                        <span>Results</span>
                    </div>
                </div>

                <form id="assessmentForm">

                    <!-- Step 1: Organization Info -->
                    <div class="assessment-step active" id="step-1">
                        <h3><i class="fas fa-building"></i> Organization Information</h3>
                        <p class="step-description">Tell us about your organization to customize the assessment.</p>

                        <div class="form-group">
                            <label>Organization Name (Optional)</label>
                            <input type="text" name="org_name" placeholder="Your organization's name">
                        </div>
                        <div class="form-group">
                            <label>Organization Type <span class="required">*</span></label>
                            <select name="org_type" required>
                                <option value="">-- Select Type --</option>
                                <option value="ngo">Non-Governmental Organization (NGO)</option>
                                <option value="cbo">Community Based Organization (CBO)</option>
                                <option value="trust">Charitable Trust</option>
                                <option value="association">Association</option>
                                <option value="foundation">Foundation</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Years of Operation <span class="required">*</span></label>
                            <select name="years_operation" required>
                                <option value="">-- Select --</option>
                                <option value="new">Less than 1 year</option>
                                <option value="1-3">1–3 years</option>
                                <option value="3-5">3–5 years</option>
                                <option value="5-10">5–10 years</option>
                                <option value="10+">More than 10 years</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>County of Operation <span class="required">*</span></label>
                            <select name="county" required>
                                <option value="">-- Select County --</option>
                                <option>Nairobi</option>
                                <option>Mombasa</option>
                                <option>Kisumu</option>
                                <option>Nakuru</option>
                                <option>Eldoret/Uasin Gishu</option>
                                <option>Multiple Counties</option>
                                <option>All Counties (National)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Primary Sector <span class="required">*</span></label>
                            <select name="sector" required>
                                <option value="">-- Select Sector --</option>
                                <option>Human Rights</option>
                                <option>Environment</option>
                                <option>Health</option>
                                <option>Education</option>
                                <option>Governance & Democracy</option>
                                <option>Women & Gender</option>
                                <option>Youth</option>
                                <option>Disability</option>
                                <option>Economic Development</option>
                                <option>Other</option>
                            </select>
                        </div>

                        <div class="step-actions">
                            <button type="button" class="btn btn-primary" onclick="nextStep(2)">
                                Next: Registration <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Registration -->
                    <div class="assessment-step" id="step-2">
                        <h3><i class="fas fa-file-signature"></i> Registration Status</h3>
                        <p class="step-description">Questions about your registration and legal status under the PBO Act.</p>

                        <div class="question-group">
                            <div class="question-card" data-weight="20">
                                <div class="question-header">
                                    <span class="question-num">Q1</span>
                                    <p>Is your organization currently registered with the PBO Authority?</p>
                                </div>
                                <div class="answer-options">
                                    <label class="answer-opt">
                                        <input type="radio" name="q_registered" value="3"> Yes, fully registered
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_registered" value="2"> Registration pending/in process
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_registered" value="1"> Registered under old NGO Act only
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_registered" value="0"> Not registered
                                    </label>
                                </div>
                            </div>

                            <div class="question-card" data-weight="15">
                                <div class="question-header">
                                    <span class="question-num">Q2</span>
                                    <p>When did you last submit annual returns to the PBO Authority?</p>
                                </div>
                                <div class="answer-options">
                                    <label class="answer-opt">
                                        <input type="radio" name="q_returns" value="3"> Within the last 12 months
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_returns" value="2"> 1–2 years ago
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_returns" value="1"> More than 2 years ago
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_returns" value="0"> Never submitted
                                    </label>
                                </div>
                            </div>

                            <div class="question-card" data-weight="10">
                                <div class="question-header">
                                    <span class="question-num">Q3</span>
                                    <p>Is your governing document (constitution/MoA) current and filed with PBO Authority?</p>
                                </div>
                                <div class="answer-options">
                                    <label class="answer-opt">
                                        <input type="radio" name="q_constitution" value="3"> Yes, current and filed
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_constitution" value="2"> Filed but needs updating
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_constitution" value="1"> Document exists but not filed
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_constitution" value="0"> No governing document
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="step-actions">
                            <button type="button" class="btn btn-outline" onclick="prevStep(1)">
                                <i class="fas fa-arrow-left"></i> Previous
                            </button>
                            <button type="button" class="btn btn-primary" onclick="nextStep(3)">
                                Next: Governance <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Governance -->
                    <div class="assessment-step" id="step-3">
                        <h3><i class="fas fa-sitemap"></i> Governance Practices</h3>
                        <p class="step-description">Questions about your board, meetings, and internal governance.</p>

                        <div class="question-group">
                            <div class="question-card" data-weight="15">
                                <div class="question-header">
                                    <span class="question-num">Q4</span>
                                    <p>How often does your board formally meet and keep minutes?</p>
                                </div>
                                <div class="answer-options">
                                    <label class="answer-opt">
                                        <input type="radio" name="q_board_meetings" value="3"> Quarterly or more, with full minutes
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_board_meetings" value="2"> Twice a year with some minutes
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_board_meetings" value="1"> Annual only
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_board_meetings" value="0"> Rarely or no formal meetings
                                    </label>
                                </div>
                            </div>

                            <div class="question-card" data-weight="10">
                                <div class="question-header">
                                    <span class="question-num">Q5</span>
                                    <p>Does your organization have a written conflict of interest policy?</p>
                                </div>
                                <div class="answer-options">
                                    <label class="answer-opt">
                                        <input type="radio" name="q_conflict" value="3"> Yes, with active disclosure process
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_conflict" value="2"> Yes, written but rarely enforced
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_conflict" value="1"> Informal understanding only
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_conflict" value="0"> No policy exists
                                    </label>
                                </div>
                            </div>

                            <div class="question-card" data-weight="10">
                                <div class="question-header">
                                    <span class="question-num">Q6</span>
                                    <p>Does your organization hold an Annual General Meeting (AGM)?</p>
                                </div>
                                <div class="answer-options">
                                    <label class="answer-opt">
                                        <input type="radio" name="q_agm" value="3"> Yes, annually as per constitution
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_agm" value="1"> Irregularly
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_agm" value="0"> Never held
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="step-actions">
                            <button type="button" class="btn btn-outline" onclick="prevStep(2)">
                                <i class="fas fa-arrow-left"></i> Previous
                            </button>
                            <button type="button" class="btn btn-primary" onclick="nextStep(4)">
                                Next: Finance <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 4: Financial -->
                    <div class="assessment-step" id="step-4">
                        <h3><i class="fas fa-money-bill-wave"></i> Financial Compliance</h3>
                        <p class="step-description">Questions about your financial management and reporting.</p>

                        <div class="question-group">
                            <div class="question-card" data-weight="15">
                                <div class="question-header">
                                    <span class="question-num">Q7</span>
                                    <p>Are your accounts audited by an external auditor annually?</p>
                                </div>
                                <div class="answer-options">
                                    <label class="answer-opt">
                                        <input type="radio" name="q_audit" value="3"> Yes, every year
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_audit" value="2"> Audited but not every year
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_audit" value="1"> Internal review only
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_audit" value="0"> Never audited
                                    </label>
                                </div>
                            </div>

                            <div class="question-card" data-weight="10">
                                <div class="question-header">
                                    <span class="question-num">Q8</span>
                                    <p>Does your organization have documented financial policies?</p>
                                </div>
                                <div class="answer-options">
                                    <label class="answer-opt">
                                        <input type="radio" name="q_fin_policy" value="3"> Yes, comprehensive and current
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_fin_policy" value="2"> Basic policies in place
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_fin_policy" value="1"> Informal practices only
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_fin_policy" value="0"> No financial policies
                                    </label>
                                </div>
                            </div>

                            <div class="question-card" data-weight="10">
                                <div class="question-header">
                                    <span class="question-num">Q9</span>
                                    <p>Is the organization's KRA PIN and tax compliance current?</p>
                                </div>
                                <div class="answer-options">
                                    <label class="answer-opt">
                                        <input type="radio" name="q_tax" value="3"> Yes, fully compliant
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_tax" value="2"> KRA PIN exists, returns pending
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_tax" value="1"> KRA PIN exists but non-compliant
                                    </label>
                                    <label class="answer-opt">
                                        <input type="radio" name="q_tax" value="0"> No KRA PIN
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="step-actions">
                            <button type="button" class="btn btn-outline" onclick="prevStep(3)">
                                <i class="fas fa-arrow-left"></i> Previous
                            </button>
                            <button type="button" class="btn btn-primary" onclick="calculateScore()">
                                <i class="fas fa-calculator"></i> Calculate My Score
                            </button>
                        </div>
                    </div>

                    <!-- Step 5: Results -->
                    <div class="assessment-step" id="step-5">
                        <div class="results-container" id="assessmentResults">
                            <!-- Populated by JS -->
                        </div>
                        <div class="step-actions">
                            <button type="button" class="btn btn-outline" onclick="prevStep(4)">
                                <i class="fas fa-arrow-left"></i> Review Answers
                            </button>
                            <button type="button" class="btn btn-primary" onclick="downloadAssessmentReport()">
                                <i class="fas fa-download"></i> Download Full Report
                            </button>
                            <button type="button" class="btn btn-outline" onclick="resetAssessment()">
                                <i class="fas fa-redo"></i> Start Again
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div><!-- /#tab-assessment -->

        <!-- REGISTRATION GUIDE TAB -->
        <div class="tab-pane" id="tab-registration">
            <div class="section-header">
                <h2>Registration & Compliance Guidance</h2>
                <p>Step-by-step guidance through the PBO registration process with document checklists and timelines.</p>
            </div>

            <div class="guide-container">
                <!-- Registration Timeline -->
                <div class="timeline-card">
                    <h3><i class="fas fa-route"></i> PBO Registration Process</h3>
                    <div class="registration-timeline">
                        <div class="timeline-step completed">
                            <div class="timeline-icon"><i class="fas fa-lightbulb"></i></div>
                            <div class="timeline-content">
                                <h4>Step 1: Pre-Application Preparation</h4>
                                <p>Gather all required documents and ensure your organization meets PBO eligibility criteria.</p>
                                <div class="doc-list">
                                    <span class="doc-tag"><i class="fas fa-file"></i> Name reservation letter</span>
                                    <span class="doc-tag"><i class="fas fa-file"></i> Draft constitution</span>
                                    <span class="doc-tag"><i class="fas fa-file"></i> ID copies of founders</span>
                                    <span class="doc-tag"><i class="fas fa-file"></i> Passport photos</span>
                                </div>
                                <span class="timeline-duration"><i class="fas fa-clock"></i> 1–2 weeks</span>
                            </div>
                        </div>
                        <div class="timeline-step">
                            <div class="timeline-icon"><i class="fas fa-paper-plane"></i></div>
                            <div class="timeline-content">
                                <h4>Step 2: Submit Application</h4>
                                <p>Submit completed application form PBO/1 with all required documentation to PBO Authority.</p>
                                <div class="doc-list">
                                    <span class="doc-tag"><i class="fas fa-file"></i> Form PBO/1 (Application)</span>
                                    <span class="doc-tag"><i class="fas fa-file"></i> Certified constitution</span>
                                    <span class="doc-tag"><i class="fas fa-file"></i> Board resolution</span>
                                    <span class="doc-tag"><i class="fas fa-file"></i> Application fee receipt</span>
                                </div>
                                <span class="timeline-duration"><i class="fas fa-clock"></i> Day 1</span>
                            </div>
                        </div>
                        <div class="timeline-step">
                            <div class="timeline-icon"><i class="fas fa-search"></i></div>
                            <div class="timeline-content">
                                <h4>Step 3: PBO Authority Review</h4>
                                <p>The PBO Authority reviews your application and may request additional information.</p>
                                <div class="alert-box alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <p>The PBO Authority has 60 days to approve or reject. Respond promptly to any queries.</p>
                                </div>
                                <span class="timeline-duration"><i class="fas fa-clock"></i> Up to 60 days</span>
                            </div>
                        </div>
                        <div class="timeline-step">
                            <div class="timeline-icon"><i class="fas fa-certificate"></i></div>
                            <div class="timeline-content">
                                <h4>Step 4: Certificate Issuance</h4>
                                <p>Upon approval, collect your Certificate of Registration from the PBO Authority offices.</p>
                                <div class="alert-box alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <p>Congratulations! You are now a registered PBO. Keep your certificate safe and make copies.</p>
                                </div>
                                <span class="timeline-duration"><i class="fas fa-clock"></i> After approval</span>
                            </div>
                        </div>
                        <div class="timeline-step">
                            <div class="timeline-icon"><i class="fas fa-sync-alt"></i></div>
                            <div class="timeline-content">
                                <h4>Step 5: Ongoing Compliance</h4>
                                <p>Maintain compliance by filing annual returns, audited accounts, and updating your records.</p>
                                <div class="compliance-calendar">
                                    <div class="cal-item">
                                        <span class="cal-date">Jan 31</span>
                                        <span class="cal-event">Annual Returns Deadline</span>
                                    </div>
                                    <div class="cal-item">
                                        <span class="cal-date">Mar 31</span>
                                        <span class="cal-event">Audited Accounts Submission</span>
                                    </div>
                                    <div class="cal-item">
                                        <span class="cal-date">Ongoing</span>
                                        <span class="cal-event">Report material changes within 30 days</span>
                                    </div>
                                </div>
                                <span class="timeline-duration"><i class="fas fa-clock"></i> Annually</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fee Structure -->
                <div class="fee-table-card">
                    <h3><i class="fas fa-money-check-alt"></i> Registration Fee Structure</h3>
                    <div class="table-responsive">
                        <table class="fee-table">
                            <thead>
                                <tr>
                                    <th>Organization Type</th>
                                    <th>Registration Fee</th>
                                    <th>Annual Fee</th>
                                    <th>Late Penalty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>CBO (Local)</td>
                                    <td>KES 1,000</td>
                                    <td>KES 500</td>
                                    <td>KES 200/month</td>
                                </tr>
                                <tr>
                                    <td>Association</td>
                                    <td>KES 5,000</td>
                                    <td>KES 2,000</td>
                                    <td>KES 500/month</td>
                                </tr>
                                <tr>
                                    <td>NGO (National)</td>
                                    <td>KES 10,000</td>
                                    <td>KES 5,000</td>
                                    <td>KES 1,000/month</td>
                                </tr>
                                <tr>
                                    <td>Foundation</td>
                                    <td>KES 20,000</td>
                                    <td>KES 10,000</td>
                                    <td>KES 2,000/month</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="table-note"><i class="fas fa-exclamation-triangle"></i> Fees are indicative. Confirm current fees with the PBO Authority.</p>
                </div>
            </div>
        </div><!-- /#tab-registration -->

        <!-- TEMPLATES TAB -->
        <div class="tab-pane" id="tab-templates">
            <div class="section-header">
                <h2>Downloadable Templates & Resources</h2>
                <p>Ready-to-use compliance templates and governance documents for your PBO.</p>
            </div>

            <div class="templates-grid">
                <?php
                $templates = [
                    ['icon'=>'fa-file-signature','color'=>'blue','title'=>'PBO Registration Application','desc'=>'Completed sample Form PBO/1 with guidance notes','format'=>'DOCX','size'=>'245 KB','category'=>'Registration','file'=>'pbo-registration-form.docx'],
                    ['icon'=>'fa-book','color'=>'purple','title'=>'Model Constitution (NGO)','desc'=>'Template constitution compliant with PBO Act requirements','format'=>'DOCX','size'=>'189 KB','category'=>'Governance','file'=>'model-constitution-ngo.docx'],
                    ['icon'=>'fa-book','color'=>'purple','title'=>'Model Constitution (CBO)','desc'=>'Simplified constitution template for community-based organizations','format'=>'DOCX','size'=>'145 KB','category'=>'Governance','file'=>'model-constitution-cbo.docx'],
                    ['icon'=>'fa-gavel','color'=>'orange','title'=>'Board Meeting Minutes Template','desc'=>'Standard minutes format with all required elements','format'=>'DOCX','size'=>'98 KB','category'=>'Governance','file'=>'board-minutes-template.docx'],
                    ['icon'=>'fa-exclamation-triangle','color'=>'red','title'=>'Conflict of Interest Policy','desc'=>'Sample policy with disclosure forms and register','format'=>'DOCX','size'=>'134 KB','category'=>'Governance','file'=>'conflict-of-interest-policy.docx'],
                    ['icon'=>'fa-chart-line','color'=>'green','title'=>'Annual Returns Form (PBO/2)','desc'=>'Guidance on completing the annual returns submission','format'=>'PDF','size'=>'320 KB','category'=>'Reporting','file'=>'annual-returns-guide.pdf'],
                    ['icon'=>'fa-calculator','color'=>'teal','title'=>'Budget Template','desc'=>'Annual organizational budget with standard cost categories','format'=>'XLSX','size'=>'67 KB','category'=>'Finance','file'=>'annual-budget-template.xlsx'],
                    ['icon'=>'fa-receipt','color'=>'yellow','title'=>'Petty Cash Register','desc'=>'Petty cash tracking register with reconciliation sheet','format'=>'XLSX','size'=>'45 KB','category'=>'Finance','file'=>'petty-cash-register.xlsx'],
                    ['icon'=>'fa-shield-alt','color'=>'navy','title'=>'Data Protection Policy','desc'=>'Kenya DPA 2019 compliant data protection policy template','format'=>'DOCX','size'=>'178 KB','category'=>'Compliance','file'=>'data-protection-policy.docx'],
                    ['icon'=>'fa-child','color'=>'pink','title'=>'Safeguarding Policy','desc'=>'Child and vulnerable adult safeguarding policy','format'=>'DOCX','size'=>'212 KB','category'=>'Compliance','file'=>'safeguarding-policy.docx'],
                    ['icon'=>'fa-handshake','color'=>'brown','title'=>'MOU Template','desc'=>'Memorandum of Understanding for partnerships','format'=>'DOCX','size'=>'89 KB','category'=>'Legal','file'=>'mou-template.docx'],
                    ['icon'=>'fa-users','color'=>'indigo','title'=>'HR Policy Manual','desc'=>'Comprehensive HR policies for NGOs/PBOs','format'=>'DOCX','size'=>'456 KB','category'=>'HR','file'=>'hr-policy-manual.docx'],
                ];
                foreach($templates as $t): ?>
                <div class="template-card">
                    <div class="template-header">
                        <div class="template-icon" style="background:var(--<?php echo $t['color'];?>-light,#e8f4fd)">
                            <i class="fas <?php echo $t['icon'];?>" style="color:var(--<?php echo $t['color'];?>-dark,#1a73e8)"></i>
                        </div>
                        <span class="template-category"><?php echo $t['category'];?></span>
                    </div>
                    <div class="template-body">
                        <h4><?php echo $t['title'];?></h4>
                        <p><?php echo $t['desc'];?></p>
                        <div class="template-meta">
                            <span class="format-badge format-<?php echo strtolower($t['format']);?>"><?php echo $t['format'];?></span>
                            <span class="file-size"><i class="fas fa-hdd"></i> <?php echo $t['size'];?></span>
                        </div>
                    </div>
                    <div class="template-footer">
                        <a href="../../downloads/templates/<?php echo $t['file'];?>" class="btn btn-primary btn-sm" download>
                            <i class="fas fa-download"></i> Download
                        </a>
                        <button class="btn btn-outline btn-sm" onclick="previewTemplate('<?php echo $t['file'];?>')">
                            <i class="fas fa-eye"></i> Preview
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div><!-- /#tab-templates -->

    </div><!-- /.container -->
</section>

<!-- Info Modal -->
<div class="modal-overlay" id="infoModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Item Details</h3>
            <button class="modal-close" onclick="closeModal('infoModal')">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Populated by JS -->
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// ===================== TABS =====================
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('tab-' + this.dataset.tab).classList.add('active');
    });
});

// ===================== CATEGORY TOGGLE =====================
function toggleCategory(header) {
    const category = header.closest('.checklist-category');
    category.classList.toggle('open');
    const arrow = header.querySelector('.category-arrow');
    arrow.style.transform = category.classList.contains('open') ? 'rotate(180deg)' : '';
}

// ===================== CHECKLIST LOGIC =====================
const TOTAL_ITEMS = 24;
const categoryConfig = {
    registration: { items: [1,2,3,4,5,6], total: 6 },
    governance:   { items: [7,8,9,10,11], total: 5 },
    financial:    { items: [12,13,14,15,16], total: 5 },
    reporting:    { items: [17,18,19,20], total: 4 },
    datasafety:   { items: [21,22,23,24], total: 4 }
};

function updateChecklist() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    let totalChecked = 0;

    checkboxes.forEach(cb => {
        if(cb.checked) totalChecked++;
    });

    const pct = Math.round((totalChecked / TOTAL_ITEMS) * 100);

    // Update progress bar
    document.getElementById('checklistProgressBar').style.width = pct + '%';
    document.getElementById('progressText').textContent = totalChecked + ' of ' + TOTAL_ITEMS + ' completed';

    // Update meter
    const circumference = 502;
    const offset = circumference - (pct / 100) * circumference;
    document.getElementById('meterCircle').style.strokeDashoffset = offset;
    document.getElementById('meterValue').textContent = pct + '%';

    // Update status badge
    const statusEl = document.getElementById('complianceStatus');
    let statusHtml = '';
    if(pct === 0) statusHtml = '<span class="status-badge status-low">Not Started</span>';
    else if(pct < 40) statusHtml = '<span class="status-badge status-low">Low Compliance</span>';
    else if(pct < 70) statusHtml = '<span class="status-badge status-medium">Partial Compliance</span>';
    else if(pct < 100) statusHtml = '<span class="status-badge status-high">Good Compliance</span>';
    else statusHtml = '<span class="status-badge status-complete">Fully Compliant</span>';
    statusEl.innerHTML = statusHtml;

    // Update summary
    document.getElementById('compliantCount').textContent = totalChecked;
    document.getElementById('pendingCount').textContent = TOTAL_ITEMS - totalChecked;

    // Update category progress
    Object.keys(categoryConfig).forEach(cat => {
        const config = categoryConfig[cat];
        let catChecked = 0;
        config.items.forEach(itemNum => {
            const item = document.querySelector('[data-item="' + itemNum + '"] .item-checkbox');
            if(item && item.checked) catChecked++;
        });
        const el = document.getElementById('cat-progress-' + cat);
        if(el) el.textContent = catChecked + '/' + config.total;
    });

    // Recommendation
    const recEl = document.getElementById('summaryRecommendation');
    let rec = '';
    if(pct < 25) rec = 'Priority: Ensure your organization is registered with the PBO Authority and obtain your Certificate of Registration.';
    else if(pct < 50) rec = 'Good start! Focus on completing your governance requirements including board meetings and conflict of interest policies.';
    else if(pct < 75) rec = 'Well done! Ensure all financial compliance items are met, particularly audited accounts and tax compliance.';
    else if(pct < 100) rec = 'Excellent progress! Complete the remaining items to achieve full compliance status.';
    else rec = 'Congratulations! Your organization is fully compliant with all tracked PBO Act requirements.';
    recEl.querySelector('p').textContent = rec;

    saveChecklistToStorage();
}

function saveChecklistToStorage() {
    const states = [];
    document.querySelectorAll('.item-checkbox').forEach((cb, i) => {
        states.push(cb.checked);
    });
    localStorage.setItem('pbo_checklist', JSON.stringify(states));
}

function loadChecklistFromStorage() {
    const saved = localStorage.getItem('pbo_checklist');
    if(saved) {
        const states = JSON.parse(saved);
        document.querySelectorAll('.item-checkbox').forEach((cb, i) => {
            if(states[i] !== undefined) cb.checked = states[i];
        });
        updateChecklist();
    }
}

function resetChecklist() {
    if(confirm('Reset all checklist items? This cannot be undone.')) {
        document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = false);
        localStorage.removeItem('pbo_checklist');
        updateChecklist();
    }
}

function saveChecklist() {
    saveChecklistToStorage();
    showNotification('Checklist progress saved!', 'success');
}

// ===================== ASSESSMENT LOGIC =====================
let currentStep = 1;
const TOTAL_STEPS = 5;

function nextStep(step) {
    document.getElementById('step-' + currentStep).classList.remove('active');
    document.getElementById('step-' + step).classList.add('active');

    document.querySelectorAll('.step').forEach((s, i) => {
        if(i + 1 <= step) s.classList.add('active');
        else s.classList.remove('active');
    });
    currentStep = step;
}

function prevStep(step) {
    nextStep(step);
}

function calculateScore() {
    const questions = ['q_registered','q_returns','q_constitution','q_board_meetings','q_conflict','q_agm','q_audit','q_fin_policy','q_tax'];
    const weights   = [20, 15, 10, 15, 10, 10, 15, 10, 10]; // sum = 115, normalize below
    const labels    = ['Registration Status','Annual Returns','Governing Document','Board Meetings','Conflict of Interest Policy','Annual General Meeting','External Audit','Financial Policies','KRA/Tax Compliance'];

    let totalScore = 0;
    let maxScore = 0;
    let breakdown = [];

    questions.forEach((q, i) => {
        const selected = document.querySelector('input[name="' + q + '"]:checked');
        const val = selected ? parseInt(selected.value) : 0;
        const weighted = val * weights[i];
        totalScore += weighted;
        maxScore += 3 * weights[i];
        breakdown.push({ label: labels[i], score: val, max: 3, weight: weights[i] });
    });

    const pct = Math.round((totalScore / maxScore) * 100);
    let level, color, icon, recommendation;

    if(pct >= 85) {
        level = 'High Compliance'; color = '#10b981'; icon = 'fa-trophy';
        recommendation = 'Excellent! Your organization demonstrates strong compliance. Focus on maintaining standards and addressing any remaining gaps.';
    } else if(pct >= 65) {
        level = 'Good Compliance'; color = '#3b82f6'; icon = 'fa-thumbs-up';
        recommendation = 'Good work! Address the lower-scoring areas, particularly around financial reporting and governance documentation.';
    } else if(pct >= 40) {
        level = 'Partial Compliance'; color = '#f59e0b'; icon = 'fa-exclamation-circle';
        recommendation = 'There are significant gaps to address. Prioritize registration, annual returns, and financial compliance items urgently.';
    } else {
        level = 'Low Compliance'; color = '#ef4444'; icon = 'fa-times-circle';
        recommendation = 'Immediate action required. Start with your PBO registration and constitution, then work through each compliance area systematically.';
    }

    const resultsHTML = `
        <div class="results-header">
            <div class="score-circle" style="border-color: ${color}">
                <span class="score-pct" style="color: ${color}">${pct}%</span>
                <span class="score-label">Compliance Score</span>
            </div>
            <div class="score-info">
                <div class="score-level" style="color: ${color}">
                    <i class="fas ${icon}"></i> ${level}
                </div>
                <p class="score-recommendation">${recommendation}</p>
            </div>
        </div>
        <div class="breakdown-section">
            <h4>Score Breakdown</h4>
            ${breakdown.map(b => `
                <div class="breakdown-item">
                    <div class="breakdown-label">${b.label}</div>
                    <div class="breakdown-bar-wrap">
                        <div class="breakdown-bar" style="width:${Math.round(b.score/b.max*100)}%; background:${b.score >= 2 ? '#10b981' : b.score === 1 ? '#f59e0b' : '#ef4444'}"></div>
                    </div>
                    <div class="breakdown-score">${b.score}/${b.max}</div>
                </div>
            `).join('')}
        </div>
        <div class="next-steps">
            <h4><i class="fas fa-list-check"></i> Recommended Next Steps</h4>
            ${breakdown.filter(b => b.score < 2).map(b => `
                <div class="next-step-item">
                    <i class="fas fa-arrow-right" style="color:#ef4444"></i>
                    <span>Improve: <strong>${b.label}</strong></span>
                </div>
            `).join('') || '<p class="text-success"><i class="fas fa-check"></i> All areas performing well!</p>'}
        </div>
    `;

    document.getElementById('assessmentResults').innerHTML = resultsHTML;
    nextStep(5);
}

function resetAssessment() {
    document.getElementById('assessmentForm').reset();
    nextStep(1);
}

// ===================== MODAL =====================
const itemInfo = {
    reg1: { title: 'Certificate of Registration', body: '<p>All organizations operating as PBOs must obtain a valid Certificate of Registration from the Public Benefit Organizations Authority under Section 10 of the PBO Act.</p><p><strong>Action:</strong> Contact the PBO Authority at Upperhill, Nairobi or visit their portal to apply.</p>' },
    reg2: { title: 'Constitution/Memorandum of Association', body: '<p>Your organization must have a written governing document (constitution or MoA) that complies with the minimum requirements under Section 12 of the PBO Act.</p>' },
};

function showItemInfo(key) {
    const info = itemInfo[key] || { title: 'More Information', body: '<p>Please refer to the PBO Act for detailed guidance on this compliance item.</p>' };
    document.getElementById('modalTitle').textContent = info.title;
    document.getElementById('modalBody').innerHTML = info.body;
    document.getElementById('infoModal').classList.add('active');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

function showNotification(msg, type = 'info') {
    const n = document.createElement('div');
    n.className = 'notification notification-' + type;
    n.innerHTML = '<i class="fas fa-check-circle"></i> ' + msg;
    document.body.appendChild(n);
    setTimeout(() => n.remove(), 3500);
}

// ===================== INIT =====================
document.addEventListener('DOMContentLoaded', () => {
    loadChecklistFromStorage();

    // Open first category by default
    const firstCat = document.querySelector('.checklist-category');
    if(firstCat) {
        firstCat.classList.add('open');
    }

    // Animate meter on load
    setTimeout(() => updateChecklist(), 500);
});

function downloadChecklistPDF() { showNotification('Generating PDF report...', 'info'); }
function downloadAssessmentReport() { showNotification('Generating assessment report...', 'info'); }
function previewTemplate(file) { showNotification('Preview feature coming soon.', 'info'); }
</script>

<script src="../../assets/js/compliance.js"></script>
</body>
</html>