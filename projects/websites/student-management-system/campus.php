<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';

// ---- Build the scene data (injected as JSON) ----
$isAdmin = is_admin();

$depts = $pdo->query('SELECT code, name, head, building, color FROM departments')->fetchAll();
$deptByCode = [];
foreach ($depts as $d) {
    $deptByCode[$d['code']] = $d;
}

$buildings = [
    [
        'key'  => 'admin', 'label' => 'Main Block', 'sub' => 'Administration & Records',
        'color' => '#ff5c7a', 'x' => -9.5, 'z' => 7, 'w' => 4.6, 'h' => 2.3, 'd' => 2.9,
        'desc' => 'College administration, registrar and central records. Staff sign in here to manage students, timetables, the library and departments.',
        'actions' => $isAdmin
            ? [['label' => 'Open dashboard', 'href' => 'admin/dashboard.php', 'icon' => 'fa-gauge-high'],
               ['label' => 'Manage students', 'href' => 'admin/students.php', 'icon' => 'fa-users']]
            : [['label' => 'Staff login', 'href' => 'login.php', 'icon' => 'fa-user-lock']],
    ],
    [
        'key'  => 'ict', 'label' => 'ICT Building', 'sub' => 'ICT & Computer Science',
        'color' => '#6d5df6', 'x' => -9.5, 'z' => -7, 'w' => 4.2, 'h' => 2.1, 'd' => 2.7,
        'desc' => 'Software engineering, networking and cyber security programmes taught in modern computer labs.',
        'actions' => [['label' => 'Open department', 'href' => 'department.php?code=ICT', 'icon' => 'fa-building-columns'],
                      ['label' => 'View timetable', 'href' => 'timetable.php?day=', 'icon' => 'fa-calendar-days']],
    ],
    [
        'key'  => 'media', 'label' => 'Media Studio', 'sub' => 'Media & Communication',
        'color' => '#00d4ff', 'x' => 0, 'z' => -11, 'w' => 4.6, 'h' => 2.2, 'd' => 2.8,
        'desc' => 'Journalism, film production, broadcast and digital content creation with real studio experience.',
        'actions' => [['label' => 'Open department', 'href' => 'department.php?code=MCM', 'icon' => 'fa-building-columns'],
                      ['label' => 'View timetable', 'href' => 'timetable.php?day=', 'icon' => 'fa-calendar-days']],
    ],
    [
        'key'  => 'business', 'label' => 'Business School', 'sub' => 'Business & Management',
        'color' => '#ffc93c', 'x' => 9.5, 'z' => -7, 'w' => 4.2, 'h' => 2.1, 'd' => 2.7,
        'desc' => 'Entrepreneurship, accounting, marketing and business leadership focused on real-world ventures.',
        'actions' => [['label' => 'Open department', 'href' => 'department.php?code=BMS', 'icon' => 'fa-building-columns'],
                      ['label' => 'View timetable', 'href' => 'timetable.php?day=', 'icon' => 'fa-calendar-days']],
    ],
    [
        'key'  => 'engineering', 'label' => 'Engineering Block', 'sub' => 'Engineering & Technology',
        'color' => '#2dd4a7', 'x' => 9.5, 'z' => 7, 'w' => 4.2, 'h' => 2.1, 'd' => 2.7,
        'desc' => 'Electrical, mechanical and civil engineering technology with hands-on laboratory projects.',
        'actions' => [['label' => 'Open department', 'href' => 'department.php?code=ENG', 'icon' => 'fa-building-columns'],
                      ['label' => 'View timetable', 'href' => 'timetable.php?day=', 'icon' => 'fa-calendar-days']],
    ],
    [
        'key'  => 'library', 'label' => 'Library', 'sub' => 'Library & Study Spaces',
        'color' => '#f59e0b', 'x' => 0, 'z' => 9, 'w' => 4.6, 'h' => 2.2, 'd' => 3.1,
        'desc' => 'A growing catalog of books across technology, media, business, literature and engineering — plus quiet study spaces.',
        'actions' => [['label' => 'Open library', 'href' => 'library.php', 'icon' => 'fa-book-open']],
    ],
];

