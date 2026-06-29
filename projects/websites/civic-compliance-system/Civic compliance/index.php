<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/auth.php';

$auth = new Auth();
$db = Database::getInstance();
$pageTitle = APP_NAME . ' - Empowering Civil Society';
$currentPage = 'home';

// Fetch stats for homepage
$stats = [
    'articles'   => $db->fetchOne("SELECT COUNT(*) as c FROM knowledge_articles WHERE is_published = 1")['c'] ?? 0,
    'resources'  => $db->fetchOne("SELECT COUNT(*) as c FROM resources WHERE is_active = 1")['c'] ?? 0,
    'faqs'       => $db->fetchOne("SELECT COUNT(*) as c FROM faqs WHERE is_published = 1")['c'] ?? 0,
    'reports'    => $db->fetchOne("SELECT COUNT(*) as c FROM monitoring_reports WHERE status != 'rejected'")['c'] ?? 0,
];

$featuredArticles = $db->fetchAll(
    "SELECT * FROM knowledge_articles WHERE is_published = 1 AND is_featured = 1 LIMIT 6"
);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kenya's premier platform for PBO legal awareness, compliance tools, and civic space monitoring under the PBO Act 2013">
    <meta name="keywords" content="PBO Act Kenya, Public Benefit Organizations, NGO compliance, civil society Kenya, CRECO Kenya">
    <meta name="author" content="CRECO Kenya">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= APP_FULL_NAME ?>">
    <meta property="og:description" content="Empowering Kenya's civil society through legal knowledge and compliance tools">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= APP_URL ?>">
    
    <!-- Security Headers via meta -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="SAMEORIGIN">
    
    <title><?= $pageTitle ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- HERO SECTION -->
<!-- ═══════════════════════════════════════════════════════ -->
<section class="hero-section" id="hero">
    <div class="hero-overlay"></div>
    <div class="container position-relative">
        <div class="row align-items-center min-vh-85">
            <div class="col-lg-7" data-aos="fade-right">
                <div class="hero-badge mb-3">
                    <span class="badge-dot"></span>
                    <span>Kenya's Civil Society Compliance Platform</span>
                </div>
                <h1 class="hero-title">
                    Know Your Rights.<br>
                    <span class="text-accent">Stay Compliant.</span><br>
                    Protect Civil Space.
                </h1>
                <p class="hero-subtitle">
                    Kenya's comprehensive platform for Public Benefit Organizations — 
                    access plain-language legal summaries, compliance tools, 
                    AI-powered guidance, and civic space monitoring under the PBO Act 2013.
                </p>
                <div class="hero-actions">
                    <a href="modules/knowledge-hub/" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-book-open me-2"></i>Explore Knowledge Hub
                    </a>
                    <a href="modules/compliance-tools/" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-clipboard-check me-2"></i>Check Compliance
                    </a>
                </div>
                <div class="hero-languages mt-4">
                    <span class="lang-tag active" onclick="switchLang('en')">
                        <i class="fas fa-language me-1"></i>English
                    </span>
                    <span class="lang-tag" onclick="switchLang('sw')">
                        <i class="fas fa-language me-1"></i>Kiswahili
                    </span>
                </div>
            </div>
            <div class="col-lg-5" data-aos="fade-left">
                <div class="hero-cards">
                    <div class="hero-stat-card">
                        <div class="stat-icon"><i class="fas fa-gavel"></i></div>
                        <div class="stat-info">
                            <span class="stat-num" data-target="<?= $stats['articles'] ?>">0</span>
                            <span class="stat-label">Legal Articles</span>
                        </div>
                    </div>
                    <div class="hero-stat-card">
                        <div class="stat-icon bg-success"><i class="fas fa-file-download"></i></div>
                        <div class="stat-info">
                            <span class="stat-num" data-target="<?= $stats['resources'] ?>">0</span>
                            <span class="stat-label">Downloadable Resources</span>
                        </div>
                    </div>
                    <div class="hero-stat-card">
                        <div class="stat-icon bg-warning"><i class="fas fa-chart-bar"></i></div>
                        <div class="stat-info">
                            <span class="stat-num" data-target="<?= $stats['reports'] ?>">0</span>
                            <span class="stat-label">Monitoring Reports</span>
                        </div>
                    </div>
                    <div class="hero-stat-card">
                        <div class="stat-icon bg-info"><i class="fas fa-question-circle"></i></div>
                        <div class="stat-info">
                            <span class="stat-num" data-target="<?= $stats['faqs'] ?>">0</span>
                            <span class="stat-label">FAQs Answered</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-wave">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M985.66,92.83C906.67,72,823.78,31,743.84,14.19c-82.26-17.34-168.06-16.33-250.45.39-57.84,11.73-114,31.07-172,41.86A600.21,600.21,0,0,1,0,27.35V120H1200V95.8C1132.19,118.92,1055.71,111.31,985.66,92.83Z"/>
        </svg>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- QUICK ACCESS MODULES -->
