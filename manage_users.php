<?php
$pageTitle = 'Manage Users - Waves Support';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../includes/functions.php';

requireRole('admin');

$success = '';
$error = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $target_id = $_POST['user_id'] ?? 0;

    if ($action === 'deactivate') {
        $stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE id = ? AND id != ?");
        $stmt->execute([$target_id, getUserId()]);
        addAuditLog($conn, getUserId(), "Deactivated user #$target_id", 'users', $target_id);
        $success = 'User deactivated.';
    } elseif ($action === 'activate') {
        $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->execute([$target_id]);
        addAuditLog($conn, getUserId(), "Activated user #$target_id", 'users', $target_id);
        $success = 'User activated.';
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND id != ?");
        $stmt->execute([$target_id, getUserId()]);
        addAuditLog($conn, getUserId(), "Deleted user #$target_id", 'users', $target_id);
        $success = 'User deleted.';
    } elseif ($action === 'change_role') {
        $new_role = $_POST['new_role'] ?? '';
        if (in_array($new_role, ['customer', 'staff', 'admin'])) {
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ? AND id != ?");
            $stmt->execute([$new_role, $target_id, getUserId()]);
            addAuditLog($conn, getUserId(), "Changed user #$target_id role to $new_role", 'users', $target_id);
            $success = 'User role updated.';
        }
    } elseif ($action === 'add_user') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'customer';

        if (empty($name) || empty($email) || empty($password)) {
            $error = 'Name, email, and password are required.';
        } else {
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                $error = 'Email already exists.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
                $stmt->execute([$name, $email, $phone, $hashed, $role]);
                addAuditLog($conn, getUserId(), "Added new user: $email ($role)", 'users', $conn->lastInsertId());
                $success = 'User added successfully.';
            }
        }
    }
}

// Get all users
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div class="container-fluid my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users me-2"></i>Manage Users</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="fas fa-plus me-2"></i>Add User</button>
    </div>

    <?php if ($success) echo showAlert($success, 'success'); ?>
    <?php if ($error) echo showAlert($error, 'danger'); ?>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><?php echo htmlspecialchars($u['name']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo htmlspecialchars($u['phone'] ?? 'N/A'); ?></td>
                                <td><span class="badge bg-<?php echo $u['role'] === 'admin' ? 'danger' : ($u['role'] === 'staff' ? 'warning' : 'primary'); ?>"><?php echo ucfirst($u['role']); ?></span></td>
                                <td><span class="badge bg-<?php echo $u['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($u['status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                <td>
                                    <?php if ($u['id'] != getUserId()): ?>
                                        <!-- Role Change -->
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="change_role">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <select name="new_role" class="form-select form-select-sm d-inline-block" style="width:auto;" onchange="this.form.submit()">
                                                <option value="">Role</option>
                                                <option value="customer">Customer</option>
                                                <option value="staff">Staff</option>
                                                <option value="admin">Admin</option>
                                            </select>
                                        </form>
                                        <?php if ($u['status'] === 'active'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="deactivate">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-warning" title="Deactivate"><i class="fas fa-ban"></i></button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="activate">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-success" title="Activate"><i class="fas fa-check"></i></button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">Current</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_user">
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="customer">Customer</option>
                            <option value="staff">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>