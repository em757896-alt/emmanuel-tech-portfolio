<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash('error', 'Invalid security token.');
        redirect('departments.php');
    }
    $action = $_POST['action'] ?? '';

    if ($action === 'add_department' || $action === 'edit_department') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $code = strtoupper(trim((string) ($_POST['code'] ?? '')));
        $building = trim((string) ($_POST['building'] ?? ''));
        $color = trim((string) ($_POST['color'] ?? '#6d5df6'));
        $head = trim((string) ($_POST['head'] ?? ''));
        $established = (int) ($_POST['established'] ?? 2015);
        $description = trim((string) ($_POST['description'] ?? ''));

        if ($name === '' || $code === '' || $building === '' || $head === '') {
            flash('error', 'Name, code, building and head are required.');
        } elseif (!preg_match('/^#([0-9a-fA-F]{6})$/', $color)) {
            $color = '#6d5df6';
        } elseif ($action === 'add_department') {
            $stmt = $pdo->prepare('SELECT id FROM departments WHERE code = ?');
            $stmt->execute([$code]);
            if ($stmt->fetch()) {
                flash('error', 'A department with that code already exists.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO departments (name, code, building, color, head, established, description) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$name, $code, $building, $color, $head, $established, $description]);
                flash('success', 'Department "' . $name . '" created.');
            }
        } else {
            $stmt = $pdo->prepare('SELECT id FROM departments WHERE code = ? AND id <> ?');
            $stmt->execute([$code, $id]);
            if ($stmt->fetch()) {
                flash('error', 'Another department already uses that code.');
            } else {
                $stmt = $pdo->prepare('UPDATE departments SET name=?, code=?, building=?, color=?, head=?, established=?, description=? WHERE id=?');
                $stmt->execute([$name, $code, $building, $color, $head, $established, $description, $id]);
                flash('success', 'Department updated.');
            }
        }
        redirect('departments.php');
    }

    if ($action === 'delete_department') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT code FROM departments WHERE id = ?');
        $stmt->execute([$id]);
        $code = $stmt->fetchColumn();
        if ($code) {
            // Unassign students of this department before deleting
            $upd = $pdo->prepare('UPDATE students SET department = "GST" WHERE department = ?');
            $upd->execute([$code]);
            $stmt = $pdo->prepare('DELETE FROM departments WHERE id = ?');
            $stmt->execute([$id]);
            flash('success', 'Department deleted. Its students were moved to General Studies.');
        }
        redirect('departments.php');
    }
}

$departments = $pdo->query('SELECT * FROM departments ORDER BY name')->fetchAll();

