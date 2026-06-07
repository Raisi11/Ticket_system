<?php
$pageTitle = 'Settings - Waves Support';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../includes/functions.php';

requireRole('admin');

$success = '';

// Get admin profile
$admin = getUserById($conn, getUserId());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $new_password = $_POST['new_password'] ?? '';

        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
        $stmt->execute([$name, $phone, getUserId()]);

        if (!empty($new_password) && strlen($new_password) >= 6) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, getUserId()]);
        }

        $_SESSION['user_name'] = $name;
        $success = 'Profile updated successfully!';
        $admin = getUserById($conn, getUserId());
    }
}

// System Stats
$total_users = $conn->query("SELECT COUNT(*) as c FROM users")->fetch()['c'];
$total_tickets = $conn->query("SELECT COUNT(*) as c FROM tickets")->fetch()['c'];
$total_comments = $conn->query("SELECT COUNT(*) as c FROM ticket_comments")->fetch()['c'];
$total_chatbot = $conn->query("SELECT COUNT(*) as c FROM chatbot_logs")->fetch()['c'];
$total_logs = $conn->query("SELECT COUNT(*) as c FROM audit_logs")->fetch()['c'];

// Recent Audit Logs
$audit_logs = $conn->query("SELECT al.*, u.name FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 20")->fetchAll();
?>

<div class="container my-4">
    <h2 class="mb-4"><i class="fas fa-cog me-2"></i>System Settings</h2>

    <?php if ($success) echo showAlert($success, 'success'); ?>

    <div class="row g-4">
        <!-- Admin Profile -->
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Admin Profile</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password (leave empty to keep)</label>
                            <input type="password" name="new_password" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- System Info -->
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-server me-2"></i>System Information</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr><td><strong>System Name</strong></td><td>Waves Technology & Services</td></tr>
                        <tr><td><strong>Total Users</strong></td><td><?php echo $total_users; ?></td></tr>
                        <tr><td><strong>Total Tickets</strong></td><td><?php echo $total_tickets; ?></td></tr>
                        <tr><td><strong>Total Comments</strong></td><td><?php echo $total_comments; ?></td></tr>
                        <tr><td><strong>Chatbot Interactions</strong></td><td><?php echo $total_chatbot; ?></td></tr>
                        <tr><td><strong>Audit Logs</strong></td><td><?php echo $total_logs; ?></td></tr>
                        <tr><td><strong>PHP Version</strong></td><td><?php echo phpversion(); ?></td></tr>
                        <tr><td><strong>Server</strong></td><td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Logs -->
    <div class="card shadow mt-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Audit Logs</h5>
        </div>
        <div class="card-body">
            <?php if (count($audit_logs) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Table</th>
                                <th>Record ID</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($audit_logs as $log): ?>
                                <tr>
                                    <td><?php echo $log['id']; ?></td>
                                    <td><?php echo htmlspecialchars($log['name'] ?? 'System'); ?></td>
                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                    <td><?php echo htmlspecialchars($log['table_name'] ?? ''); ?></td>
                                    <td><?php echo $log['record_id'] ?? ''; ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($log['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center mb-0">No audit logs yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>