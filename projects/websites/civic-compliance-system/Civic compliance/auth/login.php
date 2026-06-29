<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

$auth = new Auth();
$error = '';
$success = '';

if ($auth->isAuthenticated()) {
    header('Location: /dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $result = $auth->login($email, $password, $remember);
        if ($result['success']) {
            session_write_close();
            if (!empty($result['redirect_url'])) {
                header('Location: ' . $result['redirect_url']);
            } else {
                header('Location: /dashboard.php');
            }
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = 'Sign In - PBO Kenya Platform';
$currentPage = 'login';
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
    <div class="auth-layout">
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
                    <h1>Welcome Back</h1>
                    <p>Sign in to access your compliance tools, monitoring reports, and legal resources.</p>
                </div>
                <div class="panel-features">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-clipboard-check"></i></div>
                        <div>
                            <strong>Track Compliance</strong>
                            <span>Monitor your organization's PBO Act compliance status</span>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                        <div>
                            <strong>Submit Reports</strong>
                            <span>File civic space monitoring reports and incident alerts</span>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-robot"></i></div>
                        <div>
                            <strong>AI Legal Assistant</strong>
                            <span>Get instant answers about the PBO Act and regulations</span>
                        </div>
                    </div>
                </div>
                <div class="panel-footer-note">
                    <i class="fas fa-shield-alt"></i>
                    <span>Your data is encrypted and protected</span>
                </div>
            </div>
        </div>
        <div class="auth-panel-right">
            <div class="auth-form-wrap">
                <div class="auth-form-header">
                    <h2>Sign In</h2>
                    <p>Enter your credentials to continue</p>
                </div>

                <?php if ($error): ?>
                <div class="auth-alert auth-alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="auth-alert auth-alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="auth-form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <input type="email" id="email" name="email"
                                   placeholder="your@email.com"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   required autofocus>
                            <i class="fas fa-envelope input-icon"></i>
                        </div>
                    </div>

                    <div class="auth-form-group">
                        <div class="label-row">
                            <label for="password">Password</label>
                        </div>
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password"
                                   placeholder="Enter your password" required>
                            <i class="fas fa-lock input-icon"></i>
                            <button type="button" class="password-toggle"
                                    onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="auth-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span class="check-box"></span>
                            <span>Remember me for 7 days</span>
                        </label>
                    </div>

                    <button type="submit" class="auth-submit-btn">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>

                <div class="auth-divider"><span>or</span></div>

                <div class="auth-switch">
                    <p>Don't have an account? <a href="register.php">Create Account</a></p>
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
    </script>
</body>
</html>
