<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash('error', 'Invalid security token.');
        redirect('library.php');
    }
    $action = $_POST['action'] ?? '';

    if ($action === 'add_book' || $action === 'edit_book') {
        $id = (int) ($_POST['id'] ?? 0);
        $title = trim((string) ($_POST['title'] ?? ''));
        $author = trim((string) ($_POST['author'] ?? ''));
        $isbn = trim((string) ($_POST['isbn'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? 'General'));
        $year = (int) ($_POST['year'] ?? 0);
        $copies = max(1, (int) ($_POST['copies'] ?? 1));
        $shelf = trim((string) ($_POST['shelf'] ?? ''));

        if ($title === '' || $author === '') {
            flash('error', 'Title and author are required.');
        } elseif ($action === 'add_book') {
            $stmt = $pdo->prepare('INSERT INTO books (title, author, isbn, category, year, copies, available, shelf) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$title, $author, $isbn ?: null, $category, $year ?: null, $copies, $copies, $shelf]);
            flash('success', 'Book "' . $title . '" added to the catalog.');
        } else {
            // Preserve available count: adjust only by copies delta
            $cur = $pdo->prepare('SELECT copies, available FROM books WHERE id = ?');
            $cur->execute([$id]);
            $book = $cur->fetch();
            if ($book) {
                $available = $book['available'] + ($copies - $book['copies']);
                $available = max(0, $available);
                $stmt = $pdo->prepare('UPDATE books SET title=?, author=?, isbn=?, category=?, year=?, copies=?, available=?, shelf=? WHERE id=?');
                $stmt->execute([$title, $author, $isbn ?: null, $category, $year ?: null, $copies, $available, $shelf, $id]);
                flash('success', 'Book updated.');
            }
        }
        redirect('library.php');
    }

    if ($action === 'delete_book') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM books WHERE id = ?');
        $stmt->execute([$id]);
        flash('success', 'Book removed from the catalog.');
        redirect('library.php');
    }

    if ($action === 'add_loan') {
        $bookId = (int) ($_POST['book_id'] ?? 0);
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $due = trim((string) ($_POST['due_date'] ?? ''));

        $avail = $pdo->prepare('SELECT available FROM books WHERE id = ?');
        $avail->execute([$bookId]);
        $a = $avail->fetchColumn();

        if ($bookId < 1 || $studentId < 1 || $due === '') {
            flash('error', 'Please select a book, student and due date.');
        } elseif (!$a) {
            flash('error', 'No copies of this book are currently available.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO loans (book_id, student_id, borrow_date, due_date) VALUES (?, ?, CURDATE(), ?)');
            $stmt->execute([$bookId, $studentId, $due]);
            $upd = $pdo->prepare('UPDATE books SET available = available - 1 WHERE id = ?');
            $upd->execute([$bookId]);
            flash('success', 'Loan recorded. Availability updated.');
        }
        redirect('library.php');
    }

    if ($action === 'return_loan') {
        $id = (int) ($_POST['id'] ?? 0);
        $loan = $pdo->prepare('SELECT book_id FROM loans WHERE id = ? AND return_date IS NULL');
        $loan->execute([$id]);
        $bookId = $loan->fetchColumn();
        if ($bookId) {
            $stmt = $pdo->prepare('UPDATE loans SET return_date = CURDATE() WHERE id = ?');
            $stmt->execute([$id]);
            $upd = $pdo->prepare('UPDATE books SET available = available + 1 WHERE id = ?');
            $upd->execute([$bookId]);
            flash('success', 'Book returned and availability updated.');
        } else {
            flash('error', 'Loan not found or already returned.');
        }
        redirect('library.php');
    }
}

$books = $pdo->query('SELECT * FROM books ORDER BY title')->fetchAll();
$students = $pdo->query('SELECT id, first_name, last_name FROM students ORDER BY last_name')->fetchAll();
$loans = $pdo->query(
    "SELECT l.*, b.title, s.first_name, s.last_name
     FROM loans l JOIN books b ON b.id = l.book_id JOIN students s ON s.id = l.student_id
     ORDER BY l.return_date IS NOT NULL, l.due_date"
)->fetchAll();

