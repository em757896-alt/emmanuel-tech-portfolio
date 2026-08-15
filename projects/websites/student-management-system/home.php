<?php
require_once __DIR__ . '/includes/config.php';

// Real stats for the dashboard strip
$totalStudents   = (int) $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalDepts      = (int) $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
$totalClassrooms = (int) $pdo->query("SELECT COUNT(*) FROM classrooms")->fetchColumn();
$totalBooks      = (int) $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$totalLoans      = (int) $pdo->query("SELECT COUNT(*) FROM loans WHERE return_date IS NULL")->fetchColumn();
$deptCounts      = $pdo->query(
    "SELECT d.code, d.name, d.building, d.color, COUNT(s.id) AS total
     FROM departments d LEFT JOIN students s ON s.department = d.code
     GROUP BY d.id ORDER BY total DESC"
)->fetchAll();

$page_title  = 'Home';
$active_page = 'home';
$main_class  = 'home-page';
include __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <canvas id="hero3d" aria-hidden="true"></canvas>
    <div class="hero-glow"></div>
    <div class="container">
        <div class="hero-inner">
            <span class="eyebrow reveal">Elevate Media College &middot; Est. 2015</span>
            <h1 class="reveal">Where creativity meets <span class="grad-text">technology.</span></h1>
            <p class="lead reveal reveal-delay-1">
                An interactive 3D campus, powerful student management, live timetables and a complete
                library — everything about the college, in one place.
            </p>
            <div class="hero-cta reveal reveal-delay-2">
                <a class="btn btn-primary" href="campus.php"><i class="fa-solid fa-cubes"></i> Explore 3D Campus</a>
                <a class="btn btn-ghost" href="departments.php"><i class="fa-solid fa-school"></i> Departments</a>
            </div>
            <div class="hero-meta reveal reveal-delay-3">
                <span><i class="fa-solid fa-graduation-cap"></i> <?= number_format($totalStudents) ?> students</span>
                <span><i class="fa-solid fa-book-open"></i> <?= number_format($totalBooks) ?> books</span>
                <span><i class="fa-solid fa-bookmark"></i> <?= number_format($totalLoans) ?> active loans</span>
            </div>
        </div>
    </div>
</section>

<!-- Stats strip -->
<section class="section-sm">
    <div class="container">
        <div class="stats-grid reveal">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div>
                <div class="stat-num" data-count="<?= $totalStudents ?>">0</div>
                <div class="stat-label">Registered Students</div>
            </div>
            <div class="stat-card dark-gold">
                <div class="stat-icon"><i class="fa-solid fa-school"></i></div>
                <div class="stat-num" data-count="<?= $totalDepts ?>">0</div>
                <div class="stat-label">Academic Departments</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-door-open"></i></div>
                <div class="stat-num" data-count="<?= $totalClassrooms ?>">0</div>
                <div class="stat-label">Classrooms &amp; Labs</div>
            </div>
            <div class="stat-card dark-gold">
                <div class="stat-icon"><i class="fa-solid fa-book-open-reader"></i></div>
                <div class="stat-num" data-count="<?= $totalBooks ?>">0</div>
                <div class="stat-label">Library Books</div>
            </div>
        </div>
    </div>
</section>

<!-- Why / features -->
<section class="section">
    <div class="container">
        <div class="section-head center reveal">
            <span class="eyebrow">Why Elevate</span>
            <h2>One campus, <span class="grad-text">everything connected</span></h2>
            <p class="text-dim">The 3D campus is the front door — every building, classroom and student is just a click away.</p>
        </div>

        <div class="feature-row">
            <div class="visual-box reveal">
                <div class="mini-3d">
                    <div class="building"></div>
                </div>
            </div>
            <ul class="feature-list reveal reveal-delay-1">
                <li>
                    <span class="fi"><i class="fa-solid fa-cubes"></i></span>
                    <div>
                        <strong>Interactive 3D Campus</strong>
                        <span>Rotate, zoom, and click buildings to open departments, classrooms and more.</span>
                    </div>
                </li>
                <li>
                    <span class="fi"><i class="fa-solid fa-calendar-days"></i></span>
                    <div>
                        <strong>Live Timetables</strong>
                        <span>Daily schedules by classroom and department, always up to date.</span>
                    </div>
                </li>
                <li>
                    <span class="fi"><i class="fa-solid fa-users"></i></span>
                    <div>
                        <strong>Student Management</strong>
                        <span>Register, search, update and manage every student record securely.</span>
                    </div>
                </li>
                <li>
                    <span class="fi"><i class="fa-solid fa-book-open"></i></span>
                    <div>
                        <strong>Library System</strong>
                        <span>Browse the catalog, check availability and track book loans.</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</section>

<!-- Departments preview -->
<section class="section" style="background:rgba(255,255,255,.02); border-block:1px solid var(--border);">
    <div class="container">
        <div class="flex-between section-head" style="margin-bottom:26px">
            <div>
                <span class="eyebrow">Our Departments</span>
                <h2 style="margin:0">Programmes that <span class="grad-text">build futures</span></h2>
            </div>
            <a class="btn btn-ghost btn-sm" href="departments.php">View all <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="card-grid">
            <?php foreach (array_slice($deptCounts, 0, 4) as $i => $d): ?>
                <a class="card reveal <?= $i ? 'reveal-delay-' . $i : '' ?>" href="department.php?code=<?= e($d['code']) ?>" style="text-decoration:none; color:inherit">
                    <div class="card-icon" style="color:<?= e($d['color']) ?>; background:<?= e($d['color']) ?>22">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <h3><?= e($d['name']) ?></h3>
                    <p><?= e($d['building']) ?> &middot; <?= $d['total'] ?> students</p>
                    <span class="card-link" style="color:<?= e($d['color']) ?>">Explore department <i class="fa-solid fa-arrow-right"></i></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="section-sm">
    <div class="container">
        <div class="card reveal" style="display:flex; flex-direction:column; gap:18px; align-items:center; text-align:center; padding:clamp(30px,5vw,52px)">
            <span class="eyebrow">Step inside</span>
            <h2 style="max-width:560px">Explore the campus in <span class="grad-text">3D</span> — or manage student records right now.</h2>
            <div class="hero-cta" style="justify-content:center; margin:0">
                <a class="btn btn-primary" href="campus.php"><i class="fa-solid fa-cubes"></i> Open 3D Campus</a>
                <?php if (!is_admin()): ?>
                    <a class="btn btn-gold" href="login.php"><i class="fa-solid fa-user-lock"></i> Staff Login</a>
                <?php else: ?>
                    <a class="btn btn-gold" href="admin/dashboard.php"><i class="fa-solid fa-gauge-high"></i> Admin Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php
$footer_scripts = '<script src="https://cdn.jsdelivr.net/npm/three@0.149.0/build/three.min.js"></script>
<script src="assets/js/hero3d.js"></script>';
include __DIR__ . '/includes/footer.php';
