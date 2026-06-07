<?php
$pageTitle = 'Assigned Tickets - Waves Support';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../includes/functions.php';

requireRole('staff');

$staff_id = getUserId();

// Filter
$filter_status = $_GET['status'] ?? '';
$filter_priority = $_GET['priority'] ?? '';

$sql = "SELECT t.*, c.category_name, u.name as customer_name FROM tickets t LEFT JOIN categories c ON t.category_id = c.id LEFT JOIN users u ON t.user_id = u.id WHERE t.assigned_to = ?";
$params = [$staff_id];

if (!empty($filter_status)) {
    $sql .= " AND t.status = ?";
    $params[] = $filter_status;
}
if (!empty($filter_priority)) {
    $sql .= " AND t.priority = ?";
    $params[] = $filter_priority;
}

$sql .= " ORDER BY FIELD(t.priority, 'high', 'medium', 'low'), t.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();
?>

<div class="container my-4">
    <h2 class="mb-4"><i class="fas fa-list me-2"></i>Assigned Tickets</h2>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Filter by Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="open" <?php echo $filter_status === 'open' ? 'selected' : ''; ?>>Open</option>
                        <option value="in_progress" <?php echo $filter_status === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="resolved" <?php echo $filter_status === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="closed" <?php echo $filter_status === 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filter by Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All Priority</option>
                        <option value="high" <?php echo $filter_priority === 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="medium" <?php echo $filter_priority === 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="low" <?php echo $filter_priority === 'low' ? 'selected' : ''; ?>>Low</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="assigned_tickets.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <?php if (count($tickets) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Sentiment</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td><?php echo $ticket['id']; ?></td>
                                    <td><?php echo htmlspecialchars($ticket['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['title']); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['category_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo getPriorityBadge($ticket['priority']); ?></td>
                                    <td><?php echo getSentimentBadge($ticket['sentiment']); ?></td>
                                    <td><?php echo getStatusBadge($ticket['status']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></td>
                                    <td>
                                        <a href="view_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-primary" title="View"><i class="fas fa-eye"></i></a>
                                        <a href="update_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-warning" title="Update"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center mb-0">No tickets found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>