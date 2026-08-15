<?php
require_once __DIR__ . '/includes/config.php';

$code = isset($_GET['code']) ? trim((string) $_GET['code']) : '';

$stmt = $pdo->prepare(
    "SELECT d.*, COUNT(s.id) AS student_count
     FROM departments d
     LEFT JOIN students s ON s.department = d.code
     WHERE d.code = ?
     GROUP BY d.id"
);
$stmt->execute([$code]);
$dept = $stmt->fetch();

if (!$dept) {
    flash('error', 'Department not found.');
    redirect('departments.php');
}

$stmt = $pdo->prepare('SELECT * FROM classrooms WHERE department_id = ? ORDER BY floor, name');
$stmt->execute([$dept['id']]);
$classrooms = $stmt->fetchAll();

$stmt = $pdo->prepare(
    "SELECT tt.*, c.name AS classroom
     FROM timetables tt
     JOIN classrooms c ON c.id = tt.classroom_id
     WHERE tt.department_id = ?
     ORDER BY FIELD(tt.day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), tt.start_time"
);
$stmt->execute([$dept['id']]);
$tts = $stmt->fetchAll();

$page_title  = $dept['name'];
$active_page = 'departments';
include __DIR__ . '/includes/header.php';
?>
<section class="page-head">
    <div class="container">
        <p class="crumb"><a href="home.php">Home</a> &rsaquo; <a href="departments.php">Departments</a> &rsaquo; <?= e($dept['code']) ?></p>
        <h1 style="display:flex; align-items:center; gap:16px; flex-wrap:wrap">
            <span class="logo-mark" style="background:<?= e($dept['color']) ?>; box-shadow:0 6px 18px <?= e($dept['color']) ?>66"><?= e($dept['code']) ?></span>
            <?= e($dept['name']) ?>
        </h1>
        <p class="lead" style="max-width:680px"><?= e($dept['description']) ?></p>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr))">
            <div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div><div class="stat-num" data-count="<?= (int) $dept['student_count'] ?>">0</div><div class="stat-label">Students</div></div>
            <div class="stat-card dark-gold"><div class="stat-icon"><i class="fa-solid fa-door-open"></i></div><div class="stat-num" data-count="<?= count($classrooms) ?>">0</div><div class="stat-label">Classrooms</div></div>
            <div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-calendar-days"></i></div><div class="stat-num" data-count="<?= count($tts) ?>">0</div><div class="stat-label">Weekly Sessions</div></div>
            <div class="stat-card dark-gold"><div class="stat-icon"><i class="fa-solid fa-user-tie"></i></div><div class="stat-num"><?= e($dept['head']) ?></div><div class="stat-label">Head of Department</div></div>
        </div>

        <div class="flex-between mt-3" style="margin-bottom:18px">
            <h2 style="margin:0">Classrooms</h2>
            <span class="badge cyan"><i class="fa-solid fa-location-dot"></i> <?= e($dept['building']) ?></span>
        </div>
        <div class="card-grid">
            <?php if ($classrooms): foreach ($classrooms as $c): ?>
                <div class="card">
                    <div class="card-icon"><i class="fa-solid fa-door-open"></i></div>
                    <h3><?= e($c['name']) ?></h3>
                    <p><?= e($c['building']) ?> &middot; Floor <?= (int) $c['floor'] ?></p>
                    <span class="chip"><i class="fa-solid fa-users"></i> Capacity <?= (int) $c['capacity'] ?></span>
                </div>
            <?php endforeach; else: ?>
                <p class="text-dim">No dedicated classrooms listed for this department yet.</p>
            <?php endif; ?>
        </div>

        <h2 class="mt-3" style="margin-bottom:18px">Weekly timetable</h2>
        <div class="table-wrap reveal">
            <div class="table-scroll">
                <table class="tt-table">
                    <thead>
                        <tr><th>Day</th><th>Time</th><th>Course</th><th>Lecturer</th><th>Classroom</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($tts): foreach ($tts as $t): ?>
                            <tr>
                                <td><span class="badge violet"><?= e(day_abbr($t['day'])) ?></span></td>
                                <td class="tt-time"><?= e(fmt_time($t['start_time'])) ?> &ndash; <?= e(fmt_time($t['end_time'])) ?></td>
                                <td class="tt-course"><?= e($t['course']) ?></td>
                                <td><span class="tt-meta"><?= e($t['lecturer']) ?></span></td>
                                <td><span class="tt-meta"><?= e($t['classroom']) ?></span></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr class="table-empty"><td colspan="5">No timetabled sessions for this department.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card reveal mt-3" style="display:flex; gap:14px; align-items:center; justify-content:space-between; flex-wrap:wrap">
            <div>
                <h3 style="margin:0 0 2px">Visit the <?= e($dept['name']) ?> on the 3D campus</h3>
                <p class="text-dim" style="margin:0">Click the <?= e($dept['building']) ?> in the campus scene.</p>
            </div>
            <a class="btn btn-primary" href="campus.php"><i class="fa-solid fa-cubes"></i> Open 3D Campus</a>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php';
