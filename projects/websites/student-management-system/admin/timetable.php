<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash('error', 'Invalid security token.');
        redirect('timetable.php');
    }
    $action = $_POST['action'] ?? '';

    if ($action === 'add_classroom') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $building = trim((string) ($_POST['building'] ?? ''));
        $departmentId = (int) ($_POST['department_id'] ?? 0);
        $capacity = max(1, (int) ($_POST['capacity'] ?? 30));
        $floor = max(0, (int) ($_POST['floor'] ?? 1));

        if ($name === '' || $building === '') {
            flash('error', 'Classroom name and building are required.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO classrooms (name, building, department_id, capacity, floor) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$name, $building, $departmentId ?: null, $capacity, $floor]);
            flash('success', 'Classroom "' . $name . '" added.');
        }
        redirect('timetable.php');
    }

    if ($action === 'add_session') {
        $classroomId = (int) ($_POST['classroom_id'] ?? 0);
        $departmentId = (int) ($_POST['department_id'] ?? 0);
        $day = trim((string) ($_POST['day'] ?? ''));
        $start = trim((string) ($_POST['start_time'] ?? ''));
        $end = trim((string) ($_POST['end_time'] ?? ''));
        $course = trim((string) ($_POST['course'] ?? ''));
        $lecturer = trim((string) ($_POST['lecturer'] ?? ''));

        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        if ($classroomId < 1 || !in_array($day, $days, true) || $start === '' || $end === '' || $course === '' || $lecturer === '') {
            flash('error', 'Please fill in all session details.');
        } elseif (strtotime($end) <= strtotime($start)) {
            flash('error', 'End time must be after the start time.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO timetables (classroom_id, department_id, day, start_time, end_time, course, lecturer) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$classroomId, $departmentId ?: null, $day, $start, $end, $course, $lecturer]);
            flash('success', 'Session "' . $course . '" added to the timetable.');
        }
        redirect('timetable.php');
    }

    if ($action === 'delete_session') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM timetables WHERE id = ?');
        $stmt->execute([$id]);
        flash('success', 'Session removed from the timetable.');
        redirect('timetable.php');
    }

    if ($action === 'delete_classroom') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM classrooms WHERE id = ?');
        $stmt->execute([$id]);
        flash('success', 'Classroom deleted (its sessions were removed too).');
        redirect('timetable.php');
    }
}

$classrooms = $pdo->query('SELECT c.*, d.name AS dept_name FROM classrooms c LEFT JOIN departments d ON d.id = c.department_id ORDER BY c.name')->fetchAll();
$departments = $pdo->query('SELECT id, code, name FROM departments ORDER BY name')->fetchAll();
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

$sessions = $pdo->query(
    "SELECT tt.*, c.name AS classroom, c.building, d.code AS dept_code
     FROM timetables tt
     JOIN classrooms c ON c.id = tt.classroom_id
     LEFT JOIN departments d ON d.id = tt.department_id
     ORDER BY FIELD(tt.day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), tt.start_time"
)->fetchAll();