<!-- ═══════════════════════════════════════════════════════ -->
<section class="modules-section py-6" id="modules">
    <div class="container">
        <div class="section-header text-center mb-5" data-aos="fade-up">
            <span class="section-badge">Platform Modules</span>
            <h2 class="section-title">Everything Your PBO Needs</h2>
            <p class="section-subtitle">Comprehensive tools and resources for civil society organizations across Kenya</p>
        </div>
        
        <div class="row g-4">
            <!-- Knowledge Hub -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <a href="modules/knowledge-hub/" class="module-card-link">
                    <div class="module-card">
                        <div class="module-icon" style="background:linear-gradient(135deg,#667eea,#764ba2)">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="module-body">
                            <h4>Legal Knowledge Hub</h4>
                            <p>Plain-language summaries of the PBO Act, Kiswahili translations, guides, FAQs, and multimedia resources.</p>
                            <div class="module-tags">
                                <span class="tag">PBO Act Summaries</span>
                                <span class="tag">Kiswahili</span>
                                <span class="tag">FAQs</span>
                            </div>
                        </div>
                        <div class="module-footer">
                            <span>Explore Hub <i class="fas fa-arrow-right ms-2"></i></span>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Compliance Tools -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <a href="modules/compliance-tools/" class="module-card-link">
                    <div class="module-card">
                        <div class="module-icon" style="background:linear-gradient(135deg,#11998e,#38ef7d)">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div class="module-body">
                            <h4>Compliance Tools</h4>
                            <p>Interactive compliance checklists with automated scoring, registration guidance, and downloadable templates.</p>
                            <div class="module-tags">
                                <span class="tag">Self-Assessment</span>
                                <span class="tag">Auto Scoring</span>
                                <span class="tag">Templates</span>
                            </div>
                        </div>
                        <div class="module-footer">
                            <span>Check Compliance <i class="fas fa-arrow-right ms-2"></i></span>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- AI Q&A Chatbot -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <a href="modules/chatbot/" class="module-card-link">
                    <div class="module-card">
                        <div class="module-icon" style="background:linear-gradient(135deg,#f093fb,#f5576c)">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="module-body">
                            <h4>AI Legal Assistant</h4>
                            <p>Get instant answers to PBO Act questions from our AI-powered chatbot trained exclusively on approved official materials.</p>
                            <div class="module-tags">
                                <span class="tag">AI-Powered</span>
                                <span class="tag">PBO Act</span>
                                <span class="tag">Instant Answers</span>
                            </div>
                        </div>
                        <div class="module-footer">
                            <span>Ask Assistant <i class="fas fa-arrow-right ms-2"></i></span>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Civic Monitoring -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <a href="modules/monitoring/" class="module-card-link">
                    <div class="module-card">
                        <div class="module-icon" style="background:linear-gradient(135deg,#4facfe,#00f2fe)">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="module-body">
                            <h4>Civic Space Monitor</h4>
                            <p>Report registration challenges, compliance barriers, delays, violations, and enabling practices securely.</p>
                            <div class="module-tags">
                                <span class="tag">Secure Reporting</span>
                                <span class="tag">Confidential</span>
                                <span class="tag">Incident Reports</span>
                            </div>
                        </div>
                        <div class="module-footer">
                            <span>Submit Report <i class="fas fa-arrow-right ms-2"></i></span>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Dashboard -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                <a href="modules/dashboard/" class="module-card-link">
                    <div class="module-card">
                        <div class="module-icon" style="background:linear-gradient(135deg,#fa709a,#fee140)">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="module-body">
                            <h4>Data Dashboard</h4>
                            <p>Visualize aggregated monitoring data, trends by region, usage analytics, and generate exportable reports.</p>
                            <div class="module-tags">
                                <span class="tag">Real-time Data</span>
                                <span class="tag">Export CSV/PDF</span>
                                <span class="tag">Region Maps</span>
                            </div>
                        </div>
                        <div class="module-footer">
                            <span>View Dashboard <i class="fas fa-arrow-right ms-2"></i></span>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Resources -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                <a href="modules/knowledge-hub/resources.php" class="module-card-link">
                    <div class="module-card">
                        <div class="module-icon" style="background:linear-gradient(135deg,#a18cd1,#fbc2eb)">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <div class="module-body">
                            <h4>Resource Library</h4>
                            <p>Download toolkits, templates, guides, infographics, and compliance resources for your organization.</p>
                            <div class="module-tags">
                                <span class="tag">Toolkits</span>
                                <span class="tag">Templates</span>
                                <span class="tag">Free Download</span>
                            </div>
                        </div>
                        <div class="module-footer">
                            <span>Browse Resources <i class="fas fa-arrow-right ms-2"></i></span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- PBO ACT HIGHLIGHT SECTION -->
