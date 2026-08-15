<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';

/**
 * setup_admin.php — one-time setup
 * Creates or resets an admin account with a password you choose.
 * The file DELETES ITSELF after successful setup.
 *
 * Usage: open this page in your browser, fill the form, and it is gone.
 */

$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['confirm'] ?? '');

    if ($username === '' || strlen($password) < 8) {
        $error = 'Username is required and the password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('SELECT id FROM admins WHERE username = ?');
        $stmt->execute([$username]);
        if ($existing = $stmt->fetch()) {
            $upd = $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
            $upd->execute([$hash, $existing['id']]);
        } else {
            $ins = $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)');
            $ins->execute([$username, $hash]);
        }

        // Self-delete for security
        @unlink(__FILE__);
        $done = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Setup Admin &middot; Elevate Media College</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
    :root { --violet:#6d5df6; --cyan:#00d4ff; --text:#eaf0ff; --text-dim:#9aa7c7; --border:rgba(255,255,255,.1); --bg:#070b1d; }
    * { box-sizing:border-box; }
    body { margin:0; min-height:100vh; display:grid; place-items:center; font-family:'Inter',sans-serif; color:var(--text);
        background:radial-gradient(800px 500px at 70% 10%, rgba(109,93,246,.2), transparent 60%), radial-gradient(600px 400px at 10% 90%, rgba(0,212,255,.14), transparent 55%), var(--bg); }
    .card { width:min(440px,92vw); background:rgba(255,255,255,.05); border:1px solid var(--border); border-radius:18px; padding:34px; }
    .logo { display:grid; place-items:center; width:56px; height:56px; border-radius:16px; background:linear-gradient(135deg,var(--violet),var(--cyan)); font-family:'Space Grotesk'; font-weight:700; font-size:20px; margin:0 auto 16px; }
    h1 { font-family:'Space Grotesk'; text-align:center; font-size:22px; margin:0 0 4px; }
    p.sub { text-align:center; color:var(--text-dim); font-size:14px; margin:0 0 24px; }
    label { display:block; font-size:13px; font-weight:600; color:var(--text-dim); margin:14px 0 6px; }
    input { width:100%; padding:12px 14px; background:rgba(7,11,29,.55); color:var(--text); border:1px solid var(--border); border-radius:11px; font-family:inherit; font-size:14.5px; }
    input:focus { outline:none; border-color:var(--violet); }
    button { width:100%; margin-top:22px; padding:13px; border:none; border-radius:12px; cursor:pointer; font-family:'Space Grotesk'; font-weight:600; font-size:15px; color:#fff; background:linear-gradient(135deg,var(--violet),var(--cyan)); }
    .err { background:rgba(255,92,122,.1); border:1px solid rgba(255,92,122,.4); color:#ffb1c0; border-radius:11px; padding:11px 14px; font-size:14px; margin-bottom:6px; }
    .ok { text-align:center; }
    .ok i { font-size:44px; color:#2dd4a7; margin-bottom:12px; }
</style>
</head>
<body>
    <div class="card">
        <?php if ($done): ?>
            <div class="ok">
                <i class="fa-solid fa-circle-check"></i>
                <h1>All set!</h1>
                <p class="sub">Your admin account has been created and this setup page has been removed for security.</p>
                <a href="login.php" style="display:inline-block; margin-top:10px; color:var(--cyan); font-weight:600">Go to login &rsaquo;</a>
            </div>
        <?php else: ?>
            <div class="logo">EM</div>
            <h1>Create admin account</h1>
            <p class="sub">Run this once to set up staff login. The page deletes itself afterwards.</p>
            <?php if (isset($error)): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <form method="post" action="setup_admin.php">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="off" value="<?= isset($username) ? htmlspecialchars($username) : 'admin' ?>">
                <label for="password">Password (min. 8 characters)</label>
                <input type="password" id="password" name="password" required autocomplete="new-password">
                <label for="confirm">Confirm password</label>
                <input type="password" id="confirm" name="confirm" required autocomplete="new-password">
                <button type="submit">Create admin account</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
