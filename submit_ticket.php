<?php
$pageTitle = 'Submit Ticket - Waves Support';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../includes/functions.php';
require_once '../ai/ai_helper.php';

requireRole('customer');

$categories = getAllCategories($conn);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';

    if (empty($title) || empty($category_id) || empty($description)) {
        $error = 'Please fill in all required fields.';
    } else {
        // AI Processing
        $ai_category = classifyTicket($title . ' ' . $description);
        $sentiment = analyzeSentiment($title . ' ' . $description);
        $ai_priority = suggestPriority($sentiment, $title . ' ' . $description);

        // File Upload
        $attachment = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'];
            $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $filename = 'ticket_' . time() . '_' . uniqid() . '.' . $ext;
                $upload_path = '../uploads/' . $filename;
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_path)) {
                    $attachment = $filename;
                }
            } else {
                $error = 'Invalid file type. Allowed: jpg, png, gif, pdf, doc, docx, txt';
            }
        }

        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO tickets (user_id, title, description, category_id, ai_category, sentiment, priority, status, attachment) VALUES (?, ?, ?, ?, ?, ?, ?, 'open', ?)");
            $stmt->execute([getUserId(), $title, $description, $category_id, $ai_category, $sentiment, $ai_priority, $attachment]);
            $ticket_id = $conn->lastInsertId();

            addAuditLog($conn, getUserId(), 'Created ticket #' . $ticket_id, 'tickets', $ticket_id);
            $success = 'Ticket #' . $ticket_id . ' submitted successfully!<br>
                        <strong>AI Category:</strong> ' . $ai_category . ' | 
                        <strong>Sentiment:</strong> ' . ucfirst($sentiment) . ' | 
                        <strong>Suggested Priority:</strong> ' . ucfirst($ai_priority);
        }
    }
}
?>

<div class="container my-4">
    <h2 class="mb-4"><i class="fas fa-plus-circle me-2"></i>Submit New Ticket</h2>

    <?php if ($error): ?>
        <?php echo showAlert($error, 'danger'); ?>
    <?php endif; ?>
    <?php if ($success): ?>
        <?php echo showAlert($success, 'success'); ?>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body p-4">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Ticket Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Brief summary of your issue" required
                           value="<?php echo htmlspecialchars($title ?? ''); ?>">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo (($category_id ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                        <small class="text-muted">AI will suggest priority based on your description</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="6" placeholder="Describe your issue in detail..." required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Attachment (optional)</label>
                    <input type="file" name="attachment" class="form-control">
                    <small class="text-muted">Allowed: jpg, png, gif, pdf, doc, docx, txt</small>
                </div>
                <button type="submit" class="btn btn-primary py-2 px-4">
                    <i class="fas fa-paper-plane me-2"></i>Submit Ticket
                </button>
                <a href="dashboard.php" class="btn btn-secondary py-2 px-4 ms-2">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>