<!-- ═══════════════════════════════════════════════════════ -->
<section class="pbo-highlight py-6 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <span class="section-badge">The Law Explained Simply</span>
                <h2 class="section-title">Understanding the PBO Act 2013</h2>
                <p class="lead">The Public Benefit Organizations Act governs all civil society organizations in Kenya. Our platform breaks it down into plain language you can understand and act on.</p>
                
                <div class="pbo-features">
                    <div class="pbo-feature-item">
                        <div class="feature-icon"><i class="fas fa-check-circle text-success"></i></div>
                        <div>
                            <h6>Registration Requirements</h6>
                            <p>Step-by-step guidance on PBO registration, documentation, and fees</p>
                        </div>
                    </div>
                    <div class="pbo-feature-item">
                        <div class="feature-icon"><i class="fas fa-check-circle text-success"></i></div>
                        <div>
                            <h6>Governance Standards</h6>
                            <p>Board composition, meeting requirements, and accountability frameworks</p>
                        </div>
                    </div>
                    <div class="pbo-feature-item">
                        <div class="feature-icon"><i class="fas fa-check-circle text-success"></i></div>
                        <div>
                            <h6>Financial Compliance</h6>
                            <p>Auditing requirements, annual returns, and financial reporting standards</p>
                        </div>
                    </div>
                    <div class="pbo-feature-item">
                        <div class="feature-icon"><i class="fas fa-check-circle text-success"></i></div>
                        <div>
                            <h6>Rights & Protections</h6>
                            <p>Understanding PBO rights, civic space protections, and legal remedies</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="modules/knowledge-hub/" class="btn btn-primary me-3">
                        <i class="fas fa-book me-2"></i>Read the Summaries
                    </a>
                    <a href="modules/knowledge-hub/resources.php" class="btn btn-outline-primary">
                        <i class="fas fa-download me-2"></i>Download Full Act
                    </a>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="pbo-act-visual">
                    <div class="act-section-card" data-aos="zoom-in" data-aos-delay="100">
                        <div class="act-section-num">Part I</div>
                        <h6>Preliminary</h6>
                        <p>Definitions, purpose, and application of the Act</p>
                        <a href="modules/knowledge-hub/?section=part1" class="act-link">Read Summary →</a>
                    </div>
                    <div class="act-section-card" data-aos="zoom-in" data-aos-delay="200">
                        <div class="act-section-num">Part II</div>
                        <h6>Registration</h6>
                        <p>Registration requirements, procedures, and the PBO Regulatory Authority</p>
                        <a href="modules/knowledge-hub/?section=part2" class="act-link">Read Summary →</a>
                    </div>
                    <div class="act-section-card" data-aos="zoom-in" data-aos-delay="300">
                        <div class="act-section-num">Part III</div>
                        <h6>Rights & Obligations</h6>
                        <p>Rights of PBOs, governance obligations, and operational requirements</p>
                        <a href="modules/knowledge-hub/?section=part3" class="act-link">Read Summary →</a>
                    </div>
                    <div class="act-section-card" data-aos="zoom-in" data-aos-delay="400">
                        <div class="act-section-num">Part IV</div>
                        <h6>Compliance & Enforcement</h6>
                        <p>Reporting, inspections, penalties, and dispute resolution</p>
                        <a href="modules/knowledge-hub/?section=part4" class="act-link">Read Summary →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- CHATBOT SECTION -->
