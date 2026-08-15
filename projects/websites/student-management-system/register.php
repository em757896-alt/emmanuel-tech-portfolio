<?php
require_once __DIR__ . '/includes/config.php';

$depts = $pdo->query('SELECT code, name FROM departments ORDER BY name')->fetchAll();
$palette = ['#6d5df6', '#00d4ff', '#ffc93c', '#2dd4a7', '#ff5c7a', '#8b5cf6', '#f59e0b', '#06b6d4', '#6366f1'];

$page_title  = 'Register Student';
$active_page = 'register';
include __DIR__ . '/includes/header.php';
?>
<section class="page-head">
    <div class="container">
        <p class="crumb"><a href="home.php">Home</a> &rsaquo; Register Student</p>
        <h1>Register a <span class="grad-text">Student</span></h1>
        <p class="lead" style="max-width:620px">Add a new student to the college records. Their avatar is generated automatically and appears on the 3D student plaza.</p>
    </div>
</section>

<section class="section-sm">
    <div class="container" style="max-width:820px">
        <form class="form-card" action="process_register.php" method="post">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="field">
                    <label for="first_name">First name <span class="req">*</span></label>
                    <input class="input" type="text" id="first_name" name="first_name" required placeholder="Jane">
                </div>
                <div class="field">
                    <label for="last_name">Last name <span class="req">*</span></label>
                    <input class="input" type="text" id="last_name" name="last_name" required placeholder="Doe">
                </div>
                <div class="field">
                    <label for="email">Email address <span class="req">*</span></label>
                    <input class="input" type="email" id="email" name="email" required placeholder="jane.doe@example.com">
                </div>
                <div class="field">
                    <label for="phone">Phone <span class="req">*</span></label>
                    <input class="input" type="text" id="phone" name="phone" required placeholder="0712 345 678">
                </div>
                <div class="field">
                    <label for="course">Course <span class="req">*</span></label>
                    <input class="input" type="text" id="course" name="course" required placeholder="Software Engineering">
                </div>
                <div class="field">
                    <label for="department">Department <span class="req">*</span></label>
                    <select class="input" id="department" name="department" required>
                        <option value="">Select department&hellip;</option>
                        <?php foreach ($depts as $d): ?>
                            <option value="<?= e($d['code']) ?>"><?= e($d['name']) ?> (<?= e($d['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field full">
                    <label for="avatar_color">Avatar colour</label>
                    <select class="input" id="avatar_color" name="avatar_color">
                        <?php foreach ($palette as $c): ?>
                            <option value="<?= e($c) ?>"><?= e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="field-hint">Used for the generated initials avatar in the student directory and 3D plaza.</span>
                </div>
            </div>
            <button class="btn btn-primary btn-block mt-2" type="submit"><i class="fa-solid fa-user-plus"></i> Register student</button>
        </form>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php';