$page_title  = 'Manage Library';
$active_page = 'admin-library';
include __DIR__ . '/../includes/header.php';
?>
<section class="page-head">
    <div class="container">
        <p class="crumb"><a href="<?= $base_url ?>home.php">Home</a> &rsaquo; Admin &rsaquo; Library</p>
        <h1>Manage <span class="grad-text">Library</span></h1>
        <p class="text-dim" style="margin:0">Add books to the catalog, record loans and process returns.</p>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="feature-row" style="grid-template-columns:1fr 1fr; margin-bottom:34px">
            <div class="card reveal">
                <h3><i class="fa-solid fa-plus" style="color:var(--cyan); margin-right:8px"></i> Add a book</h3>
                <form method="post" action="library.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add_book">
                    <div class="form-grid">
                        <div class="field"><label>Title</label><input class="input" type="text" name="title" required></div>
                        <div class="field"><label>Author</label><input class="input" type="text" name="author" required></div>
                        <div class="field"><label>Category</label><input class="input" type="text" name="category" value="General"></div>
                        <div class="field"><label>ISBN</label><input class="input" type="text" name="isbn"></div>
                        <div class="field"><label>Year</label><input class="input" type="number" name="year" min="1900" max="2100"></div>
                        <div class="form-grid" style="grid-template-columns:1fr 1fr; gap:10px">
                            <div class="field"><label>Copies</label><input class="input" type="number" name="copies" min="1" value="1"></div>
                            <div class="field"><label>Shelf</label><input class="input" type="text" name="shelf" placeholder="A-01"></div>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-2" type="submit"><i class="fa-solid fa-plus"></i> Add book</button>
                </form>
            </div>

            <div class="card reveal reveal-delay-1">
                <h3><i class="fa-solid fa-bookmark" style="color:var(--gold); margin-right:8px"></i> Record a loan</h3>
                <form method="post" action="library.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add_loan">
                    <div class="form-grid">
                        <div class="field">
                            <label>Book</label>
                            <select class="input" name="book_id" required>
                                <option value="">Select&hellip;</option>
                                <?php foreach ($books as $b): if ($b['available'] < 1) continue; ?>
                                    <option value="<?= (int) $b['id'] ?>"><?= e($b['title']) ?> (<?= (int) $b['available'] ?> avail.)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label>Student</label>
                            <select class="input" name="student_id" required>
                                <option value="">Select&hellip;</option>
                                <?php foreach ($students as $s): ?>
                                    <option value="<?= (int) $s['id'] ?>"><?= e($s['last_name']) ?>, <?= e($s['first_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field full"><label>Due date</label><input class="input" type="date" name="due_date" required value="<?= date('Y-m-d', strtotime('+14 days')) ?>"></div>
                    </div>
                    <button class="btn btn-gold mt-2" type="submit"><i class="fa-solid fa-bookmark"></i> Record loan</button>
                </form>
            </div>
        </div>

        <h2 style="margin-bottom:18px">Catalog (<?= count($books) ?>)</h2>
        <div class="table-wrap reveal">
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Title</th><th>Author</th><th>Category</th><th>Copies</th><th>Available</th><th>Shelf</th><th class="text-center">Actions</th></tr></thead>
                    <tbody>
                        <?php if ($books): foreach ($books as $b): ?>
                            <tr>
                                <td class="tt-course"><?= e($b['title']) ?></td>
                                <td><?= e($b['author']) ?></td>
                                <td><span class="badge cyan"><?= e($b['category']) ?></span></td>
                                <td><?= (int) $b['copies'] ?></td>
                                <td><span class="badge <?= $b['available'] > 0 ? 'green' : 'red' ?>"><?= (int) $b['available'] ?></span></td>
                                <td><?= e($b['shelf']) ?></td>
                                <td>
                                    <div class="flex-between" style="gap:8px; justify-content:center">
                                        <button class="btn btn-ghost btn-sm" onclick='editBook(<?= e(json_encode([
                                            'id' => (int) $b['id'], 'title' => $b['title'], 'author' => $b['author'],
                                            'isbn' => $b['isbn'], 'category' => $b['category'], 'year' => (int) $b['year'],
                                            'copies' => (int) $b['copies'], 'shelf' => $b['shelf'],
                                        ], JSON_HEX_APOS | JSON_HEX_QUOT)) ?>)'><i class="fa-solid fa-pen"></i></button>
                                        <form method="post" action="library.php" style="margin:0">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete_book">
                                            <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                                            <button class="btn btn-danger btn-sm" data-confirm="Delete this book and its loan history?" type="submit"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr class="table-empty"><td colspan="7">The catalog is empty.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <h2 class="mt-3" style="margin-bottom:18px">Loans (<?= count($loans) ?>)</h2>
        <div class="table-wrap reveal">
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Student</th><th>Book</th><th>Borrowed</th><th>Due</th><th>Status</th><th class="text-center">Action</th></tr></thead>
                    <tbody>
                        <?php if ($loans): foreach ($loans as $l):
                            $overdue = !$l['return_date'] && strtotime($l['due_date']) < time(); ?>
                            <tr>
                                <td><?= e($l['first_name'] . ' ' . $l['last_name']) ?></td>
                                <td class="tt-course"><?= e($l['title']) ?></td>
                                <td><?= e($l['borrow_date']) ?></td>
                                <td><?= e($l['due_date']) ?></td>
                                <td>
                                    <?php if ($l['return_date']): ?><span class="badge green">Returned</span>
                                    <?php elseif ($overdue): ?><span class="badge red">Overdue</span>
                                    <?php else: ?><span class="badge gold">On loan</span><?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$l['return_date']): ?>
                                        <form method="post" action="library.php" style="margin:0">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="return_loan">
                                            <input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
                                            <button class="btn btn-ghost btn-sm" type="submit"><i class="fa-solid fa-rotate-left"></i> Return</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr class="table-empty"><td colspan="6">No loans recorded.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script>