<!-- ═══════════════════════════════════════════════════════ -->
<section class="chatbot-section py-6">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-2" data-aos="fade-left">
                <span class="section-badge">AI-Powered Legal Guidance</span>
                <h2 class="section-title">Get Instant Answers About the PBO Act</h2>
                <p>Our AI Legal Assistant is trained exclusively on the PBO Act 2013 and official regulations. Get structured, accurate responses instantly — available in English and Kiswahili.</p>
                
                <div class="chatbot-features">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-shield-check text-primary fa-lg me-3"></i>
                        <span>Trained only on approved PBO Act materials</span>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-language text-primary fa-lg me-3"></i>
                        <span>Available in English and Kiswahili</span>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-flag text-primary fa-lg me-3"></i>
                        <span>Flag inaccurate responses to improve the system</span>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-exclamation-circle text-warning fa-lg me-3"></i>
                        <span>Responses do not constitute legal advice</span>
                    </div>
                </div>
                
                <a href="modules/chatbot/" class="btn btn-primary btn-lg">
                    <i class="fas fa-comments me-2"></i>Start a Conversation
                </a>
            </div>
            <div class="col-lg-6 order-lg-1" data-aos="fade-right">
                <!-- Mini Chatbot Preview -->
                <div class="chatbot-preview">
                    <div class="chat-header">
                        <div class="chat-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div>
                            <strong>PBO Legal Assistant</strong>
                            <span class="online-dot"></span>
                            <small class="d-block text-muted">Online | Powered by CRECO Kenya</small>
                        </div>
                        <div class="ms-auto">
                            <span class="badge bg-success">Active</span>
                        </div>
                    </div>
                    <div class="chat-messages" id="previewChat">
                        <div class="chat-msg bot">
                            <div class="msg-bubble">
                                <p>Habari! Hello! I'm the PBO Legal Assistant. How can I help you understand the PBO Act 2013 today?</p>
                                <small class="text-muted">⚠️ Responses do not constitute legal advice</small>
                            </div>
                        </div>
                        <div class="chat-msg user">
                            <div class="msg-bubble">How do I register my NGO as a PBO?</div>
                        </div>
                        <div class="chat-msg bot" id="typingDemo">
                            <div class="msg-bubble">
                                <div class="typing-indicator">
                                    <span></span><span></span><span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="chat-input-preview">
                        <input type="text" placeholder="Ask about the PBO Act..." class="form-control" id="previewInput">
                        <button class="btn btn-primary" onclick="window.location='modules/chatbot/'">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                    <div class="chat-disclaimer">
                        <small><i class="fas fa-info-circle me-1"></i>This assistant provides general information only. For specific legal advice, consult a qualified advocate.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- MONITORING CALL TO ACTION -->
