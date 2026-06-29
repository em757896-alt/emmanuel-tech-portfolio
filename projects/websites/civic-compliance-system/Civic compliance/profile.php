<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/auth.php';

$auth = new Auth();
$auth->requireAuth();
$db = Database::getInstance();
$user = $auth->currentUser();
$counties = KENYA_COUNTIES;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$auth->verifyCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $orgName = sanitizeInput($_POST['organization_name'] ?? '');
        $county = sanitizeInput($_POST['county'] ?? '');

        if (empty($fullName)) {
            $error = 'Full name is required.';
        } else {
            $db->update('users', [
                'full_name' => $fullName,
                'phone' => $phone,
                'organization_name' => $orgName,
                'county' => $county,
            ], 'id = :id', ['id' => $user['id']]);

            $_SESSION['user_name'] = $fullName;
            $success = 'Profile updated successfully.';
            $user = $auth->currentUser();
        }

        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        if (!empty($newPassword)) {
            if ($newPassword !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } elseif (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
                $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
            } else {
                $db->update('users', [
                    'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]),
                ], 'id = :id', ['id' => $user['id']]);
                $success = 'Password updated successfully.';
            }
        }
    }
}

$pageTitle = 'My Profile - PBO Kenya';
$currentPage = 'profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<section class="page-hero py-4 bg-primary text-white">
    <div class="container">
        <h3 class="fw-bold mb-0"><i class="fas fa-user-circle me-2"></i>My Profile</h3>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= $auth->generateCSRF() ?>">

                            <h5 class="mb-3">Personal Information</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="full_name" class="form-control form-control-lg"
                                           value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control form-control-lg"
                                           value="<?= htmlspecialchars($user['email']) ?>" disabled>
                                    <small class="text-muted">Email cannot be changed</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" name="phone" class="form-control form-control-lg"
                                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Organization</label>
                                    <input type="text" name="organization_name" class="form-control form-control-lg"
                                           value="<?= htmlspecialchars($user['organization_name'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">County</label>
                                    <select name="county" class="form-select form-select-lg">
                                        <option value="">Select County</option>
                                        <?php foreach ($counties as $c): ?>
                                        <option value="<?= htmlspecialchars($c) ?>" <?= ($user['county'] ?? '') === $c ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Role</label>
                                    <input type="text" class="form-control form-control-lg"
                                           value="<?= ucfirst(str_replace('_', ' ', $user['role'])) ?>" disabled>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3">Change Password <small class="text-muted">(leave blank to keep current)</small></h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-control form-control-lg" placeholder="Min. 8 characters">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" name="confirm_password" class="form-control form-control-lg" placeholder="Repeat password">
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary btn-lg ms-2">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({duration:700,once:true});</script>
</body>
</html>