$page_title  = 'Manage Schedules';
$active_page = 'schedules';
include __DIR__ . '/../includes/header.php';
?>
<section class="page-head">
    <div class="container">
        <p class="crumb"><a href="<?= $base_url ?>home.php">Home</a> &rsaquo; Admin &rsaquo; Schedules</p>
        <h1>Manage <span class="grad-text">Timetable</span></h1>
        <p class="text-dim" style="margin:0">Add classrooms and schedule weekly sessions. Changes publish instantly to the public timetable.</p>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="feature-row" style="grid-template-columns:1fr 1fr; margin-bottom:34px">
            <div class="card reveal">
                <h3><i class="fa-solid fa-door-open" style="color:var(--cyan); margin-right:8px"></i> Add classroom</h3>
                <form method="post" action="timetable.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add_classroom">
                    <div class="form-grid" style="grid-template-columns:1fr 1fr">
                        <div class="field"><label>Name</label><input class="input" type="text" name="name" required placeholder="ICT Lab 3"></div>
                        <div class="field"><label>Building</label><input class="input" type="text" name="building" required placeholder="ICT Building"></div>
                        <div class="field">
                            <label>Department</label>
                            <select class="input" name="department_id">
                                <option value="">General</option>
                                <?php foreach ($departments as $d): ?><option value="<?= (int) $d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-grid" style="grid-template-columns:1fr 1fr; gap:10px">
                            <div class="field"><label>Capacity</label><input class="input" type="number" name="capacity" min="1" value="30"></div>
                            <div class="field"><label>Floor</label><input class="input" type="number" name="floor" min="0" value="1"></div>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-2" type="submit"><i class="fa-solid fa-plus"></i> Add classroom</button>
                </form>
            </div>

            <div class="card reveal reveal-delay-1">
                <h3><i class="fa-solid fa-calendar-plus" style="color:var(--gold); margin-right:8px"></i> Schedule a session</h3>
                <form method="post" action="timetable.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add_session">
                    <div class="form-grid" style="grid-template-columns:1fr 1fr">
                        <div class="field">
                            <label>Classroom</label>
                            <select class="input" name="classroom_id" required>
                                <option value="">Select&hellip;</option>
                                <?php foreach ($classrooms as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label>Department</label>
                            <select class="input" name="department_id">
                                <option value="">General</option>
                                <?php foreach ($departments as $d): ?><option value="<?= (int) $d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label>Day</label>
                            <select class="input" name="day" required>
                                <?php foreach ($days as $day): ?><option value="<?= $day ?>"><?= $day ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-grid" style="grid-template-columns:1fr 1fr; gap:10px">
                            <div class="field"><label>Start</label><input class="input" type="time" name="start_time" required value="08:00"></div>
                            <div class="field"><label>End</label><input class="input" type="time" name="end_time" required value="10:00"></div>
                        </div>
                        <div class="field"><label>Course</label><input class="input" type="text" name="course" required placeholder="Web Development I"></div>
                        <div class="field"><label>Lecturer</label><input class="input" type="text" name="lecturer" required placeholder="Dr. Sarah Njeri"></div>
                    </div>
                    <button class="btn btn-gold mt-2" type="submit"><i class="fa-solid fa-plus"></i> Schedule session</button>
                </form>
            </div>
        </div>

        <h2 style="margin-bottom:18px">Classrooms</h2>
        <div class="card-grid" style="margin-bottom:34px">
            <?php if ($classrooms): foreach ($classrooms as $c): ?>
                <div class="card" style="padding:20px">
                    <div class="flex-between" style="align-items:flex-start">
                        <div>
                            <h3 style="font-size:16px; margin:0 0 4px"><?= e($c['name']) ?></h3>
                            <p class="text-dim" style="font-size:13px; margin:0"><?= e($c['building']) ?> &middot; Floor <?= (int) $c['floor'] ?> &middot; <?= (int) $c['capacity'] ?> seats</p>
                        </div>
                        <span class="badge violet"><?= e($c['dept_name'] ?: 'General') ?></span>
                    </div>
                    <form method="post" action="timetable.php" style="margin-top:12px">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_classroom">
                        <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                        <button class="btn btn-danger btn-sm" data-confirm="Delete this classroom and all its sessions?" type="submit"><i class="fa-solid fa-trash"></i> Delete</button>
                    </form>
                </div>
            <?php endforeach; else: ?>
                <p class="text-dim">No classrooms yet. Add one using the form above.</p>
            <?php endif; ?>
        </div>

        <h2 style="margin-bottom:18px">Weekly sessions (<?= count($sessions) ?>)</h2>
        <div class="table-wrap reveal">
            <div class="table-scroll">
                <table class="tt-table">
                    <thead><tr><th>Day</th><th>Time</th><th>Course</th><th>Lecturer</th><th>Classroom</th><th>Dept</th><th class="text-center">Action</th></tr></thead>
                    <tbody>
                        <?php if ($sessions): foreach ($sessions as $s): ?>
                            <tr>
                                <td><span class="badge violet"><?= e(day_abbr($s['day'])) ?></span></td>
                                <td class="tt-time"><?= e(fmt_time($s['start_time'])) ?> &ndash; <?= e(fmt_time($s['end_time'])) ?></td>
                                <td class="tt-course"><?= e($s['course']) ?></td>
                                <td><?= e($s['lecturer']) ?></td>
                                <td><span class="tt-meta"><?= e($s['classroom']) ?> &middot; <?= e($s['building']) ?></span></td>
                                <td><span class="badge cyan"><?= e($s['dept_code']) ?: 'GST' ?></span></td>
                                <td>
                                    <form method="post" action="timetable.php" style="margin:0">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_session">
                                        <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                                        <button class="btn btn-danger btn-sm" data-confirm="Remove this session?" type="submit"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr class="table-empty"><td colspan="7">No sessions scheduled yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php';