// Map departments onto buildings for extra panel metadata
foreach ($buildings as &$b) {
    foreach ($depts as $d) {
        if ($d['building'] === $b['label'] || $d['building'] === $b['label'] . '') {
            if ($d['building'] === $b['label']) {
                $b['dept'] = ['code' => $d['code'], 'name' => $d['name'], 'head' => $d['head'], 'established' => date('Y', mktime(0, 0, 0, 1, 1, (int) $d['established']))];
            }
        }
    }
}
unset($b);

// Classrooms with 3D positions around their building
$classrooms = $pdo->query('SELECT c.*, d.code AS dept_code FROM classrooms c LEFT JOIN departments d ON d.id = c.department_id ORDER BY c.building, c.name')->fetchAll();

$slotOffsets = [
    'ICT Building'       => [[2.1, -1.5], [-2.0, 1.6], [2.1, 1.6]],
    'Media Studio'       => [[2.4, -1.2], [-2.2, 1.4]],
    'Business School'    => [[2.1, -1.5], [-2.0, 1.6]],
    'Engineering Block'  => [[2.1, 1.5], [-2.0, -1.6]],
    'Main Block'         => [[-2.0, 1.6], [2.1, -1.5]],
];

$buildingPos = [];
foreach ($buildings as $b) {
    $buildingPos[$b['label']] = ['x' => $b['x'], 'z' => $b['z'], 'color' => $b['color']];
}

$campusClassrooms = [];
foreach ($classrooms as $i => $c) {
    $base = $buildingPos[$c['building']] ?? ['x' => 0, 'z' => 0, 'color' => '#6d5df6'];
    $slots = $slotOffsets[$c['building']] ?? [[1.8, 1.4], [-1.6, -1.4]];
    $slot = $slots[$i % count($slots)];
    $campusClassrooms[] = [
        'id' => (int) $c['id'],
        'name' => $c['name'],
        'building' => $c['building'],
        'dept_code' => $c['dept_code'] ?: 'GST',
        'floor' => (int) $c['floor'],
        'capacity' => (int) $c['capacity'],
        'x' => $base['x'] + $slot[0],
        'z' => $base['z'] + $slot[1],
        'color' => $base['color'],
    ];
}

// Timetable grouped by classroom
$sessions = $pdo->query('SELECT classroom_id, day, start_time, end_time, course, lecturer FROM timetables')->fetchAll();
$ttByClassroom = [];
foreach ($sessions as $s) {
    $cid = (int) $s['classroom_id'];
    if (!isset($ttByClassroom[$cid])) {
        $ttByClassroom[$cid] = [];
    }
    $ttByClassroom[$cid][] = [
        'day' => day_abbr($s['day']), 'start' => fmt_time($s['start_time']), 'end' => fmt_time($s['end_time']),
        'course' => $s['course'], 'lecturer' => $s['lecturer'],
    ];
}

// Students for the plaza
$students = $pdo->query('SELECT id, first_name, last_name, email, phone, course, department, avatar_color FROM students ORDER BY last_name LIMIT 60')->fetchAll();
$campusStudents = [];
foreach ($students as $s) {
    $campusStudents[] = [
        'id' => (int) $s['id'],
        'name' => $s['first_name'] . ' ' . $s['last_name'],
        'initials' => avatar_initials($s['first_name'], $s['last_name']),
        'color' => $s['avatar_color'],
        'course' => $s['course'],
        'dept' => $s['department'],
        'email' => $s['email'],
        'phone' => $s['phone'],
    ];
}

$campusData = [
    'buildings'  => $buildings,
    'classrooms' => $campusClassrooms,
    'timetable'  => $ttByClassroom,
    'students'   => $campusStudents,
    'legend'     => [
        ['color' => '#6d5df6', 'label' => 'Academic building'],
        ['color' => '#f59e0b', 'label' => 'Library'],
        ['color' => '#ff5c7a', 'label' => 'Administration'],
        ['color' => '#00d4ff', 'label' => 'Classroom'],
        ['color' => '#2dd4a7', 'label' => 'Student plaza'],
    ],
];