<!-- ═══════════════════════════════════════════════════════ -->
<section class="monitoring-cta py-6 bg-danger text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h2 class="fw-bold mb-2">
                    <i class="fas fa-exclamation-triangle me-3"></i>
                    Experiencing Civic Space Violations?
                </h2>
                <p class="lead mb-0">
                    Report administrative barriers, registration delays, harassment, or compliance challenges. 
                    Your reports help document and improve civic space in Kenya.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0" data-aos="fade-left">
                <a href="modules/monitoring/report.php" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-file-alt me-2"></i>Submit Report
                </a>
                <a href="modules/monitoring/incident.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-flag me-2"></i>Report Incident
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- FEATURED ARTICLES -->
<!-- ═══════════════════════════════════════════════════════ -->
<section class="articles-section py-6 bg-light">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center mb-5">
            <div data-aos="fade-right">
                <span class="section-badge">Legal Insights</span>
                <h2 class="section-title mb-0">Featured Articles</h2>
            </div>
            <a href="modules/knowledge-hub/" class="btn btn-outline-primary" data-aos="fade-left">
                View All <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
        
        <div class="row g-4">
            <?php if (empty($featuredArticles)): ?>
                <!-- Placeholder articles when DB is empty -->
                <?php
                $placeholders = [
                    ['category' => 'pbo_act', 'title' => 'Understanding PBO Registration Requirements', 'summary' => 'A plain-language guide to registering your organization under the PBO Act 2013, covering all documentation and procedural requirements.', 'icon' => 'gavel', 'color' => 'primary'],
                    ['category' => 'compliance', 'title' => 'Annual Compliance: What PBOs Must Know', 'summary' => 'Breaking down the annual reporting obligations, financial auditing requirements, and governance standards that every PBO must meet.', 'icon' => 'clipboard-check', 'color' => 'success'],
                    ['category' => 'rights', 'title' => "PBO Rights and Civic Space Protections", 'summary' => 'Understanding the rights guaranteed to PBOs under Kenyan law and the constitutional protections for civil society organizations.', 'icon' => 'shield-alt', 'color' => 'info'],
                ];
                foreach ($placeholders as $a): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="article-card">
                        <div class="article-category">
                            <span class="badge bg-<?= $a['color'] ?>"><?= ucwords(str_replace('_', ' ', $a['category'])) ?></span>
                        </div>
                        <h5 class="article-title"><?= $a['title'] ?></h5>
                        <p class="article-summary"><?= $a['summary'] ?></p>
                        <div class="article-footer">
                            <a href="modules/knowledge-hub/" class="btn btn-sm btn-outline-primary">
                                Read More <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                            <span class="text-muted"><i class="fas fa-clock me-1"></i>5 min read</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($featuredArticles as $article): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="article-card">
                        <div class="article-category">
                            <span class="badge bg-primary"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $article['category']))) ?></span>
                        </div>
                        <h5 class="article-title"><?= htmlspecialchars($article['title_en']) ?></h5>
                        <p class="article-summary"><?= htmlspecialchars(substr($article['summary_en'] ?? $article['content_en'], 0, 150)) ?>...</p>
                        <div class="article-footer">
                            <a href="modules/knowledge-hub/article.php?slug=<?= $article['slug'] ?>" class="btn btn-sm btn-outline-primary">
                                Read More <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- PARTNERS & ABOUT -->
<!-- ═══════════════════════════════════════════════════════ -->
<section class="about-section py-6">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <span class="section-badge">About the Platform</span>
                <h2 class="section-title">Developed by CRECO Kenya</h2>
                <p>The <strong>Constitution and Reform Education Consortium (CRECO Kenya)</strong> developed this platform to strengthen civil society organizations' understanding of their rights and obligations under the PBO Act 2013.</p>
                <p>This platform serves as a one-stop resource for PBOs across all 47 counties of Kenya, offering legal information, compliance tools, AI-powered guidance, and civic space monitoring capabilities.</p>
                
                <div class="row g-3 mt-3">
                    <div class="col-6">
                        <div class="stat-box">
                            <div class="stat-number">47</div>
                            <div class="stat-desc">Counties Covered</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-box">
                            <div class="stat-number">2</div>
                            <div class="stat-desc">Languages (EN & SW)</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-box">
                            <div class="stat-number">100%</div>
                            <div class="stat-desc">Free Access</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-box">
                            <div class="stat-number">256-bit</div>
                            <div class="stat-desc">SSL Encryption</div>
                        </div>
                    </div>
                </div>
                
                <a href="about.php" class="btn btn-primary mt-4">
                    <i class="fas fa-info-circle me-2"></i>Learn More About CRECO
                </a>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="about-visual p-4">
                    <div class="about-highlight-card">
                        <i class="fas fa-lock fa-3x text-primary mb-3"></i>
                        <h5>Data Protection Compliant</h5>
                        <p>All data handled in accordance with Kenya's Data Protection Act 2019</p>
                    </div>
                    <div class="about-highlight-card mt-3">
                        <i class="fas fa-universal-access fa-3x text-success mb-3"></i>
                        <h5>Accessible Design</h5>
                        <p>WCAG 2.1 compliant, optimized for low-bandwidth environments</p>
                    </div>
                    <div class="about-highlight-card mt-3">
                        <i class="fas fa-mobile-alt fa-3x text-info mb-3"></i>
                        <h5>Mobile First</h5>
                        <p>Fully responsive — works seamlessly on any device</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- NEWSLETTER / ALERTS -->
