<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';

require_admin();

$stats = [
    'students'   => (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn(),
    'departments'=> (int) $pdo->query('SELECT COUNT(*) FROM departments')->fetchColumn(),
    'classrooms' => (int) $pdo->query('SELECT COUNT(*) FROM classrooms')->fetchColumn(),
    'books'      => (int) $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn(),
    'activeLoans'=> (int) $pdo->query('SELECT COUNT(*) FROM loans WHERE return_date IS NULL')->fetchColumn(),
    'sessions'   => (int) $pdo->query('SELECT COUNT(*) FROM timetables')->fetchColumn(),
    'messages'   => (int) $pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn(),
];

// Students per department (for chart)
$deptStats = $pdo->query(
    "SELECT d.name, d.color, COUNT(s.id) AS total
     FROM departments d
     LEFT JOIN students s ON s.department = d.code
     GROUP BY d.id ORDER BY total DESC"
)->fetchAll();

// Books per category (for chart)
$catStats = $pdo->query(
    'SELECT category, COUNT(*) AS total FROM books GROUP BY category ORDER BY total DESC'
)->fetchAll();

// Recent loans
$recentLoans = $pdo->query(
    "SELECT l.borrow_date, l.due_date, l.return_date,
            b.title, s.first_name, s.last_name
     FROM loans l
     JOIN books b ON b.id = l.book_id
     JOIN students s ON s.id = l.student_id
     ORDER BY l.borrow_date DESC LIMIT 6"
)->fetchAll();

$recentStudents = $pdo->query(
    'SELECT first_name, last_name, email, course, department, avatar_color, created_at
     FROM students ORDER BY created_at DESC LIMIT 5'
)->fetchAll();

$page_title  = 'Dashboard';
$active_page = 'dashboard';
include __DIR__ . '/../includes/header.php';
?>
<section class="page-head">
    <div class="container">
        <p class="crumb"><a href="<?= $base_url ?>home.php">Home</a> &rsaquo; Admin</p>
        <div class="flex-between">
            <div>
                <h1 style="margin-bottom:6px">Welcome back, <span class="grad-text"><?= e(admin_name()) ?></span></h1>
                <p class="text-dim" style="margin:0">Here is what is happening at the college today.</p>
            </div>
            <a class="btn btn-primary" href="students.php"><i class="fa-solid fa-user-plus"></i> Manage students</a>
        </div>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div><div class="stat-num" data-count="<?= $stats['students'] ?>">0</div><div class="stat-label">Students</div></div>
            <div class="stat-card dark-gold"><div class="stat-icon"><i class="fa-solid fa-school"></i></div><div class="stat-num" data-count="<?= $stats['departments'] ?>">0</div><div class="stat-label">Departments</div></div>
            <div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-book-open"></i></div><div class="stat-num" data-count="<?= $stats['books'] ?>">0</div><div class="stat-label">Library Books</div></div>
            <div class="stat-card dark-gold"><div class="stat-icon"><i class="fa-solid fa-bookmark"></i></div><div class="stat-num" data-count="<?= $stats['activeLoans'] ?>">0</div><div class="stat-label">Active Loans</div></div>
            <div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-calendar-days"></i></div><div class="stat-num" data-count="<?= $stats['sessions'] ?>">0</div><div class="stat-label">Weekly Sessions</div></div>
            <div class="stat-card dark-gold"><div class="stat-icon"><i class="fa-solid fa-envelope"></i></div><div class="stat-num" data-count="<?= $stats['messages'] ?>">0</div><div class="stat-label">Contact Messages</div></div>
        </div>

        <div class="feature-row mt-3" style="grid-template-columns:1fr 1fr; margin-top:26px">
            <div class="card reveal">
                <h3>Students per department</h3>
                <div style="position:relative; height:260px">
                    <canvas id="chartDepts"></canvas>
                </div>
            </div>
            <div class="card reveal reveal-delay-1">
                <h3>Library collection by category</h3>
                <div style="position:relative; height:260px">
                    <canvas id="chartCats"></canvas>
                </div>
            </div>
        </div>

        <h2 class="mt-3" style="margin-bottom:18px">Recent loans</h2>
        <div class="table-wrap reveal">
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Student</th><th>Book</th><th>Borrowed</th><th>Due</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if ($recentLoans): foreach ($recentLoans as $l): ?>
                            <tr>
                                <td><?= e($l['first_name'] . ' ' . $l['last_name']) ?></td>
                                <td><?= e($l['title']) ?></td>
                                <td><?= e($l['borrow_date']) ?></td>
                                <td><?= e($l['due_date']) ?></td>
                                <td>
                                    <?php if ($l['return_date']): ?>
                                        <span class="badge green">Returned</span>
                                    <?php elseif (strtotime($l['due_date']) < time()): ?>
                                        <span class="badge red">Overdue</span>
                                    <?php else: ?>
                                        <span class="badge gold">On loan</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr class="table-empty"><td colspan="5">No loans recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <h2 class="mt-3" style="margin-bottom:18px">Newest students</h2>
        <div class="table-wrap reveal">
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Student</th><th>Email</th><th>Course</th><th>Dept</th><th>Registered</th></tr></thead>
                    <tbody>
                        <?php if ($recentStudents): foreach ($recentStudents as $s): ?>
                            <tr>
                                <td>
                                    <div class="avatar-row">
                                        <span class="avatar" style="background:<?= e($s['avatar_color']) ?>"><?= e(avatar_initials($s['first_name'], $s['last_name'])) ?></span>
                                        <span><?= e($s['first_name'] . ' ' . $s['last_name']) ?></span>
                                    </div>
                                </td>
                                <td><?= e($s['email']) ?></td>
                                <td><?= e($s['course']) ?></td>
                                <td><span class="badge cyan"><?= e($s['department']) ?></span></td>
                                <td><span class="tt-meta"><?= e(date('M j, Y', strtotime($s['created_at']))) ?></span></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr class="table-empty"><td colspan="5">No students registered yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php
$deptChartJson = json_encode([
    'labels'   => array_map(fn ($d) => $d['name'], $deptStats),
    'values'   => array_map(fn ($d) => (int) $d['total'], $deptStats),
    'colors'   => array_map(fn ($d) => $d['color'], $deptStats),
]);
$catChartJson = json_encode([
    'labels' => array_map(fn ($c) => $c['category'], $catStats),
    'values' => array_map(fn ($c) => (int) $c['total'], $catStats),
]);
$footer_scripts = <<<HTML
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    var d = {$deptChartJson};
    new Chart(document.getElementById('chartDepts'), {
        type: 'bar',
        data: { labels: d.labels, datasets: [{ data: d.values, backgroundColor: d.colors.map(function (c) { return c + 'cc'; }), borderRadius: 8, barThickness: 34 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
            scales: { x: { ticks: { color: '#9aa7c7', font: { size: 11 } }, grid: { color: 'rgba(255,255,255,.06)' } },
                     y: { ticks: { color: '#9aa7c7' }, grid: { color: 'rgba(255,255,255,.06)' }, beginAtZero: true } } }
    });
    var c = {$catChartJson};
    new Chart(document.getElementById('chartCats'), {
        type: 'doughnut',
        data: { labels: c.labels, datasets: [{ data: c.values, backgroundColor: ['#6d5df6','#00d4ff','#ffc93c','#2dd4a7','#ff5c7a','#f59e0b','#8b5cf6','#06b6d4'], borderColor: '#0c1230', borderWidth: 3 }] },
        options: { responsive: true, maintainAspectRatio: false, cutout: '62%',
            plugins: { legend: { labels: { color: '#9aa7c7', font: { size: 11 } } } } }
    });
})();
</script>
HTML;
include __DIR__ . '/../includes/footer.php';
