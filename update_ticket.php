<?php
$pageTitle = 'Update Ticket - Waves Support';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../includes/functions.php';

requireRole('staff');

$ticket_id = $_GET['id'] ?? 0;
$staff_id = getUserId();
$error = '';
$success = '';

$stmt = $conn->prepare("SELECT t.*, c.category_name, u.name as customer_name FROM tickets t LEFT JOIN categories c ON t.category_id = c.id LEFT JOIN users u ON t.user_id = u.id WHERE t.id = ? AND t.assigned_to = ?");
$stmt->execute([$ticket_id, $staff_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    echo '<div class="container my-4"><div class="alert alert-danger">Ticket not found or not assigned to you.</div></div>';
    require_once '../includes/footer.php';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'] ?? '';
    $comment = trim($_POST['comment'] ?? '');

    if (empty($new_status)) {
        $error = 'Please select a status.';
    } else {
        // Update ticket status
        $resolved_at = ($new_status === 'resolved' && $ticket['status'] !== 'resolved') ? date('Y-m-d H:i:s') : $ticket['resolved_at'];

        $stmt = $conn->prepare("UPDATE tickets SET status = ?, resolved_at = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$new_status, $resolved_at, $ticket_id]);

        // Add comment if provided
        if (!empty($comment)) {
            $stmt = $conn->prepare("INSERT INTO ticket_comments (ticket_id, user_id, comment, comment_type) VALUES (?, ?, ?, 'staff')");
            $stmt->execute([$ticket_id, $staff_id, $comment]);
        }

        // Notify customer
        $status_label = ucfirst(str_replace('_', ' ', $new_status));
        addNotification($conn, $ticket['user_id'], $ticket_id, "Ticket #$ticket_id status updated to: $status_label");

        // Audit log
        addAuditLog($conn, $staff_id, "Updated ticket #$ticket_id status to $new_status", 'tickets', $ticket_id);

        $success = 'Ticket updated successfully!';

        // Refresh ticket data
        $stmt = $conn->prepare("SELECT t.*, c.category_name, u.name as customer_name FROM tickets t LEFT JOIN categories c ON t.category_id = c.id LEFT JOIN users u ON t.user_id = u.id WHERE t.id = ? AND t.assigned_to = ?");
        $stmt->execute([$ticket_id, $staff_id]);
        $ticket = $stmt->fetch();
    }
}
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-edit me-2"></i>Update Ticket #<?php echo $ticket['id']; ?></h2>
        <a href="assigned_tickets.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <?php if ($error) echo showAlert($error, 'danger'); ?>
    <?php if ($success) echo showAlert($success, 'success'); ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Ticket Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Title:</strong> <?php echo htmlspecialchars($ticket['title']); ?></p>
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($ticket['customer_name']); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($ticket['category_name'] ?? 'N/A'); ?></p>
                    <p><strong>AI Category:</strong> <span class="badge bg-info"><?php echo htmlspecialchars($ticket['ai_category'] ?? 'N/A'); ?></span></p>
                    <p><strong>Sentiment:</strong> <?php echo getSentimentBadge($ticket['sentiment']); ?></p>
                    <hr>
                    <p><strong>Description:</strong></p>
                    <p><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Update Status</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Current Status</label>
                            <input type="text" class="form-control" value="<?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="">-- Select Status --</option>
                                <option value="open">Open</option>
                                <option value="in_progress">In Progress</option>
                                <option value="pending">Pending</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comment / Resolution Note</label>
                            <textarea name="comment" class="form-control" rows="4" placeholder="Add a comment or resolution note..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary py-2 px-4"><i class="fas fa-save me-2"></i>Update Ticket</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Status:</strong> <?php echo getStatusBadge($ticket['status']); ?></p>
                    <p><strong>Priority:</strong> <?php echo getPriorityBadge($ticket['priority']); ?></p>
                    <p><strong>Created:</strong> <?php echo date('M d, Y h:i A', strtotime($ticket['created_at'])); ?></p>
                    <p><strong>Updated:</strong> <?php echo date('M d, Y h:i A', strtotime($ticket['updated_at'])); ?></p>
                    <?php if ($ticket['resolved_at']): ?>
                        <p><strong>Resolved:</strong> <?php echo date('M d, Y h:i A', strtotime($ticket['resolved_at'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>