<!-- ═══════════════════════════════════════════════════════ -->
<section class="newsletter-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-6" data-aos="fade-up">
                <h3 class="fw-bold">Stay Updated on PBO Developments</h3>
                <p>Receive alerts on regulatory changes, compliance deadlines, and new resources.</p>
                <form class="newsletter-form" action="api/subscribe.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= (new Auth())->generateCSRF() ?>">
                    <div class="input-group">
                        <input type="email" name="email" class="form-control form-control-lg" placeholder="Enter your email address" required>
                        <button type="submit" class="btn btn-warning btn-lg px-4">
                            <i class="fas fa-bell me-2"></i>Subscribe
                        </button>
                    </div>
                    <small class="mt-2 d-block">No spam. Unsubscribe anytime. Privacy protected.</small>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- CHATBOT WIDGET (Global) -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="chatbot-widget" id="chatbotWidget">
    <div class="chatbot-popup" id="chatbotPopup" style="display:none;">
        <div class="chatbot-popup-header">
            <div class="d-flex align-items-center">
                <i class="fas fa-robot me-2"></i>
                <div>
                    <strong>PBO Legal Assistant</strong>
                    <small class="d-block">Powered by CRECO Kenya</small>
                </div>
            </div>
            <button onclick="toggleChatbot()" class="btn-close btn-close-white"></button>
        </div>
        <div class="chatbot-popup-body" id="widgetMessages">
            <div class="widget-msg bot">
                <p>Hello! Ask me anything about the PBO Act 2013.</p>
                <small class="disclaimer">⚠️ Not legal advice</small>
            </div>
        </div>
        <div class="chatbot-popup-input">
            <input type="text" id="widgetInput" placeholder="Type your question..." class="form-control">
            <button onclick="sendWidgetMessage()" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
        <div class="chatbot-lang-toggle">
            <button onclick="setWidgetLang('en')" id="langEn" class="btn btn-xs btn-primary active">EN</button>
            <button onclick="setWidgetLang('sw')" id="langSw" class="btn btn-xs btn-outline-secondary">SW</button>
        </div>
    </div>
    <button class="chatbot-fab" onclick="toggleChatbot()" title="Chat with PBO Assistant">
        <i class="fas fa-robot"></i>
        <span class="chatbot-badge">?</span>
    </button>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/chatbot.js"></script>
<script>
    AOS.init({ duration: 700, once: true });
    
    // Animate counters
    document.querySelectorAll('.stat-num[data-target]').forEach(el => {
        const target = parseInt(el.dataset.target);
        const duration = 2000;
        const increment = target / (duration / 16);
        let current = 0;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) { current = target; clearInterval(timer); }
            el.textContent = Math.floor(current);
        }, 16);
    });
    
    // Demo chat animation
    setTimeout(() => {
        const typing = document.getElementById('typingDemo');
        if (typing) {
            setTimeout(() => {
                typing.innerHTML = `<div class="msg-bubble">
                    <p>To register your organization as a PBO in Kenya, you need to: 
                    <strong>1)</strong> Prepare your organization's constitution, 
                    <strong>2)</strong> Gather founding member details, 
                    <strong>3)</strong> Complete Form PBO-1, 
                    <strong>4)</strong> Pay the registration fee, and 
                    <strong>5)</strong> Submit to the PBO Regulatory Authority.</p>
                    <small class="text-muted">Source: PBO Act 2013, Sections 10-25</small>
                </div>`;
            }, 2000);
        }
    }, 1000);
</script>
</body>
</html>