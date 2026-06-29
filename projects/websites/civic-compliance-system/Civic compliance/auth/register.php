<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

$auth = new Auth();
$error = '';

if ($auth->isAuthenticated()) {
    header('Location: /dashboard.php');
    exit;
}

$counties = KENYA_COUNTIES;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $auth->register($_POST);
    if ($result['success']) {
        $_SESSION['welcome_new'] = true;
        session_write_close();
        $redirect = !empty($result['redirect_url']) ? $result['redirect_url'] : '/dashboard.php';
        header('Location: ' . $redirect);
        exit;
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'Create Account - PBO Kenya Platform';
$currentPage = 'register';
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
    <link href="../assets/css/auth.css" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-layout auth-layout-register">
        <div class="auth-panel-left">
            <div class="panel-content">
                <a href="../index.php" class="auth-brand">
                    <div class="brand-logo-icon">P</div>
                    <div>
                        <span class="brand-name-text">PBO Kenya Platform</span>
                        <span class="brand-tagline">Compliance &amp; Awareness</span>
                    </div>
                </a>
                <div class="panel-hero">
                    <h1>Join the Platform</h1>
                    <p>Create your free account to access compliance tools, legal resources, and monitoring features.</p>
                </div>
                <div class="panel-benefits">
                    <h3>What you get</h3>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Free registration — no hidden fees</li>
                        <li><i class="fas fa-check-circle"></i> Access all knowledge &amp; compliance modules</li>
                        <li><i class="fas fa-check-circle"></i> Submit civic space monitoring reports</li>
                        <li><i class="fas fa-check-circle"></i> Track your organization's compliance status</li>
                        <li><i class="fas fa-check-circle"></i> AI-powered legal assistant (PBO Act)</li>
                    </ul>
                </div>
                <div class="panel-testimonial">
                    <blockquote>
                        This platform has made it significantly easier for our organization to understand and comply with the PBO Act.
                    </blockquote>
                    <cite>— Civil Society Organization, Nairobi</cite>
                </div>
            </div>
        </div>
        <div class="auth-panel-right">
            <div class="auth-form-wrap">
                <div class="auth-form-header">
                    <h2>Create Account</h2>
                    <p>Fill in your details to get started</p>
                </div>

                <?php if ($error): ?>
                <div class="auth-alert auth-alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-grid-2">
                        <div class="auth-form-group" style="grid-column:1/-1">
                            <label for="full_name">Full Name <span class="req">*</span></label>
                            <div class="input-wrapper">
                                <input type="text" id="full_name" name="full_name"
                                       placeholder="Your full name"
                                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                                <i class="fas fa-user input-icon"></i>
                            </div>
                        </div>
                        <div class="auth-form-group">
                            <label for="email">Email Address <span class="req">*</span></label>
                            <div class="input-wrapper">
                                <input type="email" id="email" name="email"
                                       placeholder="your@email.com"
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                <i class="fas fa-envelope input-icon"></i>
                            </div>
                        </div>
                        <div class="auth-form-group">
                            <label for="phone">Phone <span style="color:#9ca3af">(optional)</span></label>
                            <div class="input-wrapper">
                                <input type="tel" id="phone" name="phone"
                                       placeholder="+254 700 000 000"
                                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                                <i class="fas fa-phone input-icon"></i>
                            </div>
                        </div>
                        <div class="auth-form-group">
                            <label for="organization_name">Organization <span style="color:#9ca3af">(optional)</span></label>
                            <div class="input-wrapper">
                                <input type="text" id="organization_name" name="organization_name"
                                       placeholder="Your organization name"
                                       value="<?= htmlspecialchars($_POST['organization_name'] ?? '') ?>">
                                <i class="fas fa-building input-icon"></i>
                            </div>
                        </div>
                        <div class="auth-form-group">
                            <label for="county">County</label>
                            <div class="input-wrapper">
                                <select id="county" name="county">
                                    <option value="">Select County</option>
                                    <?php foreach ($counties as $c): ?>
                                    <option value="<?= htmlspecialchars($c) ?>"
                                        <?= ($_POST['county'] ?? '') === $c ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-map-marker-alt input-icon"></i>
                            </div>
                        </div>
                        <div class="auth-form-group">
                            <label for="password">Password <span class="req">*</span></label>
                            <div class="input-wrapper">
                                <input type="password" id="password" name="password"
                                       placeholder="Min. 8 characters" required>
                                <i class="fas fa-lock input-icon"></i>
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordStrength" class="match-indicator"></div>
                        </div>
                        <div class="auth-form-group">
                            <label for="confirm_password">Confirm Password <span class="req">*</span></label>
                            <div class="input-wrapper">
                                <input type="password" id="confirm_password" name="confirm_password"
                                       placeholder="Repeat password" required>
                                <i class="fas fa-lock input-icon"></i>
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordMatch" class="match-indicator"></div>
                        </div>
                    </div>

                    <div class="auth-options">
                        <label class="remember-me">
                            <input type="checkbox" name="consent" value="1" required>
                            <span class="check-box"></span>
                            <span>I agree to the <a href="/privacy.php" target="_blank">Privacy Policy</a> and <a href="/terms.php" target="_blank">Terms of Use</a> <span class="req">*</span></span>
                        </label>
                    </div>

                    <button type="submit" class="auth-submit-btn" id="registerBtn">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>

                <div class="auth-divider"><span>or</span></div>

                <div class="auth-switch">
                    <p>Already have an account? <a href="login.php">Sign In</a></p>
                </div>
            </div>
            <a href="../index.php" class="back-to-site"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </div>
    </div>

    <script>
    function togglePassword(id) {
        const btn = event.currentTarget;
        const input = document.getElementById(id);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    document.getElementById('password')?.addEventListener('input', function() {
        const val = this.value;
        const el = document.getElementById('passwordStrength');
        if (val.length < 8) {
            el.innerHTML = '<i class="fas fa-times-circle" style="color:#ef4444"></i> Weak — minimum 8 characters';
            el.style.color = '#ef4444';
        } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/.test(val)) {
            el.innerHTML = '<i class="fas fa-exclamation-circle" style="color:#f59e0b"></i> Medium — add uppercase, number & special char';
            el.style.color = '#f59e0b';
        } else {
            el.innerHTML = '<i class="fas fa-check-circle" style="color:#10b981"></i> Strong password';
            el.style.color = '#10b981';
        }
        checkPasswordsMatch();
    });

    document.getElementById('confirm_password')?.addEventListener('input', checkPasswordsMatch);

    function checkPasswordsMatch() {
        const pw = document.getElementById('password')?.value || '';
        const cpw = document.getElementById('confirm_password')?.value || '';
        const el = document.getElementById('passwordMatch');
        if (!cpw) { el.innerHTML = ''; return; }
        if (pw === cpw) {
            el.innerHTML = '<i class="fas fa-check-circle" style="color:#10b981"></i> Passwords match';
            el.style.color = '#10b981';
        } else {
            el.innerHTML = '<i class="fas fa-times-circle" style="color:#ef4444"></i> Passwords do not match';
            el.style.color = '#ef4444';
        }
    }
    </script>
</body>
</html>
