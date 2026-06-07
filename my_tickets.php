<?php
$pageTitle = 'My Tickets - Waves Support';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../includes/functions.php';

requireRole('customer');

$user_id = getUserId();

$stmt = $conn->prepare("SELECT t.*, c.category_name FROM tickets t LEFT JOIN categories c ON t.category_id = c.id WHERE t.user_id = ? ORDER BY t.created_at DESC");
$stmt->execute([$user_id]);
$tickets = $stmt->fetchAll();
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-list me-2"></i>My Tickets</h2>
        <a href="submit_ticket.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>New Ticket</a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <?php if (count($tickets) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>AI Category</th>
                                <th>Sentiment</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td><?php echo $ticket['id']; ?></td>
                                    <td><?php echo htmlspecialchars($ticket['title']); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['category_name'] ?? 'N/A'); ?></td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($ticket['ai_category'] ?? 'N/A'); ?></span></td>
                                    <td><?php echo getSentimentBadge($ticket['sentiment']); ?></td>
                                    <td><?php echo getPriorityBadge($ticket['priority']); ?></td>
                                    <td><?php echo getStatusBadge($ticket['status']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></td>
                                    <td>
                                        <a href="ticket_details.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center mb-0">You have no tickets yet. <a href="submit_ticket.php">Submit one now!</a></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>