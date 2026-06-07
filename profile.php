<?php
$pageTitle = 'Staff Profile - Waves Support';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../includes/functions.php';

requireRole('staff');

$user = getUserById($conn, getUserId());
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name)) {
        $error = 'Name is required.';
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$name, $phone, getUserId()]);

        if (!empty($new_password)) {
            if (strlen($new_password) < 6) {
                $error = 'Password must be at least 6 characters.';
            } elseif ($new_password !== $confirm_password) {
                $error = 'Passwords do not match.';
            } else {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, getUserId()]);
            }
        }

        if (empty($error)) {
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated successfully!';
            $user = getUserById($conn, getUserId());
        }
    }
}
?>

<div class="container my-4">
    <h2 class="mb-4"><i class="fas fa-user me-2"></i>My Profile</h2>

    <?php if ($error) echo showAlert($error, 'danger'); ?>
    <?php if ($success) echo showAlert($success, 'success'); ?>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email (cannot change)</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        <hr>
                        <p class="text-muted">Leave password fields empty to keep current password</p>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Minimum 6 characters">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary py-2 px-4"><i class="fas fa-save me-2"></i>Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>