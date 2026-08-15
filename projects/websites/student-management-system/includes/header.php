<?php
// header.php - shared top of every page.
// Expected: $page_title, $active_page (slug), $base_url ('' or '../'), optional $header_class, $header_scripts
$page_title  = $page_title ?? APP_NAME;
$active_page = $active_page ?? '';
// Pages inside the /admin folder need ../ to reach root assets & links.
$base_url    = $base_url ?? (basename(dirname($_SERVER['SCRIPT_FILENAME'] ?? '')) === 'admin' ? '../' : '');
$flashes     = get_flashes();
$logged_in   = is_admin();

$nav_main = [
    ['home.php',        'Home',        'fa-house',            'home'],
    ['campus.php',      '3D Campus',   'fa-cubes',            'campus'],
    ['departments.php', 'Departments', 'fa-school',           'departments'],
    ['students.php',    'Students',    'fa-users',            'students'],
    ['timetable.php',   'Timetable',   'fa-calendar-days',    'timetable'],
    ['library.php',     'Library',     'fa-book-open',        'library'],
    ['about.php',       'About',       'fa-circle-info',      'about'],
    ['contact.php',     'Contact',     'fa-envelope',         'contact'],
];

$nav_admin = $logged_in ? [
    ['admin/dashboard.php',    'Dashboard',   'fa-gauge-high',      'dashboard'],
    ['admin/students.php',     'Students',    'fa-users',           'students'],
    ['admin/timetable.php',    'Schedules',   'fa-clock',           'schedules'],
    ['admin/library.php',      'Library',     'fa-book',            'admin-library'],
    ['admin/departments.php',  'Departments', 'fa-building-columns','admin-departments'],
] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title) ?> &middot; <?= e(APP_NAME) ?></title>
<meta name="description" content="Elevate Media College — interactive 3D campus, academic departments, timetable, library and student management system.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="<?= $base_url ?>assets/css/styles.css">
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="<?= $base_url ?>home.php" aria-label="<?= e(APP_NAME) ?> home">
            <span class="logo-mark">EM</span>
            <span class="logo-text">
                <strong>Elevate Media College</strong>
                <small><?= e(APP_TAGLINE) ?></small>
            </span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false">
            <i class="fa-solid fa-bars"></i>
        </button>

        <nav class="site-nav" id="siteNav" aria-label="Main navigation">
            <ul class="nav-list">
                <?php foreach ($nav_main as $item): ?>
                    <li>
                        <a class="nav-link<?= $active_page === $item[3] ? ' active' : '' ?>"
                           href="<?= $base_url . $item[0] ?>">
                            <i class="fa-solid <?= $item[2] ?>"></i><span><?= $item[1] ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>

                <?php if ($logged_in): ?>
                    <li class="nav-sep" aria-hidden="true"></li>
                    <?php foreach ($nav_admin as $item): ?>
                        <li>
                            <a class="nav-link admin-link<?= $active_page === $item[3] ? ' active' : '' ?>"
                               href="<?= $base_url . $item[0] ?>">
                                <i class="fa-solid <?= $item[2] ?>"></i><span><?= $item[1] ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li>
                        <a class="nav-link admin-link" href="<?= $base_url ?>logout.php">
                            <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span>
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a class="btn btn-primary btn-sm nav-cta" href="<?= $base_url ?>login.php">
                            <i class="fa-solid fa-user-lock"></i> Admin Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<?php if ($flashes): ?>
    <div class="toast-stack" aria-live="polite">
        <?php foreach ($flashes as $f): $icon = $f['type'] === 'success' ? 'fa-circle-check' : ($f['type'] === 'error' ? 'fa-circle-exclamation' : 'fa-circle-info'); ?>
            <div class="toast toast-<?= e($f['type']) ?>">
                <i class="fa-solid <?= $icon ?>"></i>
                <span><?= e($f['message']) ?></span>
                <button class="toast-close" aria-label="Dismiss"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<main id="main" class="<?= isset($main_class) ? e($main_class) : '' ?>">