function editBook(data) {
    var d = typeof data === 'string' ? JSON.parse(data) : data;
    window.openModal(
        '<div class="modal-head"><h3>Edit book</h3><button class="modal-close" aria-label="Close"><i class="fa-solid fa-xmark"></i></button></div>' +
        '<form method="post" action="library.php">' +
        '<?= csrf_field() ?>' +
        '<input type="hidden" name="action" value="edit_book"><input type="hidden" name="id" value="' + d.id + '">' +
        '<div class="field"><label>Title</label><input class="input" type="text" name="title" required value="' + d.title + '"></div>' +
        '<div class="field" style="margin-top:12px"><label>Author</label><input class="input" type="text" name="author" required value="' + d.author + '"></div>' +
        '<div class="form-grid" style="margin-top:12px; grid-template-columns:1fr 1fr">' +
            '<div class="field"><label>Category</label><input class="input" type="text" name="category" value="' + d.category + '"></div>' +
            '<div class="field"><label>ISBN</label><input class="input" type="text" name="isbn" value="' + (d.isbn || '') + '"></div>' +
            '<div class="field"><label>Year</label><input class="input" type="number" name="year" min="1900" max="2100" value="' + (d.year || '') + '"></div>' +
            '<div class="field"><label>Copies</label><input class="input" type="number" name="copies" min="1" value="' + d.copies + '"></div>' +
        '</div>' +
        '<div class="field" style="margin-top:12px"><label>Shelf</label><input class="input" type="text" name="shelf" value="' + (d.shelf || '') + '"></div>' +
        '<button class="btn btn-primary btn-block mt-2" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save changes</button></form>'
    );
}
</script>
<?php include __DIR__ . '/../includes/footer.php';
