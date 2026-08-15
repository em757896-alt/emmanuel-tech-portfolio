<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';

require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    flash('error', 'Student not found.');
    redirect('admin/students.php');
}

$depts = $pdo->query('SELECT code, name FROM departments ORDER BY name')->fetchAll();

$page_title  = 'Edit Student';
$active_page = 'students';
include __DIR__ . '/includes/header.php';
?>
<section class="page-head">
    <div class="container">
        <p class="crumb"><a href="home.php">Home</a> &rsaquo; <a href="admin/students.php">Students</a> &rsaquo; Edit</p>
        <h1>Edit <span class="grad-text">Student</span></h1>
        <p class="lead">Update the records for <?= e($student['first_name']) ?> <?= e($student['last_name']) ?>.</p>
    </div>
</section>

<section class="section-sm">
    <div class="container" style="max-width:820px">
        <form class="form-card" action="process_edit.php" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $student['id'] ?>">
            <div class="form-grid">
                <div class="field">
                    <label for="first_name">First name <span class="req">*</span></label>
                    <input class="input" type="text" id="first_name" name="first_name" required value="<?= e($student['first_name']) ?>">
                </div>
                <div class="field">
                    <label for="last_name">Last name <span class="req">*</span></label>
                    <input class="input" type="text" id="last_name" name="last_name" required value="<?= e($student['last_name']) ?>">
                </div>
                <div class="field">
                    <label for="email">Email address <span class="req">*</span></label>
                    <input class="input" type="email" id="email" name="email" required value="<?= e($student['email']) ?>">
                </div>
                <div class="field">
                    <label for="phone">Phone <span class="req">*</span></label>
                    <input class="input" type="text" id="phone" name="phone" required value="<?= e($student['phone']) ?>">
                </div>
                <div class="field">
                    <label for="course">Course <span class="req">*</span></label>
                    <input class="input" type="text" id="course" name="course" required value="<?= e($student['course']) ?>">
                </div>
                <div class="field">
                    <label for="department">Department <span class="req">*</span></label>
                    <select class="input" id="department" name="department" required>
                        <?php foreach ($depts as $d): ?>
                            <option value="<?= e($d['code']) ?>" <?= $student['department'] === $d['code'] ? 'selected' : '' ?>><?= e($d['name']) ?> (<?= e($d['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex-between mt-2" style="align-items:center">
                <span class="field-hint">Changes will be reflected on the 3D campus and directory instantly.</span>
                <div style="display:flex; gap:10px">
                    <a class="btn btn-ghost" href="admin/students.php">Cancel</a>
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save changes</button>
                </div>
            </div>
        </form>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php';
