<?php
require_once __DIR__ . '/../config/auth.php';
$auth = new Auth();
$isLoggedIn = $auth->isAuthenticated();
$currentUser = $isLoggedIn ? $auth->currentUser() : null;
$csrf = $auth->generateCSRF();
$lang = $_SESSION['lang'] ?? 'en';
?>
<nav class="navbar navbar-expand-lg" role="navigation" aria-label="Main Navigation">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand" href="/">
            <div class="brand-logo" aria-hidden="true">P</div>
            <div class="brand-text">
                <strong>PBO Kenya</strong>
                <small>CRECO Kenya Platform</small>
            </div>
        </a>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler border-0" type="button" 
                data-bs-toggle="collapse" data-bs-target="#navbarMain"
                aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>" href="/">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                </li>
                
                <!-- Knowledge Hub Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-graduation-cap me-1"></i>Knowledge Hub
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/modules/knowledge-hub/">
                            <i class="fas fa-book text-primary me-2"></i>PBO Act Summaries
                        </a></li>
                        <li><a class="dropdown-item" href="/modules/knowledge-hub/?tab=kiswahili">
                            <i class="fas fa-language text-success me-2"></i>Kiswahili Content
                        </a></li>
                        <li><a class="dropdown-item" href="/modules/knowledge-hub/resources.php">
                            <i class="fas fa-download text-info me-2"></i>Downloads & Toolkits
                        </a></li>
                        <li><a class="dropdown-item" href="/modules/knowledge-hub/faqs.php">
                            <i class="fas fa-question-circle text-warning me-2"></i>FAQs
                        </a></li>
                        <li><a class="dropdown-item" href="/modules/knowledge-hub/multimedia.php">
                            <i class="fas fa-play-circle text-danger me-2"></i>Multimedia
                        </a></li>
                    </ul>
                </li>
                
                <!-- Compliance Tools -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-clipboard-check me-1"></i>Compliance
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/modules/compliance-tools/">
                            <i class="fas fa-tasks text-primary me-2"></i>Compliance Checklist
                        </a></li>
                        <li><a class="dropdown-item" href="/modules/compliance-tools/registration.php">
                            <i class="fas fa-registered text-success me-2"></i>Registration Guide
                        </a></li>
                        <li><a class="dropdown-item" href="/modules/compliance-tools/self-assessment.php">
                            <i class="fas fa-chart-bar text-info me-2"></i>Self-Assessment
                        </a></li>
                        <li><a class="dropdown-item" href="/modules/compliance-tools/templates.php">
                            <i class="fas fa-file-alt text-warning me-2"></i>Templates
                        </a></li>
                    </ul>
                </li>
                
                <!-- AI Assistant -->
                <li class="nav-item">
                    <a class="nav-link" href="/modules/chatbot/">
                        <i class="fas fa-robot me-1"></i>AI Assistant
                    </a>
                </li>
                
                <!-- Monitoring -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-shield-alt me-1"></i>Monitoring
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/modules/monitoring/">
                            <i class="fas fa-eye text-primary me-2"></i>Overview
                        </a></li>
                        <li><a class="dropdown-item" href="/modules/monitoring/report.php">
                            <i class="fas fa-file-alt text-success me-2"></i>Submit Report
                        </a></li>
                        <li><a class="dropdown-item" href="/modules/monitoring/incident.php">
                            <i class="fas fa-flag text-danger me-2"></i>Report Incident
                        </a></li>
                        <li><a class="dropdown-item" href="/modules/dashboard/">
                            <i class="fas fa-chart-pie text-info me-2"></i>Public Dashboard
                        </a></li>
                    </ul>
                </li>
                
                <!-- About -->
                <li class="nav-item">
                    <a class="nav-link" href="/about.php">
                        <i class="fas fa-info-circle me-1"></i>About
                    </a>
                </li>
            </ul>
            
            <!-- Right Side -->
            <div class="d-flex align-items-center gap-2">
                <!-- Language Switcher -->
                <div class="lang-switch">
                    <button onclick="switchLanguage('en')" 
                            class="btn btn-sm <?= $lang === 'en' ? 'btn-primary' : 'btn-outline-secondary' ?>"
                            title="Switch to English">EN</button>
                    <button onclick="switchLanguage('sw')" 
                            class="btn btn-sm <?= $lang === 'sw' ? 'btn-primary' : 'btn-outline-secondary' ?>"
                            title="Badilisha Kiswahili">SW</button>
                </div>
                
                <!-- Search -->
                <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#searchModal" title="Search">
                    <i class="fas fa-search"></i>
                </button>
                
                <?php if ($isLoggedIn && $currentUser): ?>
                <!-- User Menu -->
                <div class="dropdown">
                    <button class="btn btn-outline-primary btn-sm dropdown-toggle d-flex align-items-center gap-2" 
                            data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i>
                        <span class="d-none d-md-inline"><?= htmlspecialchars(explode(' ', $currentUser['full_name'])[0]) ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header">
                            <strong><?= htmlspecialchars($currentUser['full_name']) ?></strong>
                            <small class="d-block text-muted"><?= htmlspecialchars($currentUser['email']) ?></small>
                            <span class="badge bg-primary mt-1"><?= ucfirst($currentUser['role']) ?></span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/dashboard.php">
                            <i class="fas fa-tachometer-alt me-2 text-primary"></i>Dashboard
                        </a></li>
                        <li><a class="dropdown-item" href="/profile.php">
                            <i class="fas fa-user me-2 text-info"></i>Profile
                        </a></li>
                        <?php if ($auth->hasRole(['super_admin', 'admin', 'moderator'])): ?>
                        <li><a class="dropdown-item" href="/admin/">
                            <i class="fas fa-cog me-2 text-warning"></i>Admin Panel
                        </a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/auth/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                    </ul>
                </div>
                <?php else: ?>
                <a href="/auth/login.php" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-sign-in-alt me-1"></i>Login
                </a>
                <a href="/auth/register.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-user-plus me-1"></i>Register
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-label="Search">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title"><i class="fas fa-search me-2 text-primary"></i>Search the Platform</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="/search.php" method="GET">
                    <div class="input-group input-group-lg">
                        <input type="text" name="q" class="form-control" 
                               placeholder="Search articles, FAQs, resources, compliance tools..." 
                               autofocus required>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted me-2">Quick searches:</small>
                        <a href="/search.php?q=registration" class="badge bg-light text-dark text-decoration-none me-1">Registration</a>
                        <a href="/search.php?q=compliance" class="badge bg-light text-dark text-decoration-none me-1">Compliance</a>
                        <a href="/search.php?q=PBO+Act" class="badge bg-light text-dark text-decoration-none me-1">PBO Act</a>
                        <a href="/search.php?q=annual+returns" class="badge bg-light text-dark text-decoration-none me-1">Annual Returns</a>
                        <a href="/search.php?q=governance" class="badge bg-light text-dark text-decoration-none">Governance</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>