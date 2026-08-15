<?php
require_once __DIR__ . '/includes/config.php';

if (is_admin()) {
    redirect('admin/dashboard.php');
}

$page_title  = 'Staff Login';
$active_page = 'login';
$main_class  = 'auth-page';
include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
    <div class="auth-logo">
        <span class="logo-mark">EM</span>
        <h1>Staff <span class="grad-text">Login</span></h1>
        <p>Access the student management system and admin modules.</p>
    </div>

    <form class="form-card" action="process_login.php" method="post">
        <?= csrf_field() ?>
        <div class="field">
            <label for="username">Username</label>
            <input class="input" type="text" id="username" name="username" required autocomplete="username" placeholder="admin">
        </div>
        <div class="field" style="margin-top:16px">
            <label for="password">Password</label>
            <input class="input" type="password" id="password" name="password" required autocomplete="current-password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
        </div>
        <button class="btn btn-primary btn-block mt-2" type="submit"><i class="fa-solid fa-right-to-bracket"></i> Login</button>
        <p class="field-hint text-center mt-1" style="margin-bottom:0">
            Demo credentials: <code>admin</code> / <code>admin123</code>
        </p>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php';
