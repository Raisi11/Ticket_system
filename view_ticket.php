<?php
$pageTitle = 'View Ticket - Waves Support';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../includes/functions.php';

requireRole('staff');

$ticket_id = $_GET['id'] ?? 0;
$staff_id = getUserId();

$stmt = $conn->prepare("SELECT t.*, c.category_name, u.name as customer_name, u.email as customer_email FROM tickets t LEFT JOIN categories c ON t.category_id = c.id LEFT JOIN users u ON t.user_id = u.id WHERE t.id = ? AND t.assigned_to = ?");
$stmt->execute([$ticket_id, $staff_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    echo '<div class="container my-4"><div class="alert alert-danger">Ticket not found or not assigned to you.</div></div>';
    require_once '../includes/footer.php';
    exit();
}

// Handle comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['comment'] ?? ''))) {
    $comment = trim($_POST['comment']);
    $stmt = $conn->prepare("INSERT INTO ticket_comments (ticket_id, user_id, comment, comment_type) VALUES (?, ?, ?, 'staff')");
    $stmt->execute([$ticket_id, $staff_id, $comment]);

    addNotification($conn, $ticket['user_id'], $ticket_id, 'Staff replied to your ticket #' . $ticket_id);
    header("Location: view_ticket.php?id=$ticket_id");
    exit();
}

// Get comments
$stmt = $conn->prepare("SELECT tc.*, u.name, u.role FROM ticket_comments tc JOIN users u ON tc.user_id = u.id WHERE tc.ticket_id = ? ORDER BY tc.created_at ASC");
$stmt->execute([$ticket_id]);
$comments = $stmt->fetchAll();
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-ticket-alt me-2"></i>Ticket #<?php echo $ticket['id']; ?></h2>
        <div>
            <a href="update_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-warning"><i class="fas fa-edit me-2"></i>Update</a>
            <a href="assigned_tickets.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><?php echo htmlspecialchars($ticket['title']); ?></h5>
                </div>
                <div class="card-body">
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($ticket['customer_name']); ?> (<?php echo htmlspecialchars($ticket['customer_email']); ?>)</p>
                    <hr>
                    <p><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p>
                    <?php if ($ticket['attachment']): ?>
                        <p><strong>Attachment:</strong> <a href="../uploads/<?php echo $ticket['attachment']; ?>" target="_blank"><i class="fas fa-paperclip"></i> <?php echo $ticket['attachment']; ?></a></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Comments -->
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Comments</h5>
                </div>
                <div class="card-body">
                    <?php if (count($comments) > 0): ?>
                        <?php foreach ($comments as $c): ?>
                            <div class="mb-3 p-3 rounded <?php echo $c['role'] === 'staff' ? 'bg-info bg-opacity-10' : 'bg-light'; ?>">
                                <strong><?php echo htmlspecialchars($c['name']); ?></strong>
                                <span class="badge bg-secondary"><?php echo ucfirst($c['role']); ?></span>
                                <small class="text-muted ms-2"><?php echo date('M d, Y h:i A', strtotime($c['created_at'])); ?></small>
                                <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($c['comment'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No comments yet.</p>
                    <?php endif; ?>

                    <?php if ($ticket['status'] !== 'closed'): ?>
                        <hr>
                        <form method="POST">
                            <div class="mb-3">
                                <textarea name="comment" class="form-control" rows="3" placeholder="Add a reply..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i>Send Reply</button>
                        </form>
                    <?php endif; ?>
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
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($ticket['category_name'] ?? 'N/A'); ?></p>
                    <p><strong>AI Category:</strong> <span class="badge bg-info"><?php echo htmlspecialchars($ticket['ai_category'] ?? 'N/A'); ?></span></p>
                    <p><strong>Sentiment:</strong> <?php echo getSentimentBadge($ticket['sentiment']); ?></p>
                    <p><strong>Created:</strong> <?php echo date('M d, Y h:i A', strtotime($ticket['created_at'])); ?></p>
                    <?php if ($ticket['resolved_at']): ?>
                        <p><strong>Resolved:</strong> <?php echo date('M d, Y h:i A', strtotime($ticket['resolved_at'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>