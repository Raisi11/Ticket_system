<?php
$pageTitle = 'Manage Tickets - Waves Support';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../includes/functions.php';

requireRole('admin');

$success = '';
$error = '';
$staff_list = getAllStaff($conn);

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $ticket_id = $_POST['ticket_id'] ?? 0;

    if ($action === 'assign') {
        $staff_id = $_POST['assigned_to'] ?? '';
        $priority = $_POST['priority'] ?? '';
        if (!empty($staff_id)) {
            $sql = "UPDATE tickets SET assigned_to = ?";
            $params = [$staff_id];
            if (!empty($priority)) {
                $sql .= ", priority = ?";
                $params[] = $priority;
            }
            $sql .= ", updated_at = NOW() WHERE id = ?";
            $params[] = $ticket_id;
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            addNotification($conn, $staff_id, $ticket_id, "You have been assigned ticket #$ticket_id");
            addAuditLog($conn, getUserId(), "Assigned ticket #$ticket_id to staff #$staff_id", 'tickets', $ticket_id);
            $success = "Ticket #$ticket_id assigned successfully.";
        }
    } elseif ($action === 'update_status') {
        $new_status = $_POST['status'] ?? '';
        $resolved_at = ($new_status === 'resolved') ? date('Y-m-d H:i:s') : null;
        $stmt = $conn->prepare("UPDATE tickets SET status = ?, resolved_at = COALESCE(?, resolved_at), updated_at = NOW() WHERE id = ?");
        $stmt->execute([$new_status, $resolved_at, $ticket_id]);
        addAuditLog($conn, getUserId(), "Updated ticket #$ticket_id status to $new_status", 'tickets', $ticket_id);
        $success = "Ticket #$ticket_id status updated.";
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM tickets WHERE id = ?");
        $stmt->execute([$ticket_id]);
        addAuditLog($conn, getUserId(), "Deleted ticket #$ticket_id", 'tickets', $ticket_id);
        $success = "Ticket #$ticket_id deleted.";
    }
}

// Filters
$filter_status = $_GET['status'] ?? '';
$filter_priority = $_GET['priority'] ?? '';
$filter_category = $_GET['category'] ?? '';

$sql = "SELECT t.*, c.category_name, u.name as customer_name, s.name as staff_name 
        FROM tickets t 
        LEFT JOIN categories c ON t.category_id = c.id 
        LEFT JOIN users u ON t.user_id = u.id 
        LEFT JOIN users s ON t.assigned_to = s.id 
        WHERE 1=1";
$params = [];

if (!empty($filter_status)) { $sql .= " AND t.status = ?"; $params[] = $filter_status; }
if (!empty($filter_priority)) { $sql .= " AND t.priority = ?"; $params[] = $filter_priority; }
if (!empty($filter_category)) { $sql .= " AND t.category_id = ?"; $params[] = $filter_category; }

$sql .= " ORDER BY t.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();
$categories = getAllCategories($conn);
?>

<div class="container-fluid my-4">
    <h2 class="mb-4"><i class="fas fa-ticket-alt me-2"></i>Manage Tickets</h2>

    <?php if ($success) echo showAlert($success, 'success'); ?>
    <?php if ($error) echo showAlert($error, 'danger'); ?>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="open" <?php echo $filter_status === 'open' ? 'selected' : ''; ?>>Open</option>
                        <option value="in_progress" <?php echo $filter_status === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="resolved" <?php echo $filter_status === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="closed" <?php echo $filter_status === 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All</option>
                        <option value="high" <?php echo $filter_priority === 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="medium" <?php echo $filter_priority === 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="low" <?php echo $filter_priority === 'low' ? 'selected' : ''; ?>>Low</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $filter_category == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['category_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="manage_tickets.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <?php if (count($tickets) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>AI Category</th>
                                <th>Sentiment</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Assigned To</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $t): ?>
                                <tr>
                                    <td><?php echo $t['id']; ?></td>
                                    <td><?php echo htmlspecialchars($t['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($t['title'], 0, 30)); ?></td>
                                    <td><?php echo htmlspecialchars($t['category_name'] ?? 'N/A'); ?></td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($t['ai_category'] ?? 'N/A'); ?></span></td>
                                    <td><?php echo getSentimentBadge($t['sentiment']); ?></td>
                                    <td><?php echo getPriorityBadge($t['priority']); ?></td>
                                    <td><?php echo getStatusBadge($t['status']); ?></td>
                                    <td><?php echo htmlspecialchars($t['staff_name'] ?? 'Unassigned'); ?></td>
                                    <td><?php echo date('M d', strtotime($t['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="assignTicket(<?php echo $t['id']; ?>, '<?php echo $t['priority']; ?>')" title="Assign"><i class="fas fa-user-plus"></i></button>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="ticket_id" value="<?php echo $t['id']; ?>">
                                            <select name="status" class="form-select form-select-sm d-inline-block" style="width:auto;" onchange="this.form.submit()">
                                                <option value="">Status</option>
                                                <option value="open">Open</option>
                                                <option value="in_progress">In Progress</option>
                                                <option value="pending">Pending</option>
                                                <option value="resolved">Resolved</option>
                                                <option value="closed">Closed</option>
                                            </select>
                                        </form>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this ticket?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="ticket_id" value="<?php echo $t['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
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

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Assign Ticket</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="assign">
                    <input type="hidden" name="ticket_id" id="assign_ticket_id">
                    <div class="mb-3">
                        <label class="form-label">Assign to Staff <span class="text-danger">*</span></label>
                        <select name="assigned_to" class="form-select" required>
                            <option value="">-- Select Staff --</option>
                            <?php foreach ($staff_list as $s): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?> (<?php echo $s['email']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select" id="assign_priority">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function assignTicket(id, priority) {
    document.getElementById('assign_ticket_id').value = id;
    document.getElementById('assign_priority').value = priority;
    new bootstrap.Modal(document.getElementById('assignModal')).show();
}
</script>

<?php require_once '../includes/footer.php'; ?>