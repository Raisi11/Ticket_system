<?php
session_start();

require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="ticket_report_' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    fputcsv($output, [
        'Ticket ID',
        'Customer',
        'Title',
        'Category',
        'AI Category',
        'Sentiment',
        'Priority',
        'Status',
        'Assigned To',
        'Created',
        'Resolved'
    ]);

    $all = $conn->query("
        SELECT 
            t.*, 
            c.category_name, 
            u.name AS customer_name, 
            s.name AS staff_name 
        FROM tickets t 
        LEFT JOIN categories c ON t.category_id = c.id 
        LEFT JOIN users u ON t.user_id = u.id 
        LEFT JOIN users s ON t.assigned_to = s.id 
        ORDER BY t.created_at DESC
    ")->fetchAll();

    foreach ($all as $row) {
        fputcsv($output, [
            $row['id'],
            $row['customer_name'] ?? 'N/A',
            $row['title'] ?? 'N/A',
            $row['category_name'] ?? 'N/A',
            $row['ai_category'] ?? 'N/A',
            $row['sentiment'] ?? 'N/A',
            $row['priority'] ?? 'N/A',
            $row['status'] ?? 'N/A',
            $row['staff_name'] ?? 'Unassigned',
            $row['created_at'] ?? 'N/A',
            $row['resolved_at'] ?? 'N/A'
        ]);
    }

    fclose($output);
    exit();
}

$pageTitle = 'Reports - Waves Support';
require_once '../includes/header.php';
require_once '../includes/navbar.php';

/* =========================
   REPORT DATA
========================= */
$total_tickets = $conn->query("SELECT COUNT(*) as total FROM tickets")->fetch()['total'];

$resolved_tickets = getTicketCount($conn, "status IN ('resolved','closed')");
$resolved_ratio = $total_tickets > 0 ? round(($resolved_tickets / $total_tickets) * 100, 1) : 0;

$avg_res = $conn->query("
    SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours 
    FROM tickets 
    WHERE resolved_at IS NOT NULL
")->fetch();

$avg_hours = $avg_res['avg_hours'] ? round($avg_res['avg_hours'], 1) : 'N/A';

$top_cat = $conn->query("
    SELECT c.category_name, COUNT(t.id) as count 
    FROM tickets t 
    JOIN categories c ON t.category_id = c.id 
    GROUP BY t.category_id 
    ORDER BY count DESC 
    LIMIT 1
")->fetch();

$top_staff = $conn->query("
    SELECT u.name, COUNT(t.id) as count 
    FROM tickets t 
    JOIN users u ON t.assigned_to = u.id 
    GROUP BY t.assigned_to 
    ORDER BY count DESC 
    LIMIT 1
")->fetch();

$urgent_count = getTicketCount($conn, "priority = 'high' AND status NOT IN ('resolved','closed')");

$staff_perf = $conn->query("
    SELECT 
        u.name, 
        COUNT(t.id) as total, 
        SUM(CASE WHEN t.status IN ('resolved','closed') THEN 1 ELSE 0 END) as resolved,
        ROUND(AVG(CASE WHEN t.resolved_at IS NOT NULL 
            THEN TIMESTAMPDIFF(HOUR, t.created_at, t.resolved_at) END), 1) as avg_hours
    FROM users u 
    LEFT JOIN tickets t ON u.id = t.assigned_to 
    WHERE u.role = 'staff' 
    GROUP BY u.id, u.name 
    ORDER BY resolved DESC
")->fetchAll();

$cat_breakdown = $conn->query("
    SELECT 
        c.category_name, 
        COUNT(t.id) as count,
        SUM(CASE WHEN t.status IN ('resolved','closed') THEN 1 ELSE 0 END) as resolved
    FROM categories c 
    LEFT JOIN tickets t ON c.id = t.category_id 
    GROUP BY c.id, c.category_name 
    ORDER BY count DESC
")->fetchAll();
?>

<div class="container-fluid my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-bar me-2"></i>Reports & Analytics</h2>
        <a href="reports.php?export=csv" class="btn btn-success">
            <i class="fas fa-file-csv me-2"></i>Export CSV
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card dashboard-card bg-primary text-white">
                <h3><?php echo $total_tickets; ?></h3>
                <p class="mb-0">Total Tickets</p>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card dashboard-card bg-success text-white">
                <h3><?php echo $resolved_ratio; ?>%</h3>
                <p class="mb-0">Resolved Ratio</p>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card dashboard-card bg-info text-white">
                <h3><?php echo $avg_hours; ?></h3>
                <p class="mb-0">Avg Hours to Resolve</p>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card dashboard-card bg-warning text-dark">
                <h3><?php echo $top_cat['category_name'] ?? 'N/A'; ?></h3>
                <p class="mb-0">Top Category</p>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card dashboard-card bg-danger text-white">
                <h3><?php echo $urgent_count; ?></h3>
                <p class="mb-0">Urgent Open</p>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card dashboard-card bg-dark text-white">
                <h3><?php echo $top_staff['name'] ?? 'N/A'; ?></h3>
                <p class="mb-0">Most Active Staff</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Staff Performance</h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Staff</th>
                                    <th>Total</th>
                                    <th>Resolved</th>
                                    <th>Avg Hours</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($staff_perf as $sp): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sp['name']); ?></td>
                                        <td><?php echo $sp['total']; ?></td>
                                        <td><?php echo $sp['resolved']; ?></td>
                                        <td><?php echo $sp['avg_hours'] ?? 'N/A'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Category Breakdown</h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Total</th>
                                    <th>Resolved</th>
                                    <th>Rate</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($cat_breakdown as $cb): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cb['category_name']); ?></td>
                                        <td><?php echo $cb['count']; ?></td>
                                        <td><?php echo $cb['resolved']; ?></td>
                                        <td>
                                            <?php 
                                            echo $cb['count'] > 0 
                                                ? round(($cb['resolved'] / $cb['count']) * 100, 1) . '%' 
                                                : 'N/A'; 
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>

                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>