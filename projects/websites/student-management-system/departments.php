<?php
require_once __DIR__ . '/includes/config.php';

$depts = $pdo->query(
    "SELECT d.*, COUNT(s.id) AS student_count
     FROM departments d
     LEFT JOIN students s ON s.department = d.code
     GROUP BY d.id
     ORDER BY d.name"
)->fetchAll();

$page_title  = 'Departments';
$active_page = 'departments';
include __DIR__ . '/includes/header.php';
?>
<section class="page-head">
    <div class="container">
        <p class="crumb"><a href="home.php">Home</a> &rsaquo; Departments</p>
        <h1>Academic <span class="grad-text">Departments</span></h1>
        <p class="lead" style="max-width:620px">Choose a department to explore its programmes, classrooms and location on the 3D campus.</p>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="card-grid">
            <?php foreach ($depts as $i => $d): ?>
                <a class="card reveal <?= $i ? 'reveal-delay-' . min($i, 3) : '' ?>" href="department.php?code=<?= e($d['code']) ?>" style="text-decoration:none; color:inherit">
                    <div class="card-icon" style="color:<?= e($d['color']) ?>; background:<?= e($d['color']) ?>22">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <span class="badge violet" style="position:absolute; top:22px; right:22px"><?= e($d['code']) ?></span>
                    <h3><?= e($d['name']) ?></h3>
                    <p><?= e($d['description']) ?></p>
                    <div class="flex-between" style="margin-top:auto">
                        <span class="chip"><i class="fa-solid fa-location-dot"></i> <?= e($d['building']) ?></span>
                        <span class="chip"><i class="fa-solid fa-user-graduate"></i> <?= (int) $d['student_count'] ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="card reveal mt-3" style="display:flex; gap:14px; align-items:center; justify-content:space-between; flex-wrap:wrap">
            <div>
                <h3 style="margin:0 0 2px">See the departments live on campus</h3>
                <p class="text-dim" style="margin:0">Click each building in the 3D scene to jump straight to its department.</p>
            </div>
            <a class="btn btn-primary" href="campus.php"><i class="fa-solid fa-cubes"></i> Open 3D Campus</a>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php';
