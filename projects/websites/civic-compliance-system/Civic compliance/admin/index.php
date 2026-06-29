<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/auth.php';

$auth = new Auth();

if ($auth->isAuthenticated() && $auth->hasRole(['super_admin','admin','moderator'])) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = 'Admin Login - PBO Kenya';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if ($auth->login($email, $password)) {
        if ($auth->hasRole(['super_admin','admin','moderator'])) {
            header('Location: dashboard.php');
            exit;
        }
        $auth->logout();
        $error = 'You do not have admin access.';
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="background:#f1f5f9;display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:'Inter',sans-serif">

    <div style="width:100%;max-width:420px;padding:20px">
        <div style="text-align:center;margin-bottom:32px">
            <div style="width:56px;height:56px;background:#1a3c5e;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px">
                <i class="fas fa-balance-scale" style="color:#60a5fa;font-size:1.5rem"></i>
            </div>
            <h1 style="font-size:1.3rem;font-weight:700;color:#1a3c5e;margin-bottom:4px">Admin Portal</h1>
            <p style="font-size:0.85rem;color:#6b7280">PBO Kenya Platform | CRECO Kenya</p>
        </div>

        <div style="background:#fff;border-radius:16px;padding:32px;box-shadow:0 4px 24px rgba(0,0,0,0.06)">
            <?php if ($error): ?>
            <div style="background:#fef2f2;color:#991b1b;padding:12px 16px;border-radius:10px;font-size:0.83rem;margin-bottom:20px;display:flex;align-items:center;gap:8px">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div style="margin-bottom:18px">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Email Address</label>
                    <input type="email" name="email" required placeholder="admin@example.com"
                           style="width:100%;padding:11px 14px;border:1.5px solid #d1d5db;border-radius:10px;font-size:0.9rem;transition:border-color 0.2s"
                           onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                </div>
                <div style="margin-bottom:24px">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Password</label>
                    <input type="password" name="password" required
                           style="width:100%;padding:11px 14px;border:1.5px solid #d1d5db;border-radius:10px;font-size:0.9rem;transition:border-color 0.2s"
                           onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                </div>
                <button type="submit" style="width:100%;padding:12px;background:#1a3c5e;color:#fff;border:none;border-radius:10px;font-size:0.95rem;font-weight:600;cursor:pointer;transition:background 0.2s"
                        onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#1a3c5e'">
                    <i class="fas fa-lock me-2"></i>Sign In
                </button>
            </form>

            <div style="text-align:center;margin-top:20px">
                <a href="/auth/login.php" style="color:#6b7280;font-size:0.82rem;text-decoration:none">
                    <i class="fas fa-arrow-left me-1"></i>Back to User Login
                </a>
            </div>
        </div>

        <div style="text-align:center;margin-top:24px;font-size:0.75rem;color:#9ca3af">
            &copy; <?php echo date('Y'); ?> PBO Kenya Platform by CRECO Kenya
        </div>
    </div>

</body>
</html>