$page_title  = '3D Campus';
$active_page = 'campus';
$main_class  = 'campus-page';
include __DIR__ . '/includes/header.php';
?>
<section class="campus-hero">
    <div class="container">
        <p class="crumb"><a href="home.php">Home</a> &rsaquo; 3D Campus</p>
        <div class="flex-between" style="align-items:flex-end; margin-bottom:22px">
            <div>
                <h1 style="margin-bottom:6px">Interactive <span class="grad-text">3D Campus</span></h1>
                <p class="text-dim" style="margin:0; max-width:560px">
                    Drag to rotate, scroll or pinch to zoom. Click a building to open its department,
                    a classroom to see its timetable, or an avatar on the plaza to view a student profile.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section-sm" style="padding-top:8px">
    <div class="container">
        <div class="campus-stage-wrap">
            <div class="campus-stage">
                <div id="campus3d"></div>

                <div class="campus-loading" id="campusLoading">
                    <div>
                        <div class="spinner"></div>
                        <strong>Loading campus&hellip;</strong>
                        <small>Building your 3D experience</small>
                    </div>
                </div>

                <div class="hint" aria-hidden="true">
                    <span><i class="fa-solid fa-rotate"></i> Drag to rotate</span>
                    <span><i class="fa-solid fa-magnifying-glass-plus"></i> Scroll / pinch to zoom</span>
                    <span><i class="fa-solid fa-mouse-pointer"></i> Click to explore</span>
                </div>

                <!-- 3D info panel (populated by JS) -->
                <aside class="campus-panel" id="campusPanel" aria-live="polite">
                    <button class="cp-close" data-close-panel aria-label="Close panel"><i class="fa-solid fa-xmark"></i></button>
                    <div class="cp-title" id="cpTitle"></div>
                    <div class="cp-tag" id="cpTag"></div>
                    <p class="cp-desc" id="cpDesc"></p>
                    <div class="cp-meta" id="cpMeta"></div>
                    <div class="cp-actions" id="cpActions"></div>
                </aside>

                <!-- Fallback 2D map (shown when WebGL is unavailable) -->
                <div class="campus-fallback">
                    <div style="width:100%; text-align:left">
                        <p style="color:var(--text-dim); font-size:13px; margin:0 0 14px">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            3D view is not available on this device — use this map instead.
                        </p>
                    </div>
                    <div class="fb-row">
                        <?php foreach ($buildings as $b): ?>
                            <a class="fb-tile" href="<?= e($b['actions'][0]['href']) ?>" style="border-top:3px solid <?= e($b['color']) ?>">
                                <i class="fa-solid fa-building-columns" style="color:<?= e($b['color']) ?>"></i>
                                <?= e($b['label']) ?>
                                <small><?= e($b['sub']) ?></small>
                            </a>
                        <?php endforeach; ?>
                        <a class="fb-tile" href="timetable.php" style="border-top:3px solid #00d4ff">
                            <i class="fa-solid fa-calendar-days" style="color:#00d4ff"></i>
                            Classrooms
                            <small>View all timetables</small>
                        </a>
                        <a class="fb-tile" href="students.php" style="border-top:3px solid #2dd4a7">
                            <i class="fa-solid fa-users" style="color:#2dd4a7"></i>
                            Student Plaza
                            <small>Student directory</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="campus-legend">
            <?php foreach ($campusData['legend'] as $lg): ?>
                <span class="lg"><span class="dot" style="background:<?= e($lg['color']) ?>"></span><?= e($lg['label']) ?></span>
            <?php endforeach; ?>
            <span class="lg" style="margin-left:auto"><i class="fa-solid fa-mobile-screen"></i> Works with touch too</span>
        </div>
    </div>
</section>

<script>
    window.EMC_CAMPUS = <?= json_encode($campusData, JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script type="importmap">
{
    "imports": {
        "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js",
        "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/"
    }
}
</script>
<script>
    (function () {
        function webglOk() {
            try {
                var c = document.createElement('canvas');
                return !!(window.WebGLRenderingContext &&
                    (c.getContext('webgl') || c.getContext('experimental-webgl')));
            } catch (e) { return false; }
        }
        // 3D is enabled on desktop AND mobile whenever WebGL is available.
        if (!webglOk()) {
            document.documentElement.classList.add('no-webgl');
        } else {
            var s = document.createElement('script');
            s.type = 'module';
            s.src = 'assets/js/campus3d.js';
            document.body.appendChild(s);
            // Watchdog: if the 3D module hasn't produced a canvas after 9s,
            // fall back to the 2D map so visitors are never stuck on the loader.
            window.setTimeout(function () {
                var c = document.getElementById('campus3d');
                if (c && !c.querySelector('canvas')) {
                    document.documentElement.classList.add('no-webgl');
                }
            }, 9000);
        }
    })();
</script>
<?php include __DIR__ . '/includes/footer.php';
