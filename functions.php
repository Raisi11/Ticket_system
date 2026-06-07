<?php
require_once __DIR__ . '/db.php';

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function getStatusBadge($status) {
    $label = ucfirst(str_replace('_', ' ', $status));
    return '<span class="badge badge-' . $status . '">' . $label . '</span>';
}

function getPriorityBadge($priority) {
    $label = ucfirst($priority);
    return '<span class="badge badge-' . $priority . '">' . $label . '</span>';
}

function getSentimentBadge($sentiment) {
    $label = ucfirst($sentiment);
    return '<span class="badge badge-' . $sentiment . '">' . $label . '</span>';
}

function getCategoryName($conn, $category_id) {
    $stmt = $conn->prepare("SELECT category_name FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $result = $stmt->fetch();
    return $result ? $result['category_name'] : 'Unknown';
}

function getUserById($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function getTicketCount($conn, $condition = "1=1", $params = []) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM tickets WHERE $condition");
    $stmt->execute($params);
    return $stmt->fetch()['total'];
}

function getAllCategories($conn) {
    $stmt = $conn->query("SELECT * FROM categories ORDER BY category_name");
    return $stmt->fetchAll();
}

function getAllStaff($conn) {
    $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE role = 'staff' AND status = 'active'");
    $stmt->execute();
    return $stmt->fetchAll();
}

function addNotification($conn, $user_id, $ticket_id, $message) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, ticket_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $ticket_id, $message]);
}

function addAuditLog($conn, $user_id, $action, $table_name = null, $record_id = null) {
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $table_name, $record_id]);
}

function showAlert($message, $type = 'success') {
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}
?>