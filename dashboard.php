<?php
$pageTitle = 'Staff Dashboard - Waves Support';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../includes/functions.php';

requireRole('staff');

$staff_id = getUserId();

$assigned = getTicketCount($conn, "assigned_to = ?", [$staff_id]);
$urgent = getTicketCount($conn, "assigned_to = ? AND priority = 'high' AND status != 'closed'", [$staff_id]);
$in_progress = getTicketCount($conn, "assigned_to = ? AND status = 'in_progress'", [$staff_id]);
$resolved = getTicketCount($conn, "assigned_to = ? AND status = 'resolved'", [$staff_id]);
?>

<div class="container my-4">
    <h2 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>Welcome, <?php echo htmlspecialchars(getUserName()); ?>!</h2>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card dashboard-card bg-primary text-white">
                <i class="fas fa-ticket-alt"></i>
                <h3><?php echo $assigned; ?></h3>
                <p class="mb-0">Assigned Tickets</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card bg-danger text-white">
                <i class="fas fa-exclamation-triangle"></i>
                <h3><?php echo $urgent; ?></h3>
                <p class="mb-0">Urgent</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card bg-warning text-dark">
                <i class="fas fa-spinner"></i>
                <h3><?php echo $in_progress; ?></h3>
                <p class="mb-0">In Progress</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card bg-success text-white">
                <i class="fas fa-check-circle"></i>
                <h3><?php echo $resolved; ?></h3>
                <p class="mb-0">Resolved</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <a href="assigned_tickets.php" class="text-decoration-none">
                <div class="card dashboard-card h-100">
                    <i class="fas fa-list text-primary"></i>
                    <h5>My Assigned Tickets</h5>
                    <p class="text-muted">View and manage your tickets</p>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="profile.php" class="text-decoration-none">
                <div class="card dashboard-card h-100">
                    <i class="fas fa-user text-info"></i>
                    <h5>My Profile</h5>
                    <p class="text-muted">Update your account details</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Assigned Tickets -->
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Assigned Tickets</h5>
        </div>
        <div class="card-body">
            <?php
            $stmt = $conn->prepare("SELECT t.*, c.category_name, u.name as customer_name FROM tickets t LEFT JOIN categories c ON t.category_id = c.id LEFT JOIN users u ON t.user_id = u.id WHERE t.assigned_to = ? ORDER BY t.created_at DESC LIMIT 5");
            $stmt->execute([$staff_id]);
            $recent = $stmt->fetchAll();
            ?>
            <?php if (count($recent) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Title</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent as $ticket): ?>
                                <tr>
                                    <td><?php echo $ticket['id']; ?></td>
                                    <td><?php echo htmlspecialchars($ticket['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['title']); ?></td>
                                    <td><?php echo getPriorityBadge($ticket['priority']); ?></td>
                                    <td><?php echo getStatusBadge($ticket['status']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></td>
                                    <td><a href="view_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center mb-0">No tickets assigned to you yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>