$page_title  = 'Manage Departments';
$active_page = 'admin-departments';
include __DIR__ . '/../includes/header.php';
?>
<section class="page-head">
    <div class="container">
        <p class="crumb"><a href="<?= $base_url ?>home.php">Home</a> &rsaquo; Admin &rsaquo; Departments</p>
        <h1>Manage <span class="grad-text">Departments</span></h1>
        <p class="text-dim" style="margin:0">Departments power the 3D campus buildings, timetables and student records.</p>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="card reveal" style="margin-bottom:34px">
            <h3><i class="fa-solid fa-building-columns" style="color:var(--cyan); margin-right:8px"></i> Add department</h3>
            <form method="post" action="departments.php">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add_department">
                <div class="form-grid">
                    <div class="field"><label>Name</label><input class="input" type="text" name="name" required placeholder="Data Science"></div>
                    <div class="field"><label>Code</label><input class="input" type="text" name="code" required maxlength="10" placeholder="DSC"></div>
                    <div class="field"><label>Building</label><input class="input" type="text" name="building" required placeholder="Data Science Block"></div>
                    <div class="field"><label>Head of department</label><input class="input" type="text" name="head" required placeholder="Dr. Jane Wanjiku"></div>
                    <div class="field"><label>Accent colour</label><input class="input" type="color" name="color" value="#6d5df6"></div>
                    <div class="field"><label>Established</label><input class="input" type="number" name="established" min="1900" max="2100" value="2020"></div>
                    <div class="field full"><label>Description</label><textarea class="input" name="description" placeholder="What does this department offer?"></textarea></div>
                </div>
                <button class="btn btn-primary mt-2" type="submit"><i class="fa-solid fa-plus"></i> Add department</button>
            </form>
        </div>

        <h2 style="margin-bottom:18px">Departments (<?= count($departments) ?>)</h2>
        <div class="card-grid">
            <?php if ($departments): foreach ($departments as $d): ?>
                <div class="card" style="padding:22px">
                    <div class="flex-between" style="align-items:flex-start; margin-bottom:10px">
                        <span class="logo-mark" style="background:<?= e($d['color']) ?>; box-shadow:0 6px 18px <?= e($d['color']) ?>55"><?= e($d['code']) ?></span>
                        <span class="badge violet">Est. <?= (int) $d['established'] ?></span>
                    </div>
                    <h3 style="font-size:17px; margin:0 0 2px"><?= e($d['name']) ?></h3>
                    <p class="text-dim" style="font-size:13px; margin:0 0 8px"><?= e($d['building']) ?> &middot; <?= e($d['head']) ?></p>
                    <p style="font-size:13px; color:var(--text-mute); margin:0 0 14px"><?= e(mb_strimwidth($d['description'], 0, 120, '&hellip;')) ?></p>
                    <div class="flex-between" style="gap:8px">
                        <a class="btn btn-ghost btn-sm" href="../department.php?code=<?= e($d['code']) ?>"><i class="fa-solid fa-eye"></i> View</a>
                        <button class="btn btn-ghost btn-sm" onclick='editDept(<?= e(json_encode([
                            'id' => (int) $d['id'], 'name' => $d['name'], 'code' => $d['code'], 'building' => $d['building'],
                            'color' => $d['color'], 'head' => $d['head'], 'established' => (int) $d['established'],
                            'description' => $d['description'],
                        ], JSON_HEX_APOS | JSON_HEX_QUOT)) ?>)'><i class="fa-solid fa-pen"></i> Edit</button>
                        <form method="post" action="departments.php" style="margin:0">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete_department">
                            <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                            <button class="btn btn-danger btn-sm" data-confirm="Delete this department? Its students move to General Studies." type="submit"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; else: ?>
                <p class="text-dim">No departments yet. Add one above.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
function editDept(data) {
    var d = typeof data === 'string' ? JSON.parse(data) : data;
    window.openModal(
        '<div class="modal-head"><h3>Edit department</h3><button class="modal-close" aria-label="Close"><i class="fa-solid fa-xmark"></i></button></div>' +
        '<form method="post" action="departments.php">' +
        '<?= csrf_field() ?>' +
        '<input type="hidden" name="action" value="edit_department"><input type="hidden" name="id" value="' + d.id + '">' +
        '<div class="form-grid" style="grid-template-columns:1fr 1fr">' +
            '<div class="field"><label>Name</label><input class="input" type="text" name="name" required value="' + d.name + '"></div>' +
            '<div class="field"><label>Code</label><input class="input" type="text" name="code" required maxlength="10" value="' + d.code + '"></div>' +
            '<div class="field"><label>Building</label><input class="input" type="text" name="building" required value="' + d.building + '"></div>' +
            '<div class="field"><label>Head</label><input class="input" type="text" name="head" required value="' + d.head + '"></div>' +
            '<div class="field"><label>Colour</label><input class="input" type="color" name="color" value="' + d.color + '"></div>' +
            '<div class="field"><label>Established</label><input class="input" type="number" name="established" min="1900" max="2100" value="' + d.established + '"></div>' +
        '</div>' +
        '<div class="field" style="margin-top:12px"><label>Description</label><textarea class="input" name="description">' + d.description + '</textarea></div>' +
        '<button class="btn btn-primary btn-block mt-2" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save changes</button></form>'
    );
}
</script>
<?php include __DIR__ . '/../includes/footer.php';
