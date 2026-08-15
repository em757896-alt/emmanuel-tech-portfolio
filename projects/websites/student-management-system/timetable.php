<?php
require_once __DIR__ . '/includes/config.php';

$classroomId = isset($_GET['classroom']) ? (int) $_GET['classroom'] : 0;
$day = isset($_GET['day']) ? trim((string) $_GET['day']) : '';
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

$classrooms = $pdo->query('SELECT id, name, building FROM classrooms ORDER BY name')->fetchAll();

$sql = "SELECT tt.*, c.name AS classroom, c.building, d.code AS dept_code
        FROM timetables tt
        JOIN classrooms c ON c.id = tt.classroom_id
        LEFT JOIN departments d ON d.id = tt.department_id
        WHERE 1=1";
$params = [];
if ($classroomId) {
    $sql .= ' AND tt.classroom_id = ?';
    $params[] = $classroomId;
}
if (in_array($day, $days, true)) {
    $sql .= ' AND tt.day = ?';
    $params[] = $day;
}
$sql .= " ORDER BY FIELD(tt.day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), tt.start_time";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$page_title  = 'Timetable';
$active_page = 'timetable';
include __DIR__ . '/includes/header.php';
?>
<section class="page-head">
    <div class="container">
        <p class="crumb"><a href="home.php">Home</a> &rsaquo; Timetable</p>
        <h1>Weekly <span class="grad-text">Timetable</span></h1>
        <p class="lead" style="max-width:620px">Browse lectures and practical sessions by classroom or day. Timetables are managed by staff from the admin panel.</p>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="toolbar">
            <div class="tb-left">
                <span class="chip"><i class="fa-solid fa-calendar-days"></i> <?= count($rows) ?> session<?= count($rows) === 1 ? '' : 's' ?></span>
                <?php if ($classroomId || $day): ?>
                    <a class="btn btn-ghost btn-sm" href="timetable.php"><i class="fa-solid fa-rotate-left"></i> Reset filters</a>
                <?php endif; ?>
            </div>
            <form class="search-bar" method="get" action="timetable.php">
                <select class="input" name="classroom" onchange="this.form.submit()">
                    <option value="">All classrooms</option>
                    <?php foreach ($classrooms as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= $classroomId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?> &middot; <?= e($c['building']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="input" name="day" onchange="this.form.submit()">
                    <option value="">All days</option>
                    <?php foreach ($days as $d): ?>
                        <option value="<?= e($d) ?>" <?= $day === $d ? 'selected' : '' ?>><?= e($d) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="table-wrap reveal">
            <div class="table-scroll">
                <table class="tt-table">
                    <thead>
                        <tr><th>Day</th><th>Time</th><th>Course</th><th>Lecturer</th><th>Classroom</th><th>Department</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($rows): foreach ($rows as $r): ?>
                            <tr>
                                <td><span class="badge violet"><?= e(day_abbr($r['day'])) ?></span></td>
                                <td class="tt-time"><?= e(fmt_time($r['start_time'])) ?> &ndash; <?= e(fmt_time($r['end_time'])) ?></td>
                                <td class="tt-course"><?= e($r['course']) ?></td>
                                <td><?= e($r['lecturer']) ?></td>
                                <td><span class="tt-meta"><?= e($r['classroom']) ?> <i class="fa-solid fa-location-dot"></i> <?= e($r['building']) ?></span></td>
                                <td><span class="badge cyan"><?= e($r['dept_code']) ?: 'General' ?></span></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr class="table-empty"><td colspan="6">No sessions match your filters. <a href="timetable.php">Reset filters</a></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